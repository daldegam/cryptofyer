<?php
/*
  This example file will loop and watch the last rate of a currency.
  You can only run this example from command line!
*/
if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

include("includes.php");

cls(); // clear screen

$exchangeName = "";

fwrite(STDOUT, "Select exchange: \n");
$count  = 0;
$exchanges  = array();
foreach($config as $key=>$value) {
  fwrite(STDOUT, "[$count] $key\n");
  $exchanges[]  = $key;
  $count++;
}
$defaultExchange  = $exchanges[0];
fwrite(STDOUT, "> [$defaultExchange] ");
$exchangeIndex = fgets(STDIN);
$exchangeIndex  = trim(preg_replace('/\s+/', '', $exchangeIndex));
$exchangeName = strtolower($exchanges[$exchangeIndex]);

if(empty($exchangeName)) {
  $exchangeName = $exchanges[0];
}

if(empty($exchanges)) die("no exchange found!\n");
$exchangeName = strtolower(trim($exchangeName));
if(!isSet($config) || !isSet($exchangesInstances[$exchangeName])) die("no config for ". $exchangeName ." found!");

$exchange = $exchangesInstances[$exchangeName];
if(empty($exchange)) die("cannot init exchange " . $exchangeName);


$version  = $exchange->getVersion();
fwrite(STDOUT, "api version: $version\n");

$_market  = "BTC";
fwrite(STDOUT, "Enter market: \n");
fwrite(STDOUT, "> [$_market] ");
$marketSelection = fgets(STDIN);
$marketSelection  = trim(preg_replace('/\s+/', '', $marketSelection));
$_market  = !empty($marketSelection) ? $marketSelection : $_market;

$_currency  = "ETH";
fwrite(STDOUT, "Enter currency: \n");
fwrite(STDOUT, "> [$_currency] ");
$currencySelection = fgets(STDIN);
$currencySelection  = trim(preg_replace('/\s+/', '', $currencySelection));
$_currency  = !empty($currencySelection) ? $currencySelection : $_currency;

$market     = $exchange->getMarketPair($_market,$_currency);
fwrite(STDOUT, "watching: $market on $exchangeName\n");

$market = trim(preg_replace('/\s+/', '', $market));
$prevLast = 0;
do {
  $tickerOBJ  = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));
  if(!empty($tickerOBJ)) {
    if($tickerOBJ["success"]  == true) {
      $last = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
      $time = time();
      if($prevLast != $last) {
        $direction  = $last > $prevLast ? "+" : "-";

        $diff = 0;
        if($last > $prevLast ) {
          $diff = $last -  $prevLast;
        } else {
          $diff = $prevLast - $last;
        }
        $diff = number_format($diff, 8, '.', '');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, "[$time] $exchangeName : $market $last ($direction $diff)\n");
        $prevLast = $last;
      } else {
        fwrite(STDOUT, ".");
      }
    } else {
      $error  = $tickerOBJ["message"];
      fwrite(STDOUT, "[ERROR] : $error \n");
      exit(0);
    }
  } else {
    var_dump($tickerOBJ);
    exit(0);
  }
  sleep(1);

} while (true);
 ?>
