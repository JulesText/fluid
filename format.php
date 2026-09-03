<?php

//lazy format correction
//call with include('format.php');

$t = microtime(true);

$previousDirectory = getcwd();
chdir(__DIR__);
require_once('headerDB.inc.php');
if ($previousDirectory !== false) {
    chdir($previousDirectory);
}

$fdb = new PDO(
    'mysql:host=' . $config["host"] . ';dbname=' . $config["db"],
    $config["user"],
    $config["pass"],
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
);

$fqueries = array();
$fsets = array();
$ftables = array('checklist', 'list', 'items');
$ffields = array('title', 'premiseA', 'premiseB', 'conclusion', 'behaviour', 'standard', 'conditions');
$fucases = array(0);
$fsets[] = array($ftables, $ffields, $fucases);
$ftables = array('checklistitems', 'listitems');
$ffields = array('item');
$fucases = array(0);
$fsets[] = array($ftables, $ffields, $fucases);

foreach ((array) $fsets as $fset) {
    $ftables = $fset[0];
    $ffields = $fset[1];
    $fucases = $fset[2];
    foreach ((array) $ftables as $ftable) {
        $i = 0;
        foreach ((array) $ffields as $ffield) {
            // remove double spaces
            $fqueries[] = "UPDATE `" . $ftable . "` SET `" . $ffield . "` = REPLACE(`" . $ffield . "`, '  ', ' ')";
            // remove leading and trailing spaces
            $fqueries[] = "UPDATE `" . $ftable . "` SET `" . $ffield . "` = TRIM(`" . $ffield . "`)";
            // ucase else scase
            if (in_array($i, $fucases)) {
                $fqueries[] = "UPDATE `" . $ftable . "` SET `" . $ffield . "` = UCASE(`" . $ffield . "`)";
            } else {
                $fqueries[] = "UPDATE `" . $ftable . "` SET `" . $ffield . "` = "
                    . "CONCAT(UCASE(LEFT(`" . $ffield . "`,1)),SUBSTRING(`" . $ffield . "`, 2))";
            }
            $i++;
        }
    }
}

if ($config['formatTidy']) {
    foreach ($fqueries as $fquery) {
        $fdb->exec($fquery);
    }
}

$fdb = null; // destroy connection

//echo round(1000 * (microtime(true) - $t), 0); // est. 25ms separately, but much more if in other code

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
