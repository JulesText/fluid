<?php
require_once('headerDB.inc.php');

// check if unqhoursresearch, Hours Research exists
$_POST["id4"] = 21;
include('matrixquery.php');
$unqhoursresearch = $value;
// get p if exists
$_POST["id4"] = 22;
include('matrixquery.php');
$unqhoursresearch_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if unqeffortday, Effort / Day exists
$_POST["id4"] = 515;
include('matrixquery.php');
$unqeffortday = (isset($value) && ctype_digit($value)) ? intval($value) : 7.5;
// get p if exists
$_POST["id4"] = 516;
include('matrixquery.php');
$unqeffortday_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if unqtravelday, Travel / Day exists
$_POST["id4"] = 517;
include('matrixquery.php');
$unqtravelday = $value;
// get p if exists
$_POST["id4"] = 518;
include('matrixquery.php');
$unqtravelday_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if unqnumdays, Days / Year exists
$_POST["id4"] = 519;
include('matrixquery.php');
$unqnumdays = $value;
// get p if exists
$_POST["id4"] = 520;
include('matrixquery.php');
$unqnumdays_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if Cost Start exists
$_POST["id4"] = 621;
include('matrixquery.php');
$coststart = (isset($value) && ctype_digit($value)) ? intval($value) : 0;

// check if unqcostbasday, Basics / Day exists
$_POST["id4"] = 623;
include('matrixquery.php');
$unqcostbasday = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
// get p if exists
$_POST["id4"] = 624;
include('matrixquery.php');
$unqcostbasday_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if unqcostaccday, Accom / Day exists
$_POST["id4"] = 625;
include('matrixquery.php');
$unqcostaccday = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
// get p if exists
$_POST["id4"] = 626;
include('matrixquery.php');
$unqcostaccday_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;

// check if Cost Extras exists
$_POST["id4"] = 627;
include('matrixquery.php');
$costextras = (isset($value) && ctype_digit($value)) ? intval($value) : 0;



// calculations

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

// calculation for unqhours, Effort / Year
if (isset($unqnumdays) && isset($unqeffortday)) {

  $_POST["id4"] = 511;
  $_POST["updVal"] = intval((($unqnumdays * 10 / $unqnumdays_p) * ($unqeffortday * 10 / $unqeffortday_p)) + ($unqhoursresearch * 10 / $unqhoursresearch_p));
  include('matrixsave.php');

  if ($unqnumdays_p !== 10 || $unqeffortday_p !== 10) {
    $_POST["id4"] = 512;
    $_POST["updVal"] = intval(($unqnumdays_p + $unqeffortday_p - 2) / 2);
    include('matrixsave.php');
  }

}

// calculation for unqhourstravel, Travel / Year
if (isset($unqnumdays) && isset($unqtravelday)) {

  $_POST["id4"] = 513;
  $_POST["updVal"] = intval(($unqnumdays * 10 / $unqnumdays_p) * ($unqtravelday * 10 / $unqtravelday_p));
  include('matrixsave.php');

  if ($unqnumdays_p !== 10 || $unqtravelday_p !== 10) {
    $_POST["id4"] = 514;
    $_POST["updVal"] = intval(($unqnumdays_p + $unqtravelday_p - 2) / 2);
    include('matrixsave.php');
  }

}

// calculation for Cost / Year
if (isset($unqnumdays) && ($unqcostbasday > 0 || $unqcostaccday > 0 || $coststart > 0 || $costextras > 0)) {

  $_POST["id4"] = 622;
  $_POST["updVal"] = intval(
                          ($unqnumdays * 10 / $unqnumdays_p) * ($unqcostbasday * 10 / $unqcostbasday_p)
                         + ($unqnumdays * 10 / $unqnumdays_p) * ($unqcostaccday * 10 / $unqcostaccday_p)
                         + $coststart
                         + $costextras
                        );
  include('matrixsave.php');

}

// destroy connection

$db = NULL;

?>
