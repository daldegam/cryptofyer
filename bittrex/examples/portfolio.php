<?php
  /*
    This example file will get your portfolio.
  */
  include("../../includes/tools.inc.php");
  include("../../includes/cryptoexchange.class.php");
  include("../bittrex_api.class.php");

  include("../config.inc.php");

  if(!isSet($apiKey)) die("please configure the apiKey");
  if(!isSet($apiSecret)) die("please configure the apiSecret");

  $exchange  = new BittrexxApi($apiKey , $apiSecret );

  $totalBtcBalanceFormatted = 0;

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
            $item["Balance"]    = number_format($item["Balance"], 10, '.', '');
            $item["Available"]  = number_format($item["Available"], 10, '.', '');

            $currency = $item["Currency"];
            $market   = "BTC-" . $currency;

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

            $btcValue = number_format($last * $item["Balance"], 10, '.', '');

            // --- BEGIN history
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

            $profitSell = number_format($BtcBalanceFormatted + $lastEstSellFormatted, 10, '.', '');

            $breakEvenRate = 0;
            $breakEvenRate1 = 0;
            if($totalUnitsFilled > 0) {
              $breakEvenRate1  = number_format($BtcBalanceFormatted / $totalUnitsFilled, 10, '.' , '');
            }
            if($breakEvenRate1 < 0) {
              $breakEvenRate = $breakEvenRate1 * -1;
            } else {
              $breakEvenRate  = $breakEvenRate1;
            }

            $breakEvenRate1  = number_format($breakEvenRate, 10, '.' , '');
            // --- END history

            $totalBtcBalanceFormatted += $lastEstSellFormatted;

            $color  = $profitSell>0 ? "background-color:green;color:white;" : "";

            echo "<tr style='" . $color   . "'>";
            echo "<td><a href='holdings.php?c=" . $item["Currency"] . "&usd=". $btcUsdtRate . "' target='_blank' style=''>";
            if($profitSell > 0) echo "<strong>";
            echo $item["Currency"];
            if($profitSell > 0) echo "</strong>";
            echo "</a></td>";

            echo "<td>" . $item["Balance"] . "</td>";
            echo "<td>" . $last ."</td>";

            echo "<td>";
            echo $lastEstSellFormatted . " BTC";
            echo " / ";
            echo round(number_format($lastEstSellFormatted*$btcUsdtRate, 10, '.', ''),2) . " USD";
            echo "</td>";

            echo "<td>";
            echo $BtcBalanceFormatted;
            echo "</td>";

            echo "<td>";
            echo $profitSell . " BTC";
            echo " / ";
            echo round(number_format($profitSell*$btcUsdtRate, 10, '.', ''),2) . " USD";
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
        echo "Estimated total value: <strong>" . $totalBtcBalanceFormatted . "</strong> BTC / " . round(number_format($totalBtcBalanceFormatted*$btcUsdtRate, 10, '.', ''),2) . " USD<br>";
        echo "Top currency in profit on sell: <a href='holdings.php?c=" . $higestProfitCellCurrency . "&usd=". $btcUsdtRate . "' target='_blank'>" . $higestProfitCellCurrency . "</a> (" . $higestProfitCellCurrencyValue . " BTC / " . round(number_format($higestProfitCellCurrencyValue*$btcUsdtRate, 10, '.', ''),2) . " USD) <br>";
        echo "Top currency in loss on sell: <a href='holdings.php?c=" . $lowestProfitCellCurrency . "&usd=". $btcUsdtRate . "' target='_blank'>" . $lowestProfitCellCurrency . "</a> (" . $lowestProfitCellCurrencyValue . " BTC / " . round(number_format($lowestProfitCellCurrencyValue*$btcUsdtRate, 10, '.', ''),2) . " USD) <br>";
    }
  }

 ?>
