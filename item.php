<?php
//INCLUDES
include_once('header.php');

$values = array();
$values['itemId']= (isset($_GET['itemId']))?(int) $_GET['itemId']:0;
$values['parentId']=array();

//SQL CODE
if ($values['itemId']) { // editing an item
    $where='edit';
    $result = query("selectitem",$config,$values,$sort);
    if ($result) {
        $values = $result[0];
        //Test to see if nextaction
        $result = query("testnextaction",$config,$values,$sort);
        $nextaction= ($result && $result[0]['nextaction']==$values['itemId']);
        $parents = query("lookupparent",$config,$values);
        // if any are somedays, turn type 'p' into type 's'
    } else {
        echo "<p class='error'>Failed to retrieve item {$values['itemId']}</p>";
        return;
    }

} else { // creating an item
    $where='create';
    //set defaults
    $nextaction=false;
    $values['title']='';
    $values['description']='';
    $values['premiseA']='';
    $values['premiseB']='';
    $values['conclusion']='';
    $values['behaviour']='';
    $values['standard']='';
    $values['conditions']='';
    $values['hyperlink']='';
    $values['metaphor']='';
    $values['deadline']=null;
    $values['isTrade']=(isset($_GET['isTrade']) &&  $_GET['isTrade']=='y')?'y':'n';
    $values['tradeCondition']='';
    $values['tradeConditionId']=0;
    $values['dateCompleted']=null;
    $values['dateCreated']=($values['isTrade'] == 'y') ? date("Y-m-d") : null;
    $values['repeat']=null;
    $values['suppressUntil']=null;
    $values['type']=$_GET['type'];
    $values['isSomeday']=(isset($_GET['someday']) &&  $_GET['someday']=='true')?'y':'n';
    $nextaction=isset($_GET['nextonly']) && ($_GET['nextonly']=='true' || $_GET['nextonly']==='y');
    foreach ( array('category','context','timeframe') as $cat)
        $values[$cat.'Id']= (isset($_GET[$cat.'Id']))?(int) $_GET[$cat.'Id']:0;

    if ($values['type']==='s') {
        $values['isSomeday']='y';
        $values['type']='p';
    } elseif ($values['type']==='n') {
        $nextaction=true;
        $values['type']='a';
    } elseif ($values['type']==='a') {
        $nextaction=true;
    }
}

$show=getShow($where,$values);
// var_dump($show);die;

if (!$values['itemId']) {

    if ($show['suppress'] && isset($_GET['suppress']) && ($_GET['suppress']=='true' || $_GET['suppress']==='y')) {
        $values['suppress']='y';
        $values['suppressUntil']=$_GET['suppressUntil'];
    } else $values['suppress']='n';

    if ($show['deadline'] && !empty($_GET['deadline']))$values['deadline']=$_GET['deadline'];

    $parents=array();
    if ($show['ptitle'] && !empty($_GET['parentId'])) {
        $values['parentId']=explode(',',$_GET['parentId']);
        foreach ($values['parentId'] as $parent) {
            $result=query("selectitemshort",$config,array('itemId'=>$parent),$sort);
            if ($result) {
                $newparent=array(
                     'parentId'=>$result[0]['itemId']
                    ,'ptitle'=>$result[0]['title']
                    ,'isSomeday'=>$result[0]['isSomeday']
                    ,'ptype'=>$result[0]['type']);
                $parents[]=$newparent;
            }
        }
    }

}

if (is_array($parents) && count($parents))
    foreach ($parents as $row)
        $values['parentId'][]=$row['parentId'];

if ($values['isSomeday']==="y")
    $typename="Someday/Maybe";
else
    $typename=getTypes($values['type']);
if ($nextaction) $typename="Next ".$typename;
//create filters for selectboxes
$values['timefilterquery'] = ($config['useTypesForTimeContexts'] && $values['type']!=='i')?" WHERE ".sqlparts("timetype",$config,$values):'';

//create item, timecontext, and spacecontext selectboxes
// default
if ($values['timeframeId'] == '') $values['timeframeId'] = 2;
$tshtml = timecontextselectbox($config,$values,$sort);
$cashtml = categoryselectbox($config,$values,$sort);
$cshtml = contextselectbox($config,$values,$sort);
$tcshtml = tradeconditionselectbox($config,$values,$sort);

