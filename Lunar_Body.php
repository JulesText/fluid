<?php

include('Lunar_Calc.php');

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
  header('Location:reportLists.php?listId=97&type=C');
}
if ( $current > 0.00 && $current < 0.20 ) { // wax crescent
  header('Location:reportLists.php?listId=78&type=C');
}
if ( $current > 0.20 && $current < 0.40 ) { // wax gibbous
  header('Location:reportLists.php?listId=80&type=C');
}
if ( $current > 0.40 && $current < 0.50 ) { // full
  header('Location:reportLists.php?listId=92&type=C');
}
if ( $current > 0.50 && $current < 0.70 ) { // wane gibbous
  header('Location:reportLists.php?listId=listId&type=C');
}
if ( $current > 0.70 && $current < 0.83 ) { // wane crescent
  header('Location:reportLists.php?listId=listId&type=C');
}

?>
