<?php
include_once('header.php');
include_once('lists.inc.php');

if ($values['itemId']) {
    $row = query("select{$check}listitem",$config,$values,$sort);
    if (!$row) {
        echo "<p class='error'>That {$check}list item does not exist</p>\n";
        include_once('footer.php');
        exit();
    }
    foreach (array('listId','item','notes', 'hyperlink', 'priority') as $field)
            $values[$field]=$row[0][$field];
    if ($isChecklist) {
        $values['checked']=$row[0]['checked'];
        $values['ignored']=$row[0]['ignored'];
        $values['effort']=$row[0]['effort'];
        $values['score']=$row[0]['score'];
        $values['assessed']=$row[0]['assessed'];
    }
    else
        $values['dateCompleted']=$row[0]['dateCompleted'];
    $action='itemedit';

		# page title
		$arr = array();
		preg_match_all('/\b([0-9A-Za-z]{3,})\b/', $values['notes'], $arr); # put words into array
		$keywords = array_slice(array_unique($arr[0]), 0, 2); # choose first 2 words
		$values['title'] = join(' ', $keywords);

} else {
    $values['listId']=(int) $_GET['listId'];
    $values['item']='';
    $values['notes']='';
    $values['hyperlink']='';
    $values['dateCompleted']=null;
    $values['checked']=null;
    $action='itemcreate';
}

$row = query("select{$check}list",$config,$values,$sort);
$ptitle = $row[0]['title'];

$lshtml = listselectbox($config,$values,$sort,$check);

require_once("headerHtml.inc.php");

