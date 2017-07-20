<?php
  include("../includes/tools.inc.php");
  include("../includes/cryptoexchange.class.php");
  include("cryptopia_api.class.php");

  include("config.inc.php");

  if(!isSet($apiKey)) die("please configure the apiKey");
  if(!isSet($apiSecret)) die("please configure the apiSecret");

  $exchange  = new CryptopiaApi($apiKey , $apiSecret );

  $currency = "BTC";
  $market   = "BTC_USDT";


  echo "<h1>Version</h1>";
  $result = $exchange->getVersion();
  debug($result);

  echo "<h1>Get Balance</h1>";
  $result = $exchange->getBalance(array("currency" => $currency));
  debug($result);

  echo "<h1>Ticker</h1>";
  $result = $exchange->getTicker(array("market" => $market));
  debug($result);
?>
