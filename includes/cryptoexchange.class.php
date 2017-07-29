<?php
  include("cryptoexchange.interface.php");
  /*
  *
  * @package    cryptofyer
  * @class CryptoExchange
  * @author     Fransjo Leihitu
  * @version    0.4
  *
  */
  class CryptoExchange {

    private $apiKey		    = null;
    private $apiSecret    = null;
    private $baseUrl      = null;
    private $exchangeUrl   = null;

    private $version_major  = "0";
    private $version_minor  = "4";
    private $version  = "";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;
    }

    private function send($method = null , $args = array() , $secure = true) {
      $this->getErrorReturn("please implement the send() function");
    }

    public function withdraw($args = null) {
      $this->getErrorReturn("please implement the withdraw() function");
    }

    public function transfer($args = null) {
      return $this->getErrorReturn("please implement the transfer() function");
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


  }
?>
