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

  $higestProfitCellCurrency = "";
  $higestProfitCellCurrencyValue  = 0;

  $lowestProfitCellCurrency = "";
  $lowestProfitCellCurrencyValue  = 0;

  echo "<h1>My portfolio</h1>";

  echo "<form method='GET'>";
  echo "1 BTC = <input type='text' name='usd' value='" . $btcUsdtRate . "'> USD<br>";
  echo "<input type='submit' value='update'>";
  echo "</form>";

  $portfolioOBJ = $exchange->getBalances();
  if(!empty($portfolioOBJ)) {
    if($portfolioOBJ["success"] === true) {
        $portoflio  = $portfolioOBJ["result"];

        echo "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        echo "<td><strong>Currency</strong></td>";
        echo "<td><strong>Units</strong></td>";
        echo "<td><strong>Rate</strong></td>";
        echo "<td><strong>Value</strong></td>";
        echo "<td><strong>Cost / Proceeds</strong></td>";
        echo "<td><strong>Profit on sell</strong></td>";
        echo "<td><strong>Breakeven rate</strong></td>";
        echo "<td><strong>Breakeven profit %</strong></td>";
        echo "</tr>";

        foreach($portoflio as $item) {
          if($item["Balance"] > 0) {
            $item["Balance"]    = number_format($item["Balance"], 8, '.', '');
            $item["Available"]  = number_format($item["Available"], 8, '.', '');

            $currency = $item["Currency"];
            $market   = $exchange->getMarketPair("BTC" , $currency);

            $bid  = 0;
            $ask  = 0;
            $last = 0;
            $tickerOBJ = $exchange->getTicker(array("_market" => "BTC" , "_currency" => $currency));
            if($tickerOBJ) {
              if($tickerOBJ["success"] == true) {
                $bid  = number_format($tickerOBJ["result"]["Bid"], 8, '.', '');
                $ask  = number_format($tickerOBJ["result"]["Ask"], 8, '.', '');
                $last  = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
              }
            }

            $btcValue = number_format($last * $item["Balance"], 8, '.', '');

            // --- BEGIN history
            $historyBalance = 0;
            $historyUnits   = 0;
            $totalCommision     = 0;

            $totalUnitsFilled = 0;
            $BtcBalance = 0;
            $BtcLoss = 0;
            $BtcGain = 0;
            $historyOBJ = $exchange->getOrderHistory(array("_market" => "BTC" , "_currency" => $currency));


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

                  $commision  = number_format($history["Commission"], 8, '.', '');
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
                  $history["_BtcBalance"] = number_format($BtcBalance, 8, '.', '');
                  $history["_commision"]  = number_format($history["Commission"], 8, '.', '');
                  $history["_timestamp"]  = str_replace("T" , " " , $history["TimeStamp"]);
                  $history["PricePerUnit"]  = number_format($history["PricePerUnit"], 8, '.', '');
                  $_history[$timestamp] = $history;

                }
              }
            }

            $bidEstSellFormatted = number_format($totalUnitsFilled * $bid, 8, '.', '');
            $askEstSellFormatted = number_format($totalUnitsFilled * $ask, 8, '.', '');
            $lastEstSellFormatted = number_format($totalUnitsFilled * $last, 8, '.', '');


            $BtcBalanceFormatted = number_format($BtcBalance, 8, '.', '');

            $profitSell = number_format($BtcBalanceFormatted + $lastEstSellFormatted, 8, '.', '');

            $breakEvenRate = 0;
            $breakEvenRate1 = 0;
            if($totalUnitsFilled > 0) {
              $breakEvenRate1  = number_format($BtcBalanceFormatted / $totalUnitsFilled, 8, '.' , '');
            }
            if($breakEvenRate1 < 0) {
              $breakEvenRate = $breakEvenRate1 * -1;
            } else {
              $breakEvenRate  = $breakEvenRate1;
            }

            $breakEvenRate1  = number_format($breakEvenRate, 8, '.' , '');
            // --- END history

            $totalBtcBalanceFormatted += $lastEstSellFormatted;

            $color  = $profitSell>0 ? "background-color:green;color:white;" : "";

            echo "<tr style='" . $color   . "'>";
            echo "<td>";
            echo "<a href='holdings.php?c=" . $item["Currency"] . "&usd=". $btcUsdtRate . "' target='_blank' style=''>";
            if($profitSell > 0) echo "<strong>";
            echo $item["Currency"];
            if($profitSell > 0) echo "</strong>";
            echo "</a>";
            echo "&nbsp;<a href='" . $exchange->getCurrencyUrl(array("_market" => "BTC" , "_currency" => $item["Currency"])) . "' target='_blank'>[bittrex]</a>";
            echo "</td>";

            echo "<td>" . $item["Balance"] . "</td>";
            echo "<td>" . $last ."</td>";

            echo "<td>";
            echo $lastEstSellFormatted . " BTC";
            echo " / ";
            echo round(number_format($lastEstSellFormatted*$btcUsdtRate, 8, '.', ''),2) . " USD";
            echo "</td>";

            echo "<td>";
            echo $BtcBalanceFormatted;
            echo "</td>";

            echo "<td>";
            echo $profitSell . " BTC";
            echo " / ";
            echo round(number_format($profitSell*$btcUsdtRate, 8, '.', ''),2) . " USD";
            echo "</td>";

            if($profitSell > $higestProfitCellCurrencyValue) {
              $higestProfitCellCurrency = $item["Currency"];
              $higestProfitCellCurrencyValue  = $profitSell;
            }


            if($profitSell < $lowestProfitCellCurrencyValue) {
              $lowestProfitCellCurrency = $item["Currency"];
              $lowestProfitCellCurrencyValue  = $profitSell;
            }






            echo "<td>" . $breakEvenRate1 . "</td>";

            echo "<td>";
            if($breakEvenRate1 > 0) {
              //if($last > $breakEvenRate1) {
                $profitFromBreakEven  = (($last - $breakEvenRate1) / $breakEvenRate1) * 100;
                echo round($profitFromBreakEven,2) . "%";
              //}
            } else {
              echo "0%";
            }
            echo "</td>";

            echo "</tr>";

            //die();
          }

        }

        echo "</table>";

        echo "<br>";
        echo "Estimated total value: <strong>" . $totalBtcBalanceFormatted . "</strong> BTC / " . round(number_format($totalBtcBalanceFormatted*$btcUsdtRate, 8, '.', ''),2) . " USD<br>";
        echo "Top currency in profit on sell: <a href='holdings.php?c=" . $higestProfitCellCurrency . "&usd=". $btcUsdtRate . "' target='_blank'>" . $higestProfitCellCurrency . "</a> (" . $higestProfitCellCurrencyValue . " BTC / " . round(number_format($higestProfitCellCurrencyValue*$btcUsdtRate, 8, '.', ''),2) . " USD) <br>";
        echo "Top currency in loss on sell: <a href='holdings.php?c=" . $lowestProfitCellCurrency . "&usd=". $btcUsdtRate . "' target='_blank'>" . $lowestProfitCellCurrency . "</a> (" . $lowestProfitCellCurrencyValue . " BTC / " . round(number_format($lowestProfitCellCurrencyValue*$btcUsdtRate, 8, '.', ''),2) . " USD) <br>";
    }
  }

 ?>
