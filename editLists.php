<?php
include_once('header.php');
include_once('lists.inc.php');

if ($values['id']) {
    $row = query("select{$check}list",$config,$values,$sort);
    if (!$row) {
        echo "<p class='error'>That {$check}list does not exist</p>\n";
        include_once('footer.php');
        exit();
    }
    foreach (array('title','categoryId','premiseA','premiseB','conclusion','behaviour','standard','conditions','metaphor','hyperlink','sortBy','frequency','effort','scored','menu','prioritise') as $field)
        $values[$field]=$row[0][$field];
    $action='listedit';
    $urlVars = "?id=" . $values['id'] . "&type=". $type;
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
    $action='listcreate';
}
$cashtml = categoryselectbox($config,$values,$sort);
require_once("headerHtml.inc.php");
?>
<h2><?php echo ($values['id'])?'Edit':'Create'," $check"; ?>list</h2>
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
			Sort: <input class="JKPadding" type='text' id='sortBy' name='sortBy' size='4' value='<?php echo makeclean($values['sortBy']); ?>' />
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
		<input type="submit" value="<?php echo ($values['id'])?'Update':'Create'; ?>" name="submit" />
    	<input type="checkbox" name="scored" id='scored' class='notfirst' title="" value="y" <?php if ($values['scored']==='y') echo " checked='checked'";?>/>
    	<label for='scored'>Scored</label>
    	<input type="checkbox" name="menu" id='menu' class='notfirst' title="" value="y" <?php if ($values['menu']==='y') echo " checked='checked'";?>/>
    	<label for='menu'>Menu</label>
		<?php if ($values['id']) { ?>
		  <input type="checkbox" name="delete" id='delete' class='notfirst' title="ALL items will be deleted!" value="y" />
		  <label for='delete'>Delete&nbsp;List</label>
		<?php } ?>
		<br>
		<br>
		<br>
		<?php 
	        $values['urlInst'] = $urlVars . $urlBulk . '&instanceId=';
            echo instanceselectbox($config,$values,$sort);
        ?>
		<input type="submit" value="Reset Scores" name="reset" />

        <input type='hidden' name='instanceId'      value='<?php echo $values['instanceId'];         ?>' />
        <input type='hidden' name='type'      value='<?php echo $type;         ?>' />
        <input type='hidden' name='id'        value='<?php echo $values['id']; ?>' />
        <input type='hidden' name='action'    value='<?php echo $action;       ?>' />
	</div>
</form>
<?php include_once('footer.php'); ?>
