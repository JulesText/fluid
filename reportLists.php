<?php
include_once('header.php');
include_once('lists.inc.php');

$result = query("select{$check}list",$config,$values,$sort);

if ($result==1) {
    echo "<p class='error'>That {$check}list does not exist</p>\n";
    include_once('footer.php');
    exit();
}
$row=$result[0];

if (!isset($_REQUEST['content']) || $_REQUEST['content'] !== 'bulk') {
  $sort['getlistitems'] = $sort['getlistitemsprioritise'];
  $sort['getchecklistitems'] = $sort['getchecklistitemsprioritise'];
}

if (isset($_REQUEST['content']) && $_REQUEST['content'] == 'bulk') {
  $sort['getlistitems'] = $sort['getlistitemsbulk'];
  $sort['getchecklistitems'] = $sort['getchecklistitemsbulk'];
}

$values['filterquery']= " AND ".sqlparts("activelistitems",$config,$values);
$result1=query("get{$check}listitems",$config,$values,$sort);

if (!$isChecklist) {
    $values['filterquery']= " AND ".sqlparts("completedlistitems",$config,$sort);
    $result2=query("get{$check}listitems",$config,$values,$sort);
    if (!$result2) $result2=array();
}
$createURL="editListItems.php?id={$row['id']}&amp;$urlSuffix";

$prioritise = $row['prioritise'];

$item['title'] = $row['title']; // page title

$scored = false;
if (isset($check) && $check == 'check' && $row['scored'] == 'y') $scored = true;

require_once("headerHtml.inc.php");
$urlVars = "?id=" . $row['id'] . "&type=". $type;
$urlInst = "&instanceId=". $values['instanceId'];
$urlBulk = "&content=". $_REQUEST['content'];
?>

<script type='text/javascript' >
$( document ).ready(function() {
    //document.getElementById("itemtable").focus();
    //$('input[name="submit"]').focus();

});
</script>

<h1><?php echo $row['title']; ?>&nbsp;&nbsp;&nbsp;

<?php
  if (isset($_REQUEST['content']) && $_REQUEST['content'] == 'bulk') {
      echo "[&nbsp;<a href=\"reportLists.php". $urlVars . $urlInst . "\">Show List</a>&nbsp;]&nbsp;&nbsp;&nbsp;";
  }
?>
  <span class='editbar'>[
    <a href='editLists.php<?php echo $urlVars . $urlInst; ?>'>Edit List</a>
]</span>
<?php
    if ($row['hyperlink']) {
        echo "&nbsp;&nbsp;&nbsp;[&nbsp;" . faLink($row['hyperlink']) . "&nbsp;]";
    }
    if (!isset($_REQUEST['content'])) {
        echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"reportLists.php". $urlVars . $urlInst . "&content=bulk\">Edit Items</a>&nbsp;]";
        echo "&nbsp;&nbsp;&nbsp;[&nbsp;<a href=\"reportLists.php". $urlVars . $urlInst . "&content=limit\">Limit</a>&nbsp;]";
    }
    if ($check) {
        $values['urlInst'] = $urlVars . $urlBulk . '&instanceId=';
        echo '&nbsp;&nbsp;&nbsp;' . instanceselectbox($config,$values,$sort);
    }
?>
</h1>
<p>
<?php
    #echo 'Prioritised: ',$prioritise,", ";
    $values['urlVars'] = $urlVars;
    $values['queryTable'] = $check . 'listitems';
    $values['queryKey'] = $check . 'listId';
    $values['queryValue'] = $row['id'];
    $values['priorityId'] = $prioritise;
    echo 'Prioritised: ' . priorityselectbox($config,$values,$sort) . ', ';

    if (!empty($row['category'])) echo 'Category: ',$row['category'],", ";
	?>Sort: <?php echo makeclean($row['sortBy']);
	if ($check) {
	    $effort = $row['effort']
	?>, Frequency: <?php echo makeclean($row['frequency']); ?> / Year, Effort: <?php echo $effort; ?> Hours
    <?php
      if ($scored) {
        $row['instanceId'] = $values['instanceId'];
        $row['score_total'] = scoreCL($config, $row, $sort);
        echo ', Score: ' . $row['score_total'];
      }
      echo '.';
     } ?>
