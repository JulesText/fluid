<?php
//This adjusts for apache GMT errors
date_default_timezone_set('Australia/Canberra');

//get hour of day
$today = getdate();
$hour = $today['hours'];
$month = $today['mon'];

//Adjust Daylight Saving
if ( $month > 3 && $month < 10 ) $hour = $hour - 1;

if ( $hour > 2 && $hour < 6 ) {
  $ToD='L';

}
if ( $hour > 4 && $hour < 8 ) {
  $ToD='LI';

}
if ( $hour > 6 && $hour < 10 ) {
  $ToD='St';
  header('Location:reportLists.php?id=36&type=C');
}
if ( $hour > 8 && $hour < 12 ) {
  $ToD='Sp';
  header('Location:reportLists.php?id=37&type=C');
}
if ( $hour > 10 && $hour < 14 ) {
  $ToD='H';
  header('Location:reportLists.php?id=38&type=C');
}
if ( $hour > 12 && $hour < 16 ) {
  $ToD='SI';
  header('Location:reportLists.php?id=39&type=C');
}
if ( $hour > 14 && $hour < 18 ) {
  $ToD='B';
  header('Location:reportLists.php?id=40&type=C');
}
if ( $hour > 16 && $hour < 20 ) {
  $ToD='K';
  header('Location:reportLists.php?id=41&type=C');
}
if ( $hour > 18 && $hour < 22 ) {
  $ToD='X';
  header('Location:reportLists.php?id=42&type=C');
}
if ( $hour > 20 && $hour < 23.1 ) {
  $ToD='T';
  header('Location:reportLists.php?id=43&type=C');
}
if ( ( $hour > 22 && $hour < 23.1 ) || ( $hour < 1 ) ) {
  $ToD='GB';
  header('Location:reportLists.php?id=13&type=C');
}
if ( $hour > 0 && $hour < 4 ) {
  $ToD='Li';
  header('Location:reportLists.php?id=13&type=C');
}
//JJK MOD End

?>