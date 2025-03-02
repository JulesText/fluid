<?php
require_once('headerDB.inc.php');

$month_key = [
  587 => ['Jan', 1]
  , 588 => ['Feb', 2]
  , 589 => ['Mar', 3]
  , 590 => ['Apr', 4]
  , 591 => ['May', 5]
  , 592 => ['Jun', 6]
  , 593 => ['Jul', 7]
  , 594 => ['Aug', 8]
  , 595 => ['Sep', 9]
  , 596 => ['Oct', 10]
  , 597 => ['Nov', 11]
  , 598 => ['Dec', 12]
];

// check if Month Start exists
$_POST["id4"] = 583;
include('matrixquery.php');
$month_start = $value;

// check if Month End exists
$_POST["id4"] = 584;
include('matrixquery.php');
$month_end = $value;

// set calculation range
if (isset($month_start) && isset($month_end)) {

  foreach ($month_key as $arr) if($month_start == $arr[0]) { $month_start = $arr[1]; break; }
  foreach ($month_key as $arr) if($month_end == $arr[0]) { $month_end = $arr[1]; break; }

  if ($month_start <= $month_end) {

    $month_range = [$month_start];
    while ($month_start < $month_end) {
      $month_start++;
      $month_range[] = $month_start;
    }

  } else {

    $month_range = [$month_start, $month_end];
    while ($month_start < 12) {
      $month_start++;
      $month_range[] = $month_start;
    }
    while ($month_end > 1) {
      $month_end--;
      $month_range[] = $month_end;
    }

  }
} else {
  die;
}

// check if months exist / relevant
$month_p = [];
foreach ($month_key as $id => $arr) {
  if (!in_array($arr[1], $month_range)) continue;
  $_POST["id4"] = $id;
  include('matrixquery.php');
  if (isset($value) && ctype_digit($value)) $month_p[] = intval($value);
}

// calculation for Sum / Season
$_POST["id4"] = 581;
if (count($month_p) == 0) $_POST["updVal"] = '';
else $_POST["updVal"] = intval(array_sum($month_p) / count($month_p));
include('matrixsave.php');

?>
