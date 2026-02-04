<?php

    // setup
    list($usec, $sec) = explode(" ", microtime());
    $starttime=(float)$usec + (float)$sec;
    require_once("headerDB.inc.php");

    // get server IP
    $servip = explode(".",$_SERVER['REMOTE_ADDR']);
    $serv = $servip[0] . "." . $servip[1] . "." . $servip[2] . "." . $servip[3];

    // check if https
    if ($_SERVER["REQUEST_SCHEME"] == "https") $is_https = true;
    else $is_https = false;

    // check if special page to redirect to http
    $to_http = false;
    if ($_SERVER["HTTP_HOST"] == 'localhost') $to_http = true;

    // redirect if needed
    if (!$is_https && !$to_http) {
      $url = 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
      nextScreen($url);
    }

    // check password
    include("password_protect.php");

    // header definition
    if (!headers_sent()) {
        $header="Content-Type: text/html; charset=" . $config['charset'];
        header($header);
    }

?>
