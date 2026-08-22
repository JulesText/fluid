<?php

require_once('request.inc.php');
normaliseCurrentRequest();

if (isset($_GET['link'])) {
    header('Location: ' . $_GET['link']);
}
