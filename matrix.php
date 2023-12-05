<?php
/* fixed header
<section class="">
  <div class="container">
*/

//INCLUDES
include_once('header.php');

// empty any null records
$values = array();
$result = query("nullqualities",$config,$values,$sort);

//set item labels
$typename=array();
$typename=getTypes();

$variables = array(

    );

/*
$childtype=array();
$childtype[0]='';
$childtype[1]='s';
*/

// capture url
$url = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
if (strpos($url,'matrix.php?') === false) $url = str_replace('matrix.php', 'matrix.php?', $url);

// limit qualities
if (isset($_GET['qLimit'])) { $qLimit = $_GET['qLimit']; } else { $qLimit = 'a'; }

// limit display
if (isset($_GET['nometa']) && $_GET['nometa'] == true) { $meta = false; } else { $meta = true; }
if (isset($_GET['scen']) && $_GET['scen'] == true) { $scen = true; } else { $scen = false; }
if (isset($_GET['calc']) && $_GET['calc'] == true) { $calc = 'true'; } else { $calc = 'false'; }
if (isset($_GET['data']) && $_GET['data'] == true) { $data = true; } else { $data = false; }
if (isset($_GET['career']) && $_GET['career'] == true) { $career = true; } else { $career = false; }

// call qualities angles
$values = array();
$values['qQuery'] = "`qType`";
$values['qValue'] = 'angle';
$values['qSearch'] = "`disp`";
$values['qNeedle'] = $qLimit;
$angles = query("getqualities",$config,$values,$sort);

// call angles qualities
$i = 0;
$attrStat = array();
foreach ((array) $angles as $row) {
    $values['qQuery'] = "`parId`";
    $values['qValue'] = $row['qId'];
    $qualities = query("getqualities",$config,$values,$sort);

    // call qualities attributes
    $j = 0;
    foreach ((array) $qualities as $rowN) {
        $values['qValue'] = $rowN['qId'];
        $qualities[$j]['attributes'] = query("getqualities",$config,$values,$sort);
        foreach ((array) $qualities[$j]['attributes'] as $attr) $attrStat[] = $attr;
        $j++;
    }
    $angles[$i]['qualities'] = $qualities;
    $i++;
}

// remove attributes referring to existing tables
// build array of non editable cell types
$nonEdit = array();
$i = 0;
foreach ((array) $attrStat as $attr) {
    if (isset($attr['existTable'])) $attrStat[$i]['existTable'] = unserialize($attr['existTable']); // values in other db tables
    if (isset($attr['typeNoEd'])) $nonEdit[] = array('qId' => $attr['qId'], 'typeNoEd' => $attr['typeNoEd']); // non-editable attributes
    $i++;
}
//echo '<pre>';var_dump($nonEdit);die;

// retrieve any vision attributes
$visAttr = array();
foreach ((array) $attrStat as $attr) {
    if (
				!empty($attr) &&
        !isset($attr['existTable']) &&
				(!isset($attr['typeNoEd']) ||
        strpos($attr['typeNoEd'],'v') === false
				)
    ) $visAttr[] = $attr['qId'];
}

// store timeline variables
$tLimit = '';
foreach ((array) $attrStat as $attr) {
    if (!empty($attr) && !empty($attr['format']) && $attr['format'] == 'unqtimeline') $tLimit .= $attr['qId'] . ',';
}
$tLimit = substr($tLimit, 0, -1);

// special itemMeta
$values['qQuery'] = "`qType`";
$values['qValue'] = 'itemMeta';
$attrMeta = query("getqualities",$config,$values,$sort);

// special variables
$values['qQuery'] = "`qType`";
$values['qValue'] = 'variable';
$attrVar = query("getqualities",$config,$values,$sort);

foreach ((array) $attrVar as $row) {
    if ($row['format'] == 'unqhoursyear') $unqhoursyear = $row['title'];
    if ($row['format'] == 'unqhoursyearbrainless') $unqhoursyearbrainless = $row['title'];
    if ($row['format'] == 'unqcorrelpref') $unqcorrelpref = $row['title'];
    if ($row['format'] == 'unqcorrelbala') $unqcorrelbala = $row['title'];
}

$values = array();
$values['itemType'] = 'v';
$visAttrRes = query("lookupqualities",$config,$values,$sort);

$mxTitle = 'Matrix';
if ($qLimit == 'h') {
    $values = array();
    $values['qaId'] = 1;
    $res = query("lookupqualities",$config,$values,$sort);
    foreach ((array) $res as $r) $mxTitle = $r['value'];
}

require_once("headerHtml.inc.php");

$thisurl=parse_url($_SERVER['PHP_SELF']);
$thisfile=makeclean(basename($thisurl['path']));

$dispArray=array();

if (!$data) $dispArray['toggle']='';
/*
$dispArray['vision']='Vision';
$dispArray['role']='Role';
$dispArray['goal']='Goal';
$dispArray['project']='Project';
*/
$dispArray['item'] = 'Item';
if ($qLimit == 'h') {
    $values = array();
    $values['qaId'] = 2;
    $res = query("lookupqualities",$config,$values,$sort);
    foreach ((array) $res as $r) $nbsp = $r['value'];
    $i = 0;
    while ($i < $nbsp) { $dispArray['item'] .= "&nbsp;"; $i++; }
}
if (!$data) $dispArray['items']='Items';
if ($data) {
    $dispArray['type']='Type';
    $dispArray['visId']='visId';
    $dispArray['pId']='parentId';
    $dispArray['itemId']='itemId';
}
$dispArray['someday']='Sday';
$dispArray['complete']='Comp';
foreach ((array) $angles as $angle) {
    foreach ((array) $angle['qualities'] as $qual) {
        foreach ((array) $qual['attributes'] as $attr) {
            $dispArray['attr ' . $attr['qId']] = $attr['title'];
        }
    }
}

foreach ((array) $dispArray as $key=>$val) $show[$key]=true;

$i=0;
$maintable=array();

// limit visions
if (isset($_GET['live']) && $_GET['live'] == true) { $live = true; } else { $live = false; }
if (isset($_GET['test']) && $_GET['test'] == true) { $test = true; } else { $test = false; }
if (isset($_GET['vLimit'])) { $vLimit = $_GET['vLimit']; } else { $vLimit = false; }

$values = array();
$values['filterquery'] = ' WHERE ia.type = "v" ';
if ($test) $values['filterquery'] .= ' AND i.title = "a test" ';
if ($live) $values['filterquery'] .= ' AND ia.isSomeday = "n" AND its.dateCompleted IS NULL ';
if ($vLimit) $values['filterquery'] .= ' AND i.itemId = ' . $vLimit;
$altsort['getitems'] = $sort['getitemsvisn'];
$vis=query("getitems",$config,$values,$altsort);
$visIds = array();

