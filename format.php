<?php
//lazy format correction
//call with include('format.php');

$t = microtime(true);

require_once('headerDB.inc.php');

$fdb = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

$fquery = "";

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
            $fquery .= "UPDATE `" . $ftable . "` SET `" . $ffield . "` = REPLACE(`" . $ffield . "`, '  ', ' '); \n";
            // remove leading and trailing spaces
            $fquery .= "UPDATE `" . $ftable . "` SET `" . $ffield . "` = TRIM(`" . $ffield . "`); \n";
            // ucase else scase
            if (in_array($i, $fucases)) { 
                $fquery .= "UPDATE `" . $ftable . "` SET `" . $ffield . "` = UCASE(`" . $ffield . "`); \n";
            } else {
                $fquery .= "UPDATE `" . $ftable . "` SET `" . $ffield . "` = CONCAT(UCASE(LEFT(`" . $ffield . "`,1)),SUBSTRING(`" . $ffield . "`, 2)); \n";
            }
            $i++;
        }
    }
}

//echo '<pre>' . $fquery;

if ($config['formatTidy']) $fresult = $fdb->query($fquery);

$fdb = NULL; // destroy connection

//echo round(1000 * (microtime(true) - $t), 0); // est. 25ms separately, but much more if in other code

?>