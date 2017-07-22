<?php
  include("cryptoexchange.interface.php");
  /*
  *
  * @package    cryptofyer
  * @class CryptoExchange
  * @author     Fransjo Leihitu
  * @version    0.2
  *
  */
  class CryptoExchange {

    private $apiKey		    = null;
    private $apiSecret    = null;
    private $baseUrl      = null;
    private $exchangeUrl   = null;

    private $version_major  = "0";
    private $version_minor  = "2";
    private $version  = "";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;
    }

    public function setVersion($major = "0" , $minor = "0") {
      $this->version_major  = $major;
      $this->version_minor = $minor;
      $this->version  = $major . "." . $minor;
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($market . "-" . $currency);
    }

    public function getVersion() {
      return $this->version;
    }

    public function setBaseUrl($url=null) {
      $this->baseUrl = $url;
    }

    public function getBaseUrl() {
      return $this->baseUrl;
    }

    public function getErrorReturn($message = null ) {
      return array(
          "success" => false,
          "message" => $message
      );
    }

    public function getReturn($success = null , $message = null , $result = null) {
      return array(
          "success" => $success,
          "message" => $message,
          "result"    => $result
      );
    }

    public function getTicker($args  = null){
      return $this->getErrorReturn("please implement getTicker()");
    }

    public function getBalance($args  = null){
      return $this->getErrorReturn("please implement getBalance()");
    }

    public function buy($args = null) {
      return $this->getErrorReturn("please implement buy()");
    }

    public function sell($args = null) {
      return $this->getErrorReturn("please implement sell()");
    }

    public function getOrders($args = null) {
      return $this->getErrorReturn("please implement orders()");
    }


  }
?>
