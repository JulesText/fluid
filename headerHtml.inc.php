<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $config['charset'];?>" />
<?php
$thisurl=parse_url($_SERVER['PHP_SELF']);

$title = $config['title'];
/*    if ($_GET['content'] == 'limit') {
        $title .= $config['title'];
    } else {
        switch($_GET['type']) {
            case 'C' :
                if ($values['item']) $title .= $values['item'];
                if ($row['title']) $title .= $row['title'] . " checklist";
                break;
            case 'L' :
                if ($values['item']) $title .= $values['item'];
                if ($row['title']) $title .= $row['title'] . " list";
                break;
            default:*/
if (isset($item['title'])) $title = $item['title'];
if (isset($values['title'])) $title = $values['title'];
$arr = str_split($title, 3);
$arr[0] = preg_replace("/[0-9_,.]/", "", $arr[0]);
$title = implode("", $arr);
$title = ucwords(strtolower(ltrim($title)));
#if (isset($type) && $type==='c') $title .= ' CL';
#if (isset($type) && $type==='l') $title .= ' List';
if (isset($mxTitle)) $title = $mxTitle;
     /*   }
    } */

echo "<title>" . $title . "</title>\n";

if ($config['debug'] || defined('_DEBUG'))
	echo '<style type="text/css">pre,.debug {}</style>';
if (!empty($_SESSION['theme']))
    $config['theme']=$_SESSION['theme'];
if (!isset($_SESSION['useLiveEnhancements']))
    $_SESSION['useLiveEnhancements']=$config['useLiveEnhancements'];
?>

<!-- theme main stylesheet -->
<link rel="stylesheet" href="themes/<?php echo $config['theme']; ?>/style.css" type="text/css"/>

<!-- theme screen stylesheet (should check to see if this actually exists) -->
<link rel="stylesheet" href="themes/<?php echo $config['theme']; ?>/style_screen.css" type="text/css" media="Screen" />

<!-- theme script (should check to see if this actually exists) -->
<script type="text/javascript" src="themes/<?php echo $config['theme']; ?>/theme.js"></script>

<!-- printing stylesheet -->
<link rel="stylesheet" href="print.css" type="text/css" media="print" />

<link rel="shortcut icon" href="./favicon.gif" />

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="calendar-win2k-cold-1.css" title="win2k-cold-1" />

<!-- main calendar program -->
<script type="text/javascript" src="calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
	  adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="calendar-setup.js"></script>

<!-- sort tables, and other utilities -->
<script type="text/javascript" src="gtdfuncs.js"></script>

<?php if ($_SESSION['useLiveEnhancements']) { ?>
<!-- enhancements for live feedback -->
<script type="text/javascript" src="enhancers.js"></script>

<?php
}
if ($config['debug'] || defined('_DEBUG'))
	echo '<script type="text/javascript">aps_debugInit("',$config['debugKey'],'");</script>';

// try to prevent low key errors
error_reporting( error_reporting() & ~E_NOTICE );

// try to prevent caching page
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
echo "<meta http-equiv='pragma' content='no-cache' /><meta http-equiv='Expires' content='Mon, 26 Jul 1997 05:00:00 GMT' /><meta http-equiv='Cache-Control' content='no-store, no-cache, must-revalidate' /><meta http-equiv='Cache-Control' content='post-check=0, pre-check=0' />";

echo "
<script type='text/javascript' language='javascript'>
	function moveWindow (){window.location.hash='top';}
</script>
</head><body" . (preg_match("/itemReport.php/", $_SERVER['REQUEST_URI']) ? " onload='moveWindow()'" : '') . "><a name=\"top\"></a><div id='container'>\n";
//require_once("headerMenu.inc.php");
echo "<div id='main'>\n";
if ($config['debug'] & _GTD_DEBUG)
    echo '<br /><hr /><pre>Session:',print_r($_SESSION,true)
        ,'<br />Post:',print_r($_POST,true),'</pre><hr />';
include_once('showMessage.inc.php');
