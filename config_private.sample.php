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
        "host"    => '', //the hostname of your database server
        "db"      => '', //the name of your database
        "prefix"	=> '', // the GTD table prefix for your installation (optional)
        "user"    => '', //username for database access
        "pass"    => '', //database password
    //database information
        "dbtype"  => 'mysql',  //database type: currently only mysql is valid.  DO NOT CHANGE!
				"conn"    => NULL // store the sqli connection here
);

/*********  openAI API ************/

$config['openAI'] = 'xxx';

/*********  password filter ************/

$config['email_admin'] = 'gg@gmail.com';

$login_information = array(
  'mickey' => 'abc123'
);

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
