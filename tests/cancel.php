<?php
  include("includes.php");

  $orderid  = isSet($_GET["orderid"]) ? strtoupper($_GET["orderid"]) : "";

  echo "<form method='get'>";
  echo "<table border='1' cellpadding='5'  cellspacing='0'>";
  echo "<tr>";
  echo "<td><strong>Exchange</strong></td>";
  echo "<td><strong>Orderid</strong></td>";
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
  echo "<td><input type='text' name='orderid' value='" . $orderid . "'></td>";
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

  echo "<h1>Method: cancel()</h1>";

  $sellOBJ  = $exchange->cancel(array("orderid" => $orderid));
  debug($sellOBJ);
?>
