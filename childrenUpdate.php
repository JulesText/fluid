<?php
include_once('header.php');
//include_once('lists.inc.php');


$values = array();
if (!isset($_GET['categoryId'])) $_GET['categoryId'] = false;
if (isset($_GET['catMultiId'])) { $qry = " OR ia.`categoryId` = '' OR ia.`categoryId` = '" . $_GET["catMultiId"] . "'"; } else { $qry = ''; }
$values['filterquery'] = " WHERE 1 = 1 ";
if (isset($_GET['type'])) $values['filterquery'] .= " AND ia.type = '" . $_GET['type'] . "' ";
if ($_GET['categoryId']) $values['filterquery'] .= " AND (ia.categoryId = '" . $_GET['categoryId'] . "'" . $qry . ")";
$values['filterquery'] .= " AND its.dateCompleted IS NULL ";
$result = query("getitems",$config,$values,$sort);
//echo '<pre>'; var_dump($values);die;
$createURL="item.php?parentId=" . $_GET['itemId'] . "&action=create&type=" . $_GET['type'];

$values['filterquery'] = " AND ia.type = '" . $_GET['type'] . "' ";
$values['parentId'] = $_GET['itemId'];
$resultCh = query("getchildren",$config,$values,$sort);

/*
echo '<pre>';
var_dump($config);
var_dump($values);
var_dump($sort);
var_dump($resultCh);
die;
*/

require_once("headerHtml.inc.php");
?>
<h2><a href="<?php echo $createURL; ?>" title="Add new" >Add new</a></h2>

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

*/ ?>

   <form action='processItems.php' method='post'>
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
            foreach ($result as $row) {
              if ($row['itemId'] == $_GET['itemId']) continue; # not reference self
              ?>
            <tr>
                <td class="mx"><?php
                    echo makeclean($row['title']);
                ?></td>

                <td class="mx"><input class="mx" name="addedItem[]" value="<?php echo $row['itemId']; ?>" type="checkbox" <?php
                        if (is_array($resultCh)) foreach ($resultCh as $resCh) {
                            if ($resCh['itemId'] == $row['itemId']) {
                                if ($_REQUEST['visId'] > 0) {
                                    $values = array();
                                    $values['parentId'] = $_REQUEST['visId'];
                                    $values['itemId'] = $resCh['itemId'];
                                    $resultV = query("checklookup",$config,$values,$sort);
                                    if (count($resultV) > 0 && is_array($resultV)) {
                                        echo 'checked="checked"';
                                    } else {
                                        echo 'style="outline: 3px solid #0c0;"';
                                    }
                                } else {
                                echo 'checked="checked"';
                                }
                            }
                        }
                    ?>/></td>
                <?php

                    echo '<td class="mx">';
                    echo '<span style="opacity: 0.3">';
                    echo substr(makeclean($row['category']),0,3);
                    echo '</span></td>';

                ?>

            </tr><?php
                $i++;
                if ($i == 33) {
                    echo $tstre . $tstr;
                    $i = 0;
                }
            }
            echo $tstre;
            ?>
    <div class='formbuttons'>
        <input type='submit' name='submit' value='update' />JK
            <!--
            <input type='submit' name='clearitemlists' value='Clear all' />
            -->
        <input type='hidden' name='pId' value='<?php echo $_GET['itemId']; ?>' />
        <?php if ($_REQUEST['categoryId']) echo "<input type='hidden' name='categoryId' value='" . $_REQUEST['categoryId'] . "' />"; ?>
        <?php if ($_REQUEST['matrix']) echo "<input type='hidden' name='matrix' value='true' />"; ?>
        <?php if ($_REQUEST['visId'] > 0) echo "<input type='hidden' name='visId' value='" . $_REQUEST['visId'] . "' />"; ?>
        <input type='hidden' name='action' value='parentupdate' />
        <input type='hidden' name='source' value='true' />
        <input type='hidden' name='type' value='<?php echo $_GET['type']; ?>' />
    </div>
</form>
<?php

include_once('footer.php');
?>
