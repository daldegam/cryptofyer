<?php
  /*
  *
  * @package    cryptofyer
  * @class CryptopiaApi
  * @author     Fransjo Leihitu
  * @version    0.11
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
    private $_version_minor  = "11";

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
        $nonce                      = time();
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

    public function getBalance($args  = null) {
      if(!empty($args)) {
        if(isSet($args["currency"])) {
          $args["Currency"] = $args["currency"];
          unset($args["currency"]);
        }
      }
      return $this->send("GetBalance" , $args);
    }

    public function getTradeHistory($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","/",$args["market"]));
      $args["market"]=strtoupper(str_replace("_","/",$args["market"]));

      if(isSet($args["count"])) {
        $args["Count"]  = $args["count"];
        unset($args["count"]);
      }
      return $this->send("GetTradeHistory" , $args);
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

      return $this->send("GetOpenOrders" , $args);
    }

    public function cancel($args = null) {
      if(!isSet($args["type"])) return $this->getErrorReturn("required parameter: type");
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

      return $this->send("SubmitTrade" , $args);
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

      return $this->send("SubmitTrade" , $args);
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

    public function getOrderbook($args  = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");
      $args["market"]=strtoupper(str_replace("-","_",$args["market"]));
      $args["market"]=strtoupper(str_replace("/","_",$args["market"]));

      if(isSet($args["depth"])) {
          $orderCount  = isSet($args["depth"]) ? "/" . $args["depth"] : "";
      }

      $response = $this->send("GetMarketOrders/".$args["market"].$orderCount, null , false);
      return $response;
    }

    public function getMarketHistory($args = null) {
      if(isSet($args["_market"]) && isSet($args["_currency"])) {
        $args["market"] = $this->getMarketPair($args["_market"],$args["_currency"]);
      }
      if(!isSet($args["market"])) return $this->getErrorReturn("required parameter: market");

      return $this->getErrorReturn("not implemented yet");
    }

  }
?>
