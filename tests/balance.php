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
  $_currency  = isSet($_GET["currency"]) ? strtoupper($_GET["currency"]) : "ETH";

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td><strong>Market</strong></td>";
  echo "<td><strong>Currency</strong></td>";
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
  echo "<td><input type='text' name='market' value='" . $_market . "'></td>";
  echo "<td><input type='text' name='currency' value='" . $_currency . "'></td>";
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

  $market     = $exchange->getMarketPair($_market,$_currency);

  echo "<h1>Method: getBalance()</h1>";

  echo "Exchange: " . $exchangeName . "<br>";
  echo "Market: <a href='" . $exchange->getCurrencyUrl(array("_market" => $_market,"_currency"=>$_currency)) . "' target='_blank'>" . $market . "</a><br>";


  $result = $exchange->getBalance(array("currency" => $_currency));
  debug($result);
?>
