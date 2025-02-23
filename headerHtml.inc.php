<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config['charset'];?>;" />
<meta name="referrer" content="same-origin" />
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
<script type="text/javascript" src="js/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
	  adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="calendar-setup.js"></script>

<!-- sort tables, and other utilities -->
<script type="text/javascript" src="gtdfuncs.js"></script>

<?php if ($_SESSION['useLiveEnhancements']) { ?>
<!-- enhancements for live feedback -->
<script type="text/javascript" src="enhancers.js"></script>

<!-- ajax code for matrix or other files -->
<script src="matrixAjax.js"></script>
<script src="js/jquery-1.12.4.js"></script>
<script type='text/javascript' >
$(document).ready(function() {
    // prevent boxes staying checked after page reload if ajax used to check box
    $(".unchecked").prop("checked", false);
    $(".checked").prop("checked", true);
});
</script>

<!-- allow easy close for windows opened from pertinence.html -->
<script>

  function closeMe() {
    // try 2 methods, either should work normally
    try {
      window.close();
    } catch (e) {
      console.log(e);
    }
    try {
      self.close();
    } catch (e) {
      console.log(e);
    }
  }
</script>

<!-- mark all checkboxes in column at once -->
<script>
	// set checkbox id="check_name" as reportLists.php
	$(document).ready(function() {
		var checked = 0;
		$('[id^="check_"]').on('click', function(){
				if (checked == 1) checked = 0;
				else checked = 1;
				var index = $(this).parent().index();

				$('tr').each(function(i, val){
						$(val).children().eq(index).children('input[type=checkbox]').prop('checked', checked);
				});
		});
	});
</script>

<?php # if is checklist and in ToDB ids
if (isset($check) && $check == 'check' && in_array($values['listId'], array(53,36,37,38,39,40,41,42,43))) { ?>
		<script>
		window.setTimeout(function() { window.location.href = "ToD.php"; }, 1800000); // refresh each 30 minutes
		</script>
<?php } ?>

<!-- code block formatting -->
<!-- renders -->
<script src="js/showdown.min.js"></script>
<script>
// ai chat
function scrollToBottom() {
const chatWindow = document.querySelector('.chat-window');
chatWindow.scrollTop = chatWindow.scrollHeight;
}
</script>
<!-- colours and bolds
set theme as end of .css name
i.e. styles/github.min.css
they will all have .min. there
https://github.com/highlightjs/highlight.js/tree/main/src/styles
-->
<script src="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<style>

/* <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css"> */

/* code {
	display:block;
	overflow-x:auto;
	padding:1em;
	padding:3px 5px;
	color:#abb2bf;
	background:#282c34;
} */

code { /* added this definition */
	color:#abb2bf;
	background:#282c34;
	border-radius: 3px;
	font-family: monospace;
	white-space: pre-wrap;
	padding: 2px 4px 2px 4px;
  line-height: 1.5;
  box-shadow: 0px 0px 1px rgba(0,0,0, .2);
}
/* following in highlight.js stylesheet */
pre code.hljs{
	display:block;
	overflow-x:auto;
	padding:1em
	}

code.hljs{
	padding:3px 5px
}.hljs{
	color:#abb2bf;
	background:#282c34
}.hljs-comment,.hljs-quote{
	color:#5c6370;
	font-style:italic
}.hljs-doctag,.hljs-formula,.hljs-keyword{
	color:#c678dd
}.hljs-deletion,.hljs-name,.hljs-section,.hljs-selector-tag,.hljs-subst{
	color:#e06c75
}.hljs-literal{
	color:#56b6c2
}.hljs-addition,.hljs-attribute,.hljs-meta .hljs-string,.hljs-regexp,.hljs-string{
	color:#98c379
}.hljs-attr,.hljs-number,.hljs-selector-attr,.hljs-selector-class,.hljs-selector-pseudo,.hljs-template-variable,.hljs-type,.hljs-variable{
	color:#d19a66
}.hljs-bullet,.hljs-link,.hljs-meta,.hljs-selector-id,.hljs-symbol,.hljs-title{
	color:#61aeee
}.hljs-built_in,.hljs-class .hljs-title,.hljs-title.class_{
	color:#e6c07b
}.hljs-emphasis{
	font-style:italic
}.hljs-strong{
	font-weight:700
}.hljs-link{
	text-decoration:underline
}
</style>

<!-- copy to clipboard -->
<script src="js/clipboard.min.js"></script>

<?php
}
if ($config['debug'] || defined('_DEBUG'))
	echo '<script type="text/javascript">aps_debugInit("',$config['debugKey'],'");</script>';

// try to prevent low key errors
error_reporting( error_reporting() & ~E_NOTICE );

// try to prevent caching page
if ($serv != '127.0.0.1') {
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}
echo "<meta http-equiv='pragma' content='no-cache' /><meta http-equiv='Expires' content='Mon, 26 Jul 1997 05:00:00 GMT' /><meta http-equiv='Cache-Control' content='no-store, no-cache, must-revalidate' /><meta http-equiv='Cache-Control' content='post-check=0, pre-check=0' />";

echo "
<script type='text/javascript' language='javascript'>
	function moveWindow (){
		document.documentElement.scrollTop = 0;
		if ('scrollRestoration' in history) {
    	history.scrollRestoration = 'manual';
		}
	}
</script>
</head><body" . /*(preg_match("/itemReport.php/", $_SERVER['REQUEST_URI']) ? " onload='moveWindow()'" : '') . */" onload='moveWindow()'><div id='container'>\n";
//require_once("headerMenu.inc.php");
echo "<div id='main'>\n";

if ($config['debug'] & _GTD_DEBUG)
    echo '<br /><hr /><pre>Session:',print_r($_SESSION,true)
        ,'<br />Post:',print_r($_POST,true),'</pre><hr />';

if (isMobile()) echo "<input type='button' value='Close' onclick='closeMe();' class='closeButton' />";

include_once('showMessage.inc.php');
