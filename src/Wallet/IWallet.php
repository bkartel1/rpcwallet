<?php

namespace Vith27\RPCWallet\Wallet;


interface IWallet {
  
  public function debug();
  
  public function getinfo();
}