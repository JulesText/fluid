<?php
//INCLUDES
include_once('header.php');

// display
$time = FALSE; // show time column

//RETRIEVE URL VARIABLES
$values=array();
$values['itemId'] = (int) $_GET['itemId'];

//Get item details
$values['childfilterquery']=' WHERE '.sqlparts('singleitem',$config,$values);
$values['filterquery']=sqlparts('isNA',$config,$values);
$values['extravarsfilterquery'] =sqlparts("getNA",$config,$values);;
$result = query("getitemsandparent",$config,$values,$sort);
$item = ($result)?$result[0]:array();
$values['isSomeday']=($item['isSomeday']=="y")?'y':'n';
$isTrade=($item['isTrade']=="y")?'y':'n';
$values['type']=$item['type'];

$pitemId = $values['itemId'];

//Find previous and next projects
if (isset($_SESSION['idlist-'.$item['type']])) {
    $ndx=$_SESSION['idlist-'.$item['type']];
    unset($result);
} else {
    $values['filterquery']  = " WHERE ".sqlparts("typefilter",$config,$values);
    $values['filterquery'] .= " AND ".sqlparts("activeitems",$config,$values);
    $values['filterquery'] .= " AND ".sqlparts("pendingitems",$config,$values);
    $values['filterquery'] .= " AND ".sqlparts("issomeday",$config,$values);
    $result = query("getitems",$config,$values,$sort);
    $c=0;
    $ndx=array();
    if ($result) {
        foreach ($result as $row) $ndx[]=$row['itemId'];
        $_SESSION['idlist-'.$item['type']]=$ndx;
    }
}

$cnt=count($ndx);
if($cnt>1) {
    $key=array_search($values['itemId'],$ndx);
    if ($key===false) {
        $next=0;
        $prev=$cnt-1;
    } else {
        if ($key==0)
            $prev=$cnt-1;
        else
            $prev=$key-1;

        if ($key==$cnt-1)
            $next=0;
        else
            $next=$key+1;
    }
    $previousId=$ndx[$prev];
    $nextId    =$ndx[$next];
    if (isset($result)) {
        $previoustitle=$result[$prev]['title'];
        $nexttitle    =$result[$next]['title'];
    } else {
        $previtem = query("selectitemtitle",$config,array('itemId'=>$previousId),$sort);
        $previoustitle=$previtem[0]['title'];
        $nextitem = query("selectitemtitle",$config,array('itemId'=>$nextId),    $sort);
        $nexttitle    =$nextitem[0]['title'];
    }
}

//PAGE DISPLAY AREA

//set item labels
$typename=array();
$typename=getTypes();

$childtype=array();  //I don't like this... but it's the best solution at the moment...

$childtype=getChildType($item['type']);

$afterTypeChange="itemReport.php?itemId={$values['itemId']}";

if (preg_match('/~/', $item['title'])) {
$rtitle = 'Agenda';
} else {
$rtitle = $typename[$item['type']];
}

require_once("headerHtml.inc.php");

echo "<h1>";
if ($item['metaphor']) {
    if (strpos($item['metaphor'], '.swf')) {
        echo "<embed src=\"media/" . $item['metaphor'] . "\" quality=high height=101></embed>";
        } else {
        echo "<a href=\"media/" . $item['metaphor'] . "\" target=\"_new\"><img src=\"media/" . $item['metaphor'] . "\" height=\"100px\"></a>";
        }
    }

echo $rtitle."&nbsp;Report:&nbsp;".makeclean($item['title']).(($item['isSomeday']=="y")?" (Someday) ":"");
if (!isset($_GET['content'])) {
  echo "&nbsp;&nbsp;&nbsp; [&nbsp;<a href='item.php?itemId={$values['itemId']}' title='Edit "
    ,makeclean($item['title']),"'>Edit</a>&nbsp;]";
  echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a id=\"copy-button\">Copy</a>&nbsp;]";
  echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"listsUpdate.php?itemId={$values['itemId']}&type=l\">Lists</a>&nbsp;]";
  echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"listsUpdate.php?itemId={$values['itemId']}&type=c\">Checklists</a>&nbsp;]";
}
if (isset($_GET['content']) && $_GET['content'] == "limit") $urltoken = "&content=limit";
else $urltoken = "";
if (isset($_GET['showCompleted']) && $_GET['showCompleted'] == "true") $urltoken .= "&showCompleted=true";
if (!isset($_GET['content'])) echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"itemReport.php?itemId={$values['itemId']}&content=limit{$urltoken}\">Limit</a>&nbsp;]";
if (!isset($_GET['showCompleted'])) {
  echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"itemReport.php?itemId={$values['itemId']}&showCompleted=true{$urltoken}\">Complete</a>&nbsp;]";
  $showCompleted = FALSE;
} else {
  $showCompleted = (bool) $_GET['showCompleted'];
}
echo "</h1>\n";

