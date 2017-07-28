<?php
include("../includes/tools.inc.php");
include("../includes/cryptoexchange.class.php");

// exchanges api
include("../bittrex/bittrex_api.class.php");
include("../cryptopia/cryptopia_api.class.php");
include("../coinexchange/coinexchange_api.class.php");

// exchanges configs
include("../bittrex/config.inc.php");
include("../cryptopia/config.inc.php");
include("../coinexchange/config.inc.php");

$exchangesClasses = array(
  "bittrex" => "BittrexApi" ,
  "cryptopia" => "CryptopiaApi",
  "coinexchange" => "CoinexchangeApi"
);
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
 ?>
