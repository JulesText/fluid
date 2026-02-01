<?php

include_once('header.php');

if (isset($_GET["random"]) && $_GET["random"] == 'true') $randomDisplay = TRUE;
else $randomDisplay = FALSE;

include_once('reportContext.inc.php');
require_once("headerHtml.inc.php");

?>

<?php

if (!$randomDisplay) echo "<h1 style='text-align: left'>[ <a href='" . $_SERVER['REQUEST_URI'] . "&random=true'>Random</a> ]</h1>";

if ($randomDisplay) {
    $contexts = array();
    foreach ($matrixcount as $row) {
        if ($row['count'] > 0) array_push($contexts, $row['context']);
    }
    $key = rand(0, count($contexts) - 1);
    $randomContext = $contexts[$key];
    // echo "<pre>"; var_dump($matrixcount);die();
    // echo "<pre>" . $key . " " . $contexts[$key] . PHP_EOL; var_dump($contexts);die();
    echo "<h1>Random context: " . $randomContext . "</h1>";
}

?>

<!--
<h2>Contexts Skimmary</h2>
<h3>Spatial Context (row), Temporal Context (column)</h3>
-->
<table style="display: none" class="datatable" summary="table of contexts" id="contexttable">
    <thead><tr>
        <td>Context</td>
        <?php
        $runningtotals=array();
        foreach ($timeframeNames as $tcId => $tname) {
            $runningtotals["t$tcId"]=0;
            ?>
            <td><a href='editCat.php?field=time-context&amp;id=<?php echo $tcId; ?>'
                    title='Edit time context'><?php echo makeclean($tname); ?>
                </a>
            </td>
        <?php } ?>
        <td>Total</td>
    </tr></thead>
    <tbody><?php foreach ($contextNames as $cid => $cname) {
        $runningtotals["c$cid"]=0;
        ?>
        <tr>
	       <td><a href='editCat.php?field=context&amp;id=<?php echo $cid; ?>'
                title='Edit context'><?php echo makeclean($cname); ?></a>
           </td>
           <?php foreach ($timeframeNames as $tid => $tname) {
        		if ($count=$matrixcount[$cid][$tid]) {
			         echo "<td><a href='#c{$cid}t{$tid}'>$count</a></td>\n";
			         $runningtotals["c$cid"]+=$count;
			         $runningtotals["t$tid"]+=$count;
                } else { ?>
                    <td>0</td>
                <?php
                }
            }
            ?>
            <td><?php
                echo ($count=$runningtotals["c$cid"])
                    ?"<a href='#c$cid'>$count</a>"
                    :0;
                ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td>Total</td>
            <?php
            $runningtotals['grand']=0;
            foreach ($timeframeNames as $tid => $tname) {
	            $runningtotals['grand']+=$runningtotals["t$tid"];
	            ?>
        	    <td><?php echo $runningtotals["t$tid"]; ?></td>
            <?php } ?>
            <td><?php echo $runningtotals['grand']; ?></td>
        </tr>
    </tbody>
</table>
<!--
<p>To move to a particular space-time context, select the number.<br />
To edit a context select the context name.</p>
-->
<?php
foreach ($contextNames as $cid => $cname) {
    if (!$runningtotals["c$cid"]) continue;
    if ($randomDisplay && $cname != $randomContext) continue;
    // $ran = rand(1, $runningtotals["c$cid"]);
    echo "<a id='c$cid'></a>\n";
    echo "<h1>" /*<a href='editCat.php?field=context&amp;id=$cid' "
        ,"title='Edit the $cname context'>" */
        // ,"<u>Context:&nbsp;$cname</u> (r = " . $ran . ")</h1>\n";
        ,"<u>Context:&nbsp;$cname</u>";
    if ($randomDisplay) echo " (" . $matrixcount[$cid]['random'] . ")";
    echo "</h1>\n";

   foreach ($timeframeNames as $tid => $tname) {
        if (isset($matrixout[$cid][$tid])) {
            echo "<a id='c{$cid}t{$tid}'></a>\n"
                ,"<h3><!--<a href='editCat.php?field=time-context&amp;id=$tid' title='$tname'>-->"
                ,"<em>Time:&nbsp;$tname</em><!--</a>--></h3>\n";
            ?>
            <form action="processItems.php" method="post">
                <table class="datatable sortable" summary="table of actions"
                    id='actiontable<?php echo "C{$cid}T{$tid}"; ?>'>
                    <?php
                    echo $matrixout[$cid][$tid];
                    ?>
                </table>
                <div>
                	<input type="hidden" name="referrer" value="<?php echo basename($thisurl['path']),"#c{$cid}t{$tid}"; ?>" />
                    <input type="hidden" name="multi" value="y" />
        		    <input type="hidden" name="wasNAonEntry" value="<?php echo implode(' ',$wasNAonEntry[$cid][$tid]); ?> " />
                    <input type="hidden" name="action" value="complete" />
                    <input type="submit" style="height: 0px; width: 0px; border: 0px;" value="Update Actions" name="submit" />
                </div>
            </form>
            <?php
        }
    }

	echo "<br><br><br><br><br>";
}
include_once('footer.php');
?>
