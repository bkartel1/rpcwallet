<?php

namespace Vith27\RPCWallet;

class RPCWalletFactory {
  
  public static function create($symbol, $user, $password, $host, $port, $url='', $ca_certificate = NULL) {
    
    $class = "Vith27\\RPCWallet\\Wallet\\" . strtoupper($symbol) . 'Wallet';
    $wallet = new $class($user, $password, $host, $port, $url, $ca_certificate);
    return $wallet;
  }
}