<?php
    // $descriptionString = $row['description'];
    $descriptionString = '';
    if ($row['premiseA']) $descriptionString .= $row['premiseA'];
    if ($row['premiseA'] && $row['premiseB']) $descriptionString .= ", <br>";
    if ($row['premiseB']) $descriptionString .= $row['premiseB'];
    if ($row['premiseA'] || $row['premiseB']) $descriptionString .= ", <br>";
    if ($row['conclusion']) $descriptionString .= $row['conclusion'];

    if ($row['behaviour'] && empty($descriptionString)) { $desiredOutcomeStr = ""; } else { $desiredOutcomeStr = '<br><br>'; }
    if ($row['behaviour']) $desiredOutcomeStr .= $row['behaviour'];
    if ($row['standard']) $desiredOutcomeStr .= ", <br>" . $row['standard'];
    if ($row['conditions']) $desiredOutcomeStr .=  ", <br>" . $row['conditions'];
    $descriptionString .= $desiredOutcomeStr;

    if (!empty($descriptionString)) echo '<p class="JKSmallPadding">',trimTaggedString($descriptionString),"</p>\n";

    $sTTable1 = "'" . $check . "listitems'";
    $sTcol1 = "'" . $check . "listItemId','";
    if (!is_numeric($values['instanceId'])) {
        $sTTable2 = "'" . $check . "listitems'";
        $sTcol2 = "";
    } else {
        $sTTable2 = "'checklistitemsinst'";
        $sTcol2 = "'instanceId','" . $values['instanceId'] . "',";
    }

