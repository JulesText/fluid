<?php
include_once('header.php');
include_once('lists.inc.php');

$cashtml=categoryselectbox($config,$values,$sort);
$values['filterquery']="";
if ($values['categoryId']) $values['filterquery']= " WHERE ".sqlparts("listcategoryfilter",$config,$values);
if ($values['catcodeId']) $values['filterquery']= " WHERE ".sqlparts("listcatcodefilter",$config,$values);
$result = query("get{$check}lists",$config,$values,$sort);

$codes = query("getcatcodes",$config,$values,$sort);

$createURL="editLists.php?$urlSuffix"
            .(($values['categoryId']) ? "&amp;categoryId={$values['categoryId']}" : '');
require_once("headerHtml.inc.php");
?>
<h2><a href="<?php echo $createURL; ?>" title="Add new list" ><?php echo $check; ?>lists</a>
<?php
    if ($display) {
        ?>
        &nbsp;&nbsp;&nbsp;&nbsp;[ <a href="listLists.php?type=<?php echo $type; ?>">limit</a> ]
				<?php
				foreach ($codes as $code) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="listLists.php?display=true&type=' . $type . '&catcodeId=' . $code['sortBy'] . '">' . $code['title'] . '</a> ]';
				}
				?>
				</h2>
        <?php
    } else {
        ?>
        &nbsp;&nbsp;&nbsp;&nbsp;[ <a href="listLists.php?display=true&type=<?php echo $type; ?>">display</a> ]</h2>
        <?php
    }
?>
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

<?php if ($result) {
?>
    <table class="datatable sortable" id="categorytable" summary="table of categories">
        <thead><tr>
            <td <?php if (!$display) { echo 'class="togglehidden"'; } ?>>Category</td>
            <td>Title</td>
            <td <?php if (!$display) { echo 'class="togglehidden"'; } ?>>Description</td>
            <td <?php if (!$display) { echo 'class="togglehidden"'; } ?>>Desired Outcome</td>
            <td <?php if (!$display) { echo 'class="togglehidden"'; } ?>>Parents</td>
        </tr></thead>
        <tbody><?php foreach ($result as $row) { ?>
            <tr>
                <td style="text-align: center" <?php if (!$display) { echo 'class="togglehidden"'; } ?>>
                <?php
                    echo '<span style="opacity: 0.1">' . makeclean($row['sortBy']) . '</span><br>' .
										'<span style="opacity: 0.3">' .
										makeclean($row['ccctitle']) . '<br><br>' .
										makeclean($row['cctitle']) . '<br><br>' .
										makeclean($row['category']) .
										'</span>';
                ?></td>

                <?php
                    $metaphor = '';
                    if($row['metaphor']) {
                        if (strpos($row['metaphor'], '.swf')) {
                            $metaphor .= "</a>";
                            if ($display) $metaphor .= "<br>";
                            $metaphor .= "&nbsp;&nbsp;&nbsp;<embed src=\"media/" . $row['metaphor'] . "\" quality=high height=40></embed>";
                        } else {
                            if ($display) $metaphor .= "<br>";
                            $metaphor .= "&nbsp;&nbsp;&nbsp;<img src=\"media/" . $row['metaphor'] . "\" height=\"40px\"></a><a href=\"media/" . $row['metaphor'] . "\" target=\"_blank\">";
                        }
                    }
                ?>

                <td class="JKSmallPadding"><a href="reportLists.php?id=<?php echo $row['id'],'&amp;',$urlSuffix; ?>"><?php
                    echo makeclean($row['title']) . $metaphor;
                ?></a></td>

                <?php
                    $descriptionString = '';
                    if ($row['premiseA']) $descriptionString .= "<br><br>" . $row['premiseA'];
                    if ($row['premiseB']) $descriptionString .= "<br><br>" . $row['premiseB'];
                    if ($row['conclusion']) $descriptionString .= "<br><br>" . $row['conclusion'];
                    if ($row['hyperlink']) {
                        if (strlen($descriptionString) > 0) $descriptionString .= "<br><br>";
                        $descriptionString .= faLink($row['hyperlink']);
                    }

                    $desiredOutcomeStr = $row['behaviour'];
                    if ($row['standard']) $desiredOutcomeStr .= ", <br>" . $row['standard'];
                    if ($row['conditions']) $desiredOutcomeStr .=  ", <br>" . $row['conditions'];
                ?>

                <td class="JKSmallPadding<?php if (!$display) { echo ' togglehidden'; } ?>"><?php
                     echo $descriptionString;
                ?></td>

                <td class="JKSmallPadding<?php if (!$display) { echo ' togglehidden'; } ?>"><?php
                     echo $desiredOutcomeStr;
                ?></td>

                <td class="<?php if ($display) { echo "JKSmallPadding whitespace"; } else { echo "togglehidden"; } ?>">
                    <?php
                        unset($values['itemId']);
                        $values['listId'] = $row['id'];
                        $values['type'] = $type;
                        $resultParents = query("getchildlists",$config,$values,$sort);
                        if (!empty($resultParents)) {
                            foreach ($resultParents as $parent) {
                                $values['itemId'] = $parent['itemId'];
                                $title = query("getitembrief",$config,$values,$sort);
																if(empty($title)) {
																	echo "<pre>error missing parent<br>";#var_dump($resultParents); #die;
																}
                                foreach ($title as $text) {
                                    echo makeclean($text['title']) . '<br>';
                                }
                            }
                        }
                ?>
                </td>
            </tr><?php } ?>
        </tbody>
    </table>
<?php }
else {
    $message="You have not defined any lists yet.";
    $prompt="Would you like to create a new list?";
    nothingFound($message,$prompt,$createURL);
}

include_once('footer.php');
?>
