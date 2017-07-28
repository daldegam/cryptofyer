<?php
  include("../includes/tools.inc.php");
  include("../includes/cryptoexchange.class.php");

  // exchanges api
  include("../bittrex/bittrex_api.class.php");
  include("../cryptopia/cryptopia_api.class.php");

  // exchanges configs
  include("../bittrex/config.inc.php");
  include("../cryptopia/config.inc.php");

  $exchangesClasses = array("bittrex" => "BittrexApi" , "cryptopia" => "CryptopiaApi");
  $exchangesInstances = array();

  if(!isSet($config)) die("no config found!");
  $exchange = isSet($_GET["exchange"]) ? $_GET["exchange"] : null;

  $_market    = isSet($_GET["market"]) ? strtoupper($_GET["market"]) : "BTC";
  $_currency  = isSet($_GET["currency"]) ? strtoupper($_GET["currency"]) : "ETH";

  foreach($config as $key=>$value) {
    if(isSet($exchangesClasses[$key])) {
      $className  = $exchangesClasses[$key];
      $classOBJ = new $className($value["apiKey"] , $value["apiSecret"]);
      $exchangesInstances[$key] = $classOBJ;
    }
  }

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Market</strong></td>";
  echo "<td><strong>Currency</strong></td>";
  echo "<td></td>";
  echo "</tr>";

  echo "<tr>";
  echo "<td><input type='text' name='market' value='" . $_market . "'></td>";
  echo "<td><input type='text' name='currency' value='" . $_currency . "'></td>";
  echo "<td><input type='submit' value='send'></td>";
  echo "</tr>";

  echo "</table>";
  echo "</form>";

  echo "<h1>Comparing " . $_currency . "</h1>";

  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td><strong>Value</strong></td>";
  echo "</tr>";
  foreach($exchangesInstances as $key=>$exchange) {
    echo "<tr>";
    echo "<td>" . $key . "</td>";

    $value  = 0;
    $tickerOBJ  = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));
    if($tickerOBJ["success"] == true) {
      $value = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
    }

    echo "<td>" . $value . "</td>";

    echo "</tr>";
  }
  echo "</table>";
 ?>
