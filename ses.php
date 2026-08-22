<?php

require_once('request.inc.php');
normaliseCurrentRequest();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$newSession = !isset($_SESSION['views']);
$_SESSION += array(
    'categoryId' => 0,
    'contextId' => 0,
    'message' => array(),
    'version' => '',
    'views' => 0,
);
$_SESSION['views']++;
if ($newSession) {
    foreach ($_COOKIE as $key => $val) {
        $_SESSION[$key] = $val; // retrieve cookie values
    }
}

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
