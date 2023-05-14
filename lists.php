<?php

/*
firefox has global setting
about:config > dom.popup_maximum
default 20, set to 50 or 100
*/




include_once('header.php');
require_once("headerDB.inc.php");

// get active visions

$values['type']= "v";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values);
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$vres = query("getitems",$config,$values,$sort);

// get someday lists
$sdays = array();
$listMena = array();
$values = array();
$values['qId'] = 1000;
$lists = query('lookupqualities',$config,$values,$sort);
if (count($lists) > 0 && is_array($lists)) {
    foreach ((array) $lists as $l) {
        if ($l['value'] == 'y') {
            $sdays[] = $l;
        }
    }
}

// get and list CLs and lists
foreach ((array) $vres as $visn) {
    $values = array();
    $values['parentId'] = $visn['itemId'];
    $values['type'] = 'c';
    $clists = query("getchildlists",$config,$values,$sort);
    $values['type'] = 'l';
    $lists = query("getchildlists",$config,$values,$sort);
    if (!empty($lists) && is_array($lists) && count($lists) > 0 && !empty($clists) && is_array($clists) && count($clists) > 0) {
        $lists = array_merge($clists,$lists);
    } elseif (!empty($clists) && is_array($clists) && count($clists) > 0) {
        $lists = $clists;
    }
    if (!empty($lists) && is_array($lists) && count($lists) > 0) {
        foreach ((array) $lists as $list) {
            // if someday list, continue
            foreach ((array) $sdays as $s) {
                if (
                    $s['visId'] == $visn['itemId'] &&
                    $s['itemId'] == $list['id'] &&
                    $s['itemType'] == $list['type']
                    ) continue 2;
            }
            // exclude lists without priorities unless all (unprioritised) requested
            if (!isset($_GET['unprioritised'])) {
              if ($list['type'] == 'c') $check = 'check';
              else $check = '';
              $values['queryTable'] = $check . 'listitems';
              $values['queryKey'] = $check . 'listId';
              $values['queryValue'] = $list['id'];
              $priorities = query("priorityselectbox",$config,$values,$sort);
              if (count($priorities) == 1) continue 1;
            }
            // otherwise, add to menu
            // get list details
            $values['id'] = $list['id'];
            $values['type'] = $list['type'];
            if ($list['type'] == 'c') { $qry = "selectchecklist"; } else { $qry = "selectlist"; }
            $listN = query($qry,$config,$values,$sort);
            $listNarr = array(
                'id' => $list['id'],
                'type' => $list['type'],
                'title' => makeclean($listN[0]['title'] . ($list['type'] == 'c' ? ' .CL' : ' .LIST')),
                'sortBy' => substr($listN[0]['sortBy'], 0, 2)
            );
            $listMena[] = $listNarr;
        }
    }
}

// sort order for lists
function sortBys ($a, $b) {
    return $a['sortBy'] - $b['sortBy'];
}
usort($listMena, 'sortBys');

#echo '<pre>';var_dump($listMena);die;

?>

<html>
<body onload="pertinent()">
<script>
function pertinent() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
<?php
$comma = '';
foreach ($listMena as $l) {
    echo $comma. "'reportLists.php?id={$l['id']}&type={$l['type']}'";
    $comma = ',';
}
?>
    ,'reportLists.php?id=1&type=c' // first/current window
    ];

    var arrayLength = windows.length;

    async function display() {
        for (var i = 0; i < arrayLength - 1; i++) { // skip the last array item
            window.open(windows[i]);
            await sleep(200); // avoid windows from not opening due to server restrictions
        }
        window.location.assign(windows[i]); // first/current window
    }

    display();

    return true;
}
</script>
</body>
</html>
