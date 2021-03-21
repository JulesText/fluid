<?php

// 
//
// to re-calibrate insert value of timestamp of last new moon before winter solstice for $sinceBase
//
//

// calcs the Lunar day & month

$today = getdate();
$date = $today[0]; // system timestamp

$sinceBase = $date - 1368100828; // timestamp of last new moon 

$cycle = 29.53059 * 86400; 	// seconds per new moon cycle
							// 29.53059 is average days between new moon, then this is days converted to seconds between new moons

$lunarSinceBase = $sinceBase / $cycle;
$lunarMonths = fmod($lunarSinceBase, 13);
$lunarCurrent = round($lunarMonths, 0);
$currentProgress = fmod($lunarMonths, 1);
$currentDay = $currentProgress * 29.53059;
							
/*

$str = '';
$str .= '<br>hour since ' . $sinceBase / 3600;
$str .= '<br>day since ' . $sinceBase / 86400;
$str .= '<br>lunar since ' . $lunarSinceBase;
$str .= '<br>lunar this year ' . $lunarMonths;
$str .= '<br>lunar current ' . $lunarCurrent;
$str .= '<br>current cycle ' . $currentProgress;
$str .= '<br>current day ' . $currentDay;

echo $str; die; // debug 
*/

// global vars:
$current = $currentProgress;
$day = ceil($currentDay); // round up
$month = $lunarCurrent;

// end Lunar calc

?>