$oldtype=$values['type'];

//PAGE DISPLAY CODE
$title=(($values['itemId']>0)?'Edit ':'New ').$typename;

$hiddenvars=array(
            'referrer'=>(isset($_GET['referrer']))?$_GET['referrer']:''
            ,'type'   =>$values['type']
            ,'itemId' =>$values['itemId']
            );

if ($values['itemId']) {
    $hiddenvars['action']='fullUpdate';
} else
    $hiddenvars['action']='create';

$ptypes=getParentType($values['type']);
if ($_SESSION['useLiveEnhancements']) {
    $alltypes=getTypes();
    $allowedSearchTypes=array();
    if (count($ptypes)>1) $allowedSearchTypes[0]='All';
    foreach($ptypes as $ptype)
        $allowedSearchTypes[$ptype]=$alltypes[$ptype].'s';
    $values['ptypefilterquery']=" AND ia.`type` IN ('".implode("','",$ptypes)."') ";
    $potentialparents = query("parentselectbox",$config,$values,$sort);
    if (!$potentialparents) $potentialparents=array();
} elseif (count($ptypes))
    $values['ptypefilterquery']=" AND ia.`type`='{$ptypes[0]}' ";
if (count($ptypes)) $values['ptype']=$ptypes[0];

require_once("headerHtml.inc.php");

?><h2><?php
    if ($values['itemId'])
        echo "\n<a href='itemReport.php?itemId={$values['itemId']}'>"
            ,"<img src='themes/{$config['theme']}/report.gif' class='noprint' "
            ,"alt='Report' title='View Report' /></a>\n";
    echo $title;
?><?php
if (!empty($_GET['createnote'])) { ?>
    <p class='warning'>Notes have been superseded by tickler actions. These actions get
    suppressed until a specified number of days before their deadlines</p>
<?php }

echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="itemReport.php?itemId=' . $values['itemId'] . '">Report</a> ]';
echo '&nbsp;&nbsp;&nbsp;[ <a id="copy-button">Copy</a> ]';

if ($_GET['convert'] == true) {
    $canchangetypesafely=array('a','w','r','o','g','v','p');
    $other = " unlink qualities and/or children first?";
} elseif (in_array($values['type'], array('a','w','r'))) {
    $canchangetypesafely=array('a','w','r');
    $other = '[&nbsp;<a href="' . $_SERVER['REQUEST_URI'] . '&convert=true">Other</a>&nbsp;]';
} else {
    echo '&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;[&nbsp;<a href="' . $_SERVER['REQUEST_URI'] . '&convert=true">Convert</a>&nbsp;]';
		$canchangetypesafely=array();
}

if (in_array($values['type'], array('o','g','p'))) {
  echo '&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;[&nbsp;<a href="childrenMove.php?type=' . $values['type'] . '&itemId=' . $values['itemId'] . '">Move in Matrix</a>&nbsp;]';
}