// limit visions to calculate
$vLimit = '';
foreach ((array) $vis as $visn) {
    $visIds[] = $visn['itemId'];
    $vLimit .= $visn['itemId'] . ',';
}
$vLimit = substr($vLimit, 0, -1);

foreach ((array) $vis as $visn) {

    $maintable[$i]['mx']=true;
    $maintable[$i]['toggle']='+';
    $maintable[$i]['itemId']=$visn['itemId'];
    $maintable[$i]['type']='v';
    $maintable[$i]['item']= $visn['title'];
    $maintable[$i]['items']=
                    childUpd('o',$visn['itemId'],$visn['itemId'],$visn['categoryId']) . '&nbsp;' .
                    childUpd('g',$visn['itemId'],$visn['itemId'],$visn['categoryId']) . '&nbsp;' .
                    childUpd('p',$visn['itemId'],$visn['itemId'],$visn['categoryId']) . '&nbsp;' .
                    childUpd('c',$visn['itemId'],$visn['itemId']/*,$visn['categoryId']*/) . '&nbsp;' .
                    childUpd('l',$visn['itemId'],$visn['itemId']/*,$visn['categoryId']*/) . '&nbsp;' .
                    '<a href="itemReport.php?itemId=' . $visn['itemId'] . '&convert=true" target="_blank" class="mx">E</a>&nbsp;' .
                    '<a href="' . $url . '&vLimit=' . $visn['itemId'] . '" target="_blank" class="mx">*</a>';
    $flag = 'y';
    if ($visn['dateCompleted']==NULL) {
        $maintable[$i]['someday'] = checkerB('itemattributes','itemId',$visn['itemId'],'isSomeday',$visn['isSomeday']);
        $flag = '';
    }
    $maintable[$i]['complete'] = checkerB('itemstatus','itemId',$visn['itemId'],'dateCompleted',$flag);
    $maintable[$i]['row.class'] = 'vision ' . $visn['itemId'];
    foreach ((array) $visAttr as $attrId) {
        $maintable[$i]['attr ' . $attrId] = '';
        $maintable[$i]['attr ' . $attrId . '  qaId'] = '';
        foreach ((array) $visAttrRes as $res) {
            if ($res['qId'] == $attrId && $res['itemId'] == $visn['itemId']) {
                $maintable[$i]['attr ' . $attrId] = $res['value'];
                $maintable[$i]['attr ' . $attrId . ' qaId'] = $res['qaId'];
                break;
            }
        }
    }

    $i++;

    //RETRIEVE VARIABLES
    $values=array();
    $values['itemId'] = (int) $visn['itemId'];

    //Get item details
    $values['childfilterquery']=' WHERE '.sqlparts('singleitem',$config,$values);
    $values['filterquery']=sqlparts('isNA',$config,$values);
    $values['extravarsfilterquery'] =sqlparts("getNA",$config,$values);;
    $result = query("getitemsandparent",$config,$values,$sort);
    $item = ($result)?$result[0]:array();
    $values['isSomeday']=($item['isSomeday']=="y")?'y':'n';
    $values['type']=$item['type'];

    $pitemId = $values['itemId'];

    $values['parentId']=$values['itemId'];

    $maintable[$i]=array();

    $values['type']='';
    $values['filterquery'] ='';

    //$values['filterquery'] .= " AND ".sqlparts("issomeday",$config,$values);

    $result = query("getchildren",$config,$values,$sort);

    // sort children of vision

    if (!is_array($result)) continue; // vision no children, skip query, avoid error from non array result

    $j = 0;
    foreach ((array) $result as $row) {
        // default
        $result[$j]['pId'] = 0;
        // get parents
        $par = query("lookupparent",$config,$row,$sort);
        // each parent
        foreach ((array) $par as $pars) {
            // research children of vision
            foreach ((array) $result as $rowN) {
                // if that parent is in children of vision
                if ($pars['parentId'] == $rowN['itemId']) {
                    // set parent id
                    $result[$j]['pId'] = $pars['parentId'];
                    break 2;
                }
            }
        }
        $j++;
    }

    foreach ((array) $result as $row) {

        if ($row['pId'] == 0) {
            $fostId = $visn['itemId'];
        } else {
            $fostId = $row['pId'];
        }

        $maintable[$i]['toggle']='<a class="mx ho" onClick="toggleOther(this)">*</a>';
        //'<a href="processItems.php?action=deleteparlookup&itemId=' . $row['itemId'] . '&pId=' . $row['pId'] . '&matrix=true" class="mx" target="_blank">x</a>';

        $maintable[$i]['visId']=$visn['itemId'];
        $maintable[$i]['pId'] = $row['pId'];
        $maintable[$i]['itemId']=$row['itemId'];
        $maintable[$i]['mx']=true;
        $maintable[$i]['type']=$row['type'];
        $maintable[$i]['sortBy']=$row['sortBy'];
//echo '<pre>'; var_dump($row['dateCompleted']);

        $flag = 'y';
        if ($row['dateCompleted']==NULL) {
            $maintable[$i]['someday'] = checkerB('itemattributes','itemId',$row['itemId'],'isSomeday',$row['isSomeday'],'vId',$visn['itemId']);

            $flag = '';
        }
        $maintable[$i]['complete'] = checkerB('itemstatus','itemId',$row['itemId'],'dateCompleted',$flag,'vId',$visn['itemId']);

        //$maintable[$i]['project'] =
        $maintable[$i]['items'] =
                    childUpd('p',$row['itemId'],$visn['itemId'],$row['categoryId']) . '&nbsp;' .
                    childUpd('c',$row['itemId'],$visn['itemId'],$row['categoryId']) . '&nbsp;' .
                    childUpd('l',$row['itemId'],$visn['itemId'],$row['categoryId']) . '&nbsp;' .
                    '<a href="itemReport.php?itemId=' . $row['itemId'] . '&convert=true" target="_blank" class="mx" title="edit">E</a>&nbsp;' .
                    '<a href="processItems.php?action=deleteparlookup&itemId=' . $row['itemId'] . '&pId=' . $fostId . '" target="_blank" class="mx" title="orphan">-</a>';
        switch ($row['type']) {
            case 'o':
                //$maintable[$i]['role']= $row['title'];
                $maintable[$i]['item']= $row['title'];
                $maintable[$i]['row.class'] = 'role v ' . $visn['itemId'] . ' id ' . $row['itemId'];
                break;
            case 'g':
                //$maintable[$i]['goal']= $row['title'];
                $maintable[$i]['item']= $row['title'];
                $maintable[$i]['row.class'] = 'goal v ' . $visn['itemId'] . ' id ' . $row['itemId'];
                break;
            case 'p':
                //$maintable[$i]['project']= $row['title'];
                $maintable[$i]['item']= $row['title'];
                $maintable[$i]['row.class'] = 'project v ' . $visn['itemId'] . ' id ' . $row['itemId'];
                break;

        }

        $i++;

    }
} // end of foreach