//Edit, next, and previous buttons
echo "<div class='editbar'>\n";
if ($item['type']==='i') echo "[<a href='assignType.php?itemId={$values['itemId']}&amp;referrer=$afterTypeChange'>Set type</a>] \n";
/*
if(isset($previousId)) echo " [<a href='itemReport.php?itemId=$previousId' title='",makeclean($previoustitle),"'>Previous</a>] \n";
if(isset($nextId))  echo " [<a href='itemReport.php?itemId=$nextId' title='",makeclean($nexttitle),"'>Next</a>] \n";
*/
echo "</div>\n<table id='report' summary='item attributes'><tbody>";
//Item details
if ($item['description']) echo "<tr><th width=200>Description:</th><td class=\"JKPadding\">" . nl2br(escapeChars($item['description'])) . "</td></tr>\n";
if ($item['conclusion']) {
    echo "<tr><th>Reasons:</th><td class=\"JKPadding\">",nl2br(escapeChars($item['premiseA']));
    if ($item['premiseB']) echo "<br><br>", nl2br(escapeChars($item['premiseB']));
    if ($item['conclusion']) echo "<br><br>", nl2br(escapeChars($item['conclusion']));
    echo "</td></tr>\n";
}
if ($item['behaviour']) {
    echo "<tr><th>Desired&nbsp;Outcome:</th><td class=\"JKPadding\">",nl2br(escapeChars($item['behaviour']));
    if ($item['standard']) echo ", <br>", nl2br(escapeChars($item['standard']));
    if ($item['conditions']) echo ", <br>", nl2br(escapeChars($item['conditions']));
    echo "</td></tr>\n";
}
if ($item['hyperlink']) {
    echo "<tr><th>Hyperlink:</th><td>" . faLink($item['hyperlink']) . "</td></tr>\n";
}

if (!empty($item['parentId'])) {
    echo "<tr><th>Parents:&nbsp;</th><td>";
    $brk='';
    $pids=explode(',',$item['parentId']);
    $pnames=explode($config['separator'],$item['ptitle']);
    foreach ($pids as $pkey=>$pid) {
        $thisparent=makeclean($pnames[$pkey]);
        echo "$brk<a href='itemReport.php?itemId=$pid' title='Go to the $thisparent report'>$thisparent</a> ";
        $brk=', ';
    }
    echo "</td></tr>\n";
}
if ($item['categoryId']) echo "<tr><th>Category:</th><td><a href='editCat.php?id={$item['categoryId']}&amp;field=category'>".makeclean($item['category'])."</a></td></tr>\n";
if ($item['contextId']) echo "<tr><th>Space Context:</th><td><a href='editCat.php?id={$item['contextId']}&amp;field=context'>".makeclean($item['cname'])."</a></td></tr>\n";
if ($item['timeframeId']) echo "<tr><th>Time Context:</th><td><a href='editCat.php?id={$item['timeframeId']}&amp;field=time-context'>".makeclean($item['timeframe'])."</a></td></tr>\n";
if (!empty($item['deadline'])) {
    if ($item['suppress']==='y') {
        echo '<tr><th>Suppressed Until:</th>';
    } else {
        echo "<tr><th>Deadline:</th>";
    }
    $deadline=prettyDueDate($item['deadline'],$config['datemask'],$item['suppress'],$item['suppressIsDeadline']);
    echo "<td class='{$deadline['class']}' title='{$deadline['title']}'>"
        ,$deadline['date'],"</td></tr>\n";

}
if ($item['type']==='a' || $item['type']==='w') echo '<tr><th>Next Action?</th><td>',($item['NA'])?'Yes':'No',"</td></tr>\n";
if ($item['repeat']) echo '<tr><th>Repeat every</th><td>'.$item['repeat'].' days'."</td></tr>\n";

