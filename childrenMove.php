<?php
include_once('header.php');
require_once("headerHtml.inc.php");

if (empty($_REQUEST['type']) || empty($_REQUEST['itemId'])) die('invalid $_REQUEST');

$values = array();
$values['itemType'] = $_REQUEST['type'];
$values['itemId'] = $_REQUEST['itemId'];

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'replace') {
  if (empty($_REQUEST['newVisId'])) die('$_REQUEST missing newVisId');
  $values['visId'] = $_REQUEST['newVisId'];
  $result = query("updateitemvisqualities",$config,$values,$sort);
  nextScreen('childrenMove.php?type=' . $values['itemType'] . '&itemId=' . $values['itemId']);
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'truncate') {
  if (empty($_REQUEST['oldVisId'])) die('$_REQUEST missing oldVisId');
  $values['visId'] = $_REQUEST['oldVisId'];
  $values['filterquery'] = "AND `visId` = '" . $values['visId'] . "'";
  $result = query("deletequalities",$config,$values,$sort);
  nextScreen('childrenMove.php?type=' . $values['itemType'] . '&itemId=' . $values['itemId']);
}

//create blank
$result = query("lookupqualities",$config,$values,$sort);

if (!is_array($result)) die('no matrix entries, just start from scratch');

$visIds = [];
foreach ($result as $row) {
  if (isset($visIds[$row['visId']])) $visIds[$row['visId']]++;
  else $visIds[$row['visId']] = 1;
}

echo '<pre>Existing vision(s):';
foreach ($visIds as $visId => $count) {
  $values['filterquery'] = "WHERE i.`itemId` = " . $visId;
  $result = query("getitems",$config,$values,$sort);
  if (is_array($result)) {
    $row = $result[0];
    echo PHP_EOL . 'id: ' . $row["itemId"] . ' count: ' . $count . ' title: ' . $row["title"];
    echo ' [truncate <a href="childrenMove.php?type=' . $values['itemType'] . '&itemId=' . $values['itemId'] . '&oldVisId=' . $row["itemId"] . '&action=truncate" class="remove">X</a>]';
    echo ' [<a href="matrix.php?vLimit=' . $row["itemId"] . '&qLimit=f">see in Mx vis</a>]';
  }
}

echo PHP_EOL . PHP_EOL . 'New vision (replace):';

$values['filterquery'] = "WHERE ia.`type` = 'v' AND its.`dateCompleted` IS NULL";
$result = query("getitems",$config,$values,$sort);

function sortByTitle($a, $b) {
    return strcmp($a['title'], $b['title']);
}
usort($result, 'sortByTitle');

foreach ($result as $key => $row) {
  echo PHP_EOL . '<a href="childrenMove.php?type=' . $values['itemType'] . '&itemId=' . $values['itemId'] . '&newVisId=' . $row["itemId"] . '&action=replace">id: ' . $row["itemId"] . ' title: ' . $row["title"] . '</a>';
}

// var_dump($result);die;
// var_dump($visIds);die;

?>
