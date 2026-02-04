<?php
require_once('headerDB.inc.php');

if (isset($_POST['listId'])) $values['listId'] = $_POST['listId'];

$effort = 0;
$frequency = 1; // default once a year

$result = query("selectchecklist",$config,$values,$sort);

if (count($result) > 0) {
    $res = array();
    $res = $result[0];
    if (isset($res['frequency']) && $res['frequency'] > 0) $frequency = $res['frequency'];
    $values=array(
		'listId' => $res['listId'],
		'title' => $res['title'],
		'categoryId' => $res['categoryId'],
		'premiseA' => $res['premiseA'],
		'premiseB' => $res['premiseB'],
		'conclusion' => $res['conclusion'],
		'behaviour' => $res['behaviour'],
		'standard' => $res['standard'],
		'conditions' => $res['conditions'],
		'metaphor' => $res['metaphor'],
		'hyperlink' => $res['hyperlink'],
		'sortBy' => $res['sortBy'],
		'frequency' => $res['frequency'],
		'scored' => $res['scored'],
		'menu' => $res['menu'],
    'prioritise' => $res['prioritise'],
    'thrs_score' => $res['thrs_score'],
    'thrs_obs' => $res['thrs_obs'],
    'sortItems' => $res['sortItems']
    );
} else {
    die;
}

if (isset($values['frequency']) && $values['frequency'] > 0) $frequency = $values['frequency'];

$prioritise = $values['prioritise'];

$result1=query("getchecklistitems",$config,$values,$sort);

if (count($result1) > 0) {
    foreach ((array) $result1 as $row) if($prioritise == -1 || ($row['priority'] <= $prioritise && $prioritise > -1)) {

      $effort += $row['effort'];

    }
}

$values['effort'] = ceil($effort * $frequency / 60);

$result = query("updatechecklist",$config,$values,$sort);

//file_put_contents ('_response.txt',PHP_EOL . $result . ' x ' . $query, FILE_APPEND);

if (count($result) > 0) {
    $i = 1;
} else {
    $i = 0;
}
echo json_encode($i);
?>
