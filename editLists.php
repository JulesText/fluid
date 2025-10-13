<?php
include_once('header.php');
include_once('lists.inc.php');

if ($values['listId']) {
    $row = query("select{$check}list",$config,$values,$sort);
    if (!$row) {
        echo "<p class='error'>That {$check}list does not exist</p>\n";
        include_once('footer.php');
        exit();
    }
    foreach (array('title','categoryId','premiseA','premiseB','conclusion','behaviour','standard','conditions','metaphor','hyperlink','sortBy','frequency','effort','scored','menu','prioritise','thrs_score','thrs_obs','sortItems') as $field)
        $values[$field]=$row[0][$field];
    $values['score_total'] = scoreCL($config, $values, $sort);
    $action='listedit';
    $urlVars = "?listId=" . $values['listId'] . "&type=". $type;
    $urlInst = "&instanceId=". $values['instanceId'];
} else {
    $values['title']='';
    $values['categoryId']='';
    $values['premiseA']='';
    $values['premiseB']='';
    $values['conclusion']='';
    $values['behaviour']='';
    $values['standard']='';
    $values['conditions']='';
    $values['metaphor']='';
    $values['hyperlink']='';
    $values['sortBy']='';
    $values['frequency']='';
    $values['effort']='';
    $values['scored']='';
    $values['menu']='';
    $values['prioritise']='';
    $values['thrs_score']='';
    $values['thrs_obs']='';
    $values['sortItems']='';
    $action='listcreate';
}
$cashtml = categoryselectbox($config,$values,$sort);
require_once("headerHtml.inc.php");
?>
<h2><?php echo ($values['listId'])?'Edit':'Create'," $check"; ?>list <?php
echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"reportLists.php". $urlVars . $urlInst . "\">Show List</a>&nbsp;]&nbsp;&nbsp;&nbsp;"; ?>&nbsp;[ <a href="listCatCodes.php">cat codes</a> ]<?php

echo '&nbsp;&nbsp;&nbsp;[ <a href="childrenMove.php?type=' . $type . '&itemId=' . $values['listId'] . '">Move in Matrix</a> ]';

?></h2>
<form action='processLists.php' method='post' onsubmit="return validate(this);">
	<div class='form'>
	   <div class='formrow'><span class="error" id='errorMessage'></span></div>
		<div class='formrow'>
            <input type='hidden' name='required' value='title:notnull:Title cannot be blank' />
    	    <input type='hidden' name='dateformat' value='ccyy-mm-dd' />
			<label for='title' class='left first'>List Title:</label>
			<input class="JKPadding" type='text' id='title' name='title' value='<?php echo makeclean($values['title']); ?>' />
		</div>
		<div class='formrow'>
			<label for='categoryId' class='left first'>Category:</label>
			<select name='categoryId' id='categoryId'>
                <?php echo $cashtml; ?>
			</select>
			<span class='label'>
			Prioritise: <input class="JKPadding" type='text' id='prioritise' name='prioritise' size='3' value='<?php echo makeclean($values['prioritise']); ?>' />
			CatCode: <input class="JKPadding" type='text' id='sortBy' name='sortBy' size='4' value='<?php echo makeclean($values['sortBy']); ?>' />
			<?php
			    if (isset($check) && $check == 'check') { ?>
        			Frequency / Year:<input class="JKPadding" type='text' id='frequency' name='frequency' size='4' value='<?php echo makeclean($values['frequency']); ?>' />
        			Effort / Year: <?php echo $values['effort']; ?> Hours
        			<input type='hidden' name='effort'      value='<?php echo $values['effort'];         ?>' />
        	<?php } ?>
			</span>
		</div>
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
        <div class='formrow'>
                <label for='outcome' class='left first'>Behaviour:</label>
                <input class="JKPadding" type="text" name="behaviour" id="outcome" value="<?php echo makeclean($values['behaviour']); ?>" />
        </div>
        <div class='formrow'>
                <label for='standards' class='left first'>Standards:</label>
                <input class="JKPadding" type="text" name="standard" id="outcome" value="<?php echo makeclean($values['standard']); ?>" />
        </div>
        <div class='formrow'>
                <label for='conditions' class='left first'>Conditions:</label>
                <input class="JKPadding" type="text" name="conditions" id="outcome" value="<?php echo makeclean($values['conditions']); ?>" />
        </div>
        <div class='formrow'>
                <label for='metaphor' class='left first'>Metaphor:</label>
                <input class="JKPadding" type="text" name='metaphor' id='metaphor' value='<?php echo makeclean($values["metaphor"]); ?>' />
        </div>
        <div class='formrow'>
			<label for='hyperlink' class='left first'>Hyperlink:</label>
			<input class="JKPadding" type='text' id='hyperlink' name='hyperlink' value='<?php echo makeclean($values['hyperlink']); ?>' />
		</div>
	</div>
	<div class='formbuttons'>
		<input type="submit" value="<?php echo ($values['listId'])?'Update':'Create'; ?>" name="submit" />
    	<input type="checkbox" name="menu" id='menu' class='notfirst' title="" value="y" <?php if ($values['menu']==='y') echo " checked='checked'";?>/>
    	<label for='menu'>Menu</label>
		<?php if ($values['listId']) { ?>
		  <input type="checkbox" name="delete" id='delete' class='notfirst' title="ALL items will be deleted!" value="y" />
		  <label for='delete'>Delete&nbsp;List</label>
      <input type="submit" value="Up Priorities" name="up_priorities" />
      <input type="submit" value="Down Priorities" name="down_priorities" />
      <label for='sortItems' class='left first'>Sort items:</label>
      <select name='sortItems' id='sortItems'>
        <option value="priority" <?php if ($values['sortItems'] == 'priority') echo ' selected="selected"'; ?>>prioritise > title > notes</option>
        <option value="title_notes" <?php if ($values['sortItems'] == 'title_notes') echo ' selected="selected"'; ?>>title 2 > notes 4 > prioritise</option>
        <option value="title" <?php if ($values['sortItems'] == 'title') echo ' selected="selected"'; ?>>title > notes 4 > prioritise</option>
        <option value="title_priority" <?php if ($values['sortItems'] == 'title_priority') echo ' selected="selected"'; ?>>title > prioritise > notes</option>
      </select>

		<?php }
    if ($check) { ?>
		<br>
		<br>
    <input type="checkbox" name="scored" id='scored' class='notfirst' title="" value="y" <?php if ($values['scored']==='y') echo " checked='checked'";?>/>
    <label for='scored'>Scored</label>
		<?php
	        $values['urlInst'] = $urlVars . $urlBulk . '&instanceId=';
            echo instanceselectbox($config,$values,$sort);
        ?>
		<input type="submit" value="Reset Scores" name="reset" />
    <br>
    <br>
    <span class='label'>
    Score total / obs: <?php echo $values['score_total']; ?>.
    Item threshold score (%): <input class="JKPadding" type='text' id='thrs_score' name='thrs_score' size='3' value='<?php echo makeclean($values['thrs_score']); ?>' />
    Item threshold observations: <input class="JKPadding" type='text' id='thrs_obs' name='thrs_obs' size='3' value='<?php echo makeclean($values['thrs_obs']); ?>' />
    </span>
    <?php } ?>

        <input type='hidden' name='instanceId'      value='<?php echo $values['instanceId'];         ?>' />
        <input type='hidden' name='type'      value='<?php echo $type;         ?>' />
        <input type='hidden' name='listId'        value='<?php echo $values['listId']; ?>' />
        <input type='hidden' name='action'    value='<?php echo $action;       ?>' />
	</div>
</form>
<?php include_once('footer.php'); ?>