?>
<h1><?php echo ($values['itemId'])?'Edit ':'Create ',$check; ?>list item in <a href="reportLists.php?listId=<?php echo $values['listId']; ?>&type=<?php echo $type; ?>"><?php echo $ptitle . ($isChecklist ? ' CL' : ' LIST'); ?></a>
&nbsp;&nbsp;&nbsp;[&nbsp;<a id='copy-button'>Link</a>&nbsp;]
<?php
if ($check && $action == 'itemedit') {
    $values['urlCL'] = 'processLists.php?action=moveitem&itemId=' . $values['itemId'] . '&checklistId=';
    echo '&nbsp;&nbsp;&nbsp;' . parentlistselectbox($config,$values,$sort);
}
?>
</h1>
<form action="processLists.php" method="post" onsubmit="return validate(this);">
    <div class='form'>
        <div class='formrow'><span class="error" id='errorMessage'></span></div>
        <div class='formrow'>
            <label class='left first' for='title'>Title:</label>
            <?php if (!$isInst) { ?>
            <input class='JKPadding' type='text' name='title' id='title' value='<?php echo makeclean($values['item']); ?>' size='80' />
          <?php } else echo "<p>" . makeclean($values['item']) . "</p>"; ?>
        </div>
        <div class='formrow'>
            <label for='notes' class='left first'>Description:</label>
            <?php if (!$isInst) { ?>
            <textarea class='JKPadding' rows='20' name='notes' id='notes' cols='80'><?php echo makeclean($values['notes']); ?></textarea>
            <?php } else echo "<p>" . makeclean($values['notes']) . "</p>"; ?>
        </div>
        <div class='formrow'>
            <label class='left first' for='hyperlink'>Hyperlink:
            <?php   if ($values['hyperlink']) {
                        echo "<br>[&nbsp;" . faLink($values['hyperlink']) . "&nbsp;]";
                    }
            ?>
            </label>
            <input class='JKPadding' type='text' name='hyperlink' id='hyperlink' value='<?php echo makeclean($values['hyperlink']); ?>' size='80' <?php if ($isInst) echo "hidden"; ?> />
        </div>
        <div class='formrow'>
            <?php
            // if ($values['itemId']) {
                ?>
                <?php
                if (!$isInst) {
                ?>
                <label class='left notfirst'>Priority:</label>
                <input class='JKPadding' size=2 type='text' name='priority' id='priority' value='<?php echo $values['priority']; ?>' />
                <?php
                }
                if ($isChecklist) { ?>
                    <label class='left notfirst'>Effort:</label>
                    <input class='JKPadding' size=2 type='text' name='effort' id='effort' value='<?php echo $values['effort']; ?>' />
                    <?php
                    if ($values['itemId']) { ?>
                        <label class='left notfirst'>Score:</label>
                        <input class='JKPadding' type='text' name='score' id='score' value='<?php echo $values['score']; ?>' size=2 />
                        <label class='left notfirst'>Assessed:</label>
                        <input class='JKPadding' type='text' name='assessed' id='assessed' value='<?php echo $values['assessed']; ?>' size=2 />
                    <?php } ?>
                	<input type='hidden' name='required' value='title:notnull:Title cannot be blank' />
                <?php
                } else {
                ?>
                    <label class='left notfirst'>Date Completed:</label>
                    <input type='text' name='dateCompleted' id='dateCompleted' value='<?php echo $values['dateCompleted']; ?>' />
                    <button type='reset' id='f_trigger_b'>...</button>
                    <script type="text/javascript">

                        Calendar.setup({
        					firstDay       :    <?php echo (int) $config['firstDayOfWeek']; ?>,
                            inputField     :    "f_date_b",      // id of the input field
                            ifFormat       :    "%Y-%m-%d",       // format of the input field
                            showsTime      :    false,            // will display a time selector
                            button         :    "f_trigger_b",   // trigger for the calendar (button ID)
                            singleClick    :    true,           // single-click mode
                            step           :    1                // show all years in drop-down boxes (instead of every other year as default)
                        });
                    </script>
                    <button type='button' id='dateCompleted_today' onclick="javascript:completeToday('dateCompleted');">Today</button>
                	<input type='hidden' name='required'
        	           value='title:notnull:Title cannot be blank,dateCompleted:date:Completion date is not valid' />
                <?php }
            // } else {
              ?>
        	   <!-- <input type='hidden' name='required' value='title:notnull:Title cannot be blank' /> -->
            <?php
            // }
            ?>
        </div>
    </div>
   <div class='formbuttons'>
       <?php if (!$isInst) { ?>
       <label class='left notfirst'>Item completed</label>
       <input class='score' type='checkbox' name='checked' <?php echo ($values['checked']==='y')?" checked='checked' ":'' ?> />
       <?php }
        if ($values['itemId']) { ?>
            <input type='submit' value='Update item' name='submit' />
        <?php } else { ?>
            <!-- by some wacky code the order of following 2 lines matters -->
            <!-- for submitting form by enter key, the first of the two is used as default -->
            <input type='submit' value='Create and return to list' name='submit' />
            <input type='submit' value='Create, then add another item' name='again' />
        <?php } ?>
        <!-- <input type='reset' value='Reset' /> -->
        <?php if ($values['itemId'] && !$isInst) { ?>
            <input type='checkbox' name='delete' id='delete' class='notfirst' value='y' />
            <label for='delete'>Delete&nbsp;Item</label>
        <?php } ?>
        <input type='hidden' name='instanceId'      value='<?php echo $values['instanceId'];         ?>' />
        <input type='hidden' name='type'       value='<?php echo $type;             ?>' />
        <input type='hidden' name='itemId'     value='<?php echo $values['itemId']; ?>' />
        <input type='hidden' name='listId'         value='<?php echo $values['listId'];     ?>' />
        <input type='hidden' name='ignored'         value='<?php echo $values['ignored'];     ?>' />
        <input type='hidden' name='action'     value='<?php echo $action;           ?>' />
    	<input type='hidden' name='dateformat' value='ccyy-mm-dd'                       />
    </div>
</form>
<script type='text/javascript'>focusOnForm('notes');</script>
<?php include_once('footer.php'); ?>
