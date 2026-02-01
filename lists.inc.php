<?php
$isChecklist=(isset($_REQUEST['type']) && ($_REQUEST['type']==='C' || $_REQUEST['type']==='c'));
if ($isChecklist) {
    $type='c';
    $check='check';
} else {
    $type='l';
    $check='';
}

$values=array(
     'listId'        => (isset($_REQUEST['listId']))         ? (int) $_REQUEST['listId'] : 0
    ,'itemId'    => (empty($_REQUEST['itemId']))     ? '' : $_REQUEST['itemId']
    ,'instanceId'=> (isset($_REQUEST['instanceId']) && is_numeric($_REQUEST['instanceId'])) ? $_REQUEST['instanceId']:''
    ,'categoryId'=> (isset($_REQUEST['categoryId'])) ? (int)$_REQUEST['categoryId']:0
		,'catcodeId'=> (isset($_REQUEST['catcodeId'])) ? (string)$_REQUEST['catcodeId']:FALSE
    ,'scored'=> (isset($_REQUEST['scored'])) ? $_REQUEST['scored']:'n'
    ,'prioritise'=> (isset($_REQUEST['prioritise'])) ? $_REQUEST['prioritise']:'n'
    );

#debug
// #if(isset($_REQUEST['listId'])) unset($_REQUEST['listId']);
// echo "<pre>_GET ";
// var_dump($_GET);
// echo "_REQUEST ";
// var_dump($_REQUEST);
// echo "values ";
// var_dump($values);
// die;

# workaround for office network
#if(isset($_GET['listId'])) $values['listId'] = $_GET['listId'];

$urlSuffix="type=$type";
if (isset($_REQUEST['instanceId'])) $urlSuffix .= '&instanceId=' . $_REQUEST['instanceId'];

if (isset($_REQUEST['instanceId']) && is_numeric($_REQUEST['instanceId'])) $isInst = TRUE;
else $isInst = FALSE;

if(isset($_REQUEST['display']) && $_REQUEST['display']==='true') {
    $display = true;
} else {
    $display = false;
}
#echo $_REQUEST['listId']; var_dump($values);die;

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
