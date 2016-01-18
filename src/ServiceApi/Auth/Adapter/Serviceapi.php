<?php

namespace ServiceApi\Auth\Adapter;

use Hideks\Auth\Adapter\AdapterAbstract;

class Serviceapi extends AdapterAbstract
{
    private $passwordAPI = null;
    private $returnToAPI = null;
    private $tokenAPI    = null;
    
    public function __construct($token, $password) {
        $this->tokenAPI     = $token;
        $this->passwordAPI  = $password;
    }
    public function getPasswordAPI() {
        return $this->passwordAPI;
    }

    public function getReturnToAPI() {
        return $this->returnToAPI;
    }

    public function setReturnToAPI($returnToAPI) {
        $this->returnToAPI = $returnToAPI;
    }

    /**
     * Metodo para validação da api de usuario
     * @param array $data
     * @return type
     */
    public function autenticate() {
        if( !function_exists("curl_init") ){
            throw new \Exception("CURL is not installed or activated on this server!!");
        }                
        
        // Fixar os valores de token e de password
        $userData = array(
            'email'     => $this->username,
            'password'  => $this->password,
            'return_to' => $this->returnToAPI
        );
        $json = $this->api_connect($this->tokenAPI, $this->passwordAPI, $userData);
        // Lógica para criar a sessão do usuário aqui usando o json de retorno
        return json_decode($json);
        // Colocar a resposta na variavel response            
    }

    /**
     * Metodo que faz a conexao com a api de usuario
     * @param type $token
     * @param type $password
     * @param array $data
     * @return type
     */
    private function api_connect($token, $password, array $data) {
        $ch = curl_init("http://services.brasileirinhas.com.br/api/1.0/auth/");
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'token' => $token,
            'data' => openssl_encrypt(json_encode(array(
                'expires' => time() + 60,
                'email' => $data['email'],
                'password' => $data['password'],
                'remote_address' => $_SERVER['REMOTE_ADDR']
                    )), 'AES-256-CBC', $password, 0, substr($token, 16))
        ));
        $json = curl_exec($ch);
        curl_close($ch);
        return $json;
    }
}
