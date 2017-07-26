<?php
  /*
  *
  * @package    cryptofyer
  * @class CryptoExchangeInterface
  * @author     Fransjo Leihitu
  * @version    0.5
  *
  */
  interface CryptoExchangeInterface {

    // get ticket information
    public function getTicker($args  = null);

    // get balance
    public function getBalance($args  = null);

    // place buy order
    public function buy($args = null);

    // place sell order
    public function sell($args = null);

    // get open orders
    public function getOrders($args = null) ;

    // get order
    public function getOrder($args = null) ;

    // Get the exchange currency detail url
    public function getCurrencyUrl($args = null);

    // Get market history
    public function getMarketHistory($args = null);
  }
?>
