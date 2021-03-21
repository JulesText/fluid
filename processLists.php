<?php
require_once('headerDB.inc.php');
if ($config['debug'] & _GTD_DEBUG)  include_once('header.php');
include_once('lists.inc.php');

$nextURL="reportLists.php?id={$values['id']}&$urlSuffix"; // default next action is to show the report for the current list

$allclear=FALSE;

$action=$_REQUEST['action'];
if (isset($_REQUEST['delete']))
    $action=(($values['itemId'])?'item':'list').'delete';
else if (isset($_REQUEST['listclear']))
    $action='listclear';
else if (isset($_REQUEST['ignoreclear']))
    $action='ignoreclear';
else if (isset($_REQUEST['allclear'])) {
    $action='listclear';
    $allclear=TRUE;
    }
else if (isset($_REQUEST['reset']))
    $action='reset';
else if (isset($_REQUEST['clearitemlists']))
    $action='clearitemlists';

if (isset($_POST['instanceId'])) $values['instanceId'] = $_POST['instanceId'];

if(isset($_REQUEST['source'])) $nextURL="itemReport.php?itemId=" . $_REQUEST['itemId'];
        
if(isset($_REQUEST['matrix'])) $nextURL=$_SERVER['HTTP_REFERER']; //"matrix.php";

repeat:
    
