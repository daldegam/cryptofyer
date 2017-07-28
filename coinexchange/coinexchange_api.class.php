<?php
  /*
  *
  * @package    cryptofyer
  * @class    CoinexchangeApi
  * @author     Fransjo Leihitu
  * @version    0.4
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
    private $_version_minor  = "4";

    private $_markets     = null;
    private $_currencies  = null;

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl . "v" . $this->apiVersion . "/");

        $this->getMarketsFromExchange();
        $this->getCurrenciesFromExchange();
    }

    private function send($method = null , $args = array() , $secure = true) {
        if(empty($method)) return array("status" => false , "error" => "method was not defined!");

        $uri  = $this->getBaseUrl() . $method;

        $postdata = "";
        if(!empty($args)) $postdata = http_build_query($args, '', '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CryptoFyer');
        if($secure == false) {
          if(!empty($postdata)) {
            $uri  = $uri . "?" . $postdata;
          }
        } else {
          return $this->getErrorReturn("secure calls not implemented yet!");
        }
        curl_setopt($ch, CURLOPT_URL,$uri);

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

    public function getMarkets() {
      return $this->_markets;
    }

    public function getMarketSummaries() {
      $resultOBJ  = $this->send("getmarketsummaries" , null , false);
      if($resultOBJ["success"] == true) {
        foreach($resultOBJ["result"] as $result) {
          $result["Last"] = number_format($result["LastPrice"], 8, '.', '');
          $result["Bid"] = number_format($result["BidPrice"], 8, '.', '');
          $result["Ask"] = number_format($result["AskPrice"], 8, '.', '');
        }
        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      } else {
        return $resultOBJ;
      }
    }

    public function getCurrenciesFromExchange() {
      $result = $this->send("getcurrencies" , null , false);
      if($result["success"] == true) {
        $this->_currencies  = null;
        $this->_currencies  = array();
        foreach($result["result"] as $item) {
          $this->_currencies[$item["TickerCode"]]  = $item;
        }
        return $this->getReturn(true,null,$this->_currencies);
      }
      return $result;
    }

    public function getCurrency($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("getmarkets" , $args , false);
    }

    public function getMarketsFromExchange() {
      $result = $this->send("getmarkets" , null , false);
      if($result["success"] == true) {
        $this->_markets  = null;
        $this->_markets  = array();
        foreach($result["result"] as $item) {
          $pair = $item["MarketAssetCode"] . "/" . $item["BaseCurrencyCode"];
          $this->_markets[$pair]  = $item;
        }
        return $this->getReturn(true,null,$this->_markets);
      }
      return $result;
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "/" . $market);
    }

    // get ticket information
    public function getTicker($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(empty($this->_markets)) {
        $this->getMarketsFromExchange();
        if(empty($this->_markets)) $this->getErrorReturn("cannot fetch markets from server");
      }
      if(!isSet($this->_markets[$args["market"]])) {
        $this->getErrorReturn("cannot fetch market: " . $args["market"]);
      }
      $marketInfo = $this->_markets[$args["market"]];

      unset($args["market"]);
      $args["market_id"]  = $marketInfo["MarketID"];

      $resultOBJ = $this->send("getmarketsummary" , $args , false);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["Last"] = number_format($result["LastPrice"], 8, '.', '');
        $result["Bid"] = number_format($result["BidPrice"], 8, '.', '');
        $result["Ask"] = number_format($result["AskPrice"], 8, '.', '');

        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      }

      return $resultOBJ;
    }

    // get ticket information
    public function getOrderbook($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      if(empty($this->_markets)) {
        $this->getMarketsFromExchange();
        if(empty($this->_markets)) $this->getErrorReturn("cannot fetch markets from server");
      }
      if(!isSet($this->_markets[$args["market"]])) {
        $this->getErrorReturn("cannot fetch market: " . $args["market"]);
      }
      $marketInfo = $this->_markets[$args["market"]];

      unset($args["market"]);
      $args["market_id"]  = $marketInfo["MarketID"];

      $resultOBJ = $this->send("getorderbook" , $args , false);
      if($resultOBJ["success"] == true) {
        /* TODO normalize ? */
        return $resultOBJ;
      }
      return $resultOBJ;
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
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      return $this->currencyUrl . $args["market"];
    }

    // Get market history
    public function getMarketHistory($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }


  }
?>
