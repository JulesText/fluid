<?php

require_once('headerDB.inc.php');

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

$updCol = $_POST["updCol"];

if ($_POST["table"] !== 'lookupqualities') {
    // if using visId for other purpose, do not use
    if (isset($_POST["col2"]) && $_POST["col2"] == 'visId') $_POST["id2"] = '';
    if (isset($_POST["col3"]) && $_POST["col3"] == 'visId') $_POST["id3"] = '';
    if (isset($_POST["col4"]) && $_POST["col4"] == 'visId') $_POST["id4"] = '';
    if (isset($_POST["col5"]) && $_POST["col5"] == 'visId') $_POST["id5"] = '';
}

if (!isset($_POST["pid1"]) || $_POST["pid1"] == '' || $_POST["pid1"] == 'undefined') $_POST["pid1"] = false;
if (!isset($_POST["id2"]) || $_POST["id2"] == '') $_POST["id2"] = 'undefined';
if (!isset($_POST["id3"]) || $_POST["id3"] == '') $_POST["id3"] = 'undefined';
if (!isset($_POST["id4"]) || $_POST["id4"] == '') $_POST["id4"] = 'undefined';
if (!isset($_POST["id5"]) || $_POST["id5"] == '') $_POST["id5"] = 'undefined';

$query = "SELECT " . $_POST["updCol"] . " FROM " . $_POST["table"] . " WHERE 1 = 1";
if ($_POST["pid1"]) $query .= " AND `" . $_POST["pcol1"] . "` = '" . $_POST["pid1"] . "'";
if ($_POST["id2"] !== 'undefined') $query .= " AND `" . $_POST["col2"] . "` = '" . $_POST["id2"] . "'";
if ($_POST["id3"] !== 'undefined') $query .= " AND `" . $_POST["col3"] . "` = '" . $_POST["id3"] . "'";
if ($_POST["id4"] !== 'undefined') $query .= " AND `" . $_POST["col4"] . "` = '" . $_POST["id4"] . "'";
if ($_POST["id5"] !== 'undefined') $query .= " AND `" . $_POST["col5"] . "` = '" . $_POST["id5"] . "'";

$result = $db->query($query);
$value = $result->fetchColumn();

if ($result->rowCount() > 0) {
  if ($_POST["table"] == 'nextactions') echo 'y'; // special case of querying nextactions
  else echo $value; // default value, avoid encoding string
} else {
  if ($_POST["table"] == 'nextactions') echo 'n'; // special case of querying nextactions
  else echo '*no result from query*';
  unset($value);
}

// file_put_contents ('_response.txt', PHP_EOL . $query , FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . $value . PHP_EOL . '--', FILE_APPEND);

$db = NULL; // destroy connection

?>