switch ($action) {
    //-----------------------------------------------------------------------------------
    case 'itemcreate':
        $values['item']=$_POST['title'];
        $values['notes']=$_POST['notes'];
        $values['hyperlink']=$_POST['hyperlink'];
        $values['checked']='n';
        $values['dateCompleted']='NULL';
        $result = query("new{$check}listitem",$config,$values);
        if ($result) {
            $msg="Created";
            if (!empty($_REQUEST['again'])) $nextURL="editListItems.php?id={$values['id']}&$urlSuffix";
        } else {
            $msg="Failed to create";
            $nextURL="listLists.php?id={$values['id']}&$urlSuffix";
        }
        if ($check) {
            $values['lastId'] = $GLOBALS['lastinsertid'];
            $result = query("instanceselectbox",$config,$values);
            foreach ((array) $result as $res) {
                $values['instanceId'] = $res['instanceId'];
                $query = query("newchecklistiteminst",$config,$values);
            }
        }
        $_SESSION['message'][]="$msg {$check}list item: '{$values['item']}'";
        break;
    //-----------------------------------------------------------------------------------
    case 'itemdelete':
        $result=query("delete{$check}listitem",$config,$values);
        if ($check) $result=query("deletechecklistiteminst",$config,$values);
        $_SESSION['message'][]="Deleted {$check}list item: '{$_POST['title']}'";
        break;
    //-----------------------------------------------------------------------------------
    case 'itemedit':
        
        $values['item']=$_POST['title'];
        $values['notes']=$_POST['notes'];
        $values['hyperlink']=$_POST['hyperlink'];
        if ($isChecklist) {
            $values['checked']=(isset($_POST['checked']))?'y':'n';
            if (isset($_POST['ignored'])) {
                $values['ignored'] = $_POST['ignored'];
            } else {
                echo 'error ignored not posted';die;
            }
        }
        else
            $values['dateCompleted']=(empty($_POST['dateCompleted']))?'NULL':"'{$_POST['dateCompleted']}'";
        $result=query("update{$check}listitem",$config,$values);
        $msg=($result) ? "Updated" : "No changes needed to";
        $_SESSION['message'][]= "$msg {$check}list item: '{$values['item']}'";
        break;
    //-----------------------------------------------------------------------------------
    case 'listclear':
        if (isset($values['scored']) && $values['scored'] == 'y') {
            query("assesschecklist",$config,$values);
            
            $sep='';
            $ids='';
            foreach ($_POST['completed'] as $id) {
                $ids.=$sep.(int) $id;
                $sep="','";
            }
            $values['itemfilterquery']="'$ids'";
            query("scorechecklist",$config,$values);
            $_SESSION['message'][]='Scores updated';
        }
        query("clearchecklist",$config,$values);
        $_SESSION['message'][]='All checklist items have been unchecked';
        if($allclear) {
            $action='ignoreclear';
            goto repeat;        
        }
        
        break;
    //-----------------------------------------------------------------------------------
    case 'reset':
        query("clearchecklistscore",$config,$values);
            $_SESSION['message'][]='All scores reset';
        break;
    //-----------------------------------------------------------------------------------
    case 'ignoreclear':
        query("clearchecklistignore",$config,$values);
            $_SESSION['message'][]='All ignored checklist items have been unchecked';
        break;
    //-----------------------------------------------------------------------------------
    case 'clearitemlists':
        $values['type'] = $_POST['type'];
        query("clearitemlists",$config,$values);
        $_SESSION['message'][]="0 {$check}lists";
        break;
    //-----------------------------------------------------------------------------------
    case 'delitemlist':
        $values['listType'] = $_REQUEST['type'];
        $values['parentId'] = $_REQUEST['itemId'];
        $values['listId'] = $_REQUEST['listId'];
        query("delitemlist",$config,$values);
        //file_put_contents ('a.txt',var_dump($values));
        $_SESSION['message'][]="Severed";
        break;
    //-----------------------------------------------------------------------------------
    case 'listupdate':
                die;

        $values['type'] = $_POST['type'];
        /*
        // if dealing with grandchildren 
        if ($_POST['visId'] > 0) {
            $valuesV = array();
            $valuesV['parentId'] = $_POST['visId'];
            $valuesV['itemId'] = $_POST['visId'];
            $valuesV['type'] = $values['type'];
            $resultV = query("getchildlists",$config,$valuesV,$sort);
            // if any grand children, delete those
            if (count($resultV) > 0 && is_array($resultV)) {
                query("clearitemlists",$config,$valuesV);
            }
        } else { */
            query("clearitemlists",$config,$values);
        /* }
        if ($_POST['visId'] > 0) {
            // create grandparent records
            foreach ($_POST['addedList'] as $id) {
                $valuesV['id'] = $id;
                query("newlistparent",$config,$valuesV);
            }
        } 
        if ($_POST['visId'] !== $values['itemId']) { */
            $cnt=0;
            foreach ($_POST['addedList'] as $id) {
                $values['id'] = $id;
                query("newlistparent",$config,$values);
                $cnt++;
            }
            $msg = "$cnt {$check}list";
            if ($cnt!==1) $msg .= 's';
            $_SESSION['message'][]=$msg;
        //}
        break;
    //-----------------------------------------------------------------------------------
    case 'listcomplete':
        if ($isChecklist) {
            query("clearchecklist",$config,$values);
            query("clearchecklistignore",$config,$values);
            if (empty($_POST['completed']) && empty($_POST['ignored'])) {
                $_SESSION['message'][]='All checklist items have been unchecked';
                break;
            }
            $query='checkchecklistitem';
        } else {
            if (empty($_POST['completed'])) break;
            $query="completelistitem";
            if (!isset($values['dateCompleted'])) $values['dateCompleted']="'".date('Y-m-d')."'";
        }
        $sep='';
        $ids='';
        foreach ($_POST['ignored'] as $id) {
            $ids.=$sep.(int) $id;
            $sep="','";
        }
        $values['field'] = 'ignored';
        $values['itemfilterquery']="'$ids'";
        $cnt=query($query,$config,$values);
        $sep='';
        $ids='';
        foreach ($_POST['completed'] as $id) {
            $ids.=$sep.(int) $id;
            $sep="','";
        }
        $values['field'] = 'checked';
        $values['itemfilterquery']="'$ids'";
        $cnt=query($query,$config,$values);
        $msg  = "$cnt {$check}list item";
        if ($cnt!==1) $msg .= 's';
        if ($isChecklist) {
            $msg .= ($cnt!==1) ? ' are' : ' is';
            $msg .= ' now';
        } else {
            $msg .= ($cnt!==1) ? ' have' : ' has';
            $msg .= ' been';
        }
        $msg .= " marked complete";
        $_SESSION['message'][]=$msg;
        break;
    //-----------------------------------------------------------------------------------
    case 'listcreate':
        $values['title'] = $_POST['title'];
        $values['categoryId'] = $_POST['categoryId'];
        $values['premiseA'] = $_POST['premiseA'];
        $values['premiseB'] = $_POST['premiseB'];
        $values['conclusion'] = $_POST['conclusion'];
        $values['behaviour'] = $_POST['behaviour'];
        $values['standard'] = $_POST['standard'];
        $values['conditions'] = $_POST['conditions'];
        $values['metaphor'] = $_POST['metaphor'];
        $values['hyperlink'] = $_POST['hyperlink'];
        $values['sortBy'] = $_POST['sortBy'];
        $values['menu'] = $_POST['menu'];
        if ($check == 'check') {
            $values['frequency'] = $_POST['frequency'];
            $values['prioritise'] = $_POST['prioritise'];
            $values['effort'] = $_POST['effort'];
            if (isset($_POST['scored']) && $_POST['scored'] == 'y') { $values['scored'] = 'y'; } else { $values['scored'] = 'n'; }
            // update also matrixsaveCL.php
        }

        //TOFIX datecompleted, completed
        $result= query("new{$check}list",$config,$values,$sort);
        if ($result) {
            $values['id']=$GLOBALS['lastinsertid'];
            $msg='You can now create items for your newly created';
            $nextURL="editListItems.php?id={$values['id']}&$urlSuffix";
        } else {
            $msg='Failed to create';
            $nextURL="listLists.php?$urlSuffix";
        }
        $_SESSION['message'][]="$msg {$check}list: '{$values['title']}'";
        break;
    //-----------------------------------------------------------------------------------
    case 'listdelete':
        $values['type'] = $_REQUEST['type'];
        $values['id'] = $_REQUEST['id'];
        if ($values['type'] !== 'c' && $values['type'] !== 'l') die;
        query("deletelistlookup",$config,$values);
        $values['itemType'] = $values['type'];
        $values['itemId'] = $values['id'];
        query("deletequalities",$config,$values);
        query("delete{$check}list",$config,$values);
        $numDeleted=query("remove{$check}listitems",$config,$values);
        $msg="Deleted {$check}list '{$_REQUEST['title']}'";
        if ($numDeleted) {
            $msg.=" and its $numDeleted item";
            if ($numDeleted>1) $msg.='s';
        }
        $_SESSION['message'][]=$msg;
        $nextURL="listLists.php?$urlSuffix";
        break;
    //-----------------------------------------------------------------------------------
    case 'listedit':
        $values['title'] = $_POST['title'];
        $values['categoryId'] = $_POST['categoryId'];
        $values['premiseA'] = $_POST['premiseA'];
        $values['premiseB'] = $_POST['premiseB'];
        $values['conclusion'] = $_POST['conclusion'];
        $values['behaviour'] = $_POST['behaviour'];
        $values['standard'] = $_POST['standard'];
        $values['conditions'] = $_POST['conditions'];
        $values['metaphor'] = $_POST['metaphor'];
        $values['hyperlink'] = $_POST['hyperlink'];
        $values['sortBy'] = $_POST['sortBy'];
        $values['menu'] = $_POST['menu'];
        if ($check == 'check') {
            $values['frequency'] = $_POST['frequency'];
            $values['effort'] = $_POST['effort'];
            if (isset($_POST['prioritise'])) { $values['prioritise'] = $_POST['prioritise']; } else { $values['prioritise'] = -1; }
            if (isset($_POST['scored']) && $_POST['scored'] == 'y') { $values['scored'] = 'y'; } else { $values['scored'] = 'n'; }
            // update also matrixsaveCL.php
        }
        $result=query("update{$check}list",$config,$values);
        if ($check == 'check') include('matrixsaveCL.php');
        $msg=($result) ? "Updated" : "No changes needed to";
        $_SESSION['message'][]= "$msg {$check}list: '{$values['title']}'";
//        echo '<pre>';var_dump($result);die;
        break;
    //-----------------------------------------------------------------------------------
    default:
        break;
}

nextScreen($nextURL);
// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