//insert lists
// seems inefficient query
$splice = array();
//echo "<pre>a";var_dump($maintable);die;
foreach ((array) $vis as $visn) {
    $mainsearch = array();
    foreach ((array) $maintable as $r) {
        if ($r['visId'] == $visn['itemId']) {
            $values['parentId'] = $r['itemId'];
            $values['type'] = 'c';
            $clists = query("getchildlists",$config,$values,$sort);
            $values['type'] = 'l';
            $lists = query("getchildlists",$config,$values,$sort);
						if (!empty($lists) && is_array($lists) && count($lists) > 0 && !empty($clists) && is_array($clists) && count($clists) > 0) {
				        $lists = array_merge($clists,$lists);
				    } elseif (!empty($clists) && is_array($clists) && count($clists) > 0) {
				        $lists = $clists;
				    }
						if (!empty($lists) && is_array($lists) && count($lists) > 0) $mainsearch[$r['itemId']] = $lists;
            //if ($r['itemId'] == 10389) { echo '<pre>';var_dump($lists);die; }
        }
    }
    //echo '<pre>';var_dump($mainsearch);die;

    $values = array();
    $values['parentId'] = $visn['itemId'];
    $values['type'] = 'c';
    $clists = query("getchildlists",$config,$values,$sort);
    $values['type'] = 'l';
    $lists = query("getchildlists",$config,$values,$sort);
		if (!empty($lists) && is_array($lists) && count($lists) > 0 && !empty($clists) && is_array($clists) && count($clists) > 0) {
				$lists = array_merge($clists,$lists);
		} elseif (!empty($clists) && is_array($clists) && count($clists) > 0) {
				$lists = $clists;
		}
		if (!empty($lists) && is_array($lists) && count($lists) > 0) {
        foreach ((array) $lists as $list) {
        $pId = 0;
            $values['listId'] = $list['listId'];
            $values['type'] = $list['type'];
            if ($list['type'] == 'c') { $qry = "selectchecklist"; } else { $qry = "selectlist"; }
            $listN = query($qry,$config,$values,$sort);
            foreach ((array) $mainsearch as $key=>$s) {
                foreach ((array) $s as $l) {
                    if ($l['listId'] == $list['listId'] && $l['type'] == $list['type']) {
                        $pId = $key;
                        break 2;
                    }
                }
            }
            $maintable[] = array(
                'visId' => $visn['itemId'],
                'pId' => $pId,
                'itemId' => $list['listId'],
                'mx' => true,
                'type' => $list['type'],
                'item' => $listN[0]['title'] . ($list['type'] == 'c' ? ' .CL' : ' .LIST'),
                'row.class' => ($list['type'] == 'c' ? 'check' : '') . 'list v ' . $visn['itemId'] . ' listId ' . $list['listId'],
                'toggle' => '<a class="mx ho" onClick="toggleOther(this)">*</a>',
                'items' => '<a href="reportLists.php?listId=' . $list['listId'] . '&type=' . $list['type'] . '" target="_blank" class="mx">E</a>',
                'sortBy' => $listN[0]['sortBy']
            );
        }
    }
}

$maintemp = array();

