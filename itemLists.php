<?php

// rewrite of lists.inc.php and reportLists.php
// called from itemReport.php

$resultListX = query("getchildlists",$config,$values,$sort);
/*
echo '<pre>';
var_dump($config);
var_dump($values);
var_dump($sort);
var_dump($resultListN);
die;
*/
if (is_array($resultListX)) {
    foreach ($resultListX as $resultListN) {

        $isChecklist = false;
        if($resultListN['type']==='c') $isChecklist = true;
        if ($isChecklist) {
            $type='c';
            $check='check';
        } else {
            $type='l';
            $check='';
        }
        $values=array(
             'listId'        => $resultListN['listId'],
             'itemId'    => '',
            'categoryId'=> ''
            );
        $urlSuffix="type=$type";

        $resultList = query("select{$check}list",$config,$values,$sort);

        if ($resultList==1) {
            echo "<p class='error'>{$check}list" . $resultListN['listId'] . " does not exist</p>\n";
            exit();
        }

        $row=$resultList[0];

        $values['filterquery']= " AND ".sqlparts("activelistitems",$config,$values);
        $resultList1=query("get{$check}listitems",$config,$values,$sort);

        if (!$isChecklist) {
            $values['filterquery']= " AND ".sqlparts("completedlistitems",$config,$sort);
            $resultList2=query("get{$check}listitems",$config,$values,$sort);
            if (!$resultList2) $resultList2=array();
        }
        $createURL="editListItems.php?listId={$row['listId']}&amp;$urlSuffix";


        ?>
        <br>
        <h2>The <a href='reportLists.php?listId=<?php echo $row['listId'],'&amp;',$urlSuffix; ?>'>
            <?php echo $row['title'],' ',$check; ?></a>list


        <?php
            if ($row['hyperlink']) {
                echo "&nbsp;&nbsp;&nbsp;[&nbsp;" . faLink($row['hyperlink']) . "&nbsp;]";
            }
        ?></h2>
        <?php if ($resultList1) { ?>
        <form action='processLists.php' method='post'>
            <table class="datatable sortable" id="itemtable" summary="table of list items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <?php if ($type == 'c') { ?>
                        <th>Completed</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($resultList1 as $row) {
                    if ($row['checked']==='y') {
                        echo "<tr style=\"display:none\">";
                    } else {
                        echo "<tr>";
                    }
                ?>

                        <td class="JKSmallPadding"><a href="editListItems.php?itemId=<?php
                            echo $row['itemId'],'&amp;',$urlSuffix;
                        ?>" title="Edit"><?php echo makeclean($row['item']); ?></a></td>
                        <td class="JKSmallPadding">
                        <?php
                            echo trimTaggedString($row['notes']);
                                if ($row['hyperlink']) {
                                    echo "<br><br>" . faLink($row['hyperlink']);
                                }
                        ?>
                        </td>
                        <?php if ($type == 'c') { ?>

                        <td class="JKSmallPadding"><input type="checkbox" name="completed[]" title="Complete" value="<?php
                            echo $row['itemId'],'"',($isChecklist && $row['checked']==='y')?" checked='checked' ":'';
                            ?> />
                        </td>
                        <?php } ?>
                    </tr><?php

                } ?>
                </tbody>
            </table>
            <div class='formbuttons'>
                <?php if ($isChecklist) { ?>
                    <input type='submit' name='submit' value='update' />
                    JK
                    <input type='submit' name='listclear' value='Clear all' />
                <?php } ?>
                <input type='hidden' name='listId' value='<?php echo $row['listId']; ?>' />
                <input type='hidden' name='action' value='listcomplete' />
                <input type='hidden' name='source' value='true' />
                <input type='hidden' name='type' value='<?php echo $type; ?>' />
            </div>
        </form>
        <?php
        }
    }
}
?>
