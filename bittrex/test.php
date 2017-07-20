<?php
  include("../includes/tools.inc.php");
  include("../includes/cryptoexchange.class.php");
  include("bittrex_api.class.php");

  include("config.inc.php");

  if(!isSet($apiKey)) die("please configure the apiKey");
  if(!isSet($apiSecret)) die("please configure the apiSecret");

  $exchange  = new BittrexxApi($apiKey , $apiSecret );

  $currency = "BTC";
  $market   = "USDT-BTC";

  echo "<h1>Version</h1>";
  $result = $exchange->getVersion();
  debug($result);

  echo "<h1>Get Balance</h1>";
  $result = $exchange->getBalance(array("currency" => "BTC"));
  debug($result);

  echo "<h1>Ticker</h1>";
  $result = $exchange->getTicker(array("market" => $market));
  debug($result);
?>
