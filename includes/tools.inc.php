<?php
  function debug($array = null , $die = false) {
    if(!empty($array)) {
      print "<pre>";
      print_r($array);
      print "</pre>";
      if($die === true) die();
    }
  }

  function bubble_sort($arr) {
      $size = count($arr);
      for ($i=0; $i<$size; $i++) {
          for ($j=0; $j<$size-1-$i; $j++) {
              if ($arr[$j+1] < $arr[$j]) {
                  swap($arr, $j, $j+1);
              }
          }
      }
      return $arr;
  }

  function swap(&$arr, $a, $b) {
      $tmp = $arr[$a];
      $arr[$a] = $arr[$b];
      $arr[$b] = $tmp;
  }

  function cls() {
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
  }  
?>
