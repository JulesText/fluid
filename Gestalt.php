<?php

$today = getdate();
$date = $today[0];
$sinceBase = $date - 1220126280; // 1220126280 is the UTC value of a new moon in Aug, 2008
$cycle = 29.53059 * 86400; // days converted to seconds

$current = fmod($sinceBase / $cycle, 1);

/* 
if ($current < 0.5) {
  $percent = $current * 2;
} else {
  $percent = (1 - $current) * 2;
}
*/

//echo round($current * 29.53059, 1)  . ' days ';
//echo round($percent*100, 1) . '%';
//echo $current;

// calibrated to JK hormone cycle (2010-05-08 trough ends at day 24.6)

if ( $current > 0.83 || $current < 0.00 ) { // new
  header('Location:http://sphinx.jules.net.au/index.php?title=Template:DOLPHIN');
}
if ( $current > 0.00 && $current < 0.20 ) { // wax crescent
  header('Location:http://sphinx.jules.net.au/index.php?title=JULES%27_MULE');
}
if ( $current > 0.20 && $current < 0.40 ) { // wax gibbous
  header('Location:http://sphinx.jules.net.au/index.php?title=Template:SHIP_VIZ');
}
if ( $current > 0.40 && $current < 0.50 ) { // full
  header('Location:http://sphinx.jules.net.au/index.php?title=HEAVY_ARMPITS');
}
if ( $current > 0.50 && $current < 0.70 ) { // wane gibbous
  header('Location:http://sphinx.jules.net.au/index.php?title=SUKHASANA');
}
if ( $current > 0.70 && $current < 0.83 ) { // wane crescent
  header('Location:http://sphinx.jules.net.au/index.php?title=GROUND');}

?>
