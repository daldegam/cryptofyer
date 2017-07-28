<?php
  include("../includes/tools.inc.php");
  include("../includes/cryptoexchange.class.php");

  // exchanges api
  include("../bittrex/bittrex_api.class.php");
  include("../cryptopia/cryptopia_api.class.php");

  // exchanges configs
  include("../bittrex/config.inc.php");
  include("../cryptopia/config.inc.php");


  if(!isSet($config)) die("no config found!");
  $exchange = isSet($_GET["exchange"]) ? $_GET["exchange"] : null;

  $_market    = isSet($_GET["market"]) ? strtoupper($_GET["market"]) : "BTC";

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td></td>";
  echo "</tr>";

  echo "<tr>";
  echo "<td>";
  echo "<select name='exchange'>";
  foreach($config as $key=>$value) {
    $selected = $key==$exchange ? "SELECTED" : "";
    echo "<option value='" . $key ."' " . $selected . ">" .$key . "</option>";
  }
  echo "</select>";
  echo "</td>";
  echo "<td><input type='submit' value='send'></td>";
  echo "</tr>";

  echo "</table>";
  echo "</form>";


  if(empty($exchange)) die("no exchange found!");

  $exchangeName = strtolower(trim($exchange));
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange = null;
  switch($exchangeName) {
    case "bittrex" : {
      $exchange  = new BittrexApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );
      break;
    }
    case "cryptopia" : {
      $exchange  = new CryptopiaApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );
      break;
    }
  }
  if(empty($exchange)) die("cannot init exchange " . $exchangeName);
  echo "api version : " . $exchange->getVersion() . "<br>";

  echo "<h1>Method getBalances()</h1>";
  echo "Exchange: " . $exchangeName . "<br>";

  $btcUsdtRate  = 0;
  $tickerOBJ  = $exchange->getTicker(array("_market" => "USDT" , "_currency" => "BTC"));
  if($tickerOBJ["success"] == true) {
    $btcUsdtRate  = $tickerOBJ["result"]["Last"];
    $btcUsdtRate  = number_format($btcUsdtRate, 8 , '.', '');
  }

  $totalBTC = 0;
  echo "1 BTC = " . $btcUsdtRate . " USD<br>";

  echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Currency</strong></td>";
  echo "<td><strong>Units</strong></td>";
  echo "<td><strong>Rate</strong></td>";
  echo "<td><strong>Value</strong></td>";
  echo "</tr>";

  $balancesOBJ = $exchange->getBalances();
  if($balancesOBJ["success"] == true) {
    foreach($balancesOBJ["result"] as $item) {
      if($item["Balance"] > 0) {

        $balance  = $last = number_format($item["Balance"], 8, '.', '');
        $rate     = 0;

        $_market  = "BTC";
        switch($item["Currency"]) {
          case "BTC" : {
            $_market  = "USDT";
            break;
          }
        }
        $tickerOBJ  = $exchange->getTicker(array("_market" => $_market , "_currency" => $item["Currency"]));
        if($tickerOBJ["success"] == true) {
          $rate  = $tickerOBJ["result"]["Last"];
          $rate  = number_format($rate, 8 , '.', '');
        }

        if($item["Currency"] == "BTC") {
          $rate = 1;
        }
        $value  = $balance * $rate;
        $value  = number_format($value, 8 , '.', '');

        $usdValue = $value * $btcUsdtRate;
        $usdValue = number_format($usdValue, 8 , '.', '');
        $usdValue = round($usdValue , 2);

        echo "<tr>";

        echo "<td>";
        echo $item["Currency"];
        echo " <a href='" . $exchange->getCurrencyUrl(array("_market" => "BTC" , "_currency" => $item["Currency"] )) ."' target='_blank'>[view on " . $exchangeName . "]</a>";
        echo "</td>";

        echo "<td>" . $balance . "</td>";
        echo "<td>" . $rate . "</td>";
        echo "<td>" . $value. " BTC / " . $usdValue . " USD</td>";
        echo "</tr>";

        $totalBTC += $value;
      }
    }
  }

  echo "</table>";
  echo "<br>";
  echo "Total value: " . $totalBTC . " BTC / " . round(number_format($totalBTC * $btcUsdtRate, 8 , '.', ''),2) . " USD"; 
?>
