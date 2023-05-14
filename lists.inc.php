<?php
$isChecklist=(isset($_GET['type']) && ($_GET['type']==='C' || $_GET['type']==='c'));
if ($isChecklist) {
    $type='c';
    $check='check';
} else {
    $type='l';
    $check='';
}

#debug
// if(isset($_REQUEST['id'])) unset($_REQUEST['id']);
// echo "<pre>";
// var_dump($_GET);
// var_dump($_REQUEST); #die;

$values=array(
     'id'        => (isset($_GET['id']))         ? (int) $_GET['id'] : 0
    ,'itemId'    => (empty($_GET['itemId']))     ? '' : $_GET['itemId']
    ,'instanceId'=> (isset($_GET['instanceId']) && is_numeric($_GET['instanceId'])) ? $_GET['instanceId']:''
    ,'categoryId'=> (isset($_GET['categoryId'])) ? (int)$_GET['categoryId']:0
		,'catcodeId'=> (isset($_GET['catcodeId'])) ? (string)$_GET['catcodeId']:FALSE
    ,'scored'=> (isset($_GET['scored'])) ? $_GET['scored']:'n'
    ,'prioritise'=> (isset($_GET['prioritise'])) ? $_GET['prioritise']:'n'
    );

$urlSuffix="type=$type";
if (isset($_GET['instanceId'])) $urlSuffix .= '&instanceId=' . $_GET['instanceId'];

if (isset($_GET['instanceId']) && is_numeric($_GET['instanceId'])) $isInst = TRUE;
else $isInst = FALSE;

if(isset($_GET['display']) && $_GET['display']==='true') {
    $display = true;
} else {
    $display = false;
}
#echo $_GET['id']; var_dump($values);die;

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
