<?php
/*
*
* @package    cryptofyer
* @class CryptoExchangeInterface
* @author     Fransjo Leihitu
* @version    0.1
*
*/
interface CryptoExchangeInterface {

  public function getTicker($args  = null);
  public function getBalance($args  = null);
  public function buy($args = null);
  public function sell($args = null);
  public function getOrders($args = null) ;
  public function getCurrencyUrl($args = null);
  
}
 ?>
