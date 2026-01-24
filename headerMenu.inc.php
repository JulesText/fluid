<?php
require_once("headerDB.inc.php");

$expand = FALSE;
if (isset($_GET['expand']) && $_GET['expand'] == 'TRUE') $expand = TRUE;

/*
   ----------------------------------------------
   first, define the default menus
*/
$menu=array();
$menu2=array();

//$menu[] = array("link"=>"fantastical2://", 'title'=>"Calendar", 'label' => "Calendar");
//$menu[] = array("link"=>"reportLists.php?listId=120&type=C", 'title'=>"Circadian Rhythm", 'label' => "Circadian Rhythm");
//$menu[] = array("link"=>"reportLists.php?listId=13&type=C", 'title'=>"Week Schedule", 'label' => "Weekly Health DDD");
//$menu[] = array("link"=>"reportLists.php?listId=168&type=C", 'title'=>"Week Schedule", 'label' => "Weekly Chores EEE");
//$menu[] = array("link"=>"reportLists.php?listId=170&type=C", 'title'=>"Week Schedule", 'label' => "Weekly Process FFF");
$menu[] = array("link"=>"tabs_pertinence.php", 'title'=>"HHome Tabs", 'label' => "HHome Tabs");
$menu[] = array("link"=>"listItems.php?type=*&everything=true&liveparents=*", 'form' => TRUE, 'label' => 'Search ');

/*
$menu[] = array("link"=>"reportContext!Personal.php", 'title'=>"Process actions sorted by space context", 'label' => "Context report for non-personal categories");
$menu[] = array("link"=>"reportContextPersonal.php", 'title'=>"Process actions sorted by space context", 'label' => "Context report for personal category");
$menu[] = array("link"=>"listItems.php?type=a&contextId=6&notspacecontext=true&liveparents=true&", 'title'=>"Process actions sorted to exclude computer context", 'label' => "Context report without At Computer context");
*/
$menu[] = array("link"=>"listLists.php?type=C", 'title'=>"Show reusable checklists", 'label' => "Show Checklists");
#$menu[] = array("link"=>"listLists.php?type=L", 'title'=>"Show general-purpose lists", 'label' => "Show Lists");
// $menu[] = array("link"=>'', 'copy' => TRUE, 'label'=>'Copy page link');
#$menu[] = array("link"=>"itemReport.php?itemId=18792", 'title'=>"~ BETTY", 'label' => "~ BETTY");
$menu[] = array("link"=>"ToD.php", 'title'=>"Process the time of day", 'label' => "ToDB");
$menu[] = array("link"=>"matrix.php?&live=true&qLimit=b", 'title'=>"Matrix", 'label' => "Matrix");
//warning: poorly formed GET variables for matrix can generate errors in matrixformula.php and matrix.php
$menu[] = array("link"=>"media/flow.gif", 'title'=>"Process item", 'label' => "Flow.gif");
$menu[] = array("link"=>"fi.php", 'title'=>"FI Chat", 'label' => "FI Chat");
$menu[] = array("link"=>"Lunar.php", 'title'=>"Phase", 'label' => "Phase");
/*
$menu[] = array("link"=>"item.php?type=a&amp;nextonly=true", 'title'=>"Create a new next action", 'label' => "Next Action");
$menu[] = array("link"=>"item.php?type=a", 'title'=>"Create a new action", 'label' => "Action");
$menu[] = array("link"=>"item.php?type=w", 'title'=>"Create a new waiting on item", 'label' => "Waiting On");
$menu[] = array("link"=>"item.php?type=r", 'title'=>"Create a reference", 'label' => "Reference");
$menu[] = array("link"=>"item.php?type=p&amp;someday=true", 'title'=>"Create a future project", 'label' => "Someday/Maybe");
*/


