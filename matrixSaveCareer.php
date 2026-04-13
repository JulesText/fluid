<?php
require_once('headerDB.inc.php');

// populate vars

$_POST["id4"] = 791; include('matrixQuery.php');
$extinction_reduction = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 792; include('matrixQuery.php');
$extinction_reduction_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$extinction_reduction = $extinction_reduction * $extinction_reduction_p / 10;

$_POST["id4"] = 793; include('matrixQuery.php');
$global_economy = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 794; include('matrixQuery.php');
$global_economy_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$global_economy = $global_economy * $global_economy_p / 10;

$_POST["id4"] = 795; include('matrixQuery.php');
$poorest_income = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 796; include('matrixQuery.php');
$poorest_income_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$poorest_income = $poorest_income * $poorest_income_p / 10;

$_POST["id4"] = 797; include('matrixQuery.php');
$healthy_years = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 798; include('matrixQuery.php');
$healthy_years_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$healthy_years = $healthy_years * $healthy_years_p / 10;

$_POST["id4"] = 811; include('matrixQuery.php');
$annual_spending = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 812; include('matrixQuery.php');
$annual_spending_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$annual_spending = $annual_spending * $annual_spending_p / 10;

$_POST["id4"] = 813; include('matrixQuery.php');
$staff_numbers = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 814; include('matrixQuery.php');
$staff_numbers_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$staff_numbers = $staff_numbers * $staff_numbers_p / 10;

$_POST["id4"] = 815; include('matrixQuery.php');
$supporter_numbers = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 816; include('matrixQuery.php');
$supporter_numbers_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$supporter_numbers = $supporter_numbers * $supporter_numbers_p / 10;

$_POST["id4"] = 821; include('matrixQuery.php');
$solvability = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 822; include('matrixQuery.php');
$solvability_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 10;
$solvability = $solvability * $solvability_p / 10;



// calculations

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

$_POST["id4"] = 781;
$_POST["updVal"] = intval(
                      max($extinction_reduction, $global_economy, $poorest_income, $healthy_years)
                       + min($annual_spending, $staff_numbers, $supporter_numbers)
                       + $solvability
                      );
include('matrixSave.php');

// destroy connection

$db = NULL;

?>
