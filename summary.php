<?php
//INCLUDES
//include_once('header.php');
//JJK
//include_once('lists.inc.php');
//JJK end
$values=array();

//SQL Code

//JJK GET calendar details
	$values['itemId'] = 566;
	$calendar_personal = query("selectitemshort",$config,$values,$sort);

	$values['itemId'] = 4024;
	$calendar_professional = query("selectitemshort",$config,$values,$sort);
//JJK end


//JJK GET awareness details

	$values['itemId'] = 1414;
	$awareness = query("selectitemshort",$config,$values,$sort);
//JJK end

//JJK GET projects to deliver details

	$values['itemId'] = 1418;
	$deliver = query("selectitemshort",$config,$values,$sort);

//JJK end
/*
//JJK Get checklist UNDER DEVELOPMENT
$result1 = query("select{$check}list",$config,'15',$sort);

if ($result1==1) {
    echo "<p class='error'>That {$check}list does not exist</p>\n";
    include_once('footer.php');
    exit();
}
$row1=$result1[0];

$values['filterquery']= " AND ".sqlparts("activelistitems",$config,$values);
$result1=query("get{$check}listitems",$config,$values,$sort);

//JJK end  UNDER DEVELOPMENT

//Select notes
$values['filterquery'] = " WHERE ".sqlparts("notefilter",$config,$values);
$reminderresult = query("getnotes",$config,$values,$sort);

//get # space contexts
$res = query("countspacecontexts",$config,$values,$sort);
$numbercontexts=(is_array($res[0]))?(int) $res[0]['COUNT(*)']:0;

//count active items
$values['type'] = "a";
$values['isSomeday'] = "n";
$values['filterquery'] = " WHERE ".sqlparts("typefilter",$config,$values)
                                ." AND ".sqlparts("issomeday",$config,$values)
                                ." AND ".sqlparts("activeitems",$config,$values)
                                ." AND ".sqlparts("pendingitems",$config,$values);

//get # nextactions
$res = query("countnextactions",$config,$values,$sort);
$nextactionsdue=array('-1'=>0,'0'=>0,'1'=>0,'2'=>0,'3'=>0,'4'=>0);
if (is_array($res))
    foreach ($res as $line)
        $nextactionsdue[$line['duecategory']]=$line['nnextactions'];
$numbernextactions=array_sum($nextactionsdue);

// get # actions
$values['filterquery'].=" AND ".sqlparts("liveparents",$config,$values);
$res =query("countactions",$config,$values,$sort);
$numberitems =($res)?(int) $res[0]['nactions']:0;

// get and count active projects
$values['type']= "p";
$values['isSomeday'] = "n";

$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values)
        ." AND ".sqlparts("pendingitems",$config,$values);

$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$pres = query("getitems",$config,$values,$sort);
$numberprojects=($pres)?count($pres):0;

//get and count someday projects
$values['isSomeday'] = "y";
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values)." AND title NOT LIKE '~%'";
$sm = query("getitems",$config,$values,$sort);
$numbersomeday=($sm)?count($sm):0;

//JK get and count completed projects
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND its.`dateCompleted` IS NOT NULL ";
$values['filterquery'] = $stem;
$dn = query("getitems",$config,$values,$sort);
$numberdone=($dn)?count($dn):0;
*/

//PAGE DISPLAY CODE
//echo "<h2>GTD Summary</h2>\n";
//echo '<h4>Today is '.date($config['datemask']).'. (Week '.date("W").'/52 &amp; Day '.date("z").'/'.(365+date("L")).')</h4>'."\n";

require_once("headerHtml.inc.php");

//JJK DRAW calendar details
		echo "<table class='datatable' >\n";

				echo "	<tr style='text-align:top;'>\n<td align='center'>\n";

//calendar PHP code follows
			// echo "<div style='width:110px'><div style='padding:2px;background-color:#ffffff;border: 0px solid #000000'><div style='padding:0px;padding-bottom:0px;padding-top:0px;border: 0px solid #AFB2D8' align='center'><script language='JavaScript' type='text/javascript'>var ccm_cfg = { pth:'http://www.moonmodule.com/cs/', fn:'ccm_v1.swf', lg:'en', hs:2, tc:'ffffff', bg:'ffffff', mc:'', fw:79, fh:116.4, js:0, msp:0 }</script><script language='JavaScript' type='text/javascript' src='http://www.moonmodule.com/cs/ccm_fl.js'></script><div style='padding-top:0px' align='center'><a href='http://www.calculatorcat.com/moon_phases/moon_phases.phtml' target='cc_moon_ph' style='font-size:0px;font-family:arial,verdana,sans-serif;color:#888888;text-decoration:underline;border:none;'><span style='color:#888888'></span></a></div></div></div></div>";

