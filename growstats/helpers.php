<?php
  function getData($path) {
    $f = file_get_contents($path);
    $lines = explode("\n", $f);
    return $lines;
  }
  function averageOf($lst) {
    return array_sum($lst) / count($lst);
  }
  function serverIsOnline($values) {
    $condition = end($values) < 500 && $values[count($values) - 2] < 500 && $values[count($values) - 3] < 500;
    if ($condition) {
        return false;
    }
    return true;
  }
  function serverWasOnline($val) {
    return ($val >= 25000 ? true : false);
  }
  function calculateStatus($isOnline) {
    if ($isOnline) {
        return '<span style="color: green;">Server is up and running</span>';
    }
    return '<span style="color: red;">Server is experiencing some issues at the moment</span>';
  }
?>