$sep='';
if ((in_array($values['type'],$canchangetypesafely) || $values['type'] == 'i') && $values['itemId']) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Convert&nbsp;to&nbsp;&nbsp;&nbsp;[&nbsp;";
        foreach ($canchangetypesafely as $totype) {
            if ($totype!==$values['type']) {
                echo "$sep <a href='processItems.php?action=changeType&amp;itemId="
                    ,$values['itemId'],"&amp;safe=1&amp;type=$totype&amp;oldType=",$values['type'],"&amp;isSomeday="
                    ,$values['isSomeday'];
                if (!empty($referrer)) echo "&amp;referrer=$referrer";
                echo "'>",getTypes($totype),"</a>\n";
                $sep='&nbsp;]&nbsp &nbsp;[&nbsp;';
            }
        }
        echo "&nbsp;]&nbsp;&nbsp;&nbsp;" . $other;
}
/*
if ($show['type']) {
    echo $sep; ?>
    <a href='assignType.php?itemId=<?php echo $values['itemId']; ?>'>Convert to another type</a>
    (Warning, changing to another type will sever all relationships to its parent and child items)
    <?php
    $sep=' , ';
}
if ($sep!=='<p>') echo "</p>\n";
*/
echo "</h2>\n";
?>
<form action="processItems.php" method="post" onsubmit="return validate(this);"><div class='form'>
    <div class='formrow'><span class="error" id='errorMessage'></span></div>
        <?php

        if ($show['ptitle']) { ?>
            <div class='formrow'>
                <label for='parenttable' class='left first'>Parent(s):</label>
                <?php
                  // note this js enhancement could integrate live ajax updates but the effort isn't worth it for something i rarely use
                  if ($_SESSION['useLiveEnhancements']) {
                    include_once('liveParents.inc.php');
                } else { ?>
                    <select name="parentId[]" id='parenttable' multiple="multiple" size="6">
                        <?php echo parentselectbox($config,$values,$sort); ?>
                    </select>
                <?php } ?>
            </div>
        <?php } elseif (is_array($parents))
            foreach ($values['parentId'] as $parent)
                echo hidePostVar('parentId[]',$parent);
        ?><div class='formrow'>
            <?php if ($show['category']) { ?>
                <label for='category' class='left first'>Category:</label>
                <select name='categoryId' id='category' <?php
                  if ($values['itemId']) echo ajaxUpd('itemCategory', $values['itemId']);
                ?>>
                <?php echo $cashtml; ?>
                </select>
            <?php } else $hiddenvars['categoryId']=$values['categoryId'];
            if ($show['context']) { ?>
                <label for='context' class=''>Context:</label>
                <select name='contextId' id='context' <?php
                  if ($values['itemId']) echo ajaxUpd('itemContext', $values['itemId']);
                ?>>
                <?php echo $cshtml; ?>
                </select>
            <?php } else $hiddenvars['contextId']=$values['contextId'];
            if ($show['timeframe'] && $values['type'] != 'p') { ?>
                <label for='timeframe' class=''>Time:</label>
                <select name='timeframeId' id='timeframe' <?php
                  if ($values['itemId']) echo ajaxUpd('itemTime', $values['itemId']);
                  ?>>
                <?php echo $tshtml; ?>
                </select>
            <?php } else $hiddenvars['timeframeId']=$values['timeframeId']; ?>
            <?php if ($show['NA']) { ?>
                <label for='nextAction' class=''>Next Action:</label><input type="checkbox" name="nextAction" id="nextAction" value="y" <?php
                  if ($nextaction) echo " checked='checked' class='checked' ";
                  else echo " class='unchecked' ";
                  if ($values['itemId']) echo ajaxUpd('itemNA', $values['itemId']);
                ?> />
            <?php } else $hiddenvars['nextAction']=($nextaction)?'y':''; ?>
            <?php echo "<label for='isTrade'>Trade:</label>\n";
            echo "<input type='checkbox' name='isTrade' id='isTrade' value='y' title=''\n";
            if ($values['isTrade']==='y') echo " checked='checked' class='checked'";
            else echo " class = 'unchecked'";
            if ($values['itemId']) echo ajaxUpd('itemTrade', $values['itemId']);
            echo "/>";
            ?>
        </div>

        <?php if($show['dateCreated']) { ?>
            <div class='formrow'>
                <label for='dateCreated' class='left first'>Created:</label>
                <input type='text' size='10' name='dateCreated' id='dateCreated' class='hasdate' value='<?php echo $values['dateCreated']; ?>' <?php
                  if ($values['itemId']) echo ajaxUpd('itemDateCreated', $values['itemId']);
                ?>/>
                <button type='reset' id='dateCreated_trigger'>...</button>
                    <script type='text/javascript'>
                        Calendar.setup({
                            firstDay    :   <?php echo (int) $config['firstDayOfWeek']; ?>,
                            inputField  :   'dateCreated',   // id of the input field
                            ifFormat    :   '%Y-%m-%d',    // format of the input field
                            showsTime   :   false,          // will display a time selector
                            button      :   'dateCreated_trigger',   // trigger for the calendar (button ID)
                            singleClick :   true,          // single-click mode
                            step        :   1               // show all years in drop-down boxes (instead of every other year as default)
                        });
                    </script>
        <?php } else $hiddenvars['dateCreated']=$values['dateCreated']; ?>

        <?php
          if($show['tradeCondition']) { ?>
          <label for='tradeCondition' class=''>Condition:</label>
          <select name='tradeConditionId' id='tradeCondition' onChange='toggleTradeCondition()' <?php
            if ($values['itemId']) echo ajaxUpd('itemTradeCondition', $values['itemId']);
            ?>>
            <?php echo $tcshtml; ?>
            </select>* required
            <?php } else $hiddenvars['tradeConditionId']=$values['tradeConditionId']; ?>
        </div>

        <?php
        if($show['title']) { ?>
            <div class='formrow'>
                    <label for='title' class='left first'>Title:</label>
                    <input class="JKPadding" type="text" name="title" id="title" value="<?php echo makeclean($values['title']); ?>"
                    <?php # item being edited (has itemId) not created so allow ajax save
                      if ($values['itemId']) echo ajaxUpd('itemTitle', $values['itemId']);
                    ?> />
            </div>
        <?php } else $hiddenvars['title']=$values['title'];

        if ($show['description']) { ?>
            <div class='formrow' <?php
              if ($values['isTrade']==='y') {
                echo " id='descriptionForm'";
                if ($values['tradeConditionId'] > 1) echo " style='display: none'";
              }
            ?>>
                    <label for='description' class='left first'>Description:<br>Why?</label>
                    <textarea rows='10' cols='50' name='description' id='description' class='JKPadding'
                    <?php # item being edited (has itemId) not created so allow ajax save
                      if ($values['itemId']) echo ajaxUpd('itemDescription', $values['itemId']);
                    ?>><?php echo makeclean($values['description']); ?></textarea>
            </div>
        <?php } else $hiddenvars['description']=$values['description'];

        if ($show['conclusion']) { ?>
            <div <?php
                if ($values['isTrade']==='y') {
                  echo " id='conclusionForm'";
                  if ($values['tradeConditionId'] == 0) echo " style='display: none'";
                }
                ?>>
                <div class='formrow'>
                        <label for='premiseA' class='left first'>Premise&nbsp;A:</label>
                        <input class="JKPadding" type="text" name="premiseA" id="conclusion" value="<?php echo makeclean($values['premiseA']); ?>" />
                </div>
                <div class='formrow'>
                        <label for='premiseB' class='left first'>Premise&nbsp;B:</label>
                        <input class="JKPadding" type="text" name="premiseB" id="conclusion" value="<?php echo makeclean($values['premiseB']); ?>" />
                </div>
                <div class='formrow'>
                        <label for='conclusion' class='left first'>Conclusion:</label>
                        <input class="JKPadding" type="text" name="conclusion" id="conclusion" value="<?php echo makeclean($values['conclusion']); ?>" />
                </div>
            </div>
        <?php
        } // else $hiddenvars['behaviour']=$values['behaviour'];

        if ($show['behaviour']) { ?>
            <div <?php
                if ($values['isTrade']==='y') {
                  echo " id='outcomeForm'";
                  if ($values['tradeConditionId'] == 0) echo " style='display: none'";
                }
                ?>>
                <div class='formrow'>
                        <label for='outcome' class='left first'>
                        <?php if ($values['isTrade'] == 'y') {
                          echo 'Enter price:';
                        } else {
                          echo 'Behaviour:';
                        }
                        ?>
                        </label>
                        <input class="JKPadding" type="text" name="behaviour" id="outcome" value="<?php echo makeclean($values['behaviour']); ?>" />
                </div>
                <div class='formrow'>
                        <label for='standards' class='left first'>
                          <?php if ($values['isTrade'] == 'y') {
                            echo 'Exit price:';
                          } else {
                            echo 'Standards:';
                          }
                          ?>
                        </label>
                        <input class="JKPadding" type="text" name="standard" id="outcome" value="<?php echo makeclean($values['standard']); ?>" />
                </div>
                <div class='formrow'>
                        <label for='conditions' class='left first'>
                          <?php if ($values['isTrade'] == 'y') {
                            echo 'Chance (p):';
                          } else {
                            echo 'Conditions:';
                          }
                          ?>
                        </label>
                        <input class="JKPadding" type="text" name="conditions" id="outcome" value="<?php echo makeclean($values['conditions']); ?>" />
                </div>
            </div>
        <?php
        } else $hiddenvars['behaviour']=$values['behaviour'];


        ?>
        <div class='formrow'>
            <?php if ($show['deadline']) { ?>
                <label for='deadline' class='left first'>Due date:</label>
                <input type='text' size='10' name='deadline' id='deadline' class='hasdate' value='<?php echo $values['deadline']; ?>' <?php
                  if ($values['itemId']) echo ajaxUpd('itemDeadline', $values['itemId']);
                ?>/>
                <button type='reset' id='deadline_trigger'>...</button>
                    <script type='text/javascript'>
                        Calendar.setup({
                            firstDay    :   <?php echo (int) $config['firstDayOfWeek']; ?>,
                            inputField  :   'deadline',   // id of the input field
                            ifFormat    :   '%Y-%m-%d',    // format of the input field
                            showsTime   :   false,          // will display a time selector
                            button      :   'deadline_trigger',   // trigger for the calendar (button ID)
                            singleClick :   true,          // single-click mode
                            step        :   1               // show all years in drop-down boxes (instead of every other year as default)
                        });
                    </script>
            <?php } else $hiddenvars['deadline']=$values['deadline'];
            if ($show['dateCompleted']) { ?>
                <label for='dateCompleted' class=''>Completed:</label><input type='text' size='10' class='hasdate' name='dateCompleted' id='dateCompleted' value='<?php echo $values['dateCompleted'] ?>' <?php
                  if ($values['itemId'] && strlen($values['dateCompleted']) < 1) echo ajaxUpd('itemCompletedNow', $values['itemId']);
                  if ($values['itemId'] && strlen($values['dateCompleted']) > 1) echo ajaxUpd('itemCompletedEdit', $values['itemId']);
                ?>/>
                <button type='reset' id='dateCompleted_trigger' <?php if (strlen($values['dateCompleted']) < 1) echo 'hidden'; ?>>...</button>
                    <script type='text/javascript'>
                        Calendar.setup({
                            firstDay    :    <?php echo (int) $config['firstDayOfWeek']; ?>,
                            inputField  :   'dateCompleted',      // id of the input field
                            ifFormat    :   '%Y-%m-%d',    // format of the input field
                            showsTime   :   false,          // will display a time selector
                            button      :   'dateCompleted_trigger',   // trigger for the calendar (button ID)
                            singleClick :   true,          // single-click mode
                            step        :   1               // show all years in drop-down boxes (instead of every other year as default)
                        });
                    </script>
                <button type='button' id='dateCompleted_today' <?php if (strlen($values['dateCompleted']) > 1) echo 'hidden'; ?> onclick="javascript:completeToday('dateCompleted');focusOnForm('dateCompleted');">Today</button>
            <?php } else $hiddenvars['dateCompleted']=$values['dateCompleted']; ?>
        </div>
        <?php if ($show['repeat']) { ?>
        <div class='formrow'>
                <label for='repeat' class='left first'>Repeat every&nbsp;</label><input type='text' name='repeat' id='repeat' size='3' value='<?php echo $values['repeat']; ?>' /><label for='repeat'>&nbsp;days</label>
        </div>
        <?php } else $hiddenvars['repeat']=$values['repeat']; ?>
        <div class='formrow'>

            <?php
            if (/*$show['isSomeday']*/ 1) { ?><br>
                <label for='isSomeday' class='left first'>Sday/Maybe:</label> &nbsp;<input type='checkbox' name='isSomeday' id='isSomeday' value='y' title='Places item in Someday file'<?php
                  if ($values['isSomeday']==='y') echo " checked='checked' class='checked'";
                  else echo " class = 'unchecked'";
                  if ($values['itemId']) echo ajaxUpd('itemSomeday', $values['itemId']);
                ?> />
            <?php } else $hiddenvars['isSomeday']=$values['isSomeday']; ?>
        <!-- </div> -->
        <input type='hidden' name='required'
        value='title:notnull:Title can not be blank.,deadline:date:Deadline must be a valid date.,dateCompleted:date:Completion date is not valid.,suppress:depends:You must set a deadline to base the tickler on:deadline,suppress:depends:You must set a number of days for the tickler to be active from:suppressUntil' />
        <input type='hidden' name='dateformat' value='ccyy-mm-dd' />
        <?php
        if (/*$show['suppress']*/ 1) { ?>
            <!-- <div class='formrow'> -->
								<label for='suppress' class='left'>Tickler:</label>
								<input type='checkbox' name='suppress' id='suppress' value='y' title='Temporarily puts this into the tickler file, hiding it from the active view' <?php
                  if ($values['suppress']=="y") echo " checked='checked' class='checked'";
                  else echo " class = 'unchecked'";
                  if ($values['itemId']) echo ajaxUpd('itemTickler', $values['itemId']);
                ?>/>
                <label for='suppressUntil'>&nbsp;</label>
                <input type='text' size='3' name='suppressUntil' id='suppressUntil' value='<?php
                  echo $values['suppressUntil'] . "'";
                  if ($values['itemId']) echo ajaxUpd('itemTicklerDays', $values['itemId']);
                ?> /><label for='suppressUntil'>&nbsp;days before due date</label>
								<label for='suppressIsDeadline' class='left'>Tickler date is deadline?</label>
								<input type='checkbox' name='suppressIsDeadline' id='suppressIsDeadline' value='y' title='' <?php
                  if ($values['suppressIsDeadline']=="y") echo " checked='checked' class='checked'";
                  else echo " class='unchecked'";
                  if ($values['itemId']) echo ajaxUpd('itemTicklerDeadline', $values['itemId']);
                ?>/>
						</div>
        <?php } else {
            $hiddenvars['suppress']=$values['suppress'];
            $hiddenvars['suppressUntil']=$values['suppressUntil'];
        }

        if ($show['metaphor']) { ?> <!-- 'm', 'v', 'o', 'g', 'p' -->
            <div class='formrow'>
                    <label for='metaphor' class='left first'>Metaphor:</label>
                    <input class="JKPadding" type="text" name='metaphor' id='metaphor' value='<?php echo makeclean($values["metaphor"]); ?>' />
            </div>
        <?php
        } else $hiddenvars['metaphor']=$values['metaphor'];
        ?>

        <div class='formrow'>
            <label for='hyperlink' class='left first'>Hyperlink:
                <?php
                    if ($values['hyperlink']) {
                        echo "<br>[&nbsp;" . faLink($values['hyperlink']) . "&nbsp;]";
                    }
                ?>
            </label>
            <input class="JKPadding" type="text" name="hyperlink" id="hyperlink" value="<?php
              echo makeclean($values['hyperlink']) . '"';
              if ($values['itemId']) echo ajaxUpd('itemLink', $values['itemId']);
              ?> />
        </div>

        <?php
        if (!$values['itemId']) $hiddenvars['lastcreate']=$_SERVER['QUERY_STRING'];
        foreach ($hiddenvars as $key=>$val) echo hidePostVar($key,$val);
        $key='afterCreate'.$values['type'];
        // always use config value when creating
        if (!empty($config['afterCreate'][$values['type']]) && empty($_SESSION[$key]))
            $_SESSION[$key]=$config['afterCreate'][$values['type']];

        if ($values['itemId'] && !empty($_SESSION[$key]))
            $tst=$_SESSION[$key];
        else
            $tst=$config['afterCreate'][$values['type']];

        echo "<div class='formrow'>\n<label class='left first'>View"
            ,($values['itemId'])?/*'updating'*/'':''/*'creating'*/
            ,":</label>&nbsp;\n";

        if ($show['ptitle'])
            echo "<input type='radio' name='afterCreate' id='parentNext' value='parent' class='first'"
                ,($tst=='parent')?" checked='checked' ":""
                ," /><label for='parentNext' class='right'>Parent</label>\n";

        echo "<input type='radio' name='afterCreate' id='itemNext' value='item' class='notfirst'"
                ,($tst=='item')?" checked='checked' ":""
                ," /><label for='itemNext' class='right'>Item</label>\n"
            ,"<input type='radio' name='afterCreate' id='listNext' value='list' class='notfirst'"
                ,($tst=='list')?" checked='checked' ":""
                ," /><label for='listNext' class='right'>Items</label>\n"
            ,"<input type='radio' name='afterCreate' id='anotherNext' value='another' class='notfirst'"
                ,($tst=='another')?" checked='checked' ":""
                ," /><label for='anotherNext' class='right'>Create another</label>\n";
        if ($values['type']==='p')
            echo "<input type='radio' name='afterCreate' id='childNext' value='child' class='notfirst'"
                ,($tst=='child')?" checked='checked' ":""
                ," /><label for='childNext' class='right'>Create action</label>\n";

        if (!empty($hiddenvars['referrer']) || !empty($_SESSION[$key])) {
            echo "<input type='radio' name='afterCreate' id='referrer' value='referrer' class='notfirst'"
                ,($tst=='referrer')?" checked='checked' ":''
                ," /><label for='referrer' class='right'>Previous</label>\n";
        }

        echo "</div>\n</div> <!-- form div -->\n<div class='formbuttons'>\n"
            ,"<input type='submit' value='"
            ,($values['itemId'])?"Update $typename":'Create'
            ,"' name='submit' />\n"
            /*,"<input type='reset' value='Reset' />\n"*/;
            if ($values['itemId']) {
            echo "<input type='checkbox' name='delete' id='delete' value='y' title='Deletes item. Child items are orphaned, NOT deleted.'/>\n"
                ,"<label for='delete'>Delete&nbsp;$typename</label>\n"
                ,"<input type='hidden' name='oldtype' value='$oldtype' />\n";
              }

        echo "</div>\n</form>\n";

