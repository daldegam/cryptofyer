<?php
  /*
    This example file will loop and watch the last rate of a currency.
    You can only run this example from command line!
  */
  if(php_sapi_name() != 'cli') die("you need to run this script from commandline!");

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


  cls(); // clear screen

  fwrite(STDOUT, "Enter market-currency pair, for example ETH-BTC (default BTC-USDT): ");

  // Read the input
  $market = strtoupper(fgets(STDIN));
  $market = trim(preg_replace('/\s+/', '', $market));
  if(empty($market)) {
    $market = "BTC-USDT";
  }

  fwrite(STDOUT, "Watching : $market\n");

  $market = trim(preg_replace('/\s+/', '', $market));
  $prevLast = 0;
  do {
    $tickerOBJ  = $exchange->getTicker(array("market" => $market));
    if(!empty($tickerOBJ)) {
      if($tickerOBJ["success"]  == true) {
        $last = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
        $time = time();
        if($prevLast != $last) {
          $direction  = $last > $prevLast ? "+" : "-";
          fwrite(STDOUT, "\n");
          fwrite(STDOUT, "[$time] $market $last $direction\n");
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

  exit(0);
?>
