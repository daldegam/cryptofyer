<?php
  /*
    Pump and dump script
  */
  if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

  include("../../includes/cryptoexchange.class.php");

  if(!file_exists("../cryptopia_api.class.php")) die("cannot find ../cryptopia_api.class.php");
  include("../cryptopia_api.class.php");

  if(!file_exists("../config.inc.php")) die("cannot find ../config.inc.php");
  include("../config.inc.php");

  // you don't really this in production
  if(!file_exists("../../includes/tools.inc.php")) die("cannot find ../../includes/tools.inc.php");
  include("../../includes/tools.inc.php");

  if(!isSet($apiKey)) die("please configure the apiKey");
  if(!isSet($apiSecret)) die("please configure the apiSecret");

  $exchange  = new CryptopiaApi($apiKey , $apiSecret );

  cls(); // clear screen

  $_defaultMarket   = "BTC";
  $_defaultCurrency = "ETH";
  $defaultMarket    =  $exchange->getMarketPair($_defaultMarket , $_defaultCurrency);
  $market           = "";
  $orderCount       = 10;
  $total            = 0;
  $maxRetries       = 5;
  $rateBuyMultiplier   = 1.25;
  $rateSellMultiplier   = 3;

  // parse CLI args
  $args = array();
  if($argc>1) {
    parse_str(implode('&',array_slice($argv, 1)), $args);
  }
  $_market    = isSet($args["market"]) ? $args["market"] : "";
  $_currency  = isSet($args["currency"]) ? $args["currency"] : "";
  $orderCount = isSet($args["ordercount"]) ? $args["ordercount"] : "";
  $total      = isSet($args["total"]) ? $args["total"] : "";
  $_currency  = strtoupper($_currency);
  $_market    = strtoupper($_market);

  if(!empty($_market) && !empty($_currency)) {
    $market =  $exchange->getMarketPair($_market , $_currency);
  }



  if(empty($market)) {
    fwrite(STDOUT, "Enter market [$_defaultMarket] : ");
    $_market = strtoupper(fgets(STDIN));
    $_market = trim(preg_replace('/\s+/', '', $_market));
    $_market  = empty($_market) ? $_defaultMarket : $_market;

    fwrite(STDOUT, "Enter currency [$_defaultCurrency] : ");
    $_currency = strtoupper(fgets(STDIN));
    $_currency = trim(preg_replace('/\s+/', '', $_currency));
    $_currency  = empty($_currency) ? $_defaultCurrency : $_currency;

    $exchange->getMarketPair($_market , $_currency);
  }

  $market = trim(preg_replace('/\s+/', '', $market));
  $market = empty($market) ? $defaultMarket : $market;
  $market = trim(preg_replace('/\s+/', '', $market));
  $market = strtoupper($market);

  fwrite(STDOUT, "Market : $market\n");
  $command  = "";

  $startBalance = 0;
  $balanceOBJ = $exchange->getBalance(array("Currency" => $_currency));
  if($balanceOBJ["success"]) {
    $startBalance = number_format($balanceOBJ["result"][0]["Available"], 10, '.', '');
  }
  fwrite(STDOUT, "Current balance: $startBalance $_currency\n");


  $marketOrdersOBJ  = $exchange->getOrderbook(array("_market" => $_market , "_currency" => $_currency, "depth" => $orderCount));
  if($marketOrdersOBJ["success"] == true) {
    $sellOrders = array();
    foreach($marketOrdersOBJ["result"]["Sell"] as $item) {
      $item["Price"]  = number_format($item["Price"], 10, '.', '');
      $item["Total"]  = number_format($item["Total"], 10, '.', '');
      $item["Volume"]  = number_format($item["Volume"], 10, '.', '');
      $sellOrders[] = $item;
    }
    //debug($sellOrders);

    $len        = count($sellOrders);
    $sellOrder  =$sellOrders[$len-1];
    //debug($sellOrder);
    fwrite(STDOUT, "\n");

    //$sellOrder["Price"] = 0.00000010;
    $startPrice = $sellOrder["Price"];
    fwrite(STDOUT, "Starting price: $startPrice\n");

    fwrite(STDOUT, "Multiplier: $rateBuyMultiplier\n");

    $sellOrder["Price"] = $sellOrder["Price"] * $rateBuyMultiplier;
    $sellOrder["Price"] = number_format($sellOrder["Price"], 10, '.', '');

    $newPrice = $sellOrder["Price"];
    fwrite(STDOUT, "Buy price: $newPrice\n");


    $units  = number_format($total / $sellOrder["Price"], 10, '.', '');
    $rate   = number_format($sellOrder["Price"], 10, '.', '');
    $price  = number_format($sellOrder["Price"], 10, '.', '');

    fwrite(STDOUT, "Trying to place a buy order: $units $_currency at rate $price $_market (total $total $_market)\n");

    //die();

    // place the buy order
    if($buyOBJ = $exchange->buy(array("_market" => $_market, "_currency" => $_currency , "amount"=>$units,"rate"=>$rate))) {
      if($buyOBJ["success"] == true) {
        $order    = $buyOBJ["result"];
        $orderID  = $order["OrderId"];

        // need to give the server some time
        sleep(1);


        $balance  = 0;

        $stopLoop = false;
        $counter  = 0;

        // now check if we have a new balance
        do {
          $balanceOBJ = $exchange->getBalance(array("Currency" => $_currency));
          if($balanceOBJ["success"]) {
            $balance = number_format($balanceOBJ["result"][0]["Available"], 10, '.', '');
            if($startBalance < $balance) {
              fwrite(STDOUT, "new balance : $balance\n");
              $stopLoop = true;
            }
          } else {
            $error  = $balanceOBJ["message"];
            fwrite(STDOUT, "[ERROR] [getBalance] $error\n");
            $counter++;
            if($counter >= $maxRetries) {
              $stopLoop = true;
              fwrite(STDOUT, "[ERROR] max retries!\n");
            }
          }
        } while (!$stopLoop);

        // found a new balance
        // now we need to place a sell order
        $units    = $balance;
        $newRate  = $rate * $rateSellMultiplier;
        $newRate  = number_format($newRate, 10, '.', '');
        fwrite(STDOUT, "old rate: $rate\n");
        fwrite(STDOUT, "new rate: $newRate\n");
        fwrite(STDOUT, "units: $units\n");
        sleep(1);
        $sellOBJ = $exchange->sell(array("_market" => $_market, "_currency" => $_currency,"amount"=>$units,"rate"=>$rate));
        if($sellOBJ["success"] == true) {
          debug($sellOBJ);
        } else {
          debug($sellOBJ);
        }

      } else {
        debug($buyOBJ);
        $error  = $buyOBJ["message"];
        fwrite(STDOUT, "[ERROR] [buy] $error\n");
      }
    }

    fwrite(STDOUT, "\n");
  } else {
    $error  = $marketOrdersOBJ["message"];
    fwrite(STDOUT, "[ERROR] $error\n");
  }

  fwrite(STDOUT, "Done\n");

?>
