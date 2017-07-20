<?php
  /*
  *
  * @package    cryptofyer
  * @class    BittrexxApi
  * @author     Fransjo Leihitu
  * @version    0.4
  *
  * API Documentation : https://bittrex.com/home/api
  */
  class BittrexxApi extends CryptoExchange {

    private $exchangeUrl  = "https://bittrex.com/api/";
    private $apiVersion   = "1.1";

    private $_version_major  = "0";
    private $_version_minor  = "4";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "v" . $this->apiVersion . "/");
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return array("status" => false , "error" => "method was not defined!");

      if($secure) $args["apikey"] = $this->apiKey;
      $args["nonce"] = time();

      $urlParams  = array();
      foreach($args as $key => $val) {
        $urlParams[]  = $key . "=" . $val;
      }

      $uri  = $this->getBaseUrl() . $method;

      $argsString = join("&" , $urlParams);
      if(!empty($urlParams)) {
          $uri  = $uri . "?" . $argsString;
      }

      $sign = $secure == true ? hash_hmac('sha512',$uri,$this->apiSecret) : null;

      $uri = trim(preg_replace('/\s+/', '', $uri));

      $ch = curl_init($uri);
      if($secure) curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $execResult = curl_exec($ch);

      if(curl_error($ch))
      {
          return $this->getErrorReturn(curl_error($ch));
      }

      $obj = json_decode($execResult , true);

      if($obj["success"] == true) {
        return $this->getReturn($obj["success"],$obj["message"],$obj["result"]);
      } else {
        return $this->getErrorReturn($obj["message"]);
      }

    }

    /* ------ BEGIN public api methodes ------ */
    public function getMarkets($args = null) {
      return $this->send("public/getmarkets" , $args , false);
    }

    public function getCurrencies($args = null){
      return $this->send("public/getcurrencies" , $args , false);
    }

    public function getTicker($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      return $this->send("public/getticker" , $args, false);
    }

    public function getMarketSummary($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      return $this->send("public/getmarketsummary" , $args , false);
    }

    public function getOrderbook($args = null) {
      /*
        optional : depth
      */
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      if(!isSet($args["type"])) return array("status" => false , "error" => "required parameter: type");
      return $this->send("public/getmarketsummary" , $args , false);
    }

    public function getMarketHistory($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      return $this->send("public/getmarkethistory" , $args , false);
    }

    public function getMarketSummaries() {
      return $this->send("public/getmarketsummaries" , $args , false);
    }
    /* ------END public api methodes ------ */


    /* ------ BEGIN market api methodes ------ */
    public function buy($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      if(!isSet($args["quantity"])) return array("status" => false , "error" => "required parameter: quantity");
      if(!isSet($args["rate"])) return array("status" => false , "error" => "required parameter: rate");
      return $this->send("market/buylimit" , $args);
    }

    public function sell($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      if(!isSet($args["quantity"])) return array("status" => false , "error" => "required parameter: quantity");
      if(!isSet($args["rate"])) return array("status" => false , "error" => "required parameter: rate");
      return $this->send("market/selllimit" , $args);
    }

    public function cancel($args = null) {
      if(!isSet($args["uuid"])) return array("status" => false , "error" => "required parameter: uuid");
      return $this->send("market/cancel" , $args);
    }

    public function getOrders($args = null) {
      return $this->send("market/getopenorders" , $args);
    }
    /* ------ END market api methodes ------ */


    /* ------ BEGIN account api methodes ------ */


    public function getBalances($args = null) {
      return $this->send("account/getbalances" , $args);
    }

    public function getBalance($args = null) {
      if(!isSet($args["currency"])) return array("status" => false , "error" => "required parameter: currency");
      return $this->send("account/getbalance" , $args);
    }

    public function getDepositAddress($args = null) {
      if(!isSet($args["currency"])) return array("status" => false , "error" => "required parameter: currency");
      return $this->send("account/getdepositaddress" , $args);
    }


    public function withdraw($args = null) {
      /*
        optional : address
      */

      if(!isSet($args["currency"])) return array("status" => false , "error" => "required parameter: currency");
      if(!isSet($args["quantity"])) return array("status" => false , "error" => "required parameter: quantity");
      if(!isSet($args["address"])) return array("status" => false , "error" => "required parameter: address");

      return $this->send("account/withdraw" , $args);
    }

    public function getOrder($args = null) {
      if(!isSet($args["uuid"])) return array("status" => false , "error" => "required parameter: uuid");
      return $this->send("account/getorder" , $args);
    }

    public function getOrderHistory($args = null) {
      if(!isSet($args["market"])) return array("status" => false , "error" => "required parameter: market");
      return $this->send("account/getorderhistory" , $args);
    }

    public function getWithdrawalHistory($args = null) {
      if(!isSet($args["currency"])) return array("status" => false , "error" => "required parameter: currency");
      return $this->send("account/getwithdrawalhistory" , $args);
    }

    public function getDepositHistory($args = null) {
      if(!isSet($args["currency"])) return array("status" => false , "error" => "required parameter: currency");
      return $this->send("account/getdeposithistory" , $args);
    }

    /* ------ END account api methodes ------ */

  }
?>
