<?php
  /*
  *
  * @package    cryptofyer
  * @class    KrakenApi
  * @author     Fransjo Leihitu
  * @version    0.3
  *
  * API Documentation : https://www.kraken.com/help/api
  */
  class KrakenApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://api.kraken.com/";
    private $apiVersion   = "0";

    // base url for currency
    private $currencyUrl  = "";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "3";

    private $_currencies  = array();

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . $this->apiVersion . "/");

        $this->getCurrencies();
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) $this->getErrorReturn("Method was not defined!");

      // build the POST data string
      $postdata = "";
      if(!empty($args)) $postdata = http_build_query($args, '', '&');

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
        return $this->getErrorReturn("Private curl is not ready yet!");
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
      $currency = str_replace("BTC" , "XBT" , $currency);

      $currency = strtoupper($currency);
      $this->getCurrencies();

      $currency = isSet($this->_currencies[$currency]) ? $this->_currencies[$currency] : "";
      if(!empty($currency)) $currency = $currency["_currency"];

      $market   = isSet($this->_currencies[$market]) ? $this->_currencies[$market] : "";
      if(!empty($market)) $market = $market["_currency"];

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

    public function getCurrencies($args = null) {
      if(!empty($this->_currencies)) return $this->getReturn(true,null,$this->_currencies);
      $returnOBJ = $this->send("public/Assets" , $args , false);

      if($returnOBJ["success"] == true) {

        foreach($returnOBJ["result"] as $key=>$value) {
          $value["_currency"] = $key;
          $this->_currencies[$value["altname"]] = $value;
        }

        $returnOBJ["result"]  = $this->_currencies;
      }

      return $returnOBJ;
    }

    public function getAssetPairs($args = null) {
      return $this->send("public/AssetPairs" , $args , false);
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
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["pair"] = $args["market"];
      unset($args["market"]);

      return $this->send("public/Trades" , $args , false);
    }

    // Get spread
    public function getMarketSpread($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["pair"] = $args["market"];
      unset($args["market"]);

      return $this->send("public/Spread" , $args , false);
    }

    public function getOrderbook($args = null) {
      /*
        optional : depth
      */
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["pair"] = $args["market"];
      unset($args["market"]);

      if(isSet($args["depth"])) {
          $args["count"]  = $args["depth"];
          unset($args["depth"]);
      }

      return $this->send("public/Depth" , $args , false);
    }

  }
?>
