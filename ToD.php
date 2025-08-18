<?php
//This adjusts for apache GMT errors
date_default_timezone_set('Australia/Sydney');
// date_default_timezone_set('Europe/Berlin');
// date_default_timezone_set('Asia/Kolkata');
// die('date_default_timezone_set: ' . date_default_timezone_get());

//get hour of day
$today = getdate();
$hour = $today['hours'];
$month = $today['mon'];

//Adjust Daylight Saving start Oct to start Apr
//clocks wound forward so reduce clock hour
if ( $month <= 3 || $month >= 10 ) $hour = $hour - 1;

//identify meridian
if ( $hour == 7 || $hour == 8 ) $ToD = 'St';
if ( $hour == 9 || $hour == 10 ) $ToD = 'Sp';
if ( $hour == 11 || $hour == 12 ) $ToD = 'H';
if ( $hour == 13 || $hour == 14 ) $ToD = 'SI';
if ( $hour == 15 || $hour == 16 ) $ToD = 'B';
if ( $hour == 17 || $hour == 18 ) $ToD = 'K';
if ( $hour == 19 || $hour == 20 ) $ToD = 'X';
if ( $hour == 21 || $hour == 22 ) $ToD = 'T';
if ( $hour == 23 || $hour == 0 ) $ToD = 'GB';
if ( $hour == 1 || $hour == 2 ) $ToD = 'Liv';
if ( $hour == 3 || $hour == 4 ) $ToD = 'L';
if ( $hour == 5 || $hour == 6 ) $ToD = 'LI';

// die($ToD);

if ($ToD == 'St') header('Location:reportLists.php?listId=36&type=C');
if ($ToD == 'Sp') header('Location:reportLists.php?listId=37&type=C');
if ($ToD == 'H') header('Location:reportLists.php?listId=38&type=C');
if ($ToD == 'SI') header('Location:reportLists.php?listId=39&type=C');
if ($ToD == 'B') header('Location:reportLists.php?listId=40&type=C');
if ($ToD == 'K') header('Location:reportLists.php?listId=41&type=C');
if ($ToD == 'X') header('Location:reportLists.php?listId=42&type=C');
if ($ToD == 'T') header('Location:reportLists.php?listId=43&type=C');
// if ($ToD == 'GB')
// if ($ToD == 'Liv')
// if ($ToD == 'L')
if ($ToD == 'LI') header('Location:reportLists.php?listId=53&type=C');

?>