if ($values['itemId']) {
        echo "  <div class='detail'>\n";
        echo "      <span class='detail'>&nbsp;Created: " . substr($values['dateCreated'],0,10) . "</span>\n";
        echo "      <span class='detail'>Modified: " . substr($values['lastModified'],0,10) . "</span>\n";
        echo "  </div>\n";
}
if ($_SESSION['useLiveEnhancements'] && !empty($values['ptype'])) {
    include_once ('searcher.inc.php');
    $partt= $ptitle= $pid ='[';
    $sep   ='';
    foreach ($potentialparents as $oneparent) {
        $pid   .=$sep.'"'.$oneparent['itemId'].'"';
        $ptitle.=$sep.'"'.str_replace(array('\\','"'),array('\\\\','\\"'),$oneparent['title']).'"'; // escape backslashes and double-quotes
        $partt .=$sep.'"'
                .(($oneparent['isSomeday']==='y')?'s':$oneparent['type'])
                .'"';
        $sep    =',';
    }
    $pid   .=']';
    $ptitle.=']';
    $partt .=']';
    if (count($allowedSearchTypes)===1) $partt='""';
    // TOFIX - this javascript is very probably inefficient, but I don't have the resources to optimise it
    ?><script type='text/javascript'>
        /* <![CDATA[ */
        addEvent(window,'load',function() {
            var types=new Object();
            <?php
                foreach ($alltypes as $key=>$val)
                    echo "types['$key']='$val';\n";
            ?>
            mapTypeToName(types);
            gtd_searchdiv_init(
                <?php echo "$pid\n,\n$ptitle\n,\n$partt\n,\"{$values['ptype']}\" \n"; ?>
            );
            gtd_refinesearch('<?php echo $values['ptype']; ?>');
        });

        /* ]]> */
    <?php //JK cursor focus
        if ($values['itemId'] > 0) {
            // echo "focusOnForm('description');";
            echo "focusOnForm('nextAction');";
        } else {
            echo "focusOnForm('title');";
        }
    ?>
    </script><?php
    }

include_once('footer.php');
function hidePostVar($name,$val) {
    $val=makeclean($val);
    return "<input type='hidden' name='$name' value='$val' />\n";
}
?>
