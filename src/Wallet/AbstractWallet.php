<?php

namespace Vith27\RPCWallet\Wallet;


use Vith27\RPCWallet\RPC\RPCBlockChain;

abstract class AbstractWallet implements IWallet {

  protected $rpcUser;
  protected $rpcPassword;
  protected $host;
  protected $port;
  protected $url;
  protected $CACertificate;
  
  protected $rpcBC = NULL;
  
  public function __construct($user, $password, $host, $port, $url = '', $ca_certificate = NULL) {
    
    $this->rpcUser = $user;
    $this->rpcPassword = $password;
    $this->host = $host;
    $this->port = $port;
    $this->url = $url;
    $this->CACertificate = $ca_certificate;
    
    $this->rpcBC = new RPCBlockChain($this->rpcUser, $this->rpcPassword, $this->host, $this->port);
    if (isset($this->CACertificate) && !empty($this->CACertificate)) {
      $this->rpcBC->setSSL($this->CACertificate);
    }
  }
  
  /**
   * {@inheritDoc}
   * @see \Vith27\RPCWallet\wallet\IWallet::debug()
   */
  public function debug() {
    $this->rpcBC->masternode('debug');
  }
  
  /**
   * {@inheritDoc}
   * @see \Vith27\RPCWallet\wallet\IWallet::getinfo()
   */
  public function getinfo() {
    $this->rpcBC->getinfo();
  }

}