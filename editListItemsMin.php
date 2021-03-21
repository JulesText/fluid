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
    foreach (array('id','item','notes', 'hyperlink') as $field)
            $values[$field]=$row[0][$field];
    if ($isChecklist) {
        $values['checked']=$row[0]['checked'];
        $values['ignored']=$row[0]['ignored'];
    }
    else
        $values['dateCompleted']=$row[0]['dateCompleted'];
    $action='itemedit';
} else {
    $values['id']=(int) $_REQUEST['id'];
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

/* require_once("headerHtml.inc.php"); */

?>
<?php echo '<pre>' . makeclean($values['item']) . '<br><br>'; ?>
<?php echo makeclean($values['notes']);?>
<?php echo '<br><br><a href="editListItems.php?type=c&itemId=' . $values['itemId'] . '">edit</a>'; ?>