include('Lunar_Calc.php');

echo "<P>Day " . $day;

echo "<P>Month " . $month;


			//show today date

if (date("d") < 10) {
  			$day = date("d");
  			} else {
  			$day = date("d");
			}
if (date("n") < 10) {
  			$month = date("0n");
  			} else {
  			$month = date("n");
			}
  			$str = '<p>'.$day."/".$month.date(" D")."<br> ";

		    $str = strtoupper($str);
//			echo $str;




echo "</td><!-- <td><div class='reportsection'>\n";

if($numbernextactions==1) {
    $verb='is';
    $plural='';
} else {
    $verb='are';
    $plural='s';
}
$space1=" <br>$numbercontexts <a href='reportContext.php'>Spatial Context"
        .(($numbercontexts==1)?'':'s') . "</a>";
if ($config["contextsummary"] === 'nextaction') {
    $space2='';
} else {
    $space2=$space1;
    $space1='';
}
$deferredActions = $numberitems - $numbernextactions;
echo "<p>$numbernextactions"
    ," Next Action$plural<br><span"
    ,($nextactionsdue['2']==0)?'>' : " class='due'>"
    ,($nextactionsdue['2']==1)?'1 next action is':"{$nextactionsdue['2']} next  actions"
    ," <a href='listItems.php?type=a&nextonly=true&dueonly=true&liveparents=*&'>due</a> today</span><br><span"
    ,($nextactionsdue['3']==0)?'>' : " class='overdue'>"
    ,($nextactionsdue['3']==1)?'1 is':"{$nextactionsdue['3']} are"
    ," now overdue</span><br><span"
    ,($nextactionsdue['1']==0)?'>' : " class='comingdue'>"
    ,($nextactionsdue['1']==1)?"1 has its <a href='listItems.php?type=a&nextonly=true&liveparents=true&'>deadline</a>":"{$nextactionsdue['1']} have <a href='listItems.php?type=a&nextonly=true&liveparents=true&'>deadlines</a>"
    ," coming</span> <br>$deferredActions Deferred Actions <br><br>";

if($numberprojects==1){
    echo 'There is 1 Project'
        ,($numdue)    ?", which is <span class='due'>due today</span>":''
        ,($numoverdue)?", which is <span class='overdue'>overdue</span>"  :'';
}else{
    echo "$numberprojects Projects"
        ,"<br> <span"
        ,($numdue)?" class='due'>":'>'
        ,"$numdue due today</span><br> <span"
        ,($numoverdue)?" class='overdue'>":'>'
        ,"$numoverdue overdue</span>";
}
echo "</p>\n</div>\n";

/* for future, here is the HTML of the calendar code:
<!-- moon calendar follows -->
<!-- // Begin Current Moon Phase HTML (c) CalculatorCat.com // --><div style="width:110px"><div style="padding:2px;background-color:#ffffff;border: 0px solid #000000"><div style="padding:0px;padding-bottom:0px;padding-top:0px;border: 0px solid #AFB2D8" align="center"><script language="JavaScript" type="text/javascript">var ccm_cfg = { pth:'http://www.moonmodule.com/cs/', fn:'ccm_v1.swf', lg:'en', hs:2, tc:'000000', bg:'000000', mc:'', fw:79, fh:116.4, js:0, msp:0 }</script><script language="JavaScript" type="text/javascript" src="http://www.moonmodule.com/cs/ccm_fl.js"></script><div style="padding-top:0px" align="center"><a href="http://www.calculatorcat.com/moon_phases/moon_phases.phtml" target="cc_moon_ph" style="font-size:0px;font-family:arial,verdana,sans-serif;color:#888888;text-decoration:underline;border:none;"><span style="color:#888888"></span></a></div></div></div></div><!-- // end moon phase HTML // -->
<!-- moon calendar end -->
*/

				echo "	</td> --> <td style='vertical-align: text-top;' class='JKSmallPadding'>\n";
//JJK projects to deliver
		foreach ($deliver as $row) {
			echo '<a href = "item.php?itemId='.$row['itemId'].'&pType=p" title="Edit '.htmlspecialchars(stripslashes($row['title'])).'">'.stripslashes($row['title'])."</a><br>\n";
//projects to deliver proper follows
			echo nl2br(stripslashes($row['description']));
				}