if ($showCompleted) {
  echo '<tr><th>Created:</th><td>'.$item['dateCreated']."</td></tr>\n";
  if ($item['lastModified']) echo '<tr><th>Last modified:</th><td>'.substr($item['lastModified'],0,10)."</td></tr>\n";
  if ($item['dateCompleted']) echo '<tr><th>Completed On:</th><td>'.$item['dateCompleted']."</td></tr>\n";
}

echo "</tbody></table>\n";


if (!empty($childtype)) {
    $values['parentId']=$values['itemId'];

    $thisurl=parse_url($_SERVER['PHP_SELF']);
    $thisfile=makeclean(basename($thisurl['path']));

    //Create iteration arrays
    $completed = array('n','y');

    //table display loop
    foreach ($completed as $comp) foreach ($childtype as $thistype) {
        $wasNAonEntry = array(); // reset for each table
        $thistableid="i$comp$thistype";

        //Select items by type
        if ($thistype==='s') {
           //$values['type']='p';
           $values['type']='';
           $values['isSomeday']='y';
           $values['filterquery'] ='';
        } else {
            $values['isSomeday']='n';
            $values['type']=$thistype;
            $values['filterquery'] = " AND ".sqlparts("typefilter",$config,$values); // only filter on type if not a someday
        }
        $values['filterquery'] .= " AND ".sqlparts("issomeday",$config,$values);

        $q=($comp==='y')?'completeditems':'pendingitems';  //suppressed items will be shown on report page
        $values['filterquery'] .= " AND ".sqlparts($q,$config,$values);

        $sort['getchildren'] = $sort['getchildrenTrades'];
        $result = query("getchildren",$config,$values,$sort);
				if (!empty($result) && is_array($result)) {
					$count = count($result);
				} else {
					$count = 0;
				}
        if ($comp==='y' && $config['ReportMaxCompleteChildren'] && $count > $config['ReportMaxCompleteChildren']) {
            $limit=$config['ReportMaxCompleteChildren'];
            $url=   ($_SESSION['useLiveEnhancements'])
                ?'javascript:toggleHidden("'.$thistableid.'","table-row","f'.$thistableid.'");'
                :"listItems.php?type=$thistype&amp;parentId={$values['parentId']}&amp;completed=true";
            $footertext="<a href='$url'>".($count-$limit)
                ." more... (".$count." items in total)</a>";
        } else {
            $limit=$count;
            $footertext='';
        }
        ?>
<div class='reportsection'>
        <?php
        $title=$typename[$thistype].'s';
        if ($comp==="y") {
            $title="Completed ".$title;
        } else {
            $createURL="item.php?parentId={$pitemId}&amp;action=create&amp;type=$thistype";
            // inherit some defaults from parent:
            foreach (array('categoryId','contextId','deadline','isTrade') as $field)
                if ($item[$field]) $createURL.="&amp;$field={$item[$field]}";
                if ($values['isSomeday']!='y') $title="<a href='$createURL' title='Add new ".$typename[$thistype]."'>Add ".$title."</a>";
        }

        // lists insert here
        if ($values['type']==="r" && $_GET['content'] != 'limit' && $comp==="n") {
            $valuesTemp = $values;
            $values['type'] = 'c';
            include('itemLists.php');
            $values = $valuesTemp;

            $valuesTemp = $values;
            $values['type'] = 'l';
            include('itemLists.php');
            $values = $valuesTemp;
        }

        if (!$result) {
            echo "<h3>No $title</h3></div>";
            continue;
        }

        $showOutcome = false;
        foreach ($result as $row) {
          if (strlen($row['behaviour']) > 1) $showOutcome = true;
        }

        $shownext= ($comp==='n') && ($values['type']==='a' || $values['type']==='w');
        $suppressed=0;
        if ($comp==="n") {
            $descriptionField='fulldesc';
            $outcomeField='fulloutcome';
        } else {
            $descriptionField='description';
            $outcomeField='behaviour';
        }
        $dispArray=array();
        if ($shownext) if ($isTrade == 'y') {
            $dispArray['NA']='Live';
        } else {
            $dispArray['NA']='NA';
        }
        // if ($isTrade == 'y') $dispArray['dateCreated']='Trade date';
        $dispArray['title']=$typename[$thistype].'s';
        $dispArray[$descriptionField]='Description';
        //$dispArray[$outcomeField]='Objective';

        switch ($values['type']) {

            case 'a': // deliberately flows through to 'w'
                if ($isTrade == 'y') $dispArray[$outcomeField]='Outcome';
                if ($comp=="n") {
                    $dispArray['deadline']='Due';
                }
            break;
            case 'r':
            case 'w':
                if ($isTrade == 'y') $dispArray[$outcomeField]='Outcome';
                $dispArray['deadline']='Due';
                if ($time) $dispArray['timeframe']='Time';
            break;

            case 'm': // deliberately flows through to 'g'
            case 'v': // deliberately flows through to 'g'
            case 'o': // deliberately flows through to 'g'
            case 'g': // deliberately flows through to 'g'
              if ($showOutcome) $dispArray[$outcomeField]='Outcome';
              break;

            case 's': // deliberately flows through to 'p;
            case 'p': // deliberately flows through to default;
                $dispArray['context']='Context';
            default:
                $dispArray['category']='Category';
                break;
        }

        if ($comp=="n") {
            $dispArray['checkbox']='Complete';
        } else {
            $dispArray['completed']='Date Completed';
        }

        foreach ($dispArray as $key=>$val) $show[$key]=true;
        if ($config['nextaction']==='single') $dispArray['NA.type']='radio';
        $i=0;
        $maintable=array();

        foreach ($result as $row) {

            $cleantitle = makeclean($row['title']);

            $maintable[$i]=array();
            if ($i >= $limit) {
                if ($_SESSION['useLiveEnhancements']) {
                    $maintable[$i]['row.class']='togglehidden';
                } else {
                    array_pop($maintable);
                    break;
                }
            }

            $maintable[$i]['itemId']=$row['itemId'];

            if ($isTrade == 'y' && $row['isTrade'] == 'y') {
              $maintable[$i]['dateCreated'] = $row['dateCreated'];
            }

            $maintable[$i]['isTrade'] = $row['isTrade'];

            $tfield = $row['title'];

            if($row['metaphor']) {
                if (strpos($row['metaphor'], '.swf')) {
                    $tfield .= "<br><embed src=\"media/" . $row['metaphor'] . "\" quality=high height=101></embed>
                    ";
                    } else {
                    $tfield .= '<br><img src="media/' . $row['metaphor'] . '" height="100px"></a><a href="media/' . $row['metaphor'] . '" target="_new"><';
                    }
            }

            $maintable[$i]['title'] = $tfield;

            # exception if contains hyperlink or is trade then avoid ajax update
            if (strstr($row['description'], '<a href=')) {
              $maintable[$i][$descriptionField] = $row['description'];
              $maintable[$i][$descriptionField] .= '<div>';
            }
            # exception if goal/role/project
            else if (in_array($row['type'], ['v','o','g','p'])) {
              $maintable[$i][$descriptionField] = $row['description'];
              // if ($row['premiseA']) $maintable[$i][$descriptionField] .= $row['premiseA'] . '<br><br>';
              // if ($row['premiseB']) $maintable[$i][$descriptionField] .= $row['premiseB'] . '<br><br>';
              if ($row['conclusion']) $maintable[$i][$descriptionField] .= $row['conclusion'] . '<br><br>';
              $maintable[$i][$descriptionField] .= '<div>';
            }
            else if ($isTrade == 'y') {
              $maintable[$i][$descriptionField] = '';
              if (in_array($row['tradeConditionId'], ['',0,1,null])) $maintable[$i][$descriptionField] .= "<div contenteditable='true'" . ajaxUpd('itemDescription', $row['itemId']) . ">" . $row['description'] . "</div>";
              if (!in_array($row['tradeConditionId'], ['',0,null])
                  && (strlen($row['premiseA']) > 0 || strlen($row['premiseB']) > 0 || strlen($row['conclusion']) > 0)
                ) {
                  if (in_array($row['tradeConditionId'], [1])) $maintable[$i][$descriptionField] .= PHP_EOL;
                  $maintable[$i][$descriptionField] .= "PA: <div class='inline-div-editable' contenteditable='true'" . ajaxUpd('itemPremiseA', $row['itemId']) . ">" . $row['premiseA'] . "</div>";
                  $maintable[$i][$descriptionField] .= "<br>PB: <div class='inline-div-editable' contenteditable='true'" . ajaxUpd('itemPremiseB', $row['itemId']) . ">" . $row['premiseB'] . "</div>";
                  $maintable[$i][$descriptionField] .= "<br>C: <div class='inline-div-editable' contenteditable='true'" . ajaxUpd('itemConclusion', $row['itemId']) . ">" . $row['conclusion'] . "</div>";
              }
              $maintable[$i][$descriptionField] .= '<div>';
            }
            # otherwise do it
            else
              $maintable[$i][$descriptionField] = "<div contenteditable='true'" . ajaxUpd('itemDescription', $row['itemId']) . ">" . $row['description'];

            if ($row['hyperlink']) {
                if (!empty($row['description'])) $maintable[$i][$descriptionField] .= "</div><div><br>";
                else $maintable[$i][$descriptionField] .= "</div><div>";
                $maintable[$i][$descriptionField] .= faLink($row['hyperlink']);
            }
            $maintable[$i][$descriptionField] .= '</div>';
            $rfield = $row['premiseA'];
            if($row['premiseB']) $rfield .= '<br><br>' . $row['premiseB'];
            if($row['conclusion']) $rfield .= '<br><br>' . $row['conclusion'];
            $maintable[$i]['conclusion']=$rfield;

            if ($isTrade == 'y') {
               $maintable[$i]['tradeConditionId'] = $row['tradeConditionId'];
               $maintable[$i]['tradeCondition'] = $row['tradeCondition'];
               if (!is_null($row['tradeConditionId']) && $row['tradeConditionId'] > 0)
                  $maintable[$i]['rewardRisk'] =
                    ((int) $row['conditions'] / 100) // chance (p)
                    * (((int) $row['standard'] - (int) $row['behaviour'])
                      / (int) $row['behaviour']) // expected reward
                    * 100;
                $maintable[$i]['behaviour'] = $row['behaviour'];
                $maintable[$i]['standard'] = $row['standard'];
                $maintable[$i]['conditions'] = $row['conditions'];
                $ofield = '';
            } else {
              $ofield = $row['behaviour'];
              // if($row['standard']) $ofield .= ',<br>' . $row['standard'];
              // if($row['conditions']) $ofield .= ',<br>' . $row['conditions'];
            }
            $maintable[$i][$outcomeField]=$ofield;
/*            $maintable[$i]['created']=date($config['datemask'],
                    (empty($row['dateCreated']))
                        ? null
                        : strtotime($row['dateCreated']));
*/
            $maintable[$i]['categoryId']=$row['categoryId'];
            $maintable[$i]['category']=makeclean($row['category']);

            $maintable[$i]['contextId']=$row['contextId'];
            $maintable[$i]['context']=makeclean($row['cname']);
            $maintable[$i]['context.title']='Go to '.$maintable[$i]['context'].' context report';

						if ($time) {
            $maintable[$i]['timeframeId']=$row['timeframeId'];
            $maintable[$i]['timeframe']=makeclean($row['timeframe']);
            $maintable[$i]['timeframe.title']='Go to '.$maintable[$i]['timeframe'].' time-context report';
						}

            if ($comp==='n') {

                //Calculate reminder date as # suppress days prior to deadline
                if ($row['suppress']==='y' && $row['deadline']!=='') {
                    $reminddate=getTickleDate($row['deadline'],$row['suppressUntil']);
                    if ($reminddate>time()) { // item is not yet tickled - count it, then skip displaying it
                        $suppressed++;
                        if ($_SESSION['useLiveEnhancements']) {
                            $maintable[$i]['row.class']='togglehidden';
                        } else {
                            array_pop($maintable);
                            continue;
                        }
                    }
//                  $maintable[$i]['suppress']=date($config['datemask'],$reminddate);
                    $deadline=prettyDueDate($row['deadline'],$config['datemask'],$row['suppress'],$row['suppressIsDeadline']);
                    $maintable[$i]['deadline']      =$deadline['date']; // not displayed until displayItems.inc.php but used for other logical conditions
                    $maintable[$i]['deadline.class']=$deadline['class'];
                    $maintable[$i]['deadline.title']=$deadline['title'];

                } else if (empty($row['deadline'])) {
                    // $maintable[$i]['deadline']=null;

                } else {
                    $deadline=prettyDueDate($row['deadline'],$config['datemask'],$row['suppress'],$row['suppressIsDeadline']);
                    $maintable[$i]['deadline']      = $deadline['date']; // not displayed until displayItems.inc.php but used for other logical conditions
                    $maintable[$i]['deadline.class']= $deadline['class'];
                    $maintable[$i]['deadline.title']= $deadline['title'];
                }

//              $maintable[$i]['repeat']=($row['repeat']==0)?'&nbsp;':$row['repeat'];

                $maintable[$i]['checkbox.title']="Mark $cleantitle complete";
                $maintable[$i]['checkboxname']='isMarked[]';
                $maintable[$i]['checkboxvalue']=$row['itemId'];

                if ($shownext) {
                    $maintable[$i]['NA']=$comp!=="y" && $row['NA'];
                    $maintable[$i]['NA.title']='Mark as a Next Action';
                    if ($maintable[$i]['NA']) array_push($wasNAonEntry,$row['itemId']);
                }
            } else {
                $maintable[$i]['completed']=date($config['datemask'],strtotime($row['dateCompleted']));
                if (!$showCompleted) unset($maintable[$i]);
            }

            $i++;

        }

        // calculate valuations from risk_reward values
        if ($isTrade == 'y') {

          $index = array_map(function($row) {
            return ['dateCreated' => $row['dateCreated'], 'title' => $row['title']];
          }, $maintable);
          $index = array_values(array_unique($index, SORT_REGULAR));

          foreach ($index as &$element) {
            $element['isStrategy'] = false;
            $element['valuation'] = 0;
            $element['condition1'] = false;
            $element['condition2'] = false;
            $element['condition3'] = false;
            $element['condition4'] = false;
            $element['condition5'] = false;
            $element['condition6'] = false;
            $element['chance'] = 0;
            foreach ($maintable as $row) {

              if ($element['dateCreated'] == $row['dateCreated']
                  && $element['title'] == $row['title']
                  && $row['isTrade'] == 'y'
                  ) {
                  $element['isStrategy'] = true;
                  if ($row['tradeConditionId'] > 0 && $row['tradeCondition'] !== 'Evaluation*') {
                      $element['valuation'] += $row['rewardRisk'];
                      $element['chance'] += (int) $row['conditions'];
                  }
                  if ($row['tradeCondition'] == 'Evaluation*') $element['condition1'] = true;
                  if ($row['tradeCondition'] == 'Evaluation*' && $row['deadline'] != '') $element['condition2'] = true;
                  if ($row['tradeCondition'] == 'Time lapse*') $element['condition3'] = true;
                  if ($row['tradeCondition'] == 'Time lapse*' && $row['deadline'] != '') $element['condition4'] = true;
                  if ($row['tradeCondition'] == 'Stop loss*') $element['condition5'] = true;
                  if ($row['tradeCondition'] == 'Take profit*') $element['condition6'] = true;

              }
            }
          }
          unset($element);

          foreach ($maintable as &$row) {
              $row['valuation'] = '';
              foreach ($index as $element)
                  if ($element['dateCreated'] == $row['dateCreated'] && $element['title'] == $row['title']) {
                      $row['isStrategy'] = $element['isStrategy'];
                      if ($row['isStrategy']) {
                          if ($element['chance'] !== 100) {
                              $row['valuation'] .= ' total chance (p) != 100; ';
                          }
                          if (!$element['condition1']) $row['valuation'] .= ' Evaluation* condition missing;';
                          else if (!$element['condition2']) $row['valuation'] .= ' Evaluation* missing due date;';
                          if (!$element['condition3']) $row['valuation'] .= ' Time lapse* condition missing;';
                          else if (!$element['condition4']) $row['valuation'] .= ' Time lapse* missing due date;';
                          if (!$element['condition5']) $row['valuation'] .= ' Stop loss* condition missing;';
                          if (!$element['condition1']) $row['valuation'] .= ' Take profit* condition missing;';
                          if ($row['tradeCondition'] == 'Evaluation*') {
                              $row['valuation'] .= round($element['valuation'], 0) . '%';
                          }
                      }
                  }

              if (!$row['isStrategy']) $row['dateCreated'] = "";

              if ($row['tradeCondition'] == '') $row[$outcomeField] =
                    $row['valuation'];
              else if ($row['tradeCondition'] == 'Evaluation*' && $row['isTrade'] == 'y') $row[$outcomeField] =
                    "<u>" . $row['tradeCondition'] . "</u>" . PHP_EOL
                    . 'Valuation: ' . $row['valuation'];
              else if ($row['tradeCondition'] == 'Evaluation*') $row[$outcomeField] =
                    "<u>" . $row['tradeCondition'] . "</u>";
              else if ($row['tradeCondition'] != '') $row[$outcomeField] =
                    "<u>" . $row['tradeCondition'] . "</u>"
                    . PHP_EOL . "Reward/risk: " . ((string) round($row['rewardRisk'], 0)) . '%'
                    . PHP_EOL . "Enter price: <div class='inline-div-editable' contenteditable='true'"
                        . ajaxUpd('itemBehaviour', $row['itemId']) . ">" . $row['behaviour'] . "</div>"
                    . PHP_EOL . "Exit price: <div class='inline-div-editable' contenteditable='true'"
                        . ajaxUpd('itemStandard', $row['itemId']) . ">" . $row['standard'] . "</div>"
                    . PHP_EOL . "Chance (p): <div class='inline-div-editable' contenteditable='true'"
                        . ajaxUpd('itemConditions', $row['itemId']) . ">" . $row['conditions'] . "</div>" . "%"
                    ;
          }
          unset($row);

        }

        ?>
<h2><?php echo $title; ?></h2>
        <?php
        if ($suppressed) {
            $is=($suppressed===1)?'is':'are';
            $also=(count($maintable))?'also':'';
            $plural=($suppressed===1)?'':'s';
            $url=   ($_SESSION['useLiveEnhancements'])
                ?'javascript:toggleHidden("'.$thistableid.'","table-row","f'.$thistableid.'");'
                :"listItems.php?tickler=true&amp;type={$thistype}&amp;parentId={$values['parentId']}";
            $footertext="<a href='$url'><span style='background-color: #cccc66'>There $is $also $suppressed tickler "
                      .$typename[$thistype].$plural." not yet due for action</span></a>";
        }
//        $tfoot=(empty($footertext))?'': "<div id='f$thistableid'><tr><td colspan='3'>\n$footertext\n</td></tr></div>\n";
        $tfoot=(empty($footertext))?'': "<div id='f$thistableid'>\n$footertext\n</div>\n";
        if (count($maintable)) {
            if ($comp==='n') { ?>
                <form action='processItems.php' method='post'>
            <?php } ?>
            <table class='datatable sortable' id='<?php echo $thistableid; ?>' summary='table of children of this item'>
            <?php require('displayItems.inc.php'); ?>
            </table>
            <?php
        }
        if(!count($maintable)) echo "No {$typename[$thistype]} items\n";
        if ($comp==="n" && count($maintable)) { ?>
<p>
<input type="submit" class="button" value="Update marked <?php echo $typename[$thistype]; ?>s" name="submit" />
<input type='hidden' name='referrer' value='<?php echo "{$thisfile}?itemId={$values['itemId']}"; ?>' />
<input type="hidden" name="multi" value="y" />
<input type="hidden" name="action" value="complete" />
<input type="hidden" name="wasNAonEntry" value='<?php echo implode(' ',$wasNAonEntry); ?>' />
</p>
</form>
        <?php
}
?>
</div>
<?php
    } // end of foreach ($completed as $comp) foreach ($childtype as $thistype)
} // end of if ($childtype!=NULL)
include_once('footer.php');
?>
