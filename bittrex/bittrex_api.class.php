<?php
  /*
  *
  * @package    cryptofyer
  * @class    BittrexApi
  * @author     Fransjo Leihitu
  * @version    0.14
  *
  * API Documentation : https://bittrex.com/home/api
  */
  class BittrexApi extends CryptoExchange implements CryptoExchangeInterface {

    // base exchange api url
    private $exchangeUrl  = "https://bittrex.com/api/";
    private $apiVersion   = "1.1";

    // base url for currency
    private $currencyUrl  = "https://www.bittrex.com/Market/Index?MarketName=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "14";

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

      // check if there's a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // try to convert json repsonse to assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj["success"] == true) {
          return $this->getReturn($obj["success"],$obj["message"],$obj["result"]);
        } else {
          return $this->getErrorReturn($obj["message"]);
        }
      } else {
          return $this->getErrorReturn($execResult);
      }

    }

    /* ------ BEGIN public api methodes ------ */
    public function getMarkets($args = null) {
      return $this->send("public/getmarkets" , $args , false);
    }

    public function getCurrencies($args = null){
      return $this->send("public/getcurrencies" , $args , false);
    }

    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      return $this->currencyUrl . $args["market"];
    }

    public function getTicker($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $resultOBJ  = $this->send("public/getmarketsummary" , $args, false);
      if($resultOBJ["success"]) {
        $result = $resultOBJ["result"];
        return $this->getReturn($resultOBJ["success"],$resultOBJ["message"],$result[0]);
      } else {
        return $resultOBJ;
      }
    }

    public function getMarketSummary($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      return $this->send("public/getmarketsummary" , $args , false);
    }

    public function getOrderbook($args = null) {
      /*
        optional : depth
      */
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      if(!isSet($args["type"])) $args["type"] = "both";

      return $this->send("public/getorderbook" , $args , false);
    }

    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      return $this->send("public/getmarkethistory" , $args , false);
    }

    public function getMarketSummaries() {
      return $this->send("public/getmarketsummaries" , $args , false);
    }
    /* ------END public api methodes ------ */


    /* ------ BEGIN market api methodes ------ */
    public function buy($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");

      if($result = $this->send("market/buylimit" , $args)) {
        if($result["success"] == true) {
          $order  = $result["result"];
          if(isSet($order["uuid"])) {
            $order["orderid"] = $order["uuid"];
          }
          $result["result"] = $order;
          return $result;
        } else {
          return $result;
        }
      } else {
        $this->getErrorReturn("api error");
      }
    }

    public function sell($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      if($result = $this->send("market/selllimit" , $args)) {
        if($result["success"] == true) {
          $order  = $result["result"];
          if(isSet($order["uuid"])) {
            $order["orderid"] = $order["uuid"];
          }
          $result["result"] = $order;
          return $result;
        } else {
          return $result;
        }
      } else {
        $this->getErrorReturn("api error");
      }
    }

    public function cancel($args = null) {
      if(!isSet($args["orderid"])) return $this->getErrorReturn("required parameter: orderid");
      $args["uuid"] = $args["orderid"];
      unset($args["orderid"]);
      return $this->send("market/cancel" , $args);
    }

    public function getOrders($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      $result = $this->send("market/getopenorders" , $args);
      if($result["success"] == true) {
        $items  = $result["result"];
        $newItems = array();
        foreach($items as $item) {
          $item['orderid']  = $item['OrderUuid'];
          $newItems[] = $item;
        }
        $result["result"] = $newItems;
        return $result;
      } else {
        $this->getErrorReturn("API error");
      }
    }
    /* ------ END market api methodes ------ */


    /* ------ BEGIN account api methodes ------ */


    public function getBalances($args = null) {
      return $this->send("account/getbalances" , $args);
    }

    public function getBalance($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("account/getbalance" , $args);
    }

    public function getDepositAddress($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("account/getdepositaddress" , $args);
    }


    public function withdraw($args = null) {
      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["quantity"] = $args["amount"];
      unset($args["amount"]);

      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      if(!isSet($args["address"])) return $this->getErrorReturn("required parameter: address");

      return $this->send("account/withdraw" , $args);
    }

    public function getOrder($args = null) {
      if(!isSet($args["orderid"])) return $this->getErrorReturn("required parameter: orderid");
      $args["uuid"] = $args["orderid"];
      unset($args["orderid"]);
      $resultOBJ  = $this->send("account/getorder" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["orderid"]  = $result["OrderUuid"];
        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      } else {
        return $resultOBJ;
      }
    }

    public function getOrderHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      return $this->send("account/getorderhistory" , $args);
    }

    public function getWithdrawalHistory($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("account/getwithdrawalhistory" , $args);
    }

    public function getDepositHistory($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("account/getdeposithistory" , $args);
    }

    /* ------ END account api methodes ------ */

  }
?>