?>
<h2><a href='<?php echo $createURL; ?>' title='add a new item'>Add items</a></h2>
<?php if ($result1) {
    //echo '<pre>';var_dump($result1);die;
    // standard layout
    if (!isset($_REQUEST['content']) || $_REQUEST['content'] !== 'bulk') {
?>
        <form action='processLists.php' method='post'>
            <?php if ($check && $scored) { ?>
              <table class="datatable sortable" summary="table of list items">
            <?php } else { ?>
              <table class="datatable sortable" id="itemtable" summary="table of list items">
              <?php } ?>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                    <?php if ($check) { ?>
                        <?php if ($prioritise > -1) { ?>
                            <!-- <th>Priority</th> -->
                        <?php } ?>
                        <?php if ($effort) { ?>
                            <!-- <th>Mins</th> -->
                        <?php } ?>
                    <?php } ?>
                    <?php if ($check && $scored) { ?>
                        <th>Compl<a id='check_completed'>&#10004;</a></th>
                    <?php } else { ?>
                      <th>Completed</th>
                    <?php } if ($check && $scored) { ?>
                        <th>Ignored<a id='check_ignored'>&#10004;</a></th>
                    <?php } ?>
                    <?php if ($scored) { ?>
                        <th>Score</th>
                    <?php } ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                  if ($check && $scored) {
                    $str = "<tr><td></td><td></td>"
                      . "<td class='JKSmallPaddingFaded'><input type='checkbox' id='check_completed'></td>"
                      . "<td class='JKSmallPaddingFaded'><input type='checkbox' id='check_ignored'></td>"
                      . "<td></td></tr>";
                    #echo $str;
                  }
                ?>
                <?php foreach($result1 as $row) {
                    if ($row['priority'] > $prioritise && $prioritise > -1) continue;
                ?>
                    <tr>
                        <td class="JKSmallPadding" tabindex="2">
                            <?php
                            if ($prioritise > 0) echo '<span style="opacity: 0.6; font-size: medium;">P' . $row['priority'] . '</span>&nbsp;';
                            ?><a href="editListItems.php?itemId=<?php
                            echo $row['itemId'],'&amp;',$urlSuffix;
                            ?>" title="Edit"><?php echo makeclean($row['item']); ?></a>
                            </td>
                        <td class="JKSmallPadding">
                          <?php
                          # exception if contains hyperlink avoid ajax update
                          if (strstr(trimTaggedString($row['notes']), '<a href='))
                            echo trimTaggedString($row['notes']) . '<div>';
                          # otherwise do it
                          else
                            echo '<div contenteditable="true" tabindex="3"'
                                  . ajaxUpd($check . "listitemNotes", $row['itemId']) . '>' . trimTaggedString($row['notes']) . '</div><div>';
                          if ($row['hyperlink']) {
                              if (strlen($row['notes'])>0) echo "<br>";
                              echo faLink($row['hyperlink']);
                          }
                        ?></div></td>
                        <?php /*
                        if ($check) {
                            if ($prioritise > -1) { ?>
                                <td class="JKSmallPadding">
                                <?php
                                    echo trimTaggedString($row['priority']);
                                ?>
                                </td>
                            <?php }
                            if ($effort) {
                            ?>
                            <td class="JKSmallPadding">
                        <?php
                            echo trimTaggedString($row['effort']);
                        ?>
                            </td>
                        <?php
                            }
                            } */
                        ?>
                        <td class="JKSmallPaddingFaded">
													<input tabindex="1" type="checkbox" name="completed[]" title="Complete" value="<?php
                            echo $row['itemId'],
														'"',
                            ($isChecklist && $row['checked']==='y')?" checked='checked' class='checked' ":'',
                            ($isChecklist && $row['checked']==='n')?" class='unchecked' ":'';
                            if ($isChecklist) {
                              if (!is_numeric($values['instanceId'])) echo ajaxUpd('checklistitem', $row['itemId']);
                              else echo ajaxUpd('checklistiteminst', $row['itemId'], $values['instanceId']);
                            }
                            ?> />
                        </td>
                    <?php if ($check && $scored) { ?>
                        <td class="JKSmallPaddingFaded"><input type="checkbox" name="ignored[]" title="Ignore" value="<?php
                            echo $row['itemId'],'"',($isChecklist && $row['ignored']==='y')?" checked='checked' ":'';
                            if (!is_numeric($values['instanceId'])) echo ajaxUpd('checklistitemignore', $row['itemId']);
                            else echo ajaxUpd('checklistiteminstignore', $row['itemId'], $values['instanceId']);
                            ?> />
                        </td>
                    <?php } ?>
                        <?php
                            if ($scored) {
                        ?>
                        <td class="JKSmallPadding">
                        <?php
                            if ($row['assessed'] > 0) {
#                                $score = number_format((float)$row['score'] / $row['assessed'], 1, '.', '');
	                            $score = round($row['score'] / $row['assessed'], 2) * 100;
	                            if($score == 100) $score = 99;
	                            if($score > 0 && $score < 10) $score = "0" . $score;
	                            $score = $score . "%<br>" . $row['assessed'];
														} else {
																$score = $row['score'] . '/0';
														}
                           echo $score;
                        ?>
                        </td>
                        <?php } ?>
                    </tr><?php
                } ?>
                </tbody>
            </table>
            <div class='formbuttons'>
                <input type='submit' name='submit' value='update' />
                <?php if ($isChecklist) { ?> JK
                    <input type='submit' name='listclear' value='Clear checked' />
                    <?php if ($scored) { ?>
                        <input type='hidden' name='scored' value='y' />
                        <input type='submit' name='ignoreclear' value='Clear ignored' />
                        <input type='submit' name='allclear' value='Clear all' />
                    <?php } ?>
                        <input type='hidden' name='instanceId' value='<?php echo $values['instanceId']; ?>' />
                <?php } ?>
                <!-- <input type='hidden' name='prioritise' value='y' /> -->
                <input type='hidden' name='id' value='<?php echo $row['id']; ?>' />
                <input type='hidden' name='action' value='listcomplete' />
                <input type='hidden' name='type' value='<?php echo $type; ?>' />
            </div>
        </form>
<?php
    } elseif ($_REQUEST['content'] == 'bulk') {
?>
        <link rel="stylesheet" href="themes/default/dataTables.css" type="text/css" media="Screen" />

            <table class='listEd'>
                <thead>
                    <tr>
                        <?php
                            $x = 2;
                            $y = 0;
                            if ($prioritise > -1) $x++;
                            if ($check) $x++;
                            if ($scored) $x = $x + 2;
                        ?>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Item</th>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Description</th>
                    <?php if ($prioritise > -1) { ?>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Priority</th>
                    <?php } ?>
                    <?php if ($check) { ?>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Mins</th>
                    <?php } ?>
                    <?php if ($scored) { ?>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Score</th>
                        <th class='contList' onClick="<?php $y++; echo editableCol($x, $y); ?>">Assess</th>
                    <?php } ?>
                    </tr>
                </thead>
                <tbody>

                <?php foreach($result1 as $row) { ?>
                    <tr>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable1; ?>,'item',<?php echo $sTcol1 . $row['itemId']; ?>')" onFocus="sE(this)"><?php
                            echo trim(nl2br($row['item']));
                        ?></td>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable1; ?>,'notes',<?php echo $sTcol1 . $row['itemId']; ?>')" onFocus="sE(this)"><?php
                            echo ajaxLineBreak($row['notes']);
                        ?></td>
                        <?php if ($prioritise > -1) { ?>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable1; ?>,'priority',<?php echo $sTcol1 . $row['itemId']; ?>')" onFocus="sE(this)"><?php
                                echo $row['priority'];
                            ?></td>
                        <?php }
                        if ($check) { ?>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable1; ?>,'effort',<?php echo $sTcol1 . $row['itemId']; ?>'); calcCL('<?php echo $row['id']; ?>')" onFocus="sE(this)"><?php
                            echo $row['effort'];
                            ?></td>
                        <?php }
                        if ($scored) { ?>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable2; ?>,'score',<?php echo $sTcol2 . $sTcol1 . $row['itemId']; ?>')" onFocus="sE(this)"><?php
                                echo $row['score'];
                            ?></td>
                            <td class="JKSmallPadding" contenteditable="true" onBlur="sT(this,<?php echo $sTTable2; ?>,'assessed',<?php echo $sTcol2 . $sTcol1 . $row['itemId']; ?>')" onFocus="sE(this)"><?php
                                echo $row['assessed'];
                            ?></td>
                        <?php } ?>
                    </tr><?php
                } ?>
                </tbody>
            </table>

<?php
    }
} else {
?>
<p>There are no <?php
    echo ($isChecklist) ? 'check' : 'incomplete '
        ,"list items. <a href='$createURL'>"; ?>Create one</a></p>
<?php }
if (!$isChecklist && count($result2)) {  // it's an ordinary list, so split table into complete and incomplete
    ?>
    <h2>Completed List Items</h2>
    <table class="datatable sortable" id="donetable" summary="completed list items">
        <thead><tr>
            <th>Item</th>
            <th>Notes</th>
            <th>Completed</th>
        </tr></thead>
        <tbody>
            <?php foreach($result2 as $row) { ?>
                <tr>
                    <td><a href="editListItems.php?itemId=<?php
                        echo $row['itemId']; ?>" title="Edit"><?php
                            echo makeclean($row['item']);
                        ?></a>
                    </td>
                    <td><?php echo trimTaggedString($row['notes']); ?></td>
                    <td><?php echo $row['dateCompleted']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php }
include_once('footer.php'); ?>
