<?php

include_once('gtd_constants.inc.php');
/*
   ======================================================================================
*/
function query($querylabel,$config,$values=NULL,$sort=NULL) {
    //for developer testing only--- testing data handling
    //testing passed variables
    if ($config['debug'] & _GTD_DEBUG) {
        echo "<p class='debug'><b>Query label: ".$querylabel."</b></p>";
        echo "<pre>Config: ";
        print_r($config);
        echo "<br />Values: ";
        print_r($values);
        echo "<br />Sort: ";
        print_r($sort);
        echo "</pre>";
    }

    //grab correct query string from query library array
    //values automatically inserted into array
    $query=getsql($config,$values,$sort,$querylabel);

    // for testing only: display fully-formed query
    if ($config['debug'] & _GTD_DEBUG) echo "<p class='debug'>Query: ".$query."</p>";

    //perform query
    $result=doQuery($config, $query, $querylabel);

    //for developer testing only, print result array
    if ($config['debug'] & _GTD_DEBUG) {
        echo "<pre>Result: ";
        print_r($result);
        echo "</pre>";
        }

    return $result;
}
/*
   ======================================================================================
*/


function scoreCL($config, $values, $sort) {

  $score_items_pass = 0;
  $score_items_obs = 0;
  $items_total = 0;

  $list = query("selectchecklist",$config,$values,$sort);
  $list = $list[0];

  $prioritise = $list['prioritise'];

  $result = query("getchecklistitems",$config,$values,$sort);

  if (is_array($result) && count($result) > 0) {
      foreach ((array) $result as $row) if($prioritise == -1 || ($row['priority'] <= $prioritise && $prioritise > -1)) {

        $items_total++;
        if ($row['assessed'] >= $list['thrs_obs'] && $row['assessed'] > 0) {
          $score_items_obs++;
          if (100 * $row['score'] / $row['assessed'] >= $list['thrs_score']) $score_items_pass++;
        }

      }
  }

  if ($score_items_obs)
    $score_total = 100 * round($score_items_pass / $score_items_obs, 2)
                        . '% pass / '
                        . 100 * round($score_items_obs / $items_total, 2)
                        . '% obs';
  else
    $score_total = 'insufficient obs';

  return $score_total;

}

function ajaxLineBreak($textIn) {
    global $config;
    if (is_array($textIn)) {
        $cleaned=array();
        foreach ($textIn as $line) $cleaned[]=ajaxLineBreak($line);
    } else {
        $cleaned = preg_replace('/[\r\n]+/','', nl2br($textIn)); // carriage returns to <br /> then remove carriage return
    }
    return $cleaned;
}

function makeClean($textIn) {
    global $config;
    if (is_array($textIn)) {
        $cleaned=array();
        foreach ($textIn as $line) $cleaned[]=makeClean($line);
    } else {
        $cleaned=htmlentities(stripslashes($textIn),ENT_QUOTES,$config['charset']);
    }
    return $cleaned;
}

function moveElement(&$array, $from, $to) {
        $out = array_splice($array, $from, 1);
        array_splice($array, $to, 0, $out);
}

