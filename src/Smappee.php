<?php

namespace Coogle;

use GuzzleHttp\Client;

class Smappee 
{
    const SMAPPEE_OAUTH_TOKEN_URL = 'https://app1pub.smappee.net/dev/v1/oauth2/token';
    
    const AGGREGATION_5MINS = 1;
    const AGGREGATION_HOURLY = 2;
    const AGGREGATION_DAILY = 3;
    const AGGREGATION_MONTHLY = 4;
    const AGGREGATION_QUARTERLY = 5;
    
    private $_clientId;
    private $_clientSecret;
    private $_username;
    private $_password;
    private $_refreshToken;
    private $_accessToken;
    private $_httpClient;
    
    public function setHttpClient(\GuzzleHttp\Client $client)
    {
        $this->_httpClient = $client;
        return $this;
    }
    
    public function getHttpClient()
    {
        return $this->_httpClient;
    }
    
    public function getClientId()
    {
        return $this->_clientId;
    }
    
    public function setClientId($id)
    {
        $this->_clientId = (string)$id;
        return $this;
    }
    
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }
    
    public function setClientSecret($s)
    {
        $this->_clientSecret = (string)$s;
        return $this;
    }
    
    public function getUsername()
    {
        return $this->_username;
    }
    
    public function setUsername($u)
    {
        $this->_username = (string)$u;
        return $this; 
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function setPassword($p)
    {
        $this->_password = (string)$p;
        return $this;
    }
    
    public function setRefreshToken($t)
    {
        $this->_refreshToken = (string)$t;
        return $this;
    }
    
    public function getRefreshToken()
    {
        return $this->_refreshToken;
    }
    
    public function __construct($client_id, $client_secret, $username = null, $password = null)
    {
        $this->setClientId($client_id)
             ->setClientSecret($client_secret);
        
        if(!is_null($username)) {
            $this->setUsername($username);
        }
        
        if(!is_null($password)) {
            $this->setPassword($password);
        }
        
        $this->setHttpClient(new Client());
    }
    
    
    public function authenticate()
    {
        $client = $this->getHttpClient();
        
        $refreshToken = $this->getRefreshToken();
        
        if(is_null($refreshToken)) {
            $result = $client->request('POST', self::SMAPPEE_OAUTH_TOKEN_URL, [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'username' => $this->getUsername(),
                    'password' => $this->getPassword()
                ]
            ]);
            
            switch($result->getStatusCode()) {
                case 200:
                    $result = json_decode($result->getBody());
                    
                    $this->_accessToken = $result->access_token;
                    $this->setRefreshToken($result->refresh_token);
                    
                    break;
                default:
                    throw new \Exception("Failed to authenticate against Smappee oAuth Server");
            }
        } else {
            // @todo use refresh token if provided
        }
        
        if(is_null($this->_accessToken)) {
            throw new \Exception("Failed to retrieve Smappee oAuth Access Token");
        }
        
        return $this;
    }
    
    public function getServiceLocations()
    {
        $client = $this->getHttpClient();
        
        $result = $client->request('GET', 'https://app1pub.smappee.net/dev/v1/servicelocation', [
            'headers' => [
                'Authorization' => "Bearer {$this->_accessToken}"
            ]
        ]);
        
        $result = json_decode($result->getBody(), true);
        
        if(isset($result['serviceLocations'])) {
            return $result['serviceLocations'];
        }
        
        throw new \Exception("Request Failed");
    }
    
    public function getServiceLocationInfo($serviceLocationId)
    {
        
        $serviceLocationId = (int)$serviceLocationId;
        
        $client = $this->getHttpClient();
        
        $result = $client->request("GET", "https://app1pub.smappee.net/dev/v1/servicelocation/$serviceLocationId/info", [
            'headers' => [
                'Authorization' => "Bearer {$this->_accessToken}"
            ]
        ]);
        
        $result = json_decode($result->getBody(), true);
        
        if(!isset($result['name'])) {
            throw new \Exception("Request Failed");
        }
        
        return $result;
    }
    
    public function getConsumption($serviceLocationId, \DateTime $from, \DateTime $to, $aggregation = self::AGGREGATION_5MIN)
    {
        $serviceLocationId = (int)$serviceLocationId;
        
        $client = $this->getHttpClient();
        
        $from->setTimezone(new \DateTimeZone('UTC'));
        $to->setTimezone(new \DateTimeZone('UTC'));
        
        $result = $client->request('GET', "https://app1pub.smappee.net/dev/v1/servicelocation/$serviceLocationId/consumption", [
            'headers' => [
                'Authorization' => "Bearer {$this->_accessToken}"
            ],
            'query' => [
                 'from ' => $from->getTimestamp() * 1000,
                 'to' => $to->getTimestamp() * 1000,
                 'aggregation' => $aggregation
            ],
        ]);
        
        $result = json_decode($result->getBody(), true);
        
        if(!isset($result['consumptions'])) {
            throw new \Exception("Request Failed");
        }
        
        return $result['consumptions'];
    }
    
    public function getConsumptionNow($serviceLocationId)
    {
        $from = new \DateTime('-5 minutes');
        $to = new \DateTime('now');
        
        return $this->getConsumption($serviceLocationId, $from, $to, self::AGGREGATION_5MINS);
    }
    
}
