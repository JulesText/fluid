<?php

//get hour of day
$today = getdate();
$hour = $today[hours];
$minutes = $today[minutes];
echo $hour." ".$minutes;

?>