$menu2[] = array("link"=>'','label'=>'');
//-------------------------------------------
$menu2[] = array("link"=>'','label'=>'Process');
//-------------------------------------------
$menu2[] = array("link"=>"item.php?type=i", 'title'=>"Drop an item into the inbox", 'label' => "Capture FA Inbox Item");
#$menu2[] = array("link"=>"x-devonthink-item://4262B1C8-A836-47AC-90BA-09499EEFF1D5?reveal=1", 'title'=>"Drop an item into the inbox", 'label' => "Capture Betty Inbox Item");
$menu2[] = array("link"=>"listItems.php?tickler=false&type=w&contextId=25&notspacecontext=true&nextonly=true&dueonly=true&liveparents=*", 'title'=>"Due Waiting On", 'label' => "Due Waiting On");
$menu2[] = array("link"=>"listItems.php?tickler=false&type=a&contextId=25&notspacecontext=true&dueonly=true&liveparents=*", 'title'=>"Due Actions", 'label' => "Due Actions");
$menu2[] = array("link"=>"index.php", 'title'=>"Summary View", 'label' => "Summary View");
$menu2[] = array("link"=>"reportContext.php?notContext=25", 'title'=>"Process actions sorted by space context", 'label' => "All Contexts");
$menu2[] = array("link"=>"listLists.php?type=l", 'title'=>"Show Lists", 'label' => "Show Lists");
$menu2[] = array("link"=>"reportLists.php?listId=1&type=C", 'title'=>"Weekly Review", 'label' => "WW Weekly Review");
$menu2[] = array("link"=>"listItems.php?quickfind", 'title'=>'Find an item based on text in its title, description or outcome', 'label'=>'Keyword Search');

if (!$expand) {
  $menu2[] = array("link"=>'','label'=>'Expand');
  $menu2[] = array("link"=> $_SERVER['REQUEST_URI'] . "&expand=TRUE", 'title'=>"Expand menu", 'label' => "Expand menu");
  if ($config['password_on']) {
    $menu2[] = array("link"=> "index.php?pass_off=TRUE", 'title'=>"Password off", 'label' => "Password off (" . $config['pass_off_minutes'] . " minutes)");
  } else {
    $menu2[] = array("link"=>'', 'label' => "Password back on: " . $config['pass_back_on']);
  }
  $menu2[] = array("link"=>'index.php?clear_logins=TRUE', 'title'=>"", 'label' => "Clear IPs & logout");

}

if ($expand) {

$menu2[] = array("link"=>"listItems.php?type=i", 'title'=>"Inbox", 'label' => "Inbox");

$contextResults = query("getspacecontexts",$config,$values,$sort);
foreach ($contextResults as $c) {
    $menu2[] = array("link"=>"reportContext.php?isContext=" . $c["contextId"], 'title'=>"", 'label' => ucwords(strtolower($c["name"])) . " Context");
}

$menu2[] = array("link"=>"listItems.php?type=*&tickler=true&liveparents=true&", 'title'=>"Tickler File", 'label' => "Tickler");
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>"listItems.php?type=p&liveparents=*&", 'title'=>"Review projects", 'label' => "Projects");
$menu2[] = array("link"=>"listItems.php?type=p&liveparents=*&someday=true", 'title'=>"Review projects", 'label' => "Someday");
$menu2[] = array("link"=>"listItems.php?type=p&liveparents=*&tickler=true", 'title'=>"Review projects", 'label' => "Project Tickler");
$menu2[] = array("link"=>"listItems.php?type=g&liveparents=*&", 'title'=>"Review goals", 'label' => "Goals");
$menu2[] = array("link"=>"listItems.php?type=g&liveparents=*&someday=true", 'title'=>"Review goals", 'label' => "Someday");
$menu2[] = array("link"=>"listItems.php?type=o&liveparents=*&", 'title'=>"Review roles / Areas of Responsibility", 'label' => "Roles");
$menu2[] = array("link"=>"listItems.php?type=o&liveparents=*&someday=true", 'title'=>"Review roles / Areas of Responsibility", 'label' => "Someday");
$menu2[] = array("link"=>"listItems.php?type=v&liveparents=*&", 'title'=>"Review visions", 'label' => "Visions");
$menu2[] = array("link"=>"listItems.php?type=v&liveparents=*&someday=true", 'title'=>"Review visions", 'label' => "Someday");
$menu2[] = array("link"=>"listItems.php?type=m&liveparents=*&", 'title'=>"Review values / Mission", 'label' => "Values");
$menu2[] = array("link"=>"listItems.php?type=m&liveparents=*&someday=true", 'title'=>"Review values / Mission", 'label' => "Someday");
$menu2[] = array("link"=>"orphans.php", 'title'=>"Review orphan items", 'label' => "Orphan Items");

$menu2[] = array("link"=>'','label'=>'Capture');
$menu2[] = array("link"=>"item.php?type=i", 'title'=>"Drop an item into the inbox", 'label' => "Capture Inbox Item");
$menu2[] = array("link"=>"editLists.php?type=L", 'title'=>"Create a general purpose list", 'label' => "New List");
$menu2[] = array("link"=>"editLists.php?type=C", 'title'=>"Create a reusable list", 'label' => "New Checklist");
$menu2[] = array("link"=>"item.php?type=p", 'title'=>"Create a new project", 'label' => "Project");
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>"item.php?type=g", 'title'=>"Define a new goal", 'label' => "Goal");
$menu2[] = array("link"=>"item.php?type=o", 'title'=>"Define a new role", 'label' => "Role");
$menu2[] = array("link"=>"item.php?type=v", 'title'=>"Define a new vision", 'label' => "Vision");
$menu2[] = array("link"=>"item.php?type=m", 'title'=>"Define a new value", 'label' => "Value");

$menu2[] = array("link"=>'','label'=>'Configure');
$menu2[] = array("link"=>"itemReport.php?itemId=2118", 'title'=>"Export/Import DB and Files", 'label' => "Export/Import");
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>"editCat.php?field=instance", 'title'=>"Edit Meta-categories", 'label' => "Instances");
$menu2[] = array("link"=>"editCat.php?field=category", 'title'=>"Edit Meta-categories", 'label' => "Categories");
$menu2[] = array("link"=>"listCatCodes.php", 'title'=>"Category codes", 'label' => "Category codes");
$menu2[] = array("link"=>"editCat.php?field=context", 'title'=>"Edit spatial contexts", 'label' => "Space Contexts");
$menu2[] = array("link"=>"editCat.php?field=time-context", 'title'=>"Edit time contexts", 'label' => "Time Contexts");
$menu2[] = array("link"=>"editCat.php?field=trade-condition", 'title'=>"Edit trade conditions", 'label' => "Trade Conditions");
$menu2[] = array("link"=>'','label'=>'separator');
$menu2[] = array("link"=>"phpmyadmin/index.php?db=gtd8", 'title'=>"PHPMyAdmin", 'label' => "PHPMyAdmin");
if ($config['showAdmin'])
    $menu2[] = array("link"=>"admin.php", 'title'=>"Administration", 'label' => "Admin");

}
/*
   ----------------------------------------------
*/

/*
$menu[] = array("link"=>'','label'=>'Help');
//-------------------------------------------
$newbuglink="https://www.hosted-projects.com/trac/toae/gtdphp/newticket";
if (!$config['withholdVersionInfo']) $newbuglink.='?milestone='._GTDPHP_VERSION.'&amp;description='
    .urlencode('gtd-php='._GTD_REVISION.' , GTD-db='._GTD_VERSION
    .' , PHP='.PHP_VERSION.' , Database='.getDBVersion()
    );
$menu[] = array("link"=>"http://www.gtd-php.com/Users/Documentation", 'title'=>"Documentation", 'label' => "Helpfile Wiki");
$menu[] = array("link"=>$newbuglink, 'title'=>"Report a bug on the gtd-php trac system", 'label' => "Report a bug");
$menu[] = array("link"=>"listkeys.php", 'title'=>"List the shortcut keys", 'label' => "Show shortcut keys");
$menu[] = array("link"=>"http://toae.org/boards", 'title'=>"Help and development discussions", 'label' => "Support Forum");
$menu[] = array('link'=>'http://www.gtd-php.com/Developers/Contrib','title'=>'User-contributed enhancements','label'=>'Themes and add-ons');
$menu[] = array("link"=>"https://www.hosted-projects.com/trac/toae/gtdphp", 'title'=>"Bug tracking and project development", 'label' => "Developers' wiki");
$menu[] = array("link"=>"http://www.frappr.com/gtdphp", 'title'=>"Tell us where you are", 'label' => "Frappr Map");
if ($config['debug'] & _GTD_DEBUG) {
    $menu[] = array("link"=>"https://www.hosted-projects.com/trac/toae/gtdphp/log?action=stop_on_copy&amp;rev="
        ._GTD_REVISION."&amp;stop_rev=411&amp;mode=follow_copy&amp;verbose=on"
        ,'title'=>'Changelog (requires trac login)', 'label'=>'Changelog');
}
$menu[] = array("link"=>'','label'=>'separator');
$menu[] = array("link"=>"donate.php", 'title'=>"Help us defray our costs", 'label' => "Donate");
$menu[] = array("link"=>"credits.php", 'title'=>"The GTD-PHP development team", 'label' => "Credits");
$menu[] = array("link"=>"license.php", 'title'=>"The GTD-PHP license", 'label' => "License");
$menu[] = array("link"=>"version.php", 'title'=>"Version information", 'label' => "Version");
*/
/*
   ----------------------------------------------
        now process addons
*/
if (!empty($config['addons'])) foreach ($config['addons'] as $addonid=>$thisaddon) {
    $url=$thisaddon['where'];
    foreach ($menu as $key=>$line) {
        if ($url!==$line['link']) continue;
        switch ($thisaddon['when']) {
            case 'before':
                $offset=$key;
                $length=0;
                break;
            case 'replace':
                $offset=$key;
                $length=1;
                break;
            case 'after': // deliberately flows through to default
            default:
                $offset=$key+1;
                $length=0;
                break;
        }
        unset($thisaddon['where']);
        unset($thisaddon['when']);
        $thisaddon['link']="addon.php?addonid=$addonid";
        array_splice($menu,$offset,$length,array($thisaddon));
        break;
    }
}
/*
   ----------------------------------------------
        finally, echo out the menus
*/
?>
<!--
<div id="header">
	<h1 id='sitename'><a href='index.php'><?php echo $config['title'];?></a></h1>
</div>
-->
<div id="menudiv">

<?php
$menProc = '';
foreach ((array) $menu as $l) {

  if (isset($l['form'])) {
    $menProc .= "<tr>\n<td><form action='{$l['link']}' method='post'>{$l['label']}<input type='text' name='needle' id='needle' style='width: 150px'></form></td>\n</tr>\n";
  } elseif (isset($l['copy'])) {
    $menProc .= "<tr>\n
        <td><a id='copy-button'>Copy page link</a></td>\n</tr>\n";
  } else {
    $menProc .= "<tr>\n
        <td><a href='{$l['link']}'>"
        . $l['label'] . "</a></td>\n</tr>\n";
  }
}

// get active visions

$values['type']= "v";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values);
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$vres = query("getitems",$config,$values,$sort);
//echo '<pre>'; var_dump($vres);die;

// get someday lists
$sdays = array();
$listMena = array();
$listNoMena = array();
$listMen = '';
$listNoMen = '';
$values = array();
$values['qId'] = 1000;
$lists = query('lookupqualities',$config,$values,$sort);
if (count($lists) > 0 && is_array($lists)) {
    foreach ((array) $lists as $l) {
        if ($l['value'] == 'y') {
            $sdays[] = $l;
        }
    }
}
// get and list CLs and lists
foreach ((array) $vres as $visn) {
    $values = array();
    $values['parentId'] = $visn['itemId'];
    $values['type'] = 'c';
    $clists = query("getchildlists",$config,$values,$sort);
    $values['type'] = 'l';
    $lists = query("getchildlists",$config,$values,$sort);
    if (!empty($lists) && is_array($lists) && count($lists) > 0 && !empty($clists) && is_array($clists) && count($clists) > 0) {
        $lists = array_merge($clists,$lists);
    } elseif (!empty($clists) && is_array($clists) && count($clists) > 0) {
        $lists = $clists;
    }
    if (!empty($lists) && is_array($lists) && count($lists) > 0) {
        foreach ((array) $lists as $list) {
            // if someday, continue
            foreach ((array) $sdays as $s) {
                if (
                    $s['visId'] == $visn['itemId'] &&
                    $s['itemId'] == $list['listId'] &&
                    $s['itemType'] == $list['type']
                    ) continue 2;
            }
            // otherwise, add to menu
            // get list details
            $values['listId'] = $list['listId'];
            $values['type'] = $list['type'];
            if ($list['type'] == 'c') { $qry = "selectchecklist"; } else { $qry = "selectlist"; }
            $listN = query($qry,$config,$values,$sort);
            $listNarr = array(
                'listId' => $list['listId'],
                'type' => $list['type'],
                'title' => makeclean($listN[0]['title'] . ($list['type'] == 'c' ? ' .CL' : ' .LIST')),
                'sortBy' => substr($listN[0]['sortBy'], 0, 2)
            );
            if ($listN[0]['menu'] == 'y') $listMena[] = $listNarr;
            else $listNoMena[] = $listNarr;
        }
    }
}

// deduplicate where in multiple visions
$listMena = array_map('unserialize', array_unique(array_map('serialize', $listMena)));
$listNoMena = array_map('unserialize', array_unique(array_map('serialize', $listNoMena)));

// sort order for lists
function sortBys ($a, $b) {
    return $a['sortBy'] - $b['sortBy'];
}
usort($listMena, 'sortBys');

foreach ((array) $listMena as $l) {
    $listMen .= "<tr>\n
        <td><a href='reportLists.php?listId={$l['listId']}&type={$l['type']}'>"
        . $l['title'] . "</a></td>\n</tr>\n";
}
$listMen .= "<tr>\n<td></td>\n</tr>\n";

// get and count active projects
$values['type']= "p";
$values['isSomeday'] = "n";
$stem  = " WHERE ".sqlparts("typefilter",$config,$values)
        ." AND ".sqlparts("activeitems",$config,$values)
        ." AND ".sqlparts("pendingitems",$config,$values)
        ." AND title NOT LIKE '~%'";
$values['filterquery'] = $stem." AND ".sqlparts("issomeday",$config,$values);
$pres = query("getitems",$config,$values,$sort);

// eliminate items with inactive visions from array
$i=0;
foreach ($pres as $p) {
    $inactive = true;
    $values['itemId'] = $p['itemId'];
    $lu = query("lookupparentshort",$config,$values,$sort);
    foreach ((array) $lu as $mat) {
        foreach ($vres as $v) {
            if ($mat['parentId'] == $v['itemId']) $inactive = false;
        }
    }
    if ($inactive) {
        unset($pres[$i]);
    }
    $i++;
}
$pres = array_values($pres);

//echo '<pre>'; var_dump($pres);die;

$numberprojects=($pres)?count($pres):0;
/*
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
}
*/

echo "<table summary='table of projects'><tbody>\n" . $menProc . $listMen;
if ($expand && $numberprojects) echo columnedTable(1,$pres);
echo "</tbody></table>\n";

?>
	<ul id="menulist">
    <?php
    $class=$menuend='';
    foreach ($menu2 as $index=>$line) {
        if (empty($line['link'])) {
            if ($line['label']==='separator') {
                $class=" class='menuseparator' ";
            } else {
                echo "$menuend<li>{$line['label']}<ul>\n";
                $menuend="</ul></li>\n";
            }
        } else {
            if (empty($acckey[$line['link']]))
                $accesskey=$keypress='';
            else {
                $menu2[$index]['key']=$acckey[$line['link']];
                $keypress=" ({$acckey[$line['link']]})";
                $accesskey=" accesskey='{$acckey[$line['link']]}'";
            }
	        echo "<li$class>\n"
                ,"<a href='{$line['link']}' title='{$line['title']}' $accesskey>"
                ,"{$line['label']}$keypress</a></li>\n";
            $class='';
        }
    }
    echo $menuend;
    ?>
	</ul>

</div>
