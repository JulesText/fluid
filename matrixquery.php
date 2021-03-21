<?php

require_once('headerDB.inc.php');

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

$updCol = $_POST["updCol"];

if ($_POST["table"] !== 'lookupqualities') { 
    // if using visId for other purpose, do not use
    if ($_POST["col2"] == 'visId') $_POST["id2"] = '';
    if ($_POST["col3"] == 'visId') $_POST["id3"] = '';
    if ($_POST["col4"] == 'visId') $_POST["id4"] = '';
    if ($_POST["col5"] == 'visId') $_POST["id5"] = '';
}

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
$value = $result->fetchColumn();

if ($result->rowCount() > 0) {
    echo $value; // avoid encoding string
} else {
    echo 'randomstring8adf34Lror';
}

$db = NULL; // destroy connection

?>