function trimTaggedString($inStr,$inLength=0,$keepTags=TRUE) { // Ensure the visible part of a string, excluding html tags, is no longer than specified)    // TOFIX -  we don't handle "%XX" strings yet.
    // constants - might move permittedTags to config file
    $permittedTags=array(
         array('/^<a ((href)|(file))=[^>]+>/i','</a>')
        ,array('/^<b>/i','</b>')
        ,array('/^<i>/i','</i>')
        ,array('/^<ul>/i','</ul>')
        ,array('/^<ol>/i','</ol>')
        ,array('/^<li>/i','</li>')
        );
    $ellipsis='&hellip;';
    $ampStrings='/^&[#a-zA-Z0-9]+;/';

    // initialise variables
    if ($inLength==0) $inLength=strlen($inStr)+1;
    $outStr='';
    $visibleLength=0;
    $thisChar=0;
    $keepGoing=!empty($inStr);
    $tagsOpen=array();
    // main processing here
    while ($keepGoing) {
        $stillHere = TRUE;
        $tagToClose=end($tagsOpen);
        if ($tagToClose && strtolower(substr($inStr,$thisChar,strlen($tagToClose)))===strtolower($tagToClose) ) {
            $stillHere=FALSE;
            $thisChar+=strlen($tagToClose);
            if ($keepTags) $outStr.=array_pop($tagsOpen);
        } else foreach ($permittedTags as $thisTag) {
            if ($stillHere && ($inStr{$thisChar}==='<') && (preg_match($thisTag[0],substr($inStr,$thisChar),$matches)>0)) {
                $thisChar+=strlen($matches[0]);
                $stillHere=FALSE;
                if ($keepTags) {
                    array_push($tagsOpen,$thisTag[1]);
                    $outStr.=$matches[0];
                }
            } // end of if
        } // end of else foreach
        // now check for & ... control characters
        if ($stillHere && ($inStr{$thisChar}==='&') && (preg_match($ampStrings,substr($inStr,$thisChar),$matches)>0)) {
            if (strlen(html_entity_decode($matches[0]))==1) {
                $visibleLength++;
                $outStr.=$matches[0];
                $thisChar+=strlen($matches[0]);
                $stillHere=FALSE;
            }
        }
        // just a normal character, so add it to the string
        if ($stillHere) {
            $visibleLength++;
            $outStr.=$inStr{$thisChar};
            $thisChar++;
        } // end of if
        $keepGoing= (($thisChar<strlen($inStr)) && ($visibleLength<$inLength));
    } // end of while ($keepGoing)
    // add ellipsis if we have trimmed some text
    if ($thisChar<strlen($inStr) && $visibleLength>=$inLength) $outStr.=$ellipsis;
    // got the string - now close any open tags
    if ($keepTags) while (count($tagsOpen))
        $outStr.=array_pop($tagsOpen);
    $outStr=nl2br(escapeChars($outStr));
    return($outStr);
}

function getTickleDate($deadline,$days) { // returns unix timestamp of date when tickle becomes active
    $dm=(int)substr($deadline,5,2);
    $dd=(int)substr($deadline,8,2);
    $dy=(int)substr($deadline,0,4);
    // relies on PHP to sanely and clevery handle dates like "the -5th of March" or "the 50th of April"
    $remind=mktime(0,0,0,$dm,($dd-$days),$dy);
    return $remind;
}

function nothingFound($message, $prompt=NULL, $yeslink=NULL, $nolink="index.php"){
        //Give user ability to create a new entry, or go back to the index.
        echo "<h4>$message</h4>";
        if($prompt)
            echo "<p>$prompt;<a href='$yeslink'> Yes </a><a href='$nolink'>No</a></p>\n";
}

function categoryselectbox($config,$values,$sort) {
    $result = query("categoryselectbox",$config,$values,$sort);
    $cashtml='<option value="0">--</option>'."\n";
    if ($result) {
        foreach($result as $row) {
            $cashtml .= '   <option value="'.$row['categoryId'].'" title="'.makeclean($row['description']).'"';
            if($row['categoryId']==$values['categoryId']) $cashtml .= ' selected="selected"';
            $cashtml .= '>'.makeclean($row['category'])."</option>\n";
            }
        }
    return $cashtml;
    }

function instanceselectbox($config,$values,$sort) {
    $result = query("instanceselectbox",$config,$values,$sort);
    //echo '<pre>'; var_dump($result);die;
    $cashtml='<select onchange="if (this.value) window.location.href=this.value">';
    $cashtml.='<option value="' . $values['urlInst'] . '">self</option>'."\n";
    if ($result) {
        foreach($result as $row) {
            $cashtml .= '   <option value="' . $values['urlInst'] . $row['instanceId'].'" title="'.makeclean($row['name']).'"';
            if($row['instanceId']==$values['instanceId']) $cashtml .= ' selected="selected"';
            $cashtml .= '>'.makeclean($row['name'])."</option>\n";
        }
    }
    $cashtml .= '</select>';
    return $cashtml;
    }


function parentlistselectbox($config,$values,$sort) {
    $result = query("parentlistselectbox",$config,$values,$sort);
    //echo '<pre>'; var_dump($result);die;
    $cashtml='<select onchange="if (this.value) window.location.href=this.value">';
    if ($result) {
        foreach($result as $row) {
            $cashtml .= '   <option value="' . $values['urlCL'] . $row['checklistId'].'"';
            if($row['checklistId']==$values['listId']) $cashtml .= ' selected="selected"';
            $cashtml .= '>'.makeclean($row['title'])."</option>\n";
        }
    }
    $cashtml .= '</select>';
    return $cashtml;
    }

function priorityselectbox($config,$values,$sort) {
    $result = query("priorityselectbox",$config,$values,$sort);
    if ($result == 0) return -1;
    $result = array_merge([['priority' => '-1']], $result);
    $arrsize = count($result) - 1;
    $max = $result[$arrsize]['priority'];
    #echo '<pre>'; var_dump($result);die;
    $cashtml='<select onchange="if (this.value) window.location.href=this.value">';
    foreach($result as $row) {
        $cashtml .= '   <option value="processLists.php' . $values['urlVars'] . '&action=listpriority&prioritise=' . $row['priority'] . '" title="'.$row['priority'].'"';
        if($row['priority']==$values['priorityId']) $cashtml .= ' selected="selected"';
        $cashtml .= '>'.$row['priority'];
        if ($row['priority'] !== '-1') $cashtml .= '/' . $max;
        $cashtml .= '</option>' . PHP_EOL;
    }
    $cashtml .= '</select>';
    return $cashtml;
    }

function contextselectbox($config,$values,$sort) {
    $result = query("spacecontextselectbox",$config,$values,$sort);
    $cshtml='<option value="0">--</option>'."\n";
    if ($result) {
            foreach($result as $row) {
            $cshtml .= '                    <option value="'.$row['contextId'].'" title="'.makeclean($row['description']).'"';
            if(($row['contextId']==$values['contextId']) /* && $values['itemId']>0 */) $cshtml .= ' selected="selected"';
            $cshtml .= '>'.makeclean($row['name'])."</option>\n";
            }
        }
    return $cshtml;
    }

function timecontextselectbox($config,$values,$sort) {
    $result = query("timecontextselectbox",$config,$values,$sort);
    $tshtml='<option value="0">--</option>'."\n";
    if ($result) {
        foreach($result as $row) {
            $tshtml .= '                    <option value="'.$row['timeframeId'].'" title="'.makeclean($row['description']).'"';
            if ($row['timeframeId'] == $values['timeframeId']) {
                $tshtml .= ' selected="selected"';
            } elseif ($values['timeframeId'] == '' && $row['timeframeId'] == 0) {
                $tshtml .= ' selected="selected"';
            }
            $tshtml .= '>'.makeclean($row['timeframe'])."</option>\n";
            }
        }
    return $tshtml;
    }

function makeOption($row,$selected) {
    $cleandesc=makeclean($row['description']);
    $cleantitle=makeclean($row['title']);
    if ($row['isSomeday']==="y") {
        $cleandesc.=' (Someday)';
        $cleantitle.=' (S)';
    }
    $seltext = ($selected[$row['itemId']])?' selected="selected"':'';
    $out = "<option value='{$row['itemId']}' title='$cleandesc' $seltext>$cleantitle</option>";
    return $out;
}

function parentselectbox($config,$values,$sort) {
    $result = query("parentselectbox",$config,$values,$sort);
    $pshtml='';
    $parents=array();
    if (is_array($values['parentId']))
        foreach ($values['parentId'] as $key) $parents[$key]=true;
    else
        $parents[$values['parentId']]=true;
    if ($config['debug'] & _GTD_DEBUG) echo '<pre>parents:',print_r($parents,true),'</pre>';
    if ($result)
        foreach($result as $row) {
            $thisOpt= makeOption($row,$parents)."\n";
            if($parents[$row['itemId']]) {
                $pshtml =$thisOpt.$pshtml;
                $parents[$row['itemId']]=false;
            } else
                $pshtml .=$thisOpt;
        }
    foreach ($parents as $key=>$val) if ($val) {
        // $key is a parentId which wasn't found for the drop-down box, so need to add it in
        $values['itemId']=$key;
        $row=query('selectitemshort',$config,$values,$sort);
        if ($row) $pshtml = makeOption($row[0],$parents)."\n".$pshtml;
    }
    $pshtml="<option value='0'>--</option>\n".$pshtml;
    return $pshtml;
}

function listselectbox($config,&$values,$sort,$check=NULL) { // NB $values is passed by reference
    $result = query("get{$check}lists",$config,array('filterquery'=>''),$sort);
    $lshtml='';
    if ($result) {
        foreach($result as $row) {
            $lshtml .= "<option value='{$row['listId']}' title='".makeclean($row['title'])."'";
            if($row['listId']==$values['listId']) {
                $lshtml .= " selected='selected' ";
                $values['listTitle']=$row['title'];
            }
            $lshtml .= '>'.makeclean($row['title'])."</option>\n";
            }
        }
    return $lshtml;
    }

function prettyDueDate($dateToShow,$thismask,$tickle='n',$tickleIsDeadline='n') {
    $retval=array('class'=>'','title'=>'');
    if(trim($dateToShow)!='') {
        $retval['date'] = date($thismask,strtotime($dateToShow));
        if($tickle !== 'n' && $tickleIsDeadline == 'n') {
            $retval['class']='tickle';
            $retval['title']='Tickle';
        }
        elseif(time() > strtotime($dateToShow)) {
            $retval['class']='overdue';
            $retval['title']='Overdue';
        }
        elseif ((time() + (60*60*24)) > strtotime($dateToShow)) {
            $retval['class']='due';
            $retval['title']='Due today';
        }
        elseif ((time() + (3*60*60*24)) > strtotime($dateToShow)) {
            $retval['class']='duesoon';
            $retval['title']='Due soon';
        }
/*        elseif ($dateToShow>date("Y-m-d")) {
            $retval['class']='tomorrow';
            $retval['title']='Due tomorrow';
        }
*/
     } else
        $retval['date'] ='&nbsp;';
    return $retval;
}

function faLink($link, $mx = false) {
    $retval='';
    if ($mx) { $mxa = " class=\"mx\""; $mxb = " target=\"_blank\" class=\"mx\""; }
    else { $mxb = ""; $mxa = ""; }
    if($link != '') {
        if (preg_match('/devonthink/', $link)) {
            if (preg_match('/search/', $link)) {
                $retval = "<a href=\"" . $link . "\"" . $mxa . ">FR link</a>";
            } else {
                $retval = "<a href=\"" . $link . "?reveal=1\"" . $mxa . ">FR link</a>";
            }
        } elseif (preg_match('/jules/', $link)) {
            $retval = "<a href=\"" . $link . "\"" . $mxb . ">FA link</a>";
        } elseif (!preg_match('/www|http/', $link)) {
            $retval = "<a href=\"" . $link . "\"" . $mxb . ">FA link</a>";
        } else {
            $retval = "<a href=\"" . $link . "\"" . $mxb . ">ext link</a>";
        }
    }
    return $retval;
}

function ajaxUpd($query, $id, $inst = NULL) {
    $retval='';
    if($id != '') {
      if ($query === 'itemCategory') $retval = " onChange=\"sT(this,'itemattributes','categoryId','itemId','{$id}')\" ";
      if ($query === 'itemContext') $retval = " onChange=\"sT(this,'itemattributes','contextId','itemId','{$id}')\" ";
      if ($query === 'itemTime') $retval = " onChange=\"sT(this,'itemattributes','timeframeId','itemId','{$id}')\" ";
      if ($query === 'itemNA') $retval = " onClick=\"cB(this,'nextactions','nextaction','nextaction','{$id}')\" ";
      if ($query === 'itemComplete') $retval = " onClick=\"cB(this,'itemstatus','dateCompleted','itemId','{$id}')\" ";
      if ($query === 'itemTitle') $retval = " onFocus=\"sEf(this,'items','title','itemId','{$id}')\" ";
      if ($query === 'itemDescription') $retval = " onFocus=\"sEf(this,'items','description','itemId','{$id}')\" ";
      if ($query === 'itemLink') $retval = " onFocus=\"sEf(this,'items','hyperlink','itemId','{$id}')\" ";
      if ($query === 'itemDeadline') $retval = " onChange=\"sT(this,'itemattributes','deadline','itemId','{$id}')\" ";
      if ($query === 'itemCompletedNow') $retval = " onFocus=\"sT(this,'itemstatus','dateCompleted','itemId','{$id}')\" ";
      if ($query === 'itemCompletedEdit') $retval = " onChange=\"sT(this,'itemstatus','dateCompleted','itemId','{$id}')\" ";
      if ($query === 'itemSomeday') $retval = " onClick=\"cB(this,'itemattributes','isSomeday','itemId','{$id}')\" ";
      if ($query === 'itemTickler') $retval = " onClick=\"cB(this,'itemattributes','suppress','itemId','{$id}')\" ";
      if ($query === 'itemTicklerDays') $retval = " onChange=\"sT(this,'itemattributes','suppressUntil','itemId','{$id}')\" ";
      if ($query === 'itemTicklerDeadline') $retval = " onClick=\"cB(this,'itemattributes','suppressIsDeadline','itemId','{$id}')\" ";
      if ($query === 'checklistitemNotes') $retval = " onFocus=\"sEf(this,'checklistitems','notes','checklistItemId','{$id}')\" ";
      if ($query === 'checklistitem') $retval = " onClick=\"cB(this,'checklistitems','checked','checklistItemId','{$id}')\" ";
      if ($query === 'checklistiteminst') $retval = " onClick=\"cB(this,'checklistitemsinst','checked','checklistItemId','{$id}','instanceId','{$inst}')\" ";
      if ($query === 'checklistitemignore') $retval = " onClick=\"cB(this,'checklistitems','ignored','checklistItemId','{$id}')\" ";
      if ($query === 'checklistiteminstignore') $retval = " onClick=\"cB(this,'checklistitemsinst','ignored','checklistItemId','{$id}','instanceId','{$inst}')\" ";
    }
    return $retval;
}

function getVarFromGetPost($varName,$default='') {
    $retval=(isset($_GET[$varName]))?$_GET[$varName]:( (isset($_POST[$varName]))?$_POST[$varName]:$default );
    return $retval;
}

function nextScreen($url) {

    global $config;

    $cleanurl=htmlspecialchars($url);

    if ($config['debug'] & _GTD_WAIT) {
        echo "<p>Next screen is <a href='$cleanurl'>$cleanurl</a> - would be auto-refresh in non-debug mode</p>";
    } else if (headers_sent()) {
        echo "<META HTTP-EQUIV='Refresh' CONTENT='0;url=$cleanurl' />\n"
            ."<script type='text/javascript'>"
            ."var decodedUrl = decodeURIComponent('" . $cleanurl . "');"
            ."decodedUrl = decodedUrl.replace(/&amp;/g, '&');"
            ."window.location.replace(decodedUrl);"
            ."</script>\n"
            ."</head><body><a href='$cleanurl'>Click here to continue on to $cleanurl</a>\n";
            die();
    } else {
        $header="Location: "
                .$url;
        header($header);
        die();
/*        $header="Location: http"
                .((empty($_SERVER['HTTPS']))?'':'s')
                ."://"
                .$_SERVER['HTTP_HOST']
                .rtrim(dirname($_SERVER['PHP_SELF']), '/\\')
                .'/'.$url;
        header($header);
        exit;
*/
    }
}

function getChildType($parentType) {
switch ($parentType) {
    case "m" : $childtype=array("v","o","g","p","s"); break;
    case "v" : $childtype=array("o","g","p","s"); break;
    case "o" : $childtype=array("g","p","s"); break;
    case "g" : $childtype=array("p","s"); break;
    case "p" : $childtype=array("a","w","p","r","s"); break;
    case "s" : $childtype=array("a","w","p","r","s"); break;
    default  : $childtype=NULL; break; // all other items have no children
    }
return $childtype;
}

function getParentType($childType) {
$parentType=array();
switch ($childType) {
    case "a" : // deliberately flows through to "r"
    case "w" : // deliberately flows through to "r"
    case "r" : $parentType=array('p','s');
        break;
    case "i" : $parentType=array();
        break;
    case "p" :  // deliberately flows through to "s"
    case "s" : $parentType=array('g','p','s','o','v');
        break;
    case "g" : $parentType=array('o','v');
        break;
    case "o" : $parentType=array('g','v');
        break;
    case "v" : $parentType[]='m';
        break;
    default  :
        $parentType=array('p','s');
        break;
    }
return $parentType;
}

function getTypes($type=false) {
$types=array("m" => "Value",
            "v" => "Vision",
            "o" => "Role",
            "g" => "Goal",
            "p" => "Project",
            "a" => "Action",
            "i" => "Inbox",
            "s" => "Someday",
            "r" => "Reference",
            "w" => "Waiting On",
            "cl" => "Checklist",
            "cli" => "CL Item",
            "l" => "List",
            "li" => "List Item",
            // "ai" => "Ai Chat",
            "fi" => "FI Chat"
        );
if ($type===false)
    $out=$types;
elseif (empty($type))
    $out='item without a type assigned';
else
    $out=$types[$type];
return $out;
}


function escapeChars($str) {  // TOFIX consider internationalization issues with charset coding
    $outStr=str_replace(array('&','�'),array('&amp;','&hellip'),$str);
    $outStr=str_replace(array('&amp;amp;','&amp;hellip;'),array('&amp;','&hellip;'),$outStr);
    return $outStr;
}

function getShow($where,$type) {
    global $config;
    $show=array(
        'title'         => true,

        // only show if editing, not creating
        'lastModified'  =>($where==='edit'),
        'dateCreated'   =>($where==='edit'),
        'type'          =>($where==='edit' && ($type==='i' || $config['allowChangingTypes'])),

        // fields suppressed on certain types
        'description'   => ($type!=='m' && $type!=='v' && $type!=='p' && $type!=='o' && $type!=='g'),
        'conclusion'=>($type!=='r' && $type!=='a'),
        'desiredOutcome'=>($type!=='r'),
        'metaphor'=>($type!=='r' && $type!=='a' && $type!=='w' && $type!=='i'),
        'category'      =>($type!=='m'),
        'ptitle'        =>($type!=='m' && $type!=='i'),
        'dateCompleted' =>($type!=='m'),
        'complete'      =>($type!=='m' && $type!=='r'),
        'timeframe'     =>($type!=='m' && $type!=='v' && $type!=='w' && $type!=='r' && $type!=='i' && $type!=='o' && $type!=='g'),

        // fields only shown for certain types
        'context'       =>($type==='p' || $type==='i' || $type==='a' || $type==='w' || $type==='r'),
        'deadline'      =>($type==='p' || $type==='a' || $type==='w' || $type==='i'),
        'suppress'      =>($type==='p' || $type==='a' || $type==='w'),
        'suppressUntil' =>($type==='p' || $type==='a' || $type==='w'),
        'repeat'        =>($type==='null'),
        'NA'            =>($type==='a' || $type==='w'),
        'isSomeday'     =>($type==='p' || $type==='g'),

        // fields never shown on item.php
        'checkbox'      => false,
        'flags'         => false
        );

    if ($config['forceAllFields'])
        foreach ($show as $key=>$value)
            $show[$key]=true;

    return $show;
}
/*
   ======================================================================================
*/
function columnedTable($cols,$data,$link='itemReport.php') {
    $nrows=count($data);
    $displace=round($nrows/$cols+0.499,0);
    for ($i=0;$i<$nrows;) {
        echo "<tr>\n";
        for ($j=0;$j<$cols;$j++) {
            $ndx=$i/$cols+$j*$displace;
            if ($ndx<$nrows) {
                $row=$data[$ndx];
                echo "<td"
                    ,(empty($row['td.class'])) ? '' : " class='{$row['td.class']}' "
                    ,(empty($row['td.title'])) ? '' : " title='{$row['td.title']}' "
                    ,"><a href='";
                if (isset($row['type']) && $row['type'] == 'c')
                  echo "reportLists.php?listId={$row['listId']}&type=c";
                else if (isset($row['type']) && $row['type'] == 'l')
                  echo "reportLists.php?listId={$row['listId']}&type=l";
                else
                  echo "$link?itemId={$row['itemId']}";
                echo "' title='"
                    ,makeclean($row['description']),"'>"
                    ,makeclean($row['title']),"</a></td>\n";
            }
        }
        echo "</tr>\n";
        $i+=$cols;
    }
}
/*
   ======================================================================================
*/
function checkerB ($table,$itemIdCol,$itemId,$column,$checked,$col2='',$id2='',$col3='',$id3='',$col4='',$id4='',$col5='',$id5='') {
    $checkerS = '<input type="checkbox" class="mx"' . ' onClick="cB(this,\'' . $table . '\',\'' . $column . '\',\'' . $itemIdCol . '\',\'' . $itemId . '\'';
    if ($col2 != '') $checkerS .= ',\'' . $col2 . '\',\'' . $id2 . '\'';
    if ($col3 != '') $checkerS .= ',\'' . $col3 . '\',\'' . $id3 . '\'';
    if ($col4 != '') $checkerS .= ',\'' . $col4 . '\',\'' . $id4 . '\'';
    if ($col5 != '') $checkerS .= ',\'' . $col5 . '\',\'' . $id5 . '\'';
    $checkerS .= ')"' . ($checked=='y' ? ' checked' : '') . '>';
    return $checkerS;
}

function editableCol ($tmp1, $tmp2) {
    $html = '';
    for($i = 1; $i <= $tmp1; $i++) {
        $html .= "editableCol('listEd', " . $i;
        if($i == $tmp2) {
            $html .= ", true);";
        } else {
            $html .= ", false);";
        }
    }
    return $html;
}

function childUpd ($type,$itemId,$visId,$catId = '') {
    if ($catId > 0) { $catId = '&categoryId=' . $catId; } else { $catId = ''; }
    $catMultiId = '&catMultiId=6';
    switch ($type) {
        case 'l' : $title = 'list'; break;
        case 'c' : $title = 'checklist'; break;
        case 'p' : $title = 'project'; break;
        case 'g' : $title = 'goal'; break;
        case 'o' : $title = 'role'; break;
    }
    switch ($type) {
        case 'l' :
        case 'c' : $childUpd = '<a href="listsUpdate.php?itemId=' . $itemId . '&visId=' . $visId . '&type=' . $type . '&matrix=true' . $catId . $catMultiId . '" class="mx" target="_blank" title="' . $title . 's">' . strtoupper($type) . '</a>';
        break;
        default :  $childUpd = '<a href="item.php?parentId=' . $visId;
            if ($visId !== $itemId) $childUpd .= ',' . $itemId;
            $childUpd .= '&type=' . $type . '&action=create" class="mx" target="_blank" title="new ' . $title . '">+</a><a href="childrenUpdate.php?itemId=' . $itemId . '&visId=' . $visId . '&type=' . $type . '&matrix=true' . $catId . $catMultiId . '" class="mx" target="_blank" title="' . $title . 's">' . strtoupper($type) . '</a>';
    }

    return $childUpd;
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/*
   ======================================================================================
*/
function formula ($id) {

		$form = '';

    switch ($id) {
        case 'posIncTot' : // formulaSum1
            $form .= '                           $summary1 += $output1 - $pseudo2;';
            break;
        case 'negIncTot' : // formulaSum2
            $form .= '                           $summary2 += $output2 + $pseudo2;';
            break;
        case 'inc' : // formulaVis1
            $form .= '                           $output1 += ceil($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                    if (
                        is_numeric($value) &&
                        $value < 0
                        )                       $pseudo2 += floor($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                                                ';
            break;
        case 'scorCubeYr' : // formulaVis1
            $form .= 'if (
                        is_numeric($value) &&
                        $value > 0
                        )
												$output1
												+= ceil(pow($value,3)
												* $prob
												* $weight
												/ $years);
                    if (
                        is_numeric($value) &&
                        $value < 0
                        ) {
												$output1
												+= floor(pow($value,3)
												* $prob
												* $weight
												/ $years);
                        $pseudo2
												+= floor(pow($value,3)
												* $prob
												* $weight
												/ $years);
                        }';
            break;
/*
        case 'posCubeYr' : // formulaVis1
            $form .= 'if ($value > 0)            $output1 += ceil(pow($value,3) * $prob * $weight / $years);';
            break;
        case 'negCubeYr' : // formulaVis2
            $form .= 'if ($value < 0)            $output2 += floor(pow($value,3) * $prob * $weight / $years);';
            break;
        case 'posInc' : // formulaVis1
            $form .= 'if (
                        is_numeric($value) &&
                        $value > 0
                        )                       $output1 += ceil($value * ($prob > 0 ? 1/$prob : 0) * $weight);';
            break;
        case 'negInc' : // formulaVis2
            $form .= 'if (
                        is_numeric($value) &&
                        $value < 0
                        )                       $output2 += floor($value * ($prob > 0 ? 1/$prob : 0) * $weight);';
            break;
*/
        case 'incBrain' : // formulaVis1
            $form .= 'if (
                        $brainless != "y" &&
                        is_numeric($value) &&
                        $value > 0
                        )     $output1 += ceil($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                    if (
                        $brainless != "y" &&
                        is_numeric($value) &&
                        $value < 0
                        )     $pseudo2 += floor($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                        ';
            break;
        case 'incBrainless' : // formulaVis2
            $form .= 'if (
                        $brainless == "y" &&
                        is_numeric($value) &&
                        $value > 0
                        )     $bless1 += ceil($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                    if (
                        $brainless == "y" &&
                        is_numeric($value) &&
                        $value < 0
                        )     $bless2 += floor($value * ($prob > 0 ? 1/$prob : 0) * $weight);
                        ';
            break;
        case 'cubeYrItem' : // matrixfomula.php
            $form .= '                           $sum = round(pow($value,3) * $prob * $weight / $years, 2);';
            break;
        case 'maxYrs' : // formulaVis1
            $form .= 'if ($yrend > $outputYrs)    $outputYrs = $yrend;
                    if ($yearst < $outputYear &&
                        $outputYear)            $outputYear = $yearst;
                    if (!$outputYear)           $outputYear = $yearst;
                        ';
            break;
        case 'maxIntAll' : // formulaSum1
            $form .= 'if (is_numeric($output1) &&
                        (
                        !is_numeric($summary1)
                        || (
                        $output1 > $summary1 &&
                        is_numeric($summary1)
                        )))                       $summary1 = $output1;';
            break;
        case 'maxInt' : // formulaVis1
            $form .= 'if (is_numeric($value) &&
                        (
                        !is_numeric($output1)
                        || (
                        $value > $output1 &&
                        is_numeric($output1)
                        )))                      $output1 = $value;
                    if (is_numeric($value) &&
                        (
                        !is_numeric($pseudo2)
                        || (
                        $value < $pseudo2 &&
                        is_numeric($pseudo2)
                        )))                      $pseudo2 = $value;
                        ';
            break;
        case 'minIntAll' : // formulaSum2
            $form .= '
                    if (is_numeric($pseudo2))   $summary2 = $pseudo2;
                    if (is_numeric($output2) &&
                        (
                        !is_numeric($summary2)
                        || (
                        $output2 < $summary2 &&
                        is_numeric($summary2)
                        )))                      $summary2 = $output2;';
            break;
        case 'minInt' : // formulaVis2
            $form .= 'if (is_numeric($value) &&
                        (
                        !is_numeric($output2)
                        || (
                        $value < $output2 &&
                        is_numeric($output2)
                        )))                      $output2 = $value;';
            break;
    }
    return $form;
}
/*
   ======================================================================================
*/
// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
