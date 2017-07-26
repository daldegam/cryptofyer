<?php
  /*
  *
  * @package    cryptofyer
  * @class    CoinexchangeApi
  * @author     Fransjo Leihitu
  * @version    0.2
  *
  * API Documentation : http://coinexchangeio.github.io/slate/
  */
  class CoinexchangeApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://www.coinexchange.io/api/";
    private $apiVersion   = "1";

    // base url for currency
    private $currencyUrl  = "https://www.coinexchange.io/market/";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "1";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "v" . $this->apiVersion . "/");
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "/" . $market);
    }

    // get ticket information
    public function getTicker($args  = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // get balance
    public function getBalance($args  = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // place buy order
    public function buy($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // place sell order
    public function sell($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // get open orders
    public function getOrders($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // get order
    public function getOrder($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // Get the exchange currency detail url
    public function getCurrencyUrl($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    // Get market history
    public function getMarketHistory($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }


  }
?>
