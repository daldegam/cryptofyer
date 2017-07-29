<?php
  /*
  *
  * @package    cryptofyer
  * @class CryptopiaApi
  * @author     Fransjo Leihitu
  * @version    0.18
  *
  * Documentation Public Api : https://www.cryptopia.co.nz/Forum/Thread/255
  * Documentation Private Api : https://www.cryptopia.co.nz/Forum/Thread/256
  */
  class CryptopiaApi extends CryptoExchange implements CryptoExchangeInterface {

    // exchange base api url
    private $exchangeUrl   = "https://www.cryptopia.co.nz/Api/";

    // exchange currency url
    private $currencyUrl  = "https://www.cryptopia.co.nz/Exchange?market=";

    // class version
    private $_version_major  = "0";
    private $_version_minor  = "18";

    public function __construct($apiKey = null , $apiSecret = null)
    {
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;

        parent::setVersion($this->_version_major , $this->_version_minor);
        parent::setBaseUrl($this->exchangeUrl);
    }

    private function send($method = null , $args = array() , $secure = true) {
      if(empty($method)) return $this->getErrorReturn("method was not defined!");

      $urlParams  = $args;
      $uri        = $this->getBaseUrl() . $method;

      $ch = curl_init();

      if($secure) {
        $nonce                      = microtime();
        $post_data                  = json_encode( $urlParams );
        $m                          = md5( $post_data, true );
        $requestContentBase64String = base64_encode( $m );
        $signature                  = $this->apiKey . "POST" . strtolower( urlencode( $uri ) ) . $nonce . $requestContentBase64String;
        $hmacsignature              = base64_encode( hash_hmac("sha256", $signature, base64_decode( $this->apiSecret ), true ) );
        $header_value               = "amx " . $this->apiKey . ":" . $hmacsignature . ":" . $nonce;
        $headers                    = array("Content-Type: application/json; charset=utf-8", "Authorization: $header_value");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $urlParams ) );
      }

      curl_setopt($ch, CURLOPT_URL, $uri );
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      $execResult = curl_exec($ch);

      // check if there was a curl error
      if(curl_error($ch)) return $this->getErrorReturn(curl_error($ch));

      // check if we can decode the JSON string to a assoc array
      if($obj = json_decode($execResult , true)) {
        if($obj["Success"] == true) {
          if(!isSet($obj["Error"])) {
            return $this->getReturn($obj["Success"],$obj["Message"],$obj["Data"]);
          } else {
            return $this->getErrorReturn($obj["Error"]);
          }
        } else {
          return $this->getErrorReturn($obj["Error"]);
        }
      } else {
        return $this->getErrorReturn($execResult);
      }
    }

    public function getMarketPair($market = "" , $currency = "") {
      return strtoupper($currency . "-" . $market);
    }

    public function getCurrencyUrl($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"] = str_replace("-" , "_" , $args["market"]);
      $args["market"] = str_replace("/" , "_" , $args["market"]);

      return $this->currencyUrl . $args["market"];
    }

    public function getCurrencies($args = null){
      return $this->send("GetCurrencies" , $args , false);
    }

    public function getDepositAddress($args = null) {
      if(!isSet($args["currency"])) return $this->getErrorReturn("required parameter: currency");
      return $this->send("GetDepositAddress" , $args);
    }

    public function getMarkets($args  = null) {

      $market = isSet($args["market"]) ? "/" . $args["market"] : "";
      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";

      $response = $this->send("GetMarkets".$market.$hours , null , false);

      if($response["success"] == true) {
        $result = array();
        foreach($response["result"] as $item) {
          $item["Last"]     = $item["LastPrice"];
          $item["Bid"]      = $item["BidPrice"];
          $item["Ask"]      = $item["AskPrice"];
          $result[] = $item;
        }
        $response["result"] = $result;
      }
      return $response;
    }

    public function getTradePairs($args = null){
      return $this->send("GetTradePairs" , $args , false);
    }

    public function getBalances($args  = null) {
      return $this->getBalance(array("currency" => ""));
    }

    public function getBalance($args  = null) {
      if(!empty($args)) {
        if(isSet($args["currency"])) {
          $args["Currency"] = $args["currency"];
          unset($args["currency"]);
        }
      }
      $balanceOBJ = $this->send("GetBalance" , $args);
      if($balanceOBJ["success"] == true) {
        $result = array();
        foreach($balanceOBJ["result"] as $item) {
          $item["Balance"]  = $item["Total"];
          $item["Currency"] = $item["Symbol"];
          $result[] = $item;
        }
        $balanceOBJ["result"] = $result;
        return $balanceOBJ;
      } else {
        return $balanceOBJ;
      }
    }

    public function getOrder($args  = null) {
      if(!isSet($args["orderid"])) return $this->getErrorReturn("required parameter: orderid");

      $resultOBJ  = $this->getOrders($args);
      if($resultOBJ["success"] == true) {
        foreach($resultOBJ["result"] as $result) {
          if($result["orderid"] == $args["orderid"]) {
            return $this->getReturn(true , null , $result);
          }
        }
        $this->getErrorReturn("cannot find order: " . $args["orderid"]);
      } else {
        return $resultOBJ;
      }
    }

    public function getOrders($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(isSet($args["market"])) {
        $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
        $args["market"]=strtoupper(str_replace("/","_",$args["market"]));
      } else {
        $args["market"] = "";
      }

      $resultOBJ  = $this->send("GetOpenOrders" , $args);
      if($resultOBJ["success"] == true) {
        $result = array();
        foreach($resultOBJ["result"] as $item) {
          $item["orderid"]  = $item["OrderId"];
          $result[] = $item;
        }
        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      } else {
        return $resultOBJ;
      }
    }

    public function cancel($args = null) {
      if(!isSet($args["orderid"])) return $this->getErrorReturn("required parameter: orderid");
      $args["OrderId"]  = $args["orderid"];
      unset($args["orderid"]);

      if(!isSet($args["type"])) $args["type"] = "Trade";
      $args["Type"] = $args["type"];
      unset($args["type"]);

      return $this->send("CancelTrade" , $args);
    }

    public function buy($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"] = str_replace("-","/",$args["market"]);
      $args["market"] = str_replace("_","/",$args["market"]);
      $args["Market"] = strtoupper($args["market"]);
      unset($args["market"]);

      if(!isSet($args["Type"])) $args["Type"] = "Buy";

      if(isSet($args["price"])) {
        $args["rate"] = $args["price"];
        unset($args["price"]);
      }
      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["Rate"] = $args["rate"];
      unset($args["rate"]);

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["Amount"] = $args["amount"];
      unset($args["amount"]);

      $resultOBJ = $this->send("SubmitTrade" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["orderid"]  = $result["OrderId"];
        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      } else {
          return $resultOBJ;
      }
    }

    public function sell($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["Market"] = $args["market"];
      unset($args["market"]);
      $args["Market"] = strtoupper(str_replace("-","/",$args["Market"]));

      if(!isSet($args["Type"])) $args["Type"] = "Sell";

      if(isSet($args["price"])) {
        $args["rate"] = $args["price"];
        unset($args["price"]);
      }
      if(!isSet($args["rate"])) return $this->getErrorReturn("required parameter: rate");
      $args["Rate"] = $args["rate"];
      unset($args["rate"]);

      if(!isSet($args["amount"])) return $this->getErrorReturn("required parameter: amount");
      $args["Amount"] = $args["amount"];
      unset($args["amount"]);

      $resultOBJ = $this->send("SubmitTrade" , $args);
      if($resultOBJ["success"] == true) {
        $result = $resultOBJ["result"];
        $result["orderid"]  = $result["OrderId"];
        $resultOBJ["result"]  = $result;
        return $resultOBJ;
      } else {
          return $resultOBJ;
      }
    }

    public function getMarket($args = null) {
      return $this->getTicker($args);
    }
    public function getTicker($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";

      $response = $this->send("GetMarket/".$args["market"].$hours , null , false);
      if(isSet($response["result"]) && !empty($response["result"])) {
        $result             = $response["result"];
        $result["Last"]     = $result["LastPrice"];
        $result["Bid"]      = $result["BidPrice"];
        $result["Ask"]      = $result["AskPrice"];
        $response["result"] = $result;
      }
      return $response;
    }

    public function getMarketOrders($args = null) {
      return $this->getOrderbook($args);
    }
    public function getOrderbook($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      if(isSet($args["depth"])) {
        $orderCount  = isSet($args["depth"]) ? "/" . $args["depth"] : "";
        unset($args["depth"]);
      }

      $response = $this->send("GetMarketOrders/".$args["market"].$orderCount, null , false);
      return $response;
    }

    public function getMarketOrderGroups($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    public function submitTip($args = null) {
      return $this->getErrorReturn("not implemented yet!");
    }

    public function getTransactions($args = null) {
      return $this->send("GetTransactions", $args);
    }

    public function getTradeHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      $count  = isSet($args["count"]) ? "/" . $args["count"] : "";
      $method = "GetTradeHistory/".$args["market"].$count;
      unset($args["market"]);

      return $this->send($method, $args , false);
    }

    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
        unset($args["_market"]);
        unset($args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      $hours  = isSet($args["hours"]) ? "/" . $args["hours"] : "";
      $method = "GetMarketHistory/".$args["market"].$hours;
      unset($args["market"]);

      return $this->send($method, $args , false);
    }

  }
?>
