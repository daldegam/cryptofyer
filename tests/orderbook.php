<?php
  include("includes.php");

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td><strong>Market</strong></td>";
  echo "<td><strong>Currency</strong></td>";
  echo "<td></td>";
  echo "</tr>";

  echo "<tr>";
  echo "<td>";
  echo "<select name='exchange'>";
  foreach($config as $key=>$value) {
    $selected = $key==$exchange ? "SELECTED" : "";
    echo "<option value='" . $key ."' " . $selected . ">" .$key . "</option>";
  }
  echo "</select>";
  echo "</td>";
  echo "<td><input type='text' name='market' value='" . $_market . "'></td>";
  echo "<td><input type='text' name='currency' value='" . $_currency . "'></td>";
  echo "<td><input type='submit' value='send'></td>";
  echo "</tr>";

  echo "</table>";
  echo "</form>";


  if(empty($exchange)) die("no exchange found!");
  $exchangeName = strtolower(trim($exchange));
  if(!isSet($config) || !isSet($exchangesInstances[$exchangeName])) die("no config for ". $exchangeName ." found!");

  $exchange = $exchangesInstances[$exchangeName];
  if(empty($exchange)) die("cannot init exchange " . $exchangeName);
  
  echo "api version : " . $exchange->getVersion() . "<br>";

  $market     = $exchange->getMarketPair($_market,$_currency);

  echo "<h1>Method: getOrderbook()</h1>";

  echo "Exchange: " . $exchangeName . "<br>";
  echo "Market: <a href='" . $exchange->getCurrencyUrl(array("_market" => $_market,"_currency"=>$_currency)) . "' target='_blank'>" . $market . "</a><br>";


  $result = $exchange->getOrderbook(array("_market" => $_market , "_currency" => $_currency,"depth" => 5));
  debug($result);
?>