// row sort
$parentTypes = array('g','o','p');
foreach ((array) $maintable as $row) {
    // loop by vision
    if ($row['type'] == 'v') {
        // append vision
        $maintemp[] = $row;

        // create array of visions children
        $mainsearch = array();
        foreach ((array) $maintable as $r) {
            if ($r['visId'] == $row['itemId']) $mainsearch[] = $r;
        }

        // sort visions children by sortBy then title
        usort($mainsearch, function($a, $b) {
            $sortBy = strcmp($a['sortBy'], $b['sortBy']);
            // second level sort by title not working...
            if($sortBy === 0) {
								if ($a['item'] > $b['item']) {
									return 1;
								} else if ($a['item'] < $b['item']) {
									return -1;
								} else {
									return 0;
								}
                #return $a['item'] - $b['item'];
            }
            return $sortBy;
        });

        //echo '<pre>';var_dump($mainsearch);die;
        // loop visions children
        // assume that any item that is not top level must have a parent or ancestor at the top level
        foreach ((array) $mainsearch as $s) {
            // if vision child is top level (has no parent) then set no indent and append
            if ($s['pId'] == 0) {
                $s['indent'] = 0;
                $s['row.class'] .= ' i0';
                $maintemp[] = $s;
                // only allow certain item types to prevent checklist with same itemId as gop triggering as a parent
                if (in_array($s['type'], $parentTypes)) {
                    // search if item has any children and append
                    foreach ((array) $mainsearch as $t) {
                        if ($t['pId'] == $s['itemId']) {
                            $t['indent'] = 1;
                            $t['row.class'] .= ' i1';
                            $maintemp[] = $t;
                            if (in_array($t['type'], $parentTypes)) {
                                foreach ((array) $mainsearch as $u) {
                                    if ($u['pId'] == $t['itemId']) {
                                        $u['indent'] = 2;
                                        $u['row.class'] .= ' i2';
                                        $maintemp[] = $u;
                                        if (in_array($u['type'], $parentTypes)) {
                                            foreach ((array) $mainsearch as $v) {
                                                if ($v['pId'] == $u['itemId']) {
                                                    $v['indent'] = 3;
                                                    $v['row.class'] .= ' i3';
                                                    $maintemp[] = $v;
                                                    if (in_array($v['type'], $parentTypes)) {
                                                        foreach ((array) $mainsearch as $w) {
                                                            if ($w['pId'] == $v['itemId']) {
                                                                $w['indent'] = 4;
                                                                $w['row.class'] .= ' i4';
                                                                $maintemp[] = $w;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!$data) $maintemp[]['row.class'] = 'blank v ' . $visId; //blank row
    }
}
//echo '<pre>'; var_dump($maintable);var_dump($maintemp);die;
$maintable = $maintemp;

// append qualities and vision formulae
$i = 0;
foreach ((array) $maintable as $row) {
    if (isset($row['visId'])) {
        $values = array();
        $values['visId'] = $row['visId'];
        $values['itemId'] = $row['itemId'];
        $values['itemType'] = $row['type'];
        //create blank
        $result = query("lookupqualities",$config,$values,$sort);
        // create blank table rows
        foreach ((array) $angles as $angle) {
            foreach ((array) $angle['qualities'] as $quals) {
                foreach ((array) $quals['attributes'] as $attr) {
                    $exist = false;
                    foreach ((array) $result as $res) {
                        if ($attr['qId'] == $res['qId']) {
                            $exist = true;
                            $maintable[$i]['attr ' . $res['qId']] = $res['value'];
                            $maintable[$i]['attr ' . $res['qId'] . ' qaId'] = $res['qaId'];
                        }
                    }
                    if (!$exist) {
                        $maintable[$i]['attr ' . $attr['qId']] = '';
                        $maintable[$i]['attr ' . $attr['qId'] . ' qaId'] = '';
                        /*
                        $sql = "
                            INSERT INTO ". $config['prefix'] ."lookupqualities
                            (`qaId`,`visId`,`itemId`,`qId`,`itemType`,`value`)
        				    VALUES (''," . $row['visId'] . "," . $row['itemId'] . "," . $attr['qId'] . ",'" . $row['type'] . "','')
        				";
                        doQuery($config, $sql);
                        */

                    }
                }
            }
        }
        // fill existing
		$result = query("lookupqualities",$config,$values,$sort);
        foreach ((array) $result as $res) {
            $maintable[$i]['attr ' . $res['qId']] = $res['value'];
            $maintable[$i]['attr ' . $res['qId'] . ' qaId'] = $res['qaId'];
        }
    }
    $i++;
}

/* orphaned items */
if (isset($_GET['orphans']) && $_GET['orphans'] == true) {

    $values = array();
    $values['filterquery'] = " WHERE ia.type IN ('o','g','p')";
    $orphans=query("getitemsattr",$config,$values,$sort);
    $lu = query("lookuparray",$config,$values,$sort);
    $luc = array();
    foreach ((array) $lu as $l) {
        // create array of items with vision parents
        if (in_array($l['parentId'],$visIds)) $luc[] = $l;
    }
    $k=0;
    foreach ((array) $orphans as $orphan) {
        // remove non o/g/p from orphan array
        if (!in_array($orphan['type'],array('o','g','p'))) {
            unset($orphans[$k]);
        }
        $k++;
    }
    $orphans = array_values($orphans);
    $fosters = array();
    $k=0;
    foreach ((array) $orphans as $orphan) {
        foreach ((array) $luc as $l) {
            // if item has vision parent
            if ($l['itemId'] == $orphan['itemId']) {
                // remove from orphan array
                unset($orphans[$k]);
                // note item as possible foster parent
                $fosters[] = $l;
                // stop search
                break;
            }
        }
        $k++;
    }
    // second level foster parent check
/*    foreach ((array) $orphans as $orphan) {
        foreach ((array) $lu as $l) {
            foreach ((array) $fosters as $f) {
                // if item has foster parent, it is also a possible foster parent
                if ($f['itemId'] == $l['parentId'] && $orphan['itemId'] == $l['itemId']) {
                    // note item as possible foster parent
                    $fosters[] = array('parentId' => $f['parentId'], 'itemId' => $l['itemId']);
                }
            }
        }
    }
    echo '<pre>'; var_dump($fosters);die;
*/
    // set orphans meta
    $values = array();
    $presort = array();
    $i=0;
    foreach ((array) $orphans as $orphan) {

        $values['itemId'] = $orphan['itemId'];
        $res = query("selectitem",$config,$values,$sort);
        $row = $res[0];
        //var_dump($row);var_dump($res);die;

        $presort[$i]['mx']=false;
        $presort[$i]['itemId']=$row['itemId'];
        $presort[$i]['type']=$row['type'];

        $flag = 'y';
        if ($row['dateCompleted']==NULL) {
            $presort[$i]['someday'] = checkerB('itemattributes','itemId',$row['itemId'],'isSomeday',$row['isSomeday']);
            $flag = '';
        }
        $presort[$i]['complete'] = checkerB('itemstatus','itemId',$row['itemId'],'dateCompleted',$flag);

        $title = $row['title'];// . '&nbsp;&nbsp;<a href="itemReport.php?itemId=' . $row ['itemId'] . '&convert=true" target="_new" class="mx">ed</a>&nbsp;&nbsp;<a href="processItems.php?itemId=' . $row ['itemId'] . '&action=delete&type=' . $row['type'] . '" target="_new" class="mx">del</a>';

        $presort[$i]['temp'] = $row['title'];

        switch ($row['type']) {
            case 'o':
                //$presort[$i]['role']= $title;
                $presort[$i]['item']= $title;
                $presort[$i]['row.class'] = 'role v ';
                break;
            case 'g':
                //$presort[$i]['goal']= $title;
                $presort[$i]['item']= $title;
                $presort[$i]['row.class'] = 'goal v ';
                break;
            case 'p':
                //$presort[$i]['project']= $title;
                $presort[$i]['item']= $title;
                $presort[$i]['row.class'] = 'project v ';
                break;
        }

        // create meta action for orphans with non-orphan parents
        $str = '';
        $parent = '';
        foreach ((array) $lu as $l) {
            if ($row['itemId'] == $l['itemId']) {
                $parent = $l['parentId'];
                break;
            }
        }
        foreach ((array) $fosters as $f) {
            if ($f['itemId'] == (int)$parent) {
                $str = '&nbsp;<a href="processItems.php?itemId=' . $row ['itemId'] . '&action=parentupdate&type=' . $row['type'] . '&visId=' . $f['parentId'] . '" target="_new" class="mx">FOST</a>';
                break;
            }
        }

        $presort[$i]['items'] =
                    '<a href="processItems.php?itemId=' . $row ['itemId'] . '&action=delete&type=' . $row['type'] . '" target="_new" class="mx">DEL</a>&nbsp;
                    <a href="itemReport.php?itemId=' . $row['itemId'] . '" target="_blank" class="mx">E</a>' . $str;

        $i++;
    }

    // sort order for orphans
    function typeName ($a, $b) {
        if ($a["type"] == $b["type"]) return strcmp($a["temp"], $b["temp"]);
        $order = array('g','o','p');
        $a = array_search($a["type"], $order);
        $b = array_search($b["type"], $order);
        return $a - $b;
    }
    usort($presort, 'typeName');

    $i = count($maintable);
    foreach ((array) $presort as $orphan) {
        $maintable[$i] = $orphan;
        $i++;
    }
}

// capture numeric fields for datatables field type sort
$sortCols = '';
$i = 0;
foreach ((array) $dispArray as $key=>$val) {
    if (strpos($key,'attr ') === false) {
        $i++;
        continue;
    }
    foreach ((array) $attrStat as $attr) {
        if (
            $attr['qId'] == str_replace("attr ", "", $key) &&
            $attr['style'] == 'int'
        ) {
            $sortCols .= $i . ',';
            continue;
        }
    }
    $i++;
}

?>

<script src="js/matrixTable.js"></script>
<script src="js/dataTables.fixedColumns.min.js"></script>
<script src="js/dataTables.fixedHeader.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.colVis.min.js"></script>

<link rel="stylesheet" href="themes/default/dataTables.css" type="text/css" media="Screen" />
<link rel="stylesheet" href="themes/default/fixedColumns.dataTables.min.css" type="text/css" media="Screen" />
<link rel="stylesheet" href="themes/default/fixedHeader.dataTables.min.css" type="text/css" media="Screen" />
<link rel="stylesheet" href="themes/default/buttons.dataTables.min.css" type="text/css" media="Screen" />

<script>

var tableDrawn = false;
if (window.location.href.search('matrix.php') == -1) { var archive = true; } else { var archive = false; }
<?php
    if ($calc == 'true') {  ?>
    var autoCalc = true;
<?php } else { ?>
    var autoCalc = false;
<?php } ?>

function drawTable() {
    $('#filter').DataTable( {
        "fixedHeader": false, // processing power problem
        "fixedColumns": false, // untested
        "paging":   false,
        "ordering": true,
        "order": [], // overrides matrix default sort
        "columnDefs": [
            { "type": "num", "targets": [<?php echo rtrim($sortCols, ','); ?>] }
        ],
        "orderMulti": true, // shift click sequence of columns
        "info": false,
        "searchDelay": 3000,
        "bAutoWidth": false,
        "dom": 'Bfrtip',
        "buttons": []//['columnsToggle']
    } );

    var table = $('#filter').DataTable();

    // Event listener to filtering inputs to redraw on input
    $('.fMin, .fMax, .fTxt, .fEmp, .fChk').keyup( function() {
        filterCol = $(this).attr('name');
        filterType = $(this).attr('class');
        delay(function(){ // variable setting doesn't work after the delay, only call draw within delay cycle
            table.draw();
        }, 3000 );
    });

    //$(".dataTables_filter input").focus();

    tableDrawn = true;
}

/* Custom filtering function which will search data in column and hide rows*/
var filterCol = false;
var filterType = false;
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        switch (filterType) {
        case false:
            return true;
            break;
        case 'fMin':
        case 'fMax':
            var mina = $('#min'+filterCol).val();
            var min = parseFloat( mina, 10 ); // convert to integer
            var max = parseFloat( $('#max'+filterCol).val(), 10 ); // convert to integer
            var col = parseFloat( data[filterCol] ) || null;    // value for each row in column
            if ( data[filterCol] == 0) col = 0; // undo col set null if value is 0
            if ( mina == 'null' ) { // special operand for empty cells
                if (col == null) { return true; } else { return false; }
            }
            if ( mina == 'nnull' ) { // special operand for non-empty cells
                if (col == null) { return false; } else { return true; }
            }
            if ( isNaN( min ) && isNaN( max ) ) return true; // filter cleared, reset filter
            if ( col == null) return false; // field cannot be empty or text
            if (    // positive match parameters
                    ( isNaN( min ) && col <= max ) ||   // min not set, lower than max
                    ( col >= min   && isNaN( max ) ) || // greater than min, max not set
                    ( col >= min   && col <= max )      // between min and max
                ) return true;
            return false;
            break;
        case 'fTxt':
            var txt = $('#txt'+filterCol).val();
            var col = data[filterCol]; // use data for col n
            if (txt.length == 0) return true; // filter cleared
            if (txt == 'null' && col.length == 0) return true; // special operand for empty cells
            if (txt == 'nnull' && col.length > 0) return true; // special operand for non-empty cells
            var txts = txt.split(' ');
            var rnot = [];
            var ror = [];
            var rand = [];
            var qnot = 0;
            var qor = 0;
            var qand = 0;
            qry = '^';
            for (i = 0; i < txts.length; ++i) {
                switch (txts[i].toLowerCase()) {
                    case 'not':
                        qnot++;
                        i++; // skip to next
                        rnot.push(txts[i]); // append next to rnot array
                        break;
                    case 'or':
                        qor++;
                        if (qand) { // check or is not first term
                            ror.push(rand.pop()); // take term from qand array
                            qand--;
                        }
                        i++; // skip to next
                        ror.push(txts[i]); // append next to ror array
                        break;
                    case 'and': // ignore and operator as normal term
                        qand++;
                        break;
                    default:
                        qand++;
                        rand.push(txts[i]); // append to rand array
                }
            }
            if (qnot) {
                for (i = 0; i < rnot.length; ++i) {
                    qry += '(?!.*' + rnot[i] + ')';
                }
            }
            if (ror) {
                qry += '(?=.*(' + ror.join('|') + '))';
            }
            if (qand) {
                for (i = 0; i < rand.length; ++i) {
                    qry += '(?=.*' + rand[i] + ')';
                }
            }
            qry += '.*$';
            // example: ^(?=.*goal)(?=.*32)(?=.*(test|car))(?!.*b).*$
            // in any positions, 'goal' and '32', and 'test' or 'car', and not 'b'
            // alert (qry);
            var txtMatch = new RegExp(qry, 'gim'); // g global i case-insensitive m multi-line
            if (col.match(txtMatch)) return true;
            return false;
            break;
        case 'fEmp':
            var emp = $('#emp'+filterCol).is(':checked');
            alert(emp);
            break;
        case 'fChk':
            var chk = $('#chk'+filterCol).is(':checked');
            alert(chk);
            break;
        }
    }
);

function toggleCol(angleIds) {
    if (angleIds.length < 1) return;
    if ($('table.mx th:nth-child('+angleIds[0]+')').is(':visible')) {
        for (i = 0; i < angleIds.length; ++i) {
            $('table.mx td:nth-child('+angleIds[i]+'),th:nth-child('+angleIds[i]+')').hide();
        }
    } else {
        for (i = 0; i < angleIds.length; ++i) {
            $('table.mx td:nth-child('+angleIds[i]+'),th:nth-child('+angleIds[i]+')').show();
        }
    }
}

$(document).ready(function() {

    calcFormulae('<?php if ($calc == 'true') echo $vLimit; ?>');
    tableSummary();

    if (archive) return;

    <?php
        $col = 1;
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) $col++;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) {
                foreach ((array) $valN['attributes'] as $valO) $col--;
            }
        }
        foreach ((array) $angles as $vala) {
                $commaa = '';
                $commac = '';
                $needle = $qLimit . '0';
                $all = '';
                $chl = '';
                foreach ((array) $vala['qualities'] as $valN) {
                    foreach ((array) $valN['attributes'] as $valO) {
                        $all .= $commaa . "'" . $col . "'";
                        $commaa = ',';
                        if (strpos($valO['disp'], $needle) > -1) {
                            $chl .= $commac . "'" . $col . "'";
                            $commac = ',';
                        }
                        $col++;
                    }
                }
            if (strpos($vala['disp'], $needle) > -1) {
                echo "toggleCol([" . $all . "]);\n\t";
            } else {
                echo "toggleCol([" . $chl . "]);\n\t";
            }
        }

        if ($live) echo "toggleCheckB('someday');toggleCheckB('complete');\n\t";
        if ($career) echo "toggleCheckB('attr.771');\n\t";
        if (!$meta) echo "toggleCol(['3','4','5']);\n\t";

    ?>
/*
    var w = 0;
    $("#filter tr td:first").each(function(){
        if($(this).width() > w){
            w = $(this).width();
        }
    });
*/
});

var folded = false;

function toggleRow(rclasses) {
    if (folded) {
        for (i = 0; i < rclasses.length; ++i) {
            $('table.mx tr.'+rclasses[i]).show();
        }
        folded = false;
    } else {
        for (i = 0; i < rclasses.length; ++i) {
            $('table.mx tr.'+rclasses[i]).hide();
        }
        folded = true;
    }
}

function toggleOther(editableObj) {
    toggleRow(['vision','role','goal','project','checklist','list','blank']);
    $(editableObj).closest('tr').show();
}

function notEditable(tdclasses) {
    for (i = 0; i < tdclasses.length; ++i) {
        $('table.mx td.'+tdclasses[i]).attr('contenteditable','false');
    }
}

function notCheckbox() {
    $('table.mx input[type=checkbox]').attr('disabled',true);
}

var somedayDisp = true;
var completeDisp = true;
var liveDisp = true;
var brainlessDisp = true;
var careerDisp = true;

function toggleCheckB(ctype) {

    // to do: simplify with array of classes with binary values, generate index of arrays using find, process values with criteria
    // assumes each iteration table is searched the index result is the same

    var hideR = false;
    var checkd = true;

    if (ctype == 'live') {
        hideR = liveDisp;
        liveDisp ^= true;
        if (hideR) {
            $('table.mx td.someday').find("input[type='checkbox']").each(function(){
                if (!$(this).parent().parent().hasClass('vision')) {
                    if ($(this).is(':checked')) {
                        $(this).parent().parent().show();
                    } else {
                        $(this).parent().parent().hide();
                    }
                }
            });
            $('table.mx td.complete').find("input[type='checkbox']").each(function(){
                if (!$(this).parent().parent().hasClass('vision')) {
                    if ($(this).is(':checked') || !$(this).parent().parent().is(':hidden')) {
                        $(this).parent().parent().show();
                    } else {
                        $(this).parent().parent().hide();
                    }
                }
            });
        } else {
            $('table.mx td.someday').find("input[type='checkbox']").each(function(){
                if (!$(this).parent().parent().hasClass('vision')) {
                    if (!$(this).is(':checked')) {
                        $(this).parent().parent().show();
                    }
                }
            });
            $('table.mx td.complete').find("input[type='checkbox']").each(function(){
                if (!$(this).parent().parent().hasClass('vision')) {
                    if (!$(this).is(':checked')) {
                        $(this).parent().parent().show();
                    }
                }
            });
        }
        return;
    }

    if (ctype == 'someday') {
        hideR = somedayDisp;
        somedayDisp ^= true;
    }
    if (ctype == 'complete') {
        hideR = completeDisp;
        completeDisp ^= true;
    }
    if (ctype == 'attr.531') {
        hideR = brainlessDisp;
        brainlessDisp ^= true;
        checkd = false;
    }
    if (ctype == 'attr.771') {
        hideR = careerDisp;
        careerDisp ^= true;
        checkd = false;
    }
    if (hideR) {
        $('table.mx td.'+ctype).find("input[type='checkbox']").each(function(){
            if(!$(this).parent().parent().hasClass('vision')){
            if((checkd === true && $(this).is(':checked')) ||
               (checkd === false && !$(this).is(':checked'))
            ) $(this).parent().parent().hide();
            }
        });
    } else {
        $('table.mx td.'+ctype).find("input[type='checkbox']").each(function(){
            if((checkd === true && $(this).is(':checked')) ||
               (checkd === false && !$(this).is(':checked'))
            ) $(this).parent().parent().show();
        });
    }
}

function calcFormulae (vLimit) {

    var tLine = [<?php echo $tLimit; ?>];

    if (autoCalc === false || archive === true) {
        if (!tableDrawn) drawTable();
        return;
    }

    $.ajax({
        url: "matrixformula.php",
        type: "POST",
        data: 'qLimit=<?php echo $qLimit; ?>&vLimit='+vLimit,
        dataType: "json",
        success: function(result) {
            // clear timeline
            $('table.mx').find('tr').find('td.atr.mx.yr').text('');

            // write response
            for (i = 0; i < result.length; ++i) {
                // if (result[i]['value'] == '12345') alert(JSON.stringify(result[i]));
                // check item type and write attribute to table
                switch (result[i]['type']) {
                    case 'x':
                        // check effort warning for timeline cells
                        if ($.inArray(result[i]['attrId'], tLine) != -1) {

                            if (
                                (result[i]['form'] == 'a' && result[i]['value'] > <?php echo $unqhoursyear; ?>) ||
                                (result[i]['form'] == 'b' && result[i]['value'] > <?php echo $unqhoursyearbrainless; ?>)
                            ) $('table.mx').find('tr.summary').find('th.attr.'+result[i]['attrId']+'.'+result[i]['form']).css("background","#f5e6bc");
                            else $('table.mx').find('tr.summary').find('th.attr.'+result[i]['attrId']+'.'+result[i]['form']).css("background","#ecf6e6");

                            if (result[i]['form'] == 'c' && result[i]['value'] > <?php echo $unqhoursyear + $unqhoursyearbrainless; ?>
                            ) $('table.mx').find('tr.summary').find('th.attr.'+result[i]['attrId']).css("background","#edd");

                            if (result[i]['form'] == 'a') result[i]['value'] = Math.round(result[i]['value'] * 100 / <?php echo $unqhoursyear; ?>) / 100;
                            if (result[i]['form'] == 'b') result[i]['value'] = Math.round(result[i]['value'] * 100 / <?php echo $unqhoursyearbrainless; ?>) / 100;

                        }
                        // write value
                        $('table.mx').find('tr.summary').find('th.attr.'+result[i]['attrId']+'.'+result[i]['form']).text(result[i]['value']);
                        break;
                    case 'v':
                        $('table.mx').find('tr.vision.'+result[i]['visId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 'g':
                        $('table.mx').find('tr.goal.v.'+result[i]['visId']+'.id.'+result[i]['itemId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 'o':
                        $('table.mx').find('tr.role.v.'+result[i]['visId']+'.id.'+result[i]['itemId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 'p':
                        $('table.mx').find('tr.project.v.'+result[i]['visId']+'.id.'+result[i]['itemId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 'c':
                        $('table.mx').find('tr.checklist.v.'+result[i]['visId']+'.id.'+result[i]['itemId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 'l':
                        $('table.mx').find('tr.list.v.'+result[i]['visId']+'.id.'+result[i]['itemId']).find('td.attr.'+result[i]['attrId']).text(result[i]['value']);
                        break;
                    case 't':
                        $('table.summary').find('td.fgen').text(result[i]['duration']);
                        break;
                }
            }
            if (!tableDrawn) drawTable();
            <?php if ($data) echo '$("td:empty").text("NA");'; ?>
        }
    });
    autoCalc = false;
}

function tableSummary () {
    var mlive = 0;
    var vlive = 0;
    var olive = 0;
    var glive = 0;
    var plive = 0;

    $('table.mx tr').each(function(){
        var m, v, o, g, p, s = 0;
        $('td', this).each(function(){
           if ($(this).text().trim()!=="" && $(this).hasClass("col-value")) m = 1;
           if ($(this).text().trim()!=="" && $(this).hasClass("col-vision")) v = 1;
           if ($(this).text().trim()!=="" && $(this).hasClass("col-role")) o = 1;
           if ($(this).text().trim()!=="" && $(this).hasClass("col-goal")) g = 1;
           if ($(this).text().trim()!=="" && $(this).hasClass("col-project")) p = 1;
           if ($(this).is(":checked") && $(this).hasClass("col-someday")) s = 1;
        });
        if (m == 1 && s == 0) mlive += 1;
        if (v == 1 && s == 0) vlive += 1;
        if (o == 1 && s == 0) olive += 1;
        if (g == 1 && s == 0) glive += 1;
        if (p == 1 && s == 0) plive += 1;
    });

    $('table.summary').find('td.mlive').text(mlive);
    $('table.summary').find('td.vlive').text(vlive);
    $('table.summary').find('td.olive').text(olive);
    $('table.summary').find('td.glive').text(glive);
    $('table.summary').find('td.plive').text(plive);

    if (archive) $('table.matrixCont tr.qual').hide();

}

/*
$('.col-abcd').keyup(function () {
    if (this.value != this.value.replace(/[^0-9\.]/g, '')) {
       this.value = this.value.replace(/[^0-9\.]/g, '');
    }
});
*/

</script>

<?php
    //$url = str_replace('&amp;calc='. $calc, '', $url);
if (!$data) {
?>

<table class="matrixCont">
    <tr>
        <td class='cont'>&nbsp;</td>
    </tr>
    <tr>
        <td class='cont'>Rows:</td>
        <td class='cont' onClick="toggleRow(['role','goal','project','checklist','list','blank'])">Non-v</td>
        <td class='cont' onClick="toggleRow(['vision','blank'])">v</td>
        <td class='cont' onClick="toggleRow(['role','goal'])">og</td>
        <td class='cont' onClick="toggleCheckB('someday')">Sday</td>
        <td class='cont' onClick="toggleCheckB('complete')">Comp</td>
        <td class='cont' onClick="toggleCheckB('live')">Live</td>
        <td class='cont' onClick="toggleCheckB('attr.531')">Blss</td>
        <td class='cont' onClick="toggleCheckB('attr.771')">Creer</td>
        <td class='cont' onClick="toggleRow(['i0'])">in0</td>
        <td class='cont' onClick="toggleRow(['i1'])">in1</td>
        <td class='cont' onClick="toggleRow(['i2'])">in2</td>
        <td class='cont' onClick="toggleRow(['i3'])">in3</td>
        <td class='cont' onClick="toggleRow(['i4'])">in4</td>
<?php if ($qLimit != 'h') { ?>
        <td class='cont' onClick="window.location = 'matrix.php?orphans=true&qLimit=<?php echo $qLimit; ?>';">Orphs</td>
        <td class='cont' id='Calc' onClick="autoCalc = true;calcFormulae('<?php echo $vLimit; ?>');">Calc^</td>
        <td class='cont' id='ajaxResp'></td>
<?php } ?>
    </tr>
<?php
    $urlq = str_replace('&amp;qLimit=' . $qLimit, '', $url);
?>
    <tr class="qual">
        <td class='cont'>Qual:</td>
        <td class='cont' onClick="window.location = 'matrix.php';">Base</td>
<?php
    if ($qLimit == 'h') { ?>
        <td class='cont' onClick="window.location = 'backup_scenario.php?name=<?php echo $mxTitle; ?>';">Save</td>
        <td class='cont'>Name:</td>
        <td class='cont' contenteditable="true" onBlur="sT(this,'lq','val','qaId','1','qId','1001')" onFocus="sE(this)"><?php echo $mxTitle; ?></td>
        <td class='cont'>Item width:</td>
        <td class='cont' contenteditable="true" onBlur="sT(this,'lq','val','qaId','2')" onFocus="sE(this)"><?php echo $nbsp; ?></td>
<?php }
    if ($qLimit == 'j') {
        $valIds = array(121 => '', 123 => '', 125 => '', 127 => '', 129 => '', 131 => '');
        $valUpds = $valIds;
        $needle = 'j1';
        foreach ((array) $valIds as $valId => $name) {
            $values = array();
            $values['qQuery'] = "`qId`";
            $values['qValue'] = $valId;
            $result = query("getqualities",$config,$values,$sort);
            foreach ((array) $result as $r) { $rName = $r['title']; $rDisp = str_replace($needle, '', $r['disp']); }
            $valIds[$valId] = $rName;
            foreach ((array) $valUpds as $valUpId => $name) {
                if ($valUpId == $valId) {
                    $valUpds[$valUpId] .= "sT(this,'qualities','disp','qId','" . $valId . "','','','','','','','','','" . $rDisp . $needle . "');";
                } else {
                    $valUpds[$valUpId] .= "sT(this,'qualities','disp','qId','" . $valId . "','','','','','','','','','" . $rDisp . "');";
                }
            }
        }
        foreach ((array) $valUpds as $valId => $valUpd) { ?>
            <td class='cont' onClick="<?php echo $valUpd; ?>"><?php echo $valIds[$valId]; ?></td>
<?php   } ?>
        <td class='cont' onClick="window.location = '<?php echo $url; ?>';">Reload</td>
        <td class='cont' onClick="window.location = '<?php echo $url; ?>&live=true';">Live</td>
<?php
    }
    if (!$data && !$scen) { ?>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=b'; ?>';">Cor Int</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=c'; ?>';">Cor Txt</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=d'; ?>';">Ext Int</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=g'; ?>';">Ext Txt</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=h&live=true&nometa=true&calc=true&scen=true'; ?>';">Scen</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=j&scen=true&calc=true'; ?>';">Val</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=i&calc=true&data=true'; ?>';">Data</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=e&career=true'; ?>';">Creer</td>
        <td class='cont' onClick="window.location = '<?php echo $urlq . '&qLimit=f'; ?>';">All</td>
        <td class='cont' onClick="window.location = '<?php echo $url . '&live=true'; ?>';">Live</td>
        <td class='cont' onClick="window.location = '<?php echo $url . '&calc=' . ($calc == 'true' ? 'false' : 'true'); ?>';">Calc</td>
<?php } ?>
    </tr>
</table>
<?php
    $col = 1;
    $comma = '';
    $allDisp = "toggleCol([";
    foreach ((array) $dispArray as $key=>$val) {
        if (!in_array($key, array('item','toggle'),true)) {
            $allDisp .= $comma . "'" . $col . "'";
            $comma = ',';
        }
        $col++;
    }
    $allDisp .= '])';
?>
<table class="matrixCont">
    <tr>
        <td class='cont'>Cols:</td>
        <!-- <td class='cont' onClick="notEditable(['col-vision','col-role','col-goal','col-project'])">Title edit</td> -->
        <!-- <td class='cont' onClick="notCheckbox()">Checkbox</td> -->
        <td class='cont' onClick="<?php echo $allDisp; ?>">All</td>
        <td class='cont' onClick="toggleCol(['3','4','5'])">Meta</td>
    <?php
        $col = 1;
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) $col++;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) {
                foreach ((array) $valN['attributes'] as $valO) $col--;
            }
        }
        $colN = $col;
        foreach ((array) $angles as $vala) {
            $comma = '';
            echo "<td class='cont' onClick=\"toggleCol([";
            foreach ((array) $vala['qualities'] as $valN) {
                foreach ((array) $valN['attributes'] as $valO) {
                    echo $comma . "'" . $col . "'";
                    $comma = ',';
                    $col++;
                }
            }
            echo "])\">{$vala['title']}</td>\n";
        }
        echo "<td class='cont'>..</td>";
        $quals = array('Contemplation','Reasons','Values','Desired Outcome','Duration','Conditions');
        foreach ((array) $angles as $vala) {
            echo "<td class='cont'>..</td>";
            foreach ((array) $vala['qualities'] as $valN) {
            $comma = '';
                echo "<td class='cont";
                if (array_search($valN['title'],$quals) > -1) echo " italic";
                echo "' onClick=\"toggleCol([";
                foreach ((array) $valN['attributes'] as $valO) {
                    echo $comma . "'" . $colN . "'";
                    $comma = ',';
                    $colN++;
                }
                echo "])\">{$valN['title']}</td>\n";
            }
        }
        ?>
    </tr>
</table>
<?php } // end if not $data
?>
<table id='filter' class='mx'>
    <br> <!-- sloppy alternative to CSS which is fickle -->
<?php
$et1 = microtime(true);
//echo '<pre>';var_dump($maintable);die;
require('matrixDisp.inc.php');
?>
</table>

<?php
if (!$scen && !$data) {
?>
<table>
    <tr>
        <td class="mx">utility by attribute = sum of (probability x value)</td>
    </tr><tr>
        <td class="mx">summary scores show inverse multiplier of weighted preference for attribute, with equilibrium when results between summaries similar</td>
    </tr><tr>
        <td class="mx">value = reward - loss</td>
    </tr><tr>
        <td class="mx">cubic scores from -4 to +4 for loss to gain (strong, sound, weak, insignificant)</td>
    </tr><tr>
        <td class="mx">ordinal probabilities from 0 to 9 for likelihood (0%, 10%, 20% ... 90%)</td>
    </tr><tr>
        <td class="mx">ordinal weighting from 0 to 9 for preference</td>
    </tr><tr>
        <td class="mx">ordinal integers from -n to +n for time</td>
    </tr><tr>
        <td class="mx">scores based on conversation, description, research and experience</td>
    </tr><tr>
        <td class="mx">scores may be revised in light of new experience or information</td>
    </tr><tr>
        <td class="mx">scores vary over life cycle of projects and of my life</td>
    </tr><tr>
        <td class="mx">the way needs are satisfied shifts over changing roles, and short and long term</td>
    </tr><tr>
        <td class="mx">hours: 3 = a morning, 3 = an afternoon, 3/2 = an evening, <?php echo $unqhoursyear; ?> = a year, add 750 = offset a year</td>
    </tr><tr>
        <td class="mx">reality check that things take twice as long as I estimate. Offset 750 hours/year for some projects due to brain downtime.</td>
    </tr><tr>
        <td class="mx">8760 hours a year, minus offset = 8000, <?php echo $unqhoursyearbrainless; ?> brainless</td>
    </tr><tr>
        <td class="mx">items can be deferred even if hours have been spent because hours simply indicate capacity relative to the coming 12 months</td>
    </tr><tr>
        <td class="mx">camera on screen shows awkward or false expression when lying about valuation</td>
    </tr><tr>
        <td class="mx">Quality <img src="media/contract.gif" height=80px>Concern with degree of excellence to which a service or function is performed, and degree to which expectations are set and met</td>
    </tr><tr>
        <td class="mx">Human <img src="media/is it to be desired.jpg" height=80px>Concern with how myself and others are treated, rewarded, or individually affected by actions or existing situations</td>
    </tr><tr>
        <td class="mx">Resource <img src="media/sunrise.png" height=80px>Concern with accumulation of those currencies which serve the purposes of an entity, and with optimising the frameworks through which such resources are generated</td>
    </tr><tr>
        <td class="mx">Innovation <img src="media/ireland.gif" height=60px>Seeking to imagine, explore, trial and implement new or alternative merit-based patterns, and to foster curiosity</td>
    </tr><tr>
        <td class="mx">Environment <img src="media/cakes.jpg" height=80px> Attention to environments, whether natural, social, cultural, or political, and with responses to action from entities within the environments</td>
    </tr><tr>
        <td class="mx">Perception <img src="media/old-ties.jpg" height=80px> Interest in constructing representations and how representations are identified and perceived by different parties, and with rectifying negative interpretations</td>
    </tr>
</table>
<table class="summary">
    <tr>
        <td class="mx">Values</td>
        <td class='mlive mx'></td>
        <td class="mx">Visions</td>
        <td class='vlive mx'></td>
        <td class="mx">Roles</td>
        <td class='olive mx'></td>
        <td class="mx">Goals</td>
        <td class='glive mx'></td>
        <td class="mx">Projects</td>
        <td class='plive mx'></td>
        <td class="mx">Calc gen (s)</td>
        <td class='fgen mx'></td>
        <td class="mx">Page gen (s)</td>
        <td class='pgen mx'>
        <?php
        if(isset($starttime)) {
            $endtime = microtime(true);
            echo round($endtime - $starttime,1);
        }
        ?>
        </td>
        <td class='pgen mx'>Base qry:
        <?php
            echo round($et1 - $starttime,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 2:
        <?php
            echo round($et2,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 3:
        <?php
            echo round($et3,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 4:
        <?php
            echo round($et4,1);
        ?>
        </td>
        <td class='pgen mx'>Disp 5:
        <?php
            echo round($et5,1);
        ?>
        </td>
        <td class='mx' onClick="window.location = 'matrix.php?test=true';">Test</td>
    </tr>
</table>
<?php } ?>
</div>
<?php /* fixed header
see https://stackoverflow.com/questions/8423768/freeze-the-top-row-for-an-html-table-only-fixed-table-header-scrolling
</div>
</section>
or more simply float absolute and dynamic th widths
https://stackoverflow.com/questions/7246683/detect-the-widest-cell-w-jquery
*/
?>
