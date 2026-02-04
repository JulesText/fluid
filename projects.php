<?php

/*
firefox has global setting
about:config > dom.popup_maximum
default 20, set to 50 or 100
*/




include_once('header.php');
require_once("headerDB.inc.php");

// get and count active projects
$values['type']= "p";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values)
        ." AND ".sqlparts("pendingitems",$config,$values)
        //." AND title NOT LIKE '~%'"
        ;
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$pres = query("getitems",$config,$values,$sort);

// eliminate items with inactive visions from array
$values['type']= "v";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values);
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$vres = query("getitems",$config,$values,$sort);
$i=0;
//echo '<pre>'; var_dump($pres);

foreach ($pres as $p) {
    $active = false;
    $values['itemId'] = $p['itemId'];
    $lu = query("lookupparentshort",$config,$values,$sort);
    foreach ($lu as $mat) {
        foreach ($vres as $v) { // has active vision as parent?
            if ($mat['parentId'] == $v['itemId']) {
                $active = true;
                break 2;
            }
        }
        $values['itemId'] = $mat['parentId'];
        $lup = query("lookupparentshort",$config,$values,$sort);
        foreach ($lup as $matp) { // has project as parent that has active vision parent?
            foreach ($vres as $v) {
                if ($matp['parentId'] == $v['itemId']) {
                    $active = true;
                    break 3;
                }
            }
        }
    }
    if (!$active) {
        unset($pres[$i]);
    }
    $i++;
}

$pres = array_values($pres);
#$pres = array_reverse($pres);
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
foreach ($pres as $p) {
    echo $comma. "'itemReport.php?itemId={$p['itemId']}'";
    $comma = ',';
}
?>
    ,'reportLists.php?listId=1&type=c' // first/current window
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
