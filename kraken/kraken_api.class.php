<?php
  /*
  *
  * @package    cryptofyer
  * @class    KrakenApi
  * @author     Fransjo Leihitu
  * @version    0.1
  *
  * API Documentation :
  */
  class KrakenApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.kraken.com/";
    private $apiVersion   = "0";

    // base url for currency
    private $currencyUrl  = "";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "1";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . $this->apiVersion . "/");
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return array("status" => false , "error" => "method was not defined!");

      // build the POST data string
      $postdata = http_build_query($args, '', '&');

      $uri  = $this->getBaseUrl();
      $result = null;

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // make request
      if($secure == false) {
        $uri  = $uri . $method;
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        $result = curl_exec($ch);
      } else {
        // TODO !!!
      }

      if($result===false) {
        return $this->getErrorReturn(curl_error($ch));
      } else {
        $obj = json_decode($result , true);
        if(!is_array($obj)) {
          return $this->getErrorReturn("JSON decode error");
        }

        if(!empty($obj["error"])) {
          return $this->getErrorReturn($obj["error"]);
        } else {
          return $this->getReturn(true,null,$obj["result"]);
        }

      }

      return $this->getErrorReturn("ERROR sending");
    }

    public function getMarketPair($market = "" , $currency = "") {
      $market = str_replace("BTC" , "XBT" , $market);
      return strtoupper($currency . "" . $market);
    }

    // get ticket information
    public function getTicker($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }

      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["pair"] = $args["market"];

      unset($args["market"]);

      return $this->send("public/Ticker" , $args , false);
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
