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
    $menus = [];
    if ($numberagendas) {
      foreach ($ares as &$row) $row['title'] = ' ' . $row['title'];
      $menus = array_merge($menus,$ares);
    }
    if (!$expand && $numberprojects) $menus = array_merge($menus,$pres);
    if (isset($listNoMena) && count($listNoMena) > 0 )$menus = array_merge($menus,$listNoMena);
    array_multisort(array_column($menus, 'title'), SORT_ASC, SORT_STRING, $menus);
    echo columnedTable(6,$menus);
    // echo $listNoMen;
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
