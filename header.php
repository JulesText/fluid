<?php

    list($usec, $sec) = explode(" ", microtime());
    $starttime=(float)$usec + (float)$sec;
    require_once("headerDB.inc.php");

    $servip = explode(".",$_SERVER['REMOTE_ADDR']);
    $serv = $servip[0] . "." . $servip[1] . "." . $servip[2] . "." . $servip[3];
    if (
      !in_array($_SERVER['REMOTE_ADDR'], $ip)
      && !in_array($serv, $iprange)
      && $config['password_on']
      ) include("password_protect.php");

    if (
      isset($_GET['pass_off'])
      && $_GET['pass_off'] == 'TRUE'
      && $config['password_on']
    ) {

      $off_to = $config['time_now'] + 60 * $config['pass_off_minutes'];
      file_put_contents('config_pass_off_to.txt',  $off_to);
      $msg = 'Password turned off for ' . $config['pass_off_minutes'] . ' minutes';
      mail('author@jules.net.au', $msg, 'EOM');
      $_SESSION['message'][] = $msg . ' until ' . date('Y-m-d H:i', $off_to);
      nextScreen($_SERVER['HTTP_REFERER']);

    }

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
        $header="Content-Type: text/html; charset=" . $config['charset'];
        header($header);
    }

?>
