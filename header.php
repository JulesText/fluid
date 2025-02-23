<?php

    list($usec, $sec) = explode(" ", microtime());
    $starttime=(float)$usec + (float)$sec;
    require_once("headerDB.inc.php");

    include("password_protect.php");
    
    if (!headers_sent()) {
        $header="Content-Type: text/html; charset=" . $config['charset'];
        header($header);
    }

?>
