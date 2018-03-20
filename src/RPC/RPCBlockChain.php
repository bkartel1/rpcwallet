<?php

namespace Vith27\RPCWallet\RPC;

use function GuzzleHttp\json_encode;

class RPCBlockChain {
  
  private $username;
  private $password;
  private $proto;
  private $host;
  private $port;
  private $url;
  private $CACertificate;
  
  private $status;
  public function getStatus() {
    return $this->status;
  }
  private $error;
  public function getError() {
    return $this->error;
  }
  private $raw_response;
  public function getRawResponse() {
    return $this->raw_response;
  }
  private $response;
  public function getResponse() {
    return $this->response;
  }
  
  private $id = 0;
  
  public function __construct($username, $password, $host = 'localhost', $port = 8888, $url = NULL) {
    $this->username = $username;
    $this->password = $password;
    $this->host = $host;
    $this->port = $port;
    $this->url = $url;
    
    $this->proto = 'http';
    $this->CACertificate = NULL;
  }
  
  public function setSSL($certificate = NULL) {
    $this->proto = 'https';
    $this->CACertificate = $certificate;
  }
  
  public function __call($method, $params) {
    $this->status = NULL;
    $this->error = NULL;
    $this->raw_response = NULL;
    $this->response = NULL;
    
    $params = array_values($params);
    
    $this->id++;
    
    $request = json_encode(array(
      'method' => $method,
      'params' => $params,
      'id' => $this->id,
    ));
    
    $curl = curl_init("{$this->proto}://{$this->host}:{$this->port}/{$this->url}");
    $options = array(
      CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
      CURLOPT_USERPWD        => $this->username . ':' . $this->password,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $request,
    );
    
    if (ini_get('open_basedir')) {
      unset($options[CURL_FOLLOWLOCATION]);
    }
    
    if ($this->proto == 'https') {
      if (!empty($this->CACertificate)) {
        $options[CURLOPT_CAINFO] = $this->CACertificate;
        $options[CURLOPT_CAPATH] = dirname($this->CACertificate);
      }
      else {
        $options[CURLOPT_SSL_VERIFYPEER] = FALSE;
      }
      
      curl_setopt_array($curl, $options);
      
      $this->raw_response = curl_exec($curl);
      $this->response = json_decode($this->raw_response, TRUE);
      
      $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      
      $curl_error = curl_error($curl);
      curl_close($curl);
      
      if (!empty($curl_error)) {
        $this->error = $curl_error;
      }
      if ($this->response['error']) {
        $this->error = $this->response['error']['message'];
      }
      else if ($this->status != 200) {
        switch ($this->status) {
          case 400 : 
            $this->error = 'HTTP_BAD_REQUEST';
            break;
          case 401 : 
            $this->error = 'HTTP_UNAUTHORIZED';
            break;
          case 403 :
            $this->error = 'HTTP_FORBIDDEN';
            break;
          case 404 :
            $this->error = 'HTTP_NOT_FOUND';
            break;
          default : 
            $this->error = 'NOT_KNOWN_ERROR ('.$this->status.')';
            break;
        }
      }
      
      if ($this->error) {
        return FALSE;
      }
      return $this->response['result'];
    }
  }
}