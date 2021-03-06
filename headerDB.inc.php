<?php
require_once("ses.php");
require_once("config.php");
if ($config['debug'] & _GTD_NOTICE)
		error_reporting(E_ALL);
    else
		error_reporting(E_ALL ^ E_NOTICE);
/*
  Select the correct database library file:
  the library file must define the following functions:

  getDBVersion() - returns the name and version of the database software

  getDBtables($db) - returns array of table names for the specified database

  doQuery($config,$query,$label) - run the query, and return the result:
        (boolean FALSE) : indicates the query failed
        (integer 0)     : query affected no rows, and returned no rows - e.g. an empty(SELECT)
        (integer >0)    : the number of rows affected by an INSERT, UPDATE or DELETE
        (array)         : SELECT was successful, and has returned a number-indexed array of records,
                           each record is an associative array of field names=>field values.

  safeIntoDB($value,$key) - to make a value safe for database processing,
                            by escaping any control characters

  getsql($config,$values,$sort,$querylabel) - to return the sql query text,
                                              for query with name: $querylabel

  sqlparts($part,$config,$values) - to return the sql query subclause text,
                                        for the subclause with name: $querylabel

  connectdb($config) - to open a connection to the database, and return the connector handle
*/
switch ($config['dbtype']) {
    case "mysql":
		require_once("mysql.inc.php");
        break;
    /*
       only mysql is supported, at present! - all of the others are here as placeholders for later development
    */
    case "frontbase":
        require_once("frontbase.inc.php");
        break;
    case "msql":
        require_once("msql.inc.php");
        break;
    case "mssql":
        require("mssql.inc.php");
        break;
    case "postgres":
        require("postgres.inc.php");
        break;
    case "sqlite":
        require("sqlite.inc.php");
        break;
    default:
        die("Database type not configured.  Please edit the config.php file.");
}
//connect to database
$config = connectdb($config);
require_once("gtdfuncs.php");
// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
