<?php
include_once('header.php');
//include_once('lists.inc.php');

if (empty($_REQUEST['type']) || empty($_REQUEST['itemId'])) die('invalid $_REQUEST');

$values = array();
$values['itemType'] = $_REQUEST['type'];
$values['itemId'] = $_REQUEST['itemId'];

if (isset($_REQUEST['replace']) && $_REQUEST['replace'] == 'TRUE') {
  if (empty($_REQUEST['newVisId'])) die('$_REQUEST missing newVisId');
  $values['visId'] = $_REQUEST['newVisId'];
  $result = query("updateitemvisqualities",$config,$values,$sort);
  nextScreen('matrix.php?qLimit=f&vLimit=' . $values['visId']);
}

//create blank
$result = query("lookupqualities",$config,$values,$sort);

if (!is_array($result)) die('no matrix entries, just start from scratch');

$visIds = [];
foreach ($result as $row) array_push($visIds, $row['visId']);
$visIds = array_unique($visIds);

echo '<pre>Existing vision(s):';
foreach ($visIds as $key => $visId) {
  $values['filterquery'] = "WHERE i.`itemId` = " . $visId;
  $result = query("getitems",$config,$values,$sort);
  if (is_array($result)) {
    $row = $result[0];
    echo PHP_EOL . 'id: ' . $row["itemId"] . ' title: ' . $row["title"];
  }
}

echo PHP_EOL . PHP_EOL . 'New vision (replace):';

$values['filterquery'] = "WHERE ia.`type` = 'v' AND its.`dateCompleted` IS NULL";
$result = query("getitems",$config,$values,$sort);
foreach ($result as $key => $row) {
  echo PHP_EOL . '<a href="childrenMove.php?type=' . $values['itemType'] . '&itemId=' . $values['itemId'] . '&newVisId=' . $row["itemId"] . '&replace=TRUE">id: ' . $row["itemId"] . ' title: ' . $row["title"] . '</a>';
}

// var_dump($result);die;
// var_dump($visIds);die;

?>
