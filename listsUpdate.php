<?php
include_once('header.php');
include_once('lists.inc.php');

if ($_GET['type']=='C' || $_GET['type']=='c') { 
    $values['type'] = 'c';
    $check = 'check';
    $type = 'c';
}
if ($_GET['type']=='L' || $_GET['type']=='l') {
    $values['type'] = 'l';
    $check = '';
    $type = 'l';
}

$cashtml=categoryselectbox($config,$values,$sort);
$values['filterquery']="";
if ($_GET['catMultiId']) { $qry = "l.`categoryId` = '' OR l.`categoryId` = '" . $_GET["catMultiId"] . "' OR "; } else { $qry = ''; }
if ($values['categoryId']) $values['filterquery']= " WHERE " . $qry . sqlparts("listcategoryfilter",$config,$values);
$result = query("get{$check}lists",$config,$values,$sort);
$createURL="editLists.php?$urlSuffix"
            .(($values['categoryId']) ? "&amp;categoryId={$values['categoryId']}" : '');

$values['parentId'] = $_GET['itemId'];            
$resultListX = query("getchildlists",$config,$values,$sort);

/*echo '<pre>';
var_dump($result);
var_dump($values);
var_dump($resultListX);
die;*/

require_once("headerHtml.inc.php");
?>
<h2><a href="<?php echo $createURL; ?>" title="Add new list" ><?php echo $check; ?>lists</a></h2>

<?php /*

<form action="" method="post">
    <div id="filter">
        <label>Category:</label>
        <select name="categoryId" title="Filter lists by category">
            <?php echo $cashtml; ?>
        </select>
        <input type="submit" class="button" value="Filter" name="submit" title="Filter list by category" />
        <input type='hidden' name='type' value='<?php echo $type; ?>' />
    </div>
</form>

*/ 

// check children / vision parents
$highlights = array();
if ($_GET['visId'] > 0 && $_GET['visId'] != $_GET['itemId']) {
    // highlight vision's lists
    $values = array();
    $values['type'] = $type;
    $values['parentId'] = $_GET['visId'];
    $resultV = query("getchildlists",$config,$values,$sort);
    //var_dump($values);
    if (count($resultV) > 0 && is_array($resultV)) {
        foreach ((array) $resultV as $l) array_push($highlights, $l['id']);
    }
} elseif ($_GET['visId'] == $_GET['itemId']) {
    // highlight children's lists

    //RETRIEVE VARIABLES
    $values=array();
    $values['itemId'] = (int) $_GET['visId'];
    
    //Get item details
    $values['childfilterquery']=' WHERE '.sqlparts('singleitem',$config,$values);
    $values['filterquery']=sqlparts('isNA',$config,$values);
    $values['extravarsfilterquery'] =sqlparts("getNA",$config,$values);;
    $resultV = query("getitemsandparent",$config,$values,$sort);
    $item = ($resultV)?$resultV[0]:array();
    $values['isSomeday']=($item['isSomeday']=="y")?'y':'n';
    $values['type']=$item['type'];
    $pitemId = $values['itemId'];
    $values['parentId']=$values['itemId'];
    $values['type']='';
    $values['filterquery'] ='';
    $resultV = array();
    $resultV = query("getchildren",$config,$values,$sort);
    
    foreach ((array) $resultV as $child) {
        $values = array();
        $values['type'] = $type;
        $values['parentId'] = $child['itemId'];
        $resultV = query("getchildlists",$config,$values,$sort);
        //var_dump($values);
        if (count($resultV) > 0 && is_array($resultV)) {
            foreach ((array) $resultV as $l) array_push($highlights, $l['id']);
        }
    }
}

?>

<?php if ($result) { 
?>
    <form action='processLists.php<?php if ($_GET['matrix']) echo '?matrix=true'; ?>' method='post'>
    <?php $tstr = '       
    <table class="" id="categorytable" summary="table of categories" style="float: left;">
        <thead><tr>
            <td class="mx">Title</td>
            <td class="mx">Added</td>
            <td class="mx">Cat</td>
        </tr></thead>
        <tbody>';
    $tstre = '</tbody>
    </table>';

            echo $tstr;
            $i = 0; 
            foreach ($result as $row) { ?>
            <tr>
                <td class="mx"><?php
                    echo makeclean($row['title']);
                ?></td>
                <td class="mx"><input class="mx" name="addedList[]" value="<?php echo $row['id']; ?>" type="checkbox" <?php 
                        foreach ($resultListX as $resultListN) {
                            if ($resultListN['id'] == $row['id']) echo 'checked="checked"';
                        }

                        if (in_array($row['id'], $highlights)) echo ' style="outline: 3px solid #aca;"';

                    ?>/></td>
                <?php
                    
                    echo '<td class="mx">';
                    echo '<span style="opacity: 0.3">';
                    echo substr(makeclean($row['category']),0,3);
                    echo '</span></td>';
                    
                ?>
                
            </tr><?php 
                $i++;
                if ($i == 26) {
                    echo $tstre . $tstr;
                    $i = 0;
                }
            } 
            echo $tstre;
            ?>
    <div class='formbuttons'>
        <input type='submit' name='submit' value='update' />JK
            <input type='submit' name='clearitemlists' value='Clear all' />
        <input type='hidden' name='itemId' value='<?php echo $_GET['itemId']; ?>' />
        <?php if ($_GET['visId'] > 0) echo "<input type='hidden' name='visId' value='" . $_GET['visId'] . "' />"; ?>                
        <input type='hidden' name='action' value='listupdate' />
        <input type='hidden' name='source' value='true' />
        <input type='hidden' name='type' value='<?php echo $type; ?>' />
    </div>
</form>    
<?php } 
else {
    $message="You have not defined any lists yet.";
    $prompt="Would you like to create a new list?";
    nothingFound($message,$prompt,$createURL);
}

include_once('footer.php');
?>
