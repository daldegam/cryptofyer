<?php
  /*
    This example file will get all transactions (holdings) in a currency.
  */
  include("../../includes/tools.inc.php");
  include("../../includes/cryptoexchange.class.php");
  include("../bittrex_api.class.php");

  include("../config.inc.php");

  if(!isSet($apiKey)) die("please configure the apiKey");
  if(!isSet($apiSecret)) die("please configure the apiSecret");

  $exchange  = new BittrexxApi($apiKey , $apiSecret );

  $currency = isSet($_GET["c"]) ? $_GET["c"] : null;
  if(empty($currency)) die("No currency");
  $market   = "BTC-" . $currency;

  $btcUsdtRate  = 0;
  if(!isSet($_GET["usd"])) {
    $tickerOBJ = $exchange->getTicker(array("market" => "USDT-BTC"));
    if(!empty($tickerOBJ)) {
      if($tickerOBJ["success"] === true) {
        $btcUsdtRate  = number_format($tickerOBJ["result"]["Last"], 10, '.', '');
      }
    }
  } else {
    $btcUsdtRate  = $_GET["usd"];
  }

  /*
  * Get the balance
  */
  $balanceOBJ = $exchange->getBalance(array("currency" => $currency));
  if($balanceOBJ) {
    if($balanceOBJ["success"] === false) {
      debug($balanceOBJ , true);
    }
  }

  /*
  * Get the ticker information
  */
  $bid  = 0;
  $ask  = 0;
  $last = 0;
  $tickerOBJ = $exchange->getTicker(array("market" => $market));
  if($tickerOBJ) {
    if($tickerOBJ["success"] == true) {
      $bid  = number_format($tickerOBJ["result"]["Bid"], 10, '.', '');
      $ask  = number_format($tickerOBJ["result"]["Ask"], 10, '.', '');
      $last  = number_format($tickerOBJ["result"]["Last"], 10, '.', '');
    }
  }


  echo "<h1>Holdings in  " . $currency . "</h1>";
  echo "1 BTC = " . $btcUsdtRate . " USD<br>";
  $historyBalance = 0;
  $historyUnits   = 0;
  $totalCommision     = 0;

  $totalUnitsFilled = 0;
  $BtcBalance = 0;
  $BtcLoss = 0;
  $BtcGain = 0;
  $historyOBJ = $exchange->getOrderHistory(array("market" => $market));


  $_history = array();
  if($historyOBJ) {
    if($historyOBJ["success"] == true) {

      $__history  = array();
      foreach($historyOBJ["result"] as $history) {
        $timestamp  = $history["TimeStamp"];
        $timestamp  = str_replace("-" , "",$timestamp);
        $timestamp  = str_replace("T" , "",$timestamp);
        $timestamp  = str_replace(":" , "",$timestamp);
        $timestamp  = str_replace("." , "",$timestamp);
        $timestamp  = str_replace(" " , "",$timestamp);
        $__history[$timestamp] = $history;
      }
      $arr = bubble_sort($__history);
      $_history = null;
      $__history = array_reverse($arr);

      foreach($__history as $history) {
        //debug($history);

        $timestamp  = $history["TimeStamp"];
        $timestamp  = str_replace("-" , "",$timestamp);
        $timestamp  = str_replace("T" , "",$timestamp);
        $timestamp  = str_replace(":" , "",$timestamp);
        $timestamp  = str_replace("." , "",$timestamp);
        $timestamp  = str_replace(" " , "",$timestamp);

        $unitsFilled  = $history["Quantity"] - $history["QuantityRemaining"];
        $balance  = $history["PricePerUnit"] * $unitsFilled;

        $commision  = number_format($history["Commission"], 10, '.', '');
        $totalCommision += $commision;

        $orderSpend = 0;
        $orderGain  = 0;

        switch($history["OrderType"]) {
          case "LIMIT_BUY" : {
            $orderSpend = $balance + $commision;
            $BtcLoss += $orderSpend;
            $totalUnitsFilled += $unitsFilled;
            $BtcBalance -= $orderSpend;
            break;
          }
          case "LIMIT_SELL" : {
            $totalUnitsFilled -= $unitsFilled;
            $orderGain  = $balance - $commision;
            $BtcGain  += $orderGain;
            $BtcBalance += $orderGain;
            break;
          }
        }

        $history["_totalUnitsFilled"] = $totalUnitsFilled;
        $history["_unitsFilled"] = $unitsFilled;
        $history["_BtcSpend"] = $orderSpend;
        $history["_BtcGain"] = $orderGain;
        $history["_BtcBalance"] = number_format($BtcBalance, 10, '.', '');
        $history["_commision"]  = number_format($history["Commission"], 10, '.', '');
        $history["_timestamp"]  = str_replace("T" , " " , $history["TimeStamp"]);
        $history["PricePerUnit"]  = number_format($history["PricePerUnit"], 10, '.', '');
        $_history[$timestamp] = $history;

      }
    }
  }

  $bidEstSellFormatted = number_format($totalUnitsFilled * $bid, 10, '.', '');
  $askEstSellFormatted = number_format($totalUnitsFilled * $ask, 10, '.', '');
  $lastEstSellFormatted = number_format($totalUnitsFilled * $last, 10, '.', '');


  $BtcBalanceFormatted = number_format($BtcBalance, 10, '.', '');

  echo "You have <strong>" . $totalUnitsFilled . "</strong> " . $currency . " units (<strong>" . $lastEstSellFormatted . "</strong> BTC / " . round(number_format(($lastEstSellFormatted * $btcUsdtRate), 10, '.', ''),2) . " USD)<br>";
  if($BtcBalanceFormatted > 0) {
    echo "You have a profit of : <strong>" . $BtcBalanceFormatted . "</strong> BTC / "  . round(number_format(($BtcBalanceFormatted * $btcUsdtRate), 10, '.', ''),2) . " USD<br>";
  } else {
    echo "You have a loss of : <strong>" . $BtcBalanceFormatted . "</strong>  BTC / "  . round(number_format(($BtcBalanceFormatted * $btcUsdtRate), 10, '.', ''),2) . " USD<br>";
  }

  if(empty($_history)) die();

  echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>date</strong></td>";
  echo "<td><strong>type</strong></td>";
  echo "<td><strong>units</strong></td>";
  echo "<td><strong>rate</strong></td>";
  echo "<td><strong>fee</strong></td>";
  echo "<td><strong>value</strong></td>";
  echo "<td><strong>invested balance</strong></td>";
  echo "</tr>";

  foreach($_history as $history) {
    $style  = $history["_BtcBalance"] > 0 ? "background-color:green;color:white;" : "";
    echo "<tr style='" . $style . "'>";
    echo "<td>" . $history["_timestamp"] . "</td>";
    echo "<td>" . $history["OrderType"] . "</td>";
    echo "<td>" . $history["_unitsFilled"] . "</td>";
    echo "<td>" . $history["PricePerUnit"] . "</td>";
    echo "<td>" . $history["_commision"] . "</td>";

    echo "<td>";
    $value  = $history["_BtcGain"] > 0 ? $history["_BtcGain"] : $history["_BtcSpend"];
    echo $value . " BTC";
    echo " / ";
    echo round(number_format($value * $btcUsdtRate, 10, '.', ''),2) . " USD";
    echo "</td>";
    //echo "<td>" . (($history["_BtcSpend"] > 0) ? $history["_BtcSpend"] : "") . "</td>";


    echo "<td>";
    echo $history["_BtcBalance"] . " BTC";
    echo " / ";
    echo round(number_format($history["_BtcBalance"] * $btcUsdtRate, 10, '.', ''),2) . " USD";
    echo "</td>";

    echo "</tr>";
  }

  echo "</table>";

  if($totalUnitsFilled > 0) {
    echo "<h2>Estimate sell out</h2>";
    $bidEstSellFormatted = number_format($totalUnitsFilled * $bid, 10, '.', '');
    $askEstSellFormatted = number_format($totalUnitsFilled * $ask, 10, '.', '');
    $lastEstSellFormatted = number_format($totalUnitsFilled * $last, 10, '.', '');

    $currentLastBalance = number_format($BtcBalanceFormatted + $lastEstSellFormatted , 10 , '.' , '');
    $currentBidBalance  = number_format($BtcBalanceFormatted + $bidEstSellFormatted, 10 , '.' , '');
    $currentAskBalance  = number_format($BtcBalanceFormatted + $askEstSellFormatted, 10 , '.' , '');

    if($currentLastBalance < 0 && $currentBidBalance < 0 && $currentAskBalance < 0) {
      echo "<Strong>Do not sell anything now!!!</strong><br>";
      echo "You have a loss of : <strong>" . $BtcBalanceFormatted . "</strong>  BTC / "  . round(number_format(($BtcBalanceFormatted * $btcUsdtRate), 10, '.', ''),2) . " USD<br>";
    } else {
      echo "<strong>You can sell at a profit now!</strong><br>";
    }

    $breakEvenRate = 0;
    $breakEvenRate1  = number_format($BtcBalanceFormatted / $totalUnitsFilled, 10, '.' , '');
    if($breakEvenRate1 < 0) {
      $breakEvenRate = $breakEvenRate1 * -1;
    } else {
      $breakEvenRate  =$breakEvenRate1;
    }

    $breakEvenRate1  = number_format($breakEvenRate, 10, '.' , '');
    if($breakEvenRate > 0) {
      echo "You make a profit when you sell <strong>" . $totalUnitsFilled . "</strong> unit(s) above rate : <strong>" . $breakEvenRate1 . "</strong><br>";
      if($last > $breakEvenRate) {
        $profitFromBreakEven  = (($last - $breakEvenRate) / $breakEvenRate) * 100;
        echo "With current rate you have <strong>" . round($profitFromBreakEven,2) . "%</strong> profit<br>";
      }
    }

    echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    echo "<td><strong></strong></td>";
    echo "<td><strong>rate</strong></td>";
    echo "<td><strong>payout</strong></td>";
    echo "<td><strong>netto balance</strong></td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td><strong>Last</strong></td>";
    echo "<td>" . $last . "</td>";

    echo "<td>";
    echo $lastEstSellFormatted . " BTC";
    echo " / ";
    echo round(number_format($lastEstSellFormatted * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";




    echo "<td>";
    echo $currentLastBalance . " BTC";
    echo " / ";
    echo round(number_format($currentLastBalance * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";

    echo "</tr>";


    echo "<tr>";
    echo "<td><strong>Bid</strong></td>";
    echo "<td>" . $bid. "</td>";

    echo "<td>";
    echo $bidEstSellFormatted . " BTC";
    echo " / ";
    echo round(number_format($bidEstSellFormatted * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";


    echo "<td>";
    echo $currentBidBalance . " BTC";
    echo " / ";
    echo round(number_format($currentBidBalance * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";


    echo "</tr>";



    echo "<tr>";
    echo "<td><strong>Ask</strong></td>";
    echo "<td>" . $ask. "</td>";

    echo "<td>";
    echo $askEstSellFormatted . " BTC";
    echo " / ";
    echo round(number_format($askEstSellFormatted * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";


    echo "<td>";
    echo $currentAskBalance . " BTC";
    echo " / ";
    echo round(number_format($currentAskBalance * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
    echo "</td>";

    echo "</tr>";

    echo "</table>";

    echo "<h2>Alert profit marks</h2>";
    echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    echo "<td><strong>breakeven profit %</strong></td>";
    echo "<td><strong>rate</strong></td>";
    echo "<td><strong>payout </strong></td>";
    echo "<td><strong>netto balance</strong></td>";
    echo "</tr>";


    for($i = 0 ; $i <= 100 ; $i+=10) {
      echo "<tr>";

      echo "<td>";
      echo $i . "";
      echo "</td>";

      echo "<td>";
      $rate = (($breakEvenRate * $i) / 100) + $breakEvenRate;
      echo number_format($rate, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format($rate * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
      echo "</td>";

      echo "<td>";
      $recieve  = $rate * $totalUnitsFilled;
      echo number_format($recieve, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format($recieve * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
      echo "</td>";

      echo "<td>";
      echo number_format($recieve + $BtcBalanceFormatted, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format(($recieve + $BtcBalanceFormatted) * $btcUsdtRate, 10, '.' , ''),2)  . " USD";
      echo "</td>";

      echo "</tr>";
    }

    $skips  = 500;
    for($i = 200 ; $i <= (round($profitFromBreakEven,2)+($skips+$skips/2)) ; $i+=$skips) {
      echo "<tr>";

      echo "<td>";
      echo $i . "";
      echo "</td>";

      echo "<td>";
      $rate = (($breakEvenRate * $i) / 100) + $breakEvenRate;
      echo number_format($rate, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format($rate * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
      echo "</td>";

      echo "<td>";
      $recieve  = $rate * $totalUnitsFilled;
      echo number_format($recieve, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format($recieve * $btcUsdtRate, 10, '.' , '') , 2) . " USD";
      echo "</td>";

      echo "<td>";
      echo number_format($recieve + $BtcBalanceFormatted, 10, '.' , '') . " BTC";
      echo " / ";
      echo round(number_format(($recieve + $BtcBalanceFormatted) * $btcUsdtRate, 10, '.' , ''),2)  . " USD";
      echo "</td>";

      echo "</tr>";
    }

    echo "</table>";

  }
 ?>
