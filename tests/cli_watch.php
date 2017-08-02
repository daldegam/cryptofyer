<?php
/*
  This example file will loop and watch the last rate of a currency.
  You can only run this example from command line!
*/
if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

include("includes.php");

cls(); // clear screen

$exchangeName = "";

// parse CLI args
$args = array();
if($argc>1) {
  parse_str(implode('&',array_slice($argv, 1)), $args);
}
$exchangeName = isSet($args["exchange"]) ? strtolower($args["exchange"]) : "";
if(trim($exchangeName) != "") {
  if(!isSet($exchangesInstances[$exchangeName])) {
    fwrite(STDOUT, "[ERROR] $exchangeName does not exists!!\n");
    $exchangeName  = "";
  }
}

$_market    = isSet($args["market"]) ? strtolower($args["market"]) : "";
$_currency  = isSet($args["currency"]) ? strtolower($args["currency"]) : "";

if(empty($exchangeName)) {
  fwrite(STDOUT, "Exchanges: \n");
  $count  = 0;
  $exchanges  = array();
  foreach($config as $key=>$value) {
    fwrite(STDOUT, "[$count] $key\n");
    $exchanges[]  = $key;
    $count++;
  }
  $defaultExchange  = $exchanges[0];
  fwrite(STDOUT, "Select exchange : [$defaultExchange] > ");
  $exchangeIndex = fgets(STDIN);
  $exchangeIndex  = trim(preg_replace('/\s+/', '', $exchangeIndex));
  $exchangeName = strtolower($exchanges[$exchangeIndex]);

  if(empty($exchangeName)) {
    $exchangeName = $exchanges[0];
  }

  if(empty($exchanges)) die("no exchange found!\n");
}


$exchangeName = strtolower(trim($exchangeName));
if(!isSet($config) || !isSet($exchangesInstances[$exchangeName])) die("no config for ". $exchangeName ." found!");

$exchange = $exchangesInstances[$exchangeName];
if(empty($exchange)) die("cannot init exchange " . $exchangeName);

fwrite(STDOUT, "Using exchange: $exchangeName\n");

$version  = $exchange->getVersion();
fwrite(STDOUT, "api version: $version\n");

if(empty($_market)) {
  $_market  = "BTC";
  fwrite(STDOUT, "Enter market: [$_market] > ");
  $marketSelection = fgets(STDIN);
  $marketSelection  = trim(preg_replace('/\s+/', '', $marketSelection));
  $_market  = !empty($marketSelection) ? $marketSelection : $_market;
}
fwrite(STDOUT, "Using market: $_market\n");

if(empty($_currency)) {
  $_currency  = "ETH";
  fwrite(STDOUT, "Enter currency: [$_currency] > ");
  $currencySelection = fgets(STDIN);
  $currencySelection  = trim(preg_replace('/\s+/', '', $currencySelection));
  $_currency  = !empty($currencySelection) ? $currencySelection : $_currency;
}
fwrite(STDOUT, "Using currency: $_currency\n");

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