//JJK projects to deliver  end

			echo "</td>\n";


			echo "</tr><tr>\n";

//JJK routine end

/*
//JJK DRAW awareness details
			echo "<td style='vertical-align: text-top;'>\n";
		while($row = mysql_fetch_assoc($awareness)) {
			echo '<a href = "item.php?itemId='.$row['itemId'].'&pType=p" title="Edit '.htmlspecialchars(stripslashes($row['title'])).'">'.stripslashes($row['title'])."</a>\n";
//awareness proper follows
			echo nl2br(stripslashes($row['description']));
				}
			echo "</td>\n";
*/ //JJK awareness end
//			echo "</tr>\n";

//JJK second row
/* //JJK lunar schedule
			echo "<tr><td><a href='reportLists.php?listId=15&type=C'>LUNAR<br>SCHEDULE</a></td>\n";
*/ //JJk end

			echo "<td colspan='1' style='vertical-align: text-top;' class='JKSmallPadding'>\n";

//JJK calendar follows
/*
		while($row = mysqli_fetch_assoc($calendar_personal)) {

		echo nl2br(stripslashes($row['description']));
		}

		echo "</td><td style='vertical-align: text-top;' class='JKSmallPadding'>\n";

		while($row = mysql_fetch_assoc($calendar_professional)) {

		echo nl2br(stripslashes($row['description']));
		}
*/
//JJK calendar end


/* //JJK weekly schedule
			echo "</td><td><a href='reportLists.php?listId=13&type=C'>WEEKLY<br>SCHEDULE</a>\n";
*/ //JJK weekly schedule end

			echo "</td></tr>\n";


/*
//JJK checklist
		   echo "<table class='datatable sortable' id='itemtable' summary='table of list items'>\n";


			foreach($result1 as $row1) {
            echo '<tr><td><a href="editListItems.php?itemId='.$row['itemId'], '&amp;' , $urlSuffix;
            echo 'title="Edit">' . makeclean($row['item']) . '</a></td>';
            echo '<td>' . trimTaggedString($row['notes']) . '</td>';
            echo '<td><input type="checkbox" name="completed[]" title="Complete" value="' . $row['itemId'],  '"',($isChecklist && $row['checked']==='y')?" checked='checked' ":'';

			echo '</td></tr>';
        }
        	echo "</tbody></table><div class='formbuttons'><input type='submit' name='submit' value='update' />        <?php if ($isChecklist) { ?>            <input type='submit' name='listclear' value='Clear all checkmarks' />        <?php } ?>        <input type='hidden' name='id' value='<?php echo $row['id']; ?>' />        <input type='hidden' name='action' value='listcomplete' />        <input type='hidden' name='type' value='<?php echo $type; ?>' />    </div></form>";

//JJK end  UNDER DEVELOPMENT
*/

			echo "</tr>\n</table>\n";

//JJK set against calendar
	echo "<div >";
//JJK end

echo "<div class='reportsection'>\n";
if ($reminderresult) {
        echo "<br /><h3>Reminder Notes</h3>";
        $tablehtml="";
        foreach ($reminderresult as $row) {
                $notehtml .= "<p>".date($config['datemask'],strtotime($row['date'])).": ";
                $notehtml .= '<a href = "note.php?noteId='.$row['ticklerId'].'&amp;referrer=s" title="Edit '.makeclean($row['title']).'">'.makeclean($row['title'])."</a>";
                if ($row['note']!="") $notehtml .= " - ".trimTaggedString($row['note']);
                $notehtml .= "</p>\n";
        }
    echo $notehtml;
    }
echo "</div>";

echo "<div class='reportsection'>\n";

//JJK begin
	$tally0=0;
	$tally1=0;
	$tally2=0;
	$tally3=0;
	$tally4=0;
	$tally5=0;
	$tally6=0;
	$tally7=0;
	$tally8=0;
	$tally9=0;
//JJK end
$numdue=0;
$numoverdue=0;
for ($i=0; $i<$numberprojects;$i++) {
    if (empty($pres[$i]['deadline'])) {
        // do nothing
    } elseif ($pres[$i]['deadline'] < date("Y-m-d")) {
        $pres[$i]['td.class']='celloverdue';
        $pres[$i]['td.title']='Project overdue';
        $numoverdue++;
    } elseif ($pres[$i]['deadline'] === date("Y-m-d")) {
        $pres[$i]['td.class']='celldue';
        $pres[$i]['td.title']='Project due for completion today';
        $numdue++;
    }
//JJK begin
		$jjk1=substr(($pres[$i]['title']), 0 , 1);
		if ($jjk1 == '0') {$tally0++;}
		if ($jjk1 == '1') {$tally1++;}
		if ($jjk1 == '2') {$tally2++;}
		if ($jjk1 == '3') {$tally3++;}
		if ($jjk1 == '4') {$tally4++;}
		if ($jjk1 == '5') {$tally5++;}
		if ($jjk1 == '6') {$tally6++;}
		if ($jjk1 == '7') {$tally7++;}
		if ($jjk1 == '8') {$tally8++;}
		if ($jjk1 == '9') {$tally9++;}
		if ($jjk1 == '_') {
		$jjk1=substr(($pres[$i]['title']), 1 , 1);
		if ($jjk1 == '0') {$tally0++;}
		if ($jjk1 == '1') {$tally1++;}
		if ($jjk1 == '2') {$tally2++;}
		if ($jjk1 == '3') {$tally3++;}
		if ($jjk1 == '4') {$tally4++;}
		if ($jjk1 == '5') {$tally5++;}
		if ($jjk1 == '6') {$tally6++;}
		if ($jjk1 == '7') {$tally7++;}
		if ($jjk1 == '8') {$tally8++;}
		if ($jjk1 == '9') {$tally9++;}
		}
//JJK end
}

//JJK begin someday tally
	$tallyS0=0;
	$tallyS1=0;
	$tallyS2=0;
	$tallyS3=0;
	$tallyS4=0;
	$tallyS5=0;
	$tallyS6=0;
	$tallyS7=0;
	$tallyS8=0;
	$tallyS9=0;

for ($i=0; $i<$numbersomeday;$i++) {
		$jjk1=substr(($sm[$i]['title']), 0 , 1);
		if ($jjk1 == '0') {$tallyS0++;}
		if ($jjk1 == '1') {$tallyS1++;}
		if ($jjk1 == '2') {$tallyS2++;}
		if ($jjk1 == '3') {$tallyS3++;}
		if ($jjk1 == '4') {$tallyS4++;}
		if ($jjk1 == '5') {$tallyS5++;}
		if ($jjk1 == '6') {$tallyS6++;}
		if ($jjk1 == '7') {$tallyS7++;}
		if ($jjk1 == '8') {$tallyS8++;}
		if ($jjk1 == '9') {$tallyS9++;}
		if ($jjk1 == '_') {
		$jjk1=substr(($sm[$i]['title']), 1 , 1);
		if ($jjk1 == '0') {$tallyS0++;}
		if ($jjk1 == '1') {$tallyS1++;}
		if ($jjk1 == '2') {$tallyS2++;}
		if ($jjk1 == '3') {$tallyS3++;}
		if ($jjk1 == '4') {$tallyS4++;}
		if ($jjk1 == '5') {$tallyS5++;}
		if ($jjk1 == '6') {$tallyS6++;}
		if ($jjk1 == '7') {$tallyS7++;}
		if ($jjk1 == '8') {$tallyS8++;}
		if ($jjk1 == '9') {$tallyS9++;}
		}
}

//JJK end

//JJK begin completed projects tally
	$tallyC0=0;
	$tallyC1=0;
	$tallyC2=0;
	$tallyC3=0;
	$tallyC4=0;
	$tallyC5=0;
	$tallyC6=0;
	$tallyC7=0;
	$tallyC8=0;
	$tallyC9=0;

for ($i=0; $i<$numberdone;$i++) {
		$jjk1=substr(($dn[$i]['title']), 0 , 1);
		if ($jjk1 == '0') {$tallyC0++;}
		if ($jjk1 == '1') {$tallyC1++;}
		if ($jjk1 == '2') {$tallyC2++;}
		if ($jjk1 == '3') {$tallyC3++;}
		if ($jjk1 == '4') {$tallyC4++;}
		if ($jjk1 == '5') {$tallyC5++;}
		if ($jjk1 == '6') {$tallyC6++;}
		if ($jjk1 == '7') {$tallyC7++;}
		if ($jjk1 == '8') {$tallyC8++;}
		if ($jjk1 == '9') {$tallyC9++;}
		if ($jjk1 == '_') {
		$jjk1=substr(($dn[$i]['title']), 1 , 1);
		if ($jjk1 == '0') {$tallyC0++;}
		if ($jjk1 == '1') {$tallyC1++;}
		if ($jjk1 == '2') {$tallyC2++;}
		if ($jjk1 == '3') {$tallyC3++;}
		if ($jjk1 == '4') {$tallyC4++;}
		if ($jjk1 == '5') {$tallyC5++;}
		if ($jjk1 == '6') {$tallyC6++;}
		if ($jjk1 == '7') {$tallyC7++;}
		if ($jjk1 == '8') {$tallyC8++;}
		if ($jjk1 == '9') {$tallyC9++;}
		}
}

//JJK end

/*
if($numberprojects) {
    echo "<table summary='table of projects'><tbody>\n"
        ,columnedTable(1,$pres)
        ,"</tbody></table>\n";
}
*/

//JJK edit follows
/*
	echo "<table><tr>
	<td class='jjk'>
0-GTD (".$tally0."):(".$tallyS0."):(".$tallyC0.")<br>
01-Calendar<br>
02-Tasks<br>
03-Theory<br>
04-Workspace<br>
05-Attitudes<br>
	</td>
	<td class='jjk'>
1-MIND (".$tally1."):(".$tallyS1."):(".$tallyC1.")<br>
11-Mental_Health<br>
12-Jules_Magic<br>
13-Personal_Affirmations<br>
17-Rowland_Techniques<br>
18-Tolle_Techniques<br>
	</td>
	<td class='jjk'>
2-RELATIONSHIPS (".$tally2."):(".$tallyS2."):(".$tallyC2.")<br>
21-Lifestyle<br>
22-Grief<br>
23-Procreation<br>
24-Communication<br>
25-Dating<br>
26-Sweethearts<br>
27-Household<br>
28-Social<br>
	</td>
	<td class='jjk'>
3-HEALTH (".$tally3."):(".$tallyS3."):(".$tallyC3.")<br>
31-Nervous<br>
32-Circadian_Rhythm<br>
33-Length<br>
34-Posture<br>
35-Minor_Issues<br>
36-Preventative<br>
37-Learning<br>
38-Strength<br>
39-Energy<br>
	</td>
	<td class='jjk'>
4-SEXUALITY (".$tally4."):(".$tallyS4."):(".$tallyC4.")<br>
41-Myself<br>
45-Intercourse<br>
	</td>
	</tr><tr>
	<td class='jjk'>
5-CAREER (".$tally5."):(".$tallyS5."):(".$tallyC5.")<br>
51-Goals<br>
52-Service<br>
53-Creative_Expression<br>
54-Community<br>
	</td>
	<td class='jjk'>
6-EDUCATION (".$tally6."):(".$tallyS6."):(".$tallyC6.")<br>
61-Health<br>
62-Business<br>
63-Media<br>
64-I.T.<br>
69-Personal Interests<br>
	</td>
	<td class='jjk'>
7-LABOUR (".$tally7."):(".$tallyS7."):(".$tallyC7.")<br>
71-Original<br>
72-Sisyphean<br>
73-Communications<br>
74-Team_Morale<br>
75-Housework<br>
76-Processes<br>
77-Staff_Issues<br>
78-Timelines<br>
79-Workload<br>
	</td>
	<td class='jjk'>
8-FINANCES (".$tally8."):(".$tallyS8."):(".$tallyC8.")<br>
81-Resources<br>
82-Investments<br>
84-Support<br>
85-Personal<br>
86-Wages<br>
	</td>
	<td class='jjk'>
9-HOLIDAYS (".$tally9."):(".$tallyS9."):(".$tallyC9.")<br>
91-Creative Retreats<br>
92-Internal Holidays<br>
93-Long Getaways<br>
94-Short Getaways<br>
95-Day Trips<br>
96-Long Visits<br>
97-Short Visits<br>
98-Working Holidays<br>
99-Play<br>
</td></tr></table><br>";
*/
//JJK end

echo "</div>\n";

echo "<div class='reportsection'>\n";

/*
if($numbersomeday==1){
    echo '<p>There is 1 <a href="listItems.php?type=p&amp;someday=true">Someday/Maybe</a>.</p>'."\n";
}else{
    echo '<p>There are ' .$numbersomeday.' <a href="listItems.php?type=p&amp;someday=true">Someday/Maybes</a>. And ' . $numberdone . ' <a href="listItems.php?type=p&completed=true&liveparents=*&">Completed Projects/Maybes</a>.</p>'."\n";
}
*/

if($numbersomeday === 'adsfasdfasd') {
    echo "<table summary='table of someday/maybe items'><tbody>\n"
        ,columnedTable(3,$sm)
        ,"</tbody></table>\n";
}
//include_once('footer.php');

echo "</div>\n";


//JJK set against calendar
	echo "</div>";
//JJK end


?>
