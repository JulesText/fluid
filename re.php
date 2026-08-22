<?php

require_once('request.inc.php');
normaliseCurrentRequest();

if (isset($_GET['h'])) {
    header('Location: ' . $_GET['h']);
}
