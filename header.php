<?php

    list($usec, $sec) = explode(" ", microtime());
    $starttime=(float)$usec + (float)$sec;
    require_once("headerDB.inc.php");

    $servip = explode(".",$_SERVER['REMOTE_ADDR']);
    $serv = $servip[0] . "." . $servip[1] . "." . $servip[2];
    if(!in_array($_SERVER['REMOTE_ADDR'], $ip) && !in_array($serv, $iprange)) include("password_protect.php");
/*
    if ($_SESSION['version']!==_GTD_VERSION && !isset($areUpdating) ) {
        $testver=query('getgtdphpversion',$config);
        if ($testver && _GTD_VERSION === array_pop(array_pop($testver)) ) {
            $_SESSION['version']=_GTD_VERSION;
        } else {
            $msg= ($testver)
                    ? "<p class='warning'>Your version of the database needs upgrading before we can continue.</p>"
                    : "<p class='warning'>No gtd-php installation found: please check the database prefix in config.php, and then install.</p>";
            $_SESSION['message']=array($msg); // remove warning about version not being found
            nextScreen('install.php');
            die;
        }
    }
*/
    if (!headers_sent()) {
        $header="Content-Type: text/html; charset=".$config['charset'];
        header($header);
    }

?>
