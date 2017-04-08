<?php

namespace Coogle;

use GuzzleHttp\Client;

class SmappeeLocal
{
    private $_httpClient;
    private $_smappeeIp;
    private $_password;
    
    public function setPassword($p)
    {
        $this->_password = (string)$p;
        return $this;
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function setSmappeeHost($ip)
    {
        $this->_smappeeIp = $ip;
        return $this;
    }
    
    public function getSmappeeHost()
    {
        return $this->_smappeeIp;
    }
    
    public function setHttpClient(\GuzzleHttp\Client $client)
    {
        $this->_httpClient = $client;
        return $this;
    }
    
    public function getHttpClient()
    {
        return $this->_httpClient;
    }
    
    public function __construct($smappee_ip, $password)
    {
        $this->setHttpClient(new Client())
             ->setSmappeeHost($smappee_ip)
             ->setPassword($password);
    }

    public function getInstantaneous()
    {
        $client = $this->getHttpClient();
        
        $host = $this->getSmappeeHost();
        $password = $this->getPassword();
        
        if(empty($host) || empty($password)) {
            throw new \Exception("You must set the local Smappee device address and the password to access it.");
        }
        
        $loginEndpoint = "http://{$host}/gateway/apipublic/logon";
        $endPoint = "http://{$host}/gateway/apipublic/instantaneous";
        
        $result = $client->request('POST', $loginEndpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $password
        ]);
        
        $result = $client->request('POST', $endPoint, [
           'headers' => [
               'Content-Type' => 'application/json'
           ],
           'body' => 'loadInstantaneous'
        ]);
        
        $data = json_decode($result->getBody(), true);
        
        $retval = [];

        foreach($data as $item) {
            if(isset($item['key']) && isset($item['value'])) {
                $retval[$item['key']] = $item['value'];
            }
        }
        
        return $retval;
    }    
}

