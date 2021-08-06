<?php
require_once('headerDB.inc.php');

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

//table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5,updVal

$updVal = htmlspecialchars_decode($_POST["updVal"]);
$updVal = str_replace(array(' .CL', ' .LIST'), '', $updVal);
if (substr($updVal, -4) == '<br>') $updVal = substr_replace($updVal,'',-4);
$updVal = str_replace(array('<br>', '<br />'), PHP_EOL, $updVal);
if ($_POST["table"] !== 'lookupqualities') {
    // if using visId for other purpose, do not save
    if ($_POST["col2"] == 'visId') $_POST["id2"] = '';
    if ($_POST["col3"] == 'visId') $_POST["id3"] = '';
    if ($_POST["col4"] == 'visId') $_POST["id4"] = '';
    if ($_POST["col5"] == 'visId') $_POST["id5"] = '';
}

/*
if ($_POST['updCol'] == 'title' && in_array($_POST['table'], array('list','checklist'))) {
    $updVal = preg_replace('/[0-9]+/', '', $updVal);
    $updVal = trim($updVal);
}
*/
$updVal = mysqli_real_escape_string($connection, $updVal);

//    file_put_contents ('a.txt',$_POST['updCol'] . 'has' . $_POST['table'], FILE_APPEND);die;

// logic seems klunky but do not change without thorough testing

if ($_POST["pid1"] == '' || $_POST["pid1"] == 'undefined') $_POST["pid1"] = false;
if ($_POST["id2"] == '') $_POST["id2"] = 'undefined';
if ($_POST["id3"] == '') $_POST["id3"] = 'undefined';
if ($_POST["id4"] == '') $_POST["id4"] = 'undefined';
if ($_POST["id5"] == '') $_POST["id5"] = 'undefined';

$query = "SELECT " . $_POST["updCol"] . " FROM " . $_POST["table"] . " WHERE 1 = 1";
if ($_POST["pid1"]) $query .= " AND `" . $_POST["pcol1"] . "` = '" . $_POST["pid1"] . "'";
if ($_POST["id2"] !== 'undefined') $query .= " AND `" . $_POST["col2"] . "` = '" . $_POST["id2"] . "'";
if ($_POST["id3"] !== 'undefined') $query .= " AND `" . $_POST["col3"] . "` = '" . $_POST["id3"] . "'";
if ($_POST["id4"] !== 'undefined') $query .= " AND `" . $_POST["col4"] . "` = '" . $_POST["id4"] . "'";
if ($_POST["id5"] !== 'undefined') $query .= " AND `" . $_POST["col5"] . "` = '" . $_POST["id5"] . "'";

$result = $db->query($query);

if ($result->rowCount() > 0) {
//    file_put_contents ('a.txt',PHP_EOL . 'has', FILE_APPEND);
    $i = true;
} else {
//    file_put_contents ('a.txt',PHP_EOL . 'has not', FILE_APPEND);
    $i = false;
}

if ($i) {
    $query = "UPDATE " . $_POST["table"] . " SET `" . $_POST["updCol"] . "` = " . ($_POST["updVal"] == 'NULL' ? "NULL" : "'" . $updVal . "'") . " WHERE 1 = 1 ";
    if ($_POST["pid1"]) $query .= " AND `" . $_POST["pcol1"] . "` = '" . $_POST["pid1"] . "'";
    if ($_POST["id2"] !== 'undefined') $query .= " AND `" . $_POST["col2"] . "` = '" . $_POST["id2"] . "'";
    if ($_POST["id3"] !== 'undefined') $query .= " AND `" . $_POST["col3"] . "` = '" . $_POST["id3"] . "'";
    if ($_POST["id4"] !== 'undefined') $query .= " AND `" . $_POST["col4"] . "` = '" . $_POST["id4"] . "'";
    if ($_POST["id5"] !== 'undefined') $query .= " AND `" . $_POST["col5"] . "` = '" . $_POST["id5"] . "'";
} else {
    $query = "INSERT INTO " . $_POST["table"] . " (`" .
        $_POST["pcol1"] .
        "`,`" . $_POST["updCol"];
    if ($_POST["col2"] !== 'undefined') $query .= "`, `" . $_POST["col2"];
    if ($_POST["col3"] !== 'undefined') $query .= "`, `" . $_POST["col3"];
    if ($_POST["col4"] !== 'undefined') $query .= "`, `" . $_POST["col4"];
    if ($_POST["col5"] !== 'undefined') $query .= "`, `" . $_POST["col5"];
    $query .= "`)
        VALUES ('" .
        "','" . $updVal;
    if ($_POST["col2"]) $query .= "', '" . $_POST["id2"];
    if ($_POST["col3"]) $query .= "', '" . $_POST["id3"];
    if ($_POST["col4"]) $query .= "', '" . $_POST["id4"];
    if ($_POST["col5"]) $query .= "', '" . $_POST["id5"];
    $query .= "')";
}

$result = $db->query($query);
//file_put_contents ('a.txt',PHP_EOL . $i . ' x ' . $query, FILE_APPEND);
//file_put_contents ('a.txt',PHP_EOL . var_dump($result), FILE_APPEND);

$db = NULL; // destroy connection

?>
