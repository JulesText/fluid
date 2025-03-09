<?php

// login form
if (!function_exists('showLoginPasswordProtect')) {

    function showLoginPasswordProtect($error_msg) {

        ?>
        <html>
        <head>
          <title>Please enter password to access this page</title>
          <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
          <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
          <script>
            window.onload = function() {
                document.getElementById("access_login").focus();
            };
          </script>
        </head>
        <body>
          <style>
            input { border: 1px solid black; }
          </style>
          <div style="width:500px; margin-left:auto; margin-right:auto; text-align:center">
          <form method="post">
            <h3>Please enter password to access this page</h3>
            <font color="red"><?php echo $error_msg; ?></font><br />
            Login:<br />
            <input type="text" name="access_login" value="" /><br />
            Password:<br />
            <input type="password" name="access_password" id="access_password" /><p></p><input type="submit" name="Submit" value="Submit" />
          </form>
          </div>
        </body>
        </html>

        <?php
          // stop at this point and wait for form process
          die();

    }
}

// assume invalid login
$verified = false;

# check if call turn turn pass off currently active
if (!$config['password_on']) {
  // $_SESSION['message'][] = 'Password turned off until ' . date('Y-m-d H:i', $config['pass_off_to']);
  $verified = true;
}

// check if user submitted password
$submitted = false;
if (isset($_POST['access_login']) && isset($_POST['access_password'])) {
  $submitted = true;
  $cred = md5($_POST['access_login'].'%'.$_POST['access_password']);
  // clear password protector variables in case of $_POST calls in other scripts
  unset($_POST['access_login']);
  unset($_POST['access_password']);
}

// check cookie if exists
$has_cookie = false;
if (isset($_COOKIE['verify'])) {
  $has_cookie = true;
  $cred = $_COOKIE['verify'];
}

// check creds
if ($submitted || $has_cookie) {

  foreach ($LOGIN_INFORMATION as $login => $pass) {

    if ($cred == md5($login.'%'.$pass)) $verified = true;

  }

}

// if invalid, call form and die
if (!$verified) {

  setcookie('verify', '');
  showLoginPasswordProtect("Submit password");

}

// if valid submission, save
if ($submitted && $verified) {

  // cookie options
  // these are set when creating cookie in browser but can't be read back in php
  $cooked = array(
    'expires' => time() + $config['login_timeout'] * 24 * 60 * 60,
    'path' => '/',
    'domain' => $_SERVER["HTTP_HOST"], // leading dot for compatibility or use subdomain
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict' // None || Lax  || Strict - Firefox soon prefers Lax or Strict
  );

  setcookie('verify', $cred, $cooked);
  mail($config['email_admin'], 'New fluid login ' . $_SERVER['REMOTE_ADDR'], 'EOM');

}

# check if call to turn pass off, i.e. for local app site access such as FR
# if so set off and reload index
if (
  isset($_GET['pass_off'])
  && $_GET['pass_off'] == 'TRUE'
  && $config['password_on']
  && $verified
) {
  $off_to = $config['time_now'] + 60 * $config['pass_off_minutes'];
  file_put_contents('config_pass_off_to.txt',  $off_to);
  $msg = 'Password turned off for ' . $config['pass_off_minutes'] . ' minutes';
  mail($config['email_admin'], $msg, 'EOM');
  $_SESSION['message'][] = $msg . ' until ' . date('Y-m-d H:i', $off_to);
  nextScreen('index.php');
}

?>
