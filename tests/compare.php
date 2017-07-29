<?php
  include("includes.php");

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Market</strong></td>";
  echo "<td><strong>Currency</strong></td>";
  echo "<td></td>";
  echo "</tr>";

  echo "<tr>";
  echo "<td><input type='text' name='market' value='" . $_market . "'></td>";
  echo "<td><input type='text' name='currency' value='" . $_currency . "'></td>";
  echo "<td><input type='submit' value='send'></td>";
  echo "</tr>";

  echo "</table>";
  echo "</form>";

  echo "<h1>Comparing " . $_currency . "</h1>";

  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td><strong>Value</strong></td>";
  echo "<td><strong>Volume BTC</strong></td>";
  echo "</tr>";

  foreach($exchangesInstances as $key=>$exchange) {
    echo "<tr>";
    echo "<td>" . $key . "</td>";

    $value  = 0;
    $volume = 0;
    $tickerOBJ  = $exchange->getTicker(array("_market" => $_market , "_currency" => $_currency));
    if($tickerOBJ["success"] == true) {
      $value = number_format($tickerOBJ["result"]["Last"], 8, '.', '');
      $volume = number_format($tickerOBJ["result"]["Volume"], 8, '.', '');
    }

    echo "<td>" . $value . "</td>";
    echo "<td>" . $volume . "</td>";

    echo "</tr>";
  }
  echo "</table>";
 ?>
