<?php

// get active projects
$values['type']= "p";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values)
        ." AND ".sqlparts("pendingitems",$config,$values)
        //." AND title NOT LIKE '~%'"
        ;
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$pres = query("getitems",$config,$values,$sort);

// eliminate items with inactive visions from array
$values['type']= "v";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values);
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$vres = query("getitems",$config,$values,$sort);
$i=0;
//echo '<pre>'; var_dump($pres);
foreach ((array) $pres as $p) {
    $active = false;
    $values['itemId'] = $p['itemId'];
    $lu = query("lookupparentshort",$config,$values,$sort);
    foreach ((array) $lu as $mat) {
      if ($mat == 0) continue;
        foreach ($vres as $v) { // has active vision as parent?
            if ($mat['parentId'] == $v['itemId']) {
                $active = true;
                break 2;
            }
        }
        $values['itemId'] = $mat['parentId'];
        $lup = query("lookupparentshort",$config,$values,$sort);
        foreach ((array) $lup as $matp) { // has project as parent that has active vision parent?
          if ($matp == 0) continue;
            foreach ($vres as $v) {
                if ($matp['parentId'] == $v['itemId']) {
                    $active = true;
                    break 3;
                }
            }
        }
    }
    if (!$active) {
        unset($pres[$i]);
    }
    $i++;
}
$pres = array_values($pres);
$activeVis = '(';
foreach ((array) $pres as $p) $activeVis .= $p['itemId'] . ',';
$activeVis .= ')';
$activeVis = str_replace(',)',')',$activeVis);

function makeContextRow($row) {
    global $config;
    $rowout=array();
    $rowout['itemId']=$row['itemId'];
    $rowout['description'] = '<div contenteditable="true"' . ajaxUpd('itemDescription',$row['itemId']) . '>' . $row['description'] . '</div><br>' . faLink($row['hyperlink']);
	$rowout['repeat'] = ($row['repeat']=="0")?'&nbsp;':$row['repeat'];
    if($row['deadline']) {
        $deadline=prettyDueDate($row['deadline'],$config['datemask'],$row['suppress'],$row['suppressIsDeadline']);
        $rowout['deadline'] =$deadline['date'];
        $rowout['deadline.class']=$deadline['class'];
        $rowout['deadline.title']=$deadline['title'];
    } else $rowout['deadline']='';
    $rowout['title']=$row['title'];
    $rowout['title.title']='Edit';
	$rowout['ptitle']=$row['ptitle'];
	$rowout['parentId']=$row['parentId'];
	if ($row['parentId']=='') $rowout['parent.class']='noparent';
	$rowout['checkboxname']='isMarked[]';
	$rowout['checkbox.title']='Mark as complete';
	$rowout['checkboxvalue']=$row['itemId'];
    $rowout['NA'] = $row['NA'];
    return $rowout;
}
function makeContextTable($maintable) {
    global $dispArray,$show,$config,$sort,$values,$linkedItems;
    ob_start();
    require('displayItems.inc.php');
    $out=ob_get_contents();
    ob_end_clean();
    return $out;
}
$values=array();

//SQL CODE AREA
//obtain all contexts
$contextResults = query("getspacecontexts",$config,$values,$sort);
$contextNames=array(0=>'none');
if ($contextResults)
    foreach ($contextResults as $row)
	   $contextNames[(int) $row['contextId']]=makeclean($row['name']);

//obtain all timeframes
$values['type']='a';
$values['timefilterquery'] = ($config['useTypesForTimeContexts'])?" WHERE ".sqlparts("timetype",$config,$values):'';
$timeframeResults = query("gettimecontexts",$config,$values,$sort);
$timeframeNames=array(0=>'none');
$timeframeDesc=array(0=>'none');
if ($timeframeResults) foreach($timeframeResults as $row) {
	$timeframeNames[(int) $row['timeframeId']]=makeclean($row['timeframe']);
	$timeframeDesc[(int) $row['timeframeId']]=makeclean($row['description']);
	}

//obtain all active item timeframes and count instances of each
$NAfilter='isNA'.(($config["contextsummary"] === 'nextaction')?'only':'');
$values['filterquery'] = sqlparts($NAfilter,$config,$values);
$values['extravarsfilterquery'] =sqlparts("getNA",$config,$values);;

