<?php
  /*

  */
  include("../../includes/cryptoexchange.class.php");

  if(!file_exists("../cryptopia_api.class.php")) die("cannot find ../cryptopia_api.class.php");
  include("../cryptopia_api.class.php");

  if(!file_exists("../config.inc.php")) die("cannot find ../config.inc.php");
  include("../config.inc.php");

  // you don't really this in production
  if(!file_exists("../../includes/tools.inc.php")) die("cannot find ../../includes/tools.inc.php");
  include("../../includes/tools.inc.php");

  $exchangeName = "cryptopia";
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange  = new CryptopiaApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );

  $totalBTC = 0;

  $btcUsdtRate  = 1;
  $tickerOBJ  = $exchange->getTicker(array("_market" => "USDT" , "_currency" => "BTC"));
  if($tickerOBJ["success"] == true) {
    $btcUsdtRate  = $tickerOBJ["result"]["Last"];
    $btcUsdtRate  = number_format($btcUsdtRate, 8 , '.', '');
  }
  $portfolio  = array();

  $balanceOBJ  = $exchange->getBalance(array("currency" => ""));
  if($balanceOBJ["success"] == true) {
    foreach($balanceOBJ["result"] as $item) {
      if($item["Total"] > 0) {
        $item["Available"] = number_format($item["Available"], 8 , '.', '');
        $item["Total"] = number_format($item["Total"], 8 , '.', '');
        //$item["_balance"] = $balanceOBJ["result"][0];

        $tickerOBJ  = $exchange->getTicker(array("_market" => "BTC" , "_currency" => $item["Symbol"]));
        if($tickerOBJ["success"] == true) {
          $item["_ticker"]  = $tickerOBJ["result"];
        }

        $portfolio[]  = $item;
      }
    }
  } else {
    debug($balanceOBJ , true);
  }


  //debug($portfolio , true);
  echo "1 BTC = " . $btcUsdtRate . " USD<br>";

  echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Currency</strong></td>";
  echo "<td><strong>Units</strong></td>";
  echo "<td><strong>Rate</strong></td>";
  echo "<td><strong>Value</strong></td>";
  echo "</tr>";

  if(!empty($portfolio)) {
    foreach($portfolio as $portfolio) {

      if(isSet($portfolio["_ticker"]["Last"])) {
        $last = number_format($portfolio["_ticker"]["Last"], 8, '.', '');
      } else {
        $last = 1;
      }

      $btcValue = $portfolio["Total"] * $last;
      $btcValue = number_format($btcValue, 10, '.', '');

      $usdValue = round(number_format($btcValue * $btcUsdtRate, 8, '.', '') ,  2);

      $totalBTC += $btcValue;

      echo "<tr>";
      echo "<td>" . $portfolio["Symbol"] . "</td>";
      echo "<td>"  . $portfolio["Total"] . "</td>";
      echo "<td>"  . $last . "</td>";
      echo "<td>" . $btcValue . " BTC / " . $usdValue ." USD</td>";
      echo "</tr>";
    }
  }

  echo "</table>";
  echo "<br>";
  $totalUsdValue = round(number_format($totalBTC * $btcUsdtRate, 8, '.', '') ,  2);
  echo "Total value = <strong>" . $totalBTC . "</strong> BTC / " . $totalUsdValue . " USD<br>";

?>
