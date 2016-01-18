<?php

use Hideks\Auth\Adapter\AdapterAbstract;

class Serviceapi extends AdapterAbstract
{
    private $userAPI     = null;
    private $passwordAPI = null;
    private $returnToAPI = null;
    private $response    = null;
    private $token       = null;
    private $password    = null;
    
    public function __construct($token, $password) {
        $this->token = $token;
        $this->password = $password;
    }
    public function getUserAPI() {
        return $this->userAPI;
    }

    public function getPasswordAPI() {
        return $this->passwordAPI;
    }

    public function getReturnToAPI() {
        return $this->returnToAPI;
    }

    public function getResponse() {
        return $this->response;
    }

    public function setUserAPI($userAPI) {
        $this->userAPI = $userAPI;
    }

    public function setPasswordAPI($passwordAPI) {
        $this->passwordAPI = $passwordAPI;
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
        $json = array();
        // Fixar os valores de token e de password
        $userData = array(
            'email'     => $this->userAPI,
            'password'  => $this->passwordAPI,
            'return_to' => $this->returnToAPI
        );
        $json = $this->api_connect($this->token, $this->password, $userData);
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
