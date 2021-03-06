<?php
require_once('headerDB.inc.php');
$html=false;
if ($config['debug'] & _GTD_DEBUG) {
    $html=true;
    include_once('headerHtml.inc.php');
    echo "</head><body><div id='container'><pre>\n",print_r($_POST,true),"</pre>\n";
}

$values=array();
$field=$_POST['field'];

if (isset($_POST['id'])) {
    $values['id']=(int) $_POST['id'];
    $values['name']=$_POST['name'];
    $values['description']=$_POST['description'];
    switch ($field) {
        case 'instance':
            $query='instance';
            $getId='instance';
            break;
        case 'category':
            $query='category';
            $getId='category';
            break;
        case 'context':
            $query='spacecontext';
            $getId='context';
            break;
        case 'time-context':
            $query='timecontext';
            $getId='timecontext';
            if ($config['useTypesForTimeContexts'] && isset($_POST['type']) && $_POST['type']!='')
                $values['type']=$_POST['type'];
            else
                $values['type']='a';
            break;
        default:
            break;
    }
    if ($values['id']==0) {
        $result = query("new$query",$config,$values);
        $msg='Created';
        if ($field == 'instance') {
            $values['instanceId'] = $GLOBALS['lastinsertid'];
            $result = query("selectchecklistiteminst",$config,$values);
            //echo '<pre>';var_dump($result);die;
            foreach ((array) $result as $res) {
                $values['lastId'] = $res['checklistItemId'];
                $values['id'] = $res['checklistId'];
                $result = query("newchecklistiteminst",$config,$values);
            }
        }
    } elseif (isset($_POST['delete']) && $_POST['delete']==="y") {
        if ($field == 'instance') {
            $result=query("deleteinstancerecords",$config,$values); // don't delete if reassign fails
            $result=query("delete$query",$config,$values); // don't delete if reassign fails
        } else {
            $values['newId']=(int) $_POST['replacewith'];
            $result=query("reassign$query",$config,$values);
            if ($result!==false) $result=query("delete$query",$config,$values); // don't delete if reassign fails
        }
        $msg='Deleted';
    } else {
        $result=query("update$query",$config,$values);
        $msg='Updated';
    }
} // end of: if (isset($_POST['id']))
if ($result) $_SESSION['message'][]="$msg $field '{$values['name']}'";

$nexturl="editCat.php?field=$field";
if (isset($_POST['next']))
    $nexturl.='&id='.$_POST['next'];
nextScreen($nexturl);

if ($html)
    include_once('footer.php');
else
    echo '</head></html>';

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