$thisurl=parse_url($_SERVER['PHP_SELF']);
$dispArray=array('parent'=>'Project'
    ,'NA'=>'NA'
    ,'title'=>'Action'
    ,'description'=>'Description'
    ,'deadline'=>'Deadline'
//    ,'repeat'=>'Repeat'
    ,'checkbox'=>'Complete');
$show=array();
foreach ($dispArray as $key=>$val) $show[$key]=true;

$wasNAonEntry=array();

if (isset($_GET['notContext'])) {
    $notContext = $_GET['notContext'];
} else {
    $notContext = 0;
}

if (isset($_GET['isContext'])) {
    $isContext = $_GET['isContext'];
} else {
  $isContext = FALSE;
}
$inContext = "(";
foreach ($contextResults as $c) {
    if ($c["contextId"] !== $notContext
      && (!$isContext || $c["contextId"] == $isContext))
        $inContext .= $c["contextId"] . ",";
}
$inContext .= "0)";
$values['type'] = "a";
$values['isSomeday'] = "n";
// var_dump($activeVis);die;
$values['childfilterquery']  = " WHERE ".sqlparts("typefilter",$config,$values)
                            ." AND ia.contextId in ". $inContext
                            ." AND lu.`parentId` in ". $activeVis
                            ." AND ".sqlparts("activeitems",$config,$values)
                            ." AND ".sqlparts("issomeday",$config,$values)
                            ." AND ".sqlparts("pendingitems",$config,$values);
$values['filterquery']      .=' WHERE '.sqlparts("liveparents",$config,$values);;
$tstsort=array('getitemsandparent'=>'NA DESC, cname ASC,timeframeId ASC,'.$sort['getitemsandparent']);
$result = query("getitemsandparent",$config,$values,$tstsort);

$grandtot=count($result);
$index=0;
$lostitems=array();
//Item listings by context and timeframe
foreach ($contextNames as $cid=>$dummy1) {
    foreach ($timeframeNames as $tid=>$dummy2) {
        $maintable=array();
        $wasNAonEntry[$cid][$tid]=array();
        while ($index<$grandtot
                && (    !array_key_exists((int) $result[$index]['contextId'],$contextNames)
                     || !array_key_exists((int) $result[$index]['timeframeId'],$timeframeNames))) {
            array_push($lostitems,$result[$index++]);
        }
		while ($index<$grandtot &&
                (int) $result[$index]['contextId']===$cid &&
                (int) $result[$index]['timeframeId']===$tid ) {
            $row=$result[$index];
            if ($row['NA']) array_push($wasNAonEntry[$cid][$tid],$row['itemId']);
            array_push($maintable,makeContextRow($row));
            $index++;
		}
		$matrixcount[$cid][$tid]=count($maintable);
        if (count($maintable))
            $matrixout[$cid][$tid]=makeContextTable($maintable);
    }
}
$_SESSION['lastfilterp']=$_SESSION['lastfiltera']=basename($thisurl['path']);
if (count($lostitems)) {
    $cid='-1';
    $tid=0;
    $wasNAonEntry[$cid][$tid]=array();
    foreach ($timeframeNames as $thistid=>$dummy1) $matrixcount[$cid][$thistid]=0;
    $contextNames[$cid]="ERROR: Failed to find context";
    $maintable=array();
    $dispArray['spatialcontext']='Context Id';
    $dispArray['timeframe']='Timeframe Id';
    $show['spatialcontext']=true;
    $show['timeframe']=true;
    foreach ($lostitems as $row) {
        $rowout=makeContextRow($row);
        $thisCname=(array_key_exists($row['contextId'],$contextNames))
                    ? $contextNames[$row['contextId']]
                    : 'ERROR unknown space context id='.$row['contextId'];
        $thisTname=(array_key_exists($row['timeframeId'],$timeframeNames))
                    ? $timeframeNames[$row['timeframeId']]
                    : 'ERROR unknown time context id='.$row['timeframeId'];

        $rowout['spatialcontext']   =$thisCname;
        $rowout['spatialcontextId'] =$row['contextId'];
        $rowout['timeframe']        =$thisTname;
        $rowout['timeframeId']      =$row['timeframeId'];
        array_push($maintable,$rowout);
        if ($row['NA']) array_push($wasNAonEntry[$cid][$tid],$row['itemId']);
    }
    $matrixcount[$cid][$tid]=count($maintable);
    $matrixout[$cid][$tid]=makeContextTable($maintable);
}
// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
