<?php
if ($_GET['content'] != "limit") {
    include('showMessage.inc.php');
    require_once("headerMenu.inc.php");
}
?>
</div><!-- main -->

	<?php
if ($_GET['content'] != "limit") {

    // get active agendas
    $values['type']= "p";
    $values['isSomeday'] = "n";
    $stem  = " WHERE ".sqlparts("typefilter",$config,$values)
            ." AND ".sqlparts("activeitems",$config,$values)
            ." AND ".sqlparts("pendingitems",$config,$values)
            ." AND title LIKE '~%'";
    $values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
    $ares = query("getitems",$config,$values,$sort);
    $numberagendas = ($ares)?count($ares):0;

    echo "<table summary='table of projects' style='opacity: 0.3'><tbody>\n";
    if ($numberagendas) echo columnedTable(6,$ares);
    if (!$expand && $numberprojects) echo columnedTable(6,$pres);
    echo $listNoMen;
    echo "</tbody></table>\n";


    	?>

    <?php if(isset($starttime)) {
        list($usec, $sec) = explode(" ", microtime());
        $tottime=(int) (((float)$usec + (float)$sec - $starttime)*1000);
    }

    ?>
    <div id='footer'>
    page generated in <?php echo $tottime; ?>ms
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

	</div>
<?php
}
?>

</div> <!-- Container-->
</body>
</html>
