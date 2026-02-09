<?php

/******************************************/
/**********   Private settings    ********/
/******************************************/

# system settings
# php x.x
# phpmyadmin x.x.x

/*********  Database Settings ************/

$config = array(

    //connection information
        "host"    => ($_SERVER['SERVER_NAME'] == 'localhost') ? 'database' : 'localhost', //the hostname of your database server, 'database' used on localhost running Docker, 'localhost' used on hosted server
        "db"      => '', //the name of your database
        "prefix"	=> '', // the GTD table prefix for your installation (optional)
        "user"    => '', //username for database access
        "pass"    => '', //database password
    //database information
        "dbtype"  => 'mysql',  //database type: currently only mysql is valid.  DO NOT CHANGE!
				"conn"    => NULL // store the sqli connection here
);

/*********  Server IP ************/

// exception to not alter headers in headerHtml.inc.php if running on localhost server
$config['servLocalIP'] = '127.0.0.1';

/*********  openAI API ************/

$config['openAI'] = 'xxx';

/*********  password filter ************/

$config['email_admin'] = 'gg@gmail.com';

$login_information = array(
  'mickey' => 'abc123'
);

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
