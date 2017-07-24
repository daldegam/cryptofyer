<?php
  /*
    This example file will get your portfolio.
  */
  include("../../includes/tools.inc.php");
  include("../../includes/cryptoexchange.class.php");
  include("../bittrex_api.class.php");

  include("../config.inc.php");

  $exchangeName = "bittrex";
  if(!isSet($config) || !isSet($config[$exchangeName])) die("no config for ". $exchangeName ." found!");
  if(!isSet($config[$exchangeName]["apiKey"])) die("please configure the apiKey");
  if(!isSet($config[$exchangeName]["apiSecret"])) die("please configure the apiSecret");

  $exchange  = new BittrexxApi($config[$exchangeName]["apiKey"] , $config[$exchangeName]["apiSecret"] );


  $totalBtcBalanceFormatted = 0;

  $btcUsdtRate  = 0;
  if(!isSet($_GET["usd"])) {
    $tickerOBJ = $exchange->getTicker(array("_market" => "USDT" , "_currency" => "BTC"));
    if(!empty($tickerOBJ)) {
      if($tickerOBJ["success"] === true) {
        $btcUsdtRate  = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
      }
    }
  } else {
    $btcUsdtRate  = $_GET["usd"];
  }

  echo "<h1>Deposits</h1>";
  echo "<form method='GET'>";
  echo "1 BTC = <input type='text' name='usd' value='" . $btcUsdtRate . "'> USD<br>";
  echo "<input type='submit' value='update'>";
  echo "</form>";

  $currency = "BTC";
  $depositObj = $exchange->getDepositHistory(array("currency" => $currency));
  $totalDepositBtc  = 0;
  if(!empty($depositObj)) {
    if($depositObj["success"] == true) {
      $deposits = $depositObj["result"];
      echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
      echo "<tr>";
      echo "<td><strong>Currency</strong></td>";
      echo "<td><strong>Ammount</strong></td>";
      echo "</tr>";

      foreach($deposits as $deposit) {
        $ammount  = number_format($deposit["Amount"], 8, '.', '');
        echo "<tr>";
        echo "<td>"  . $deposit["Currency"] . "</td>";
        echo "<td>"  . $ammount . " " . $deposit["Currency"] . " / " . round(number_format($ammount*$btcUsdtRate, 8, '.', ''),2) . " USD</td>";
        echo "</tr>";

        $totalDepositBtc  += $ammount;
      }

      echo "</table>";
      echo "<br>";
      echo "Total : <strong>" . $totalDepositBtc . " BTC / " . round(number_format($totalDepositBtc*$btcUsdtRate, 8, '.', ''),2) . " USD</strong><br>";
    }
  }

 ?>
