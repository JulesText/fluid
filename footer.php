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
    $pres = query("getitems",$config,$values,$sort);
    $numberprojects=($pres)?count($pres):0;
    
    if($numberprojects) {
        echo "<table summary='table of projects' style='opacity: 0.3'><tbody>\n"
            ,columnedTable(6,$pres)
            ,"</tbody></table>\n";
    }
    	
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
