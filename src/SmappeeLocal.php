<?php

namespace Coogle;

use GuzzleHttp\Client;

class SmappeeLocal
{
    private $_httpClient;
    private $_smappeeIp;
    
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
    
    public function __construct($smappee_ip)
    {
        $this->setHttpClient(new Client())
             ->setSmappeeHost($ip);
    }
    
    public function getInstantaneous()
    {
        $client = $this->getHttpClient();
        
        $endPoint = "http://{$this->getSmappeeHost()}/gateway/apipublic/instantaneous";
        
        $result = $client->request('POST', $endpoint, [
           'headers' => [
               'Content-Type' => 'application/json'
           ],
           'body' => 'loadInstantaneous'
        ]);
        
        return $result;
    }
}