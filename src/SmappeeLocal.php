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
    
    protected function _postCall($uri, $body)
    {
	$client = $this->getHttpClient();
        $host = $this->getSmappeeHost();
        
        if(empty($host)) {
            throw new \Exception("You must set the local Smappee device address to access it.");
        }
        
        $url = "http://{$host}".$uri;

        $result = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $body
        ]);
        
        return $result;

    }

    public function login() 
    {
	$password = $this->getPassword();
	    
	if(empty($password)) {
            throw new \Exception("You must set the Smappee password to login");
	}
	    
	$result = $this->_postCall('/gateway/apipublic/logon', $password);
    }
    
    public function getInstantaneous()
    {            
        $result = $this->_postCall('/gateway/apipublic/instantaneous', 'loadInstantaneous');
        
        $data = json_decode($result->getBody(), true);
        
        $retval = [];

        foreach($data as $item) {
            if(isset($item['key']) && isset($item['value'])) {
                $retval[$item['key']] = $item['value'];
            }
        }
        
        return $retval;
    }
    
    public function listComfortPlugs() 
    {
        $result = $this->_postCall('/gateway/apipublic/commandControlPublic', 'load');

        $data = json_decode($result->getBody(), true);
        
        $retval = [];

        foreach($data as $item) {
            if(isset($item['key']) && isset($item['value'])) {
                $retval[$item['key']] = $item['value'];
            }
        }
        
        return $retval;
    }
	
    public function setComfortPlug($plug_id, $plug_status) 
    {
	$body = 'control,controlId=' . urlencode($plug_id) . '|' . (int)(bool)$plug_status;
	
	$result = $this->_postCall('/gateway/apipublic/commandControlPublic', $body);

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

