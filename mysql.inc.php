<?php
/*
    MySQL extensions to standard SQL have been avoided where known & where practical
  ===============================================================

*/
error_reporting(E_ALL ^ E_DEPRECATED);
function connectdb($config) {

    $config["conn"] = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db'])
        or die ("Unable to connect to MySQL server: check your host, user and pass settings in config.php!");

    return $config;
}
/*
  ===============================================================
*/
function getDBVersion() {
    return mysql_get_server_info();
}
/*
  ===============================================================
*/
function getDBtables($db) {
    $tablelist=array();
    $tables=mysql_list_tables($db);
	while ($tbl = mysql_fetch_row($tables))
	   array_push($tablelist,$tbl[0]);
    return $tablelist;
}
/*
  ===============================================================
*/
function doQuery($config, $query, $label=NULL) {

    // parse result into multitdimensional array $result[row#][field name] = field value
    $reply = mysqli_query($config["conn"], $query);

    if ($reply===false) {                       // failed query - return FALSE
        $result=false;
    } elseif ($reply===true) {                  // query was not a SELECT OR SHOW, so return number of rows affected
        $result = mysqli_affected_rows($config["conn"]);
    } else if (mysqli_num_rows($reply)===0) {   // empty SELECT/SHOW - return zero
        $result=0;
    } else {                                    // successful SELECT/SHOW - return array of results
        $result=array();
        while ($mysql_result = mysqli_fetch_assoc($reply))
            $result[]=$mysql_result;
    }

    /* get last autoincrement insert id:
        only valid for insert statements using autoincrement values;
        not updated when explicit value given for autoincrement field
        (MySQL "feature")
    */
    $GLOBALS['lastinsertid'] = mysqli_insert_id($config["conn"]);

    $error = mysqli_errno($config["conn"]);
    if ($error) $_SESSION['message'][]=
                "Error $error in query '$label': '".mysqli_error($config["conn"])."'";

    return $result;
}
/*
  ===============================================================
*/
function safeIntoDB($config, &$value, $key=NULL) {
	// don't clean arrays - clean individual strings/values
	if (is_array($value)) {
		foreach ($value as $key=>$string) $value[$key] = safeIntoDB($config, $string, $key);
		return $value;
	} else {
		// don't clean filters - we've cleaned those separately in the sqlparts function
		if (strpos($key,'filterquery')===false
			&& !preg_match("/^'\d\d\d\d-\d\d-\d\d'$/",$value) ) // and don't clean dates
			{
			if ( get_magic_quotes_gpc() && !empty($value) && is_string($value) )
				$value = stripslashes($value);
			$value = mysqli_real_escape_string($config["conn"], $value);
		} else { return $value;}
		return $value;
	}
}
/*
  ===============================================================
*/
//GENERAL RULES:
//"select" = query for something by its id; a single-row result
//"get" = query for something of a particular type; a multi-row result
//"new", "update", "delete" are self-explanatory
//"check"="complete" for checklistselectbox
//"complete" = set status to completed
//"remove" = remove by association Id (items associated with a project, etc)
//"Count" = # of a particular type in table
//"selectbox" = get results to create a selectbox- for assignment or filter
function getsql($config,$values,$sort,$querylabel) {

    if (is_array($values))
        foreach ($values as $key=>$value)
            $values[$key] = safeIntoDB($config, $value, $key);

	switch ($querylabel) {
		case "instanceselectbox":
			$sql="SELECT `instanceId`, `name`, `description`
				FROM `". $config['prefix'] ."instance`
				ORDER BY `description` ASC";
			break;

		case "categoryselectbox":
			$sql="SELECT c.`categoryId`, c.`category`, c.`description`
				FROM `". $config['prefix'] ."categories` as c
				ORDER BY {$sort['categoryselectbox']}";
			break;

		case "checkchecklistitem":
		    $instTable = '';
		    $instQuery = '';
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `{$values['field']}` = 'y'
				WHERE `checklistItemId` IN ({$values['itemfilterquery']})"
				. $instQuery;
			break;

		case "checklistselectbox":
			$sql="SELECT cl.`checklistId`, cl.`title`,
						cl.`premiseA`,cl.`premiseB`,cl.`conclusion`,cl.`behaviour`, cl.`standard`, cl.`conditions`, cl.`metaphor`, cl.`categoryId`, cl.`hyperlink`, cl.`sortBy`, c.`category`
				FROM `". $config['prefix'] ."checklist` as cl
				LEFT OUTER JOIN `". $config['prefix'] ."categories` as c USING (`categoryId`)
				ORDER BY {$sort['checklistselectbox']}";
			break;

		case "clearchecklist":
		    $instTable = '';
		    $instQuery = '';
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `checked` = 'n'
				WHERE `checklistId` = '{$values['id']}'"
				. $instQuery;
			break;

		case "clearchecklistignore":
		    $instTable = '';
		    $instQuery = '';
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `ignored` = 'n'
				WHERE `checklistId` = '{$values['id']}'"
				. $instQuery;
			break;

		case "clearchecklistscore":
		    $instTable = '';
		    $instQuery = '';
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `assessed` = 0,
				    `score` = 0
				WHERE `checklistId` = '{$values['id']}'"
				. $instQuery;
			break;

		case "assesschecklist":
		    $instTable = '';
		    $instQuery = '';
		    $instUse = FALSE;
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instUse = TRUE;
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

		    $prioritiseQuery = '';
		    if (isset($values['prioritise']) && $values['prioritise'] > -1 && !$instUse) {
		        $prioritiseQuery = " AND `expect` = 0 ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `assessed` = `assessed` + 1
				WHERE `checklistId` = '{$values['id']}'
				AND `ignored` = 'n'"
				. $instQuery
				. $prioritiseQuery;

			break;

		case "scorechecklist":

		    $instTable = '';
		    $instQuery = '';
		    $instUse = FALSE;

		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
    		    $instUse = TRUE;
    		    $instTable = 'inst';
		        $instQuery = " AND `instanceId` = '{$values['instanceId']}' ";
		    }

		    $prioritiseQuery = '';
		    if (isset($values['prioritise']) && $values['prioritise'] > -1 && !$instUse) {
		        $prioritiseQuery = " AND `expect` = 0 ";
		    }

			$sql="UPDATE `". $config['prefix'] ."checklistitems" . $instTable . "`
				SET `score` = `score` + 1
				WHERE `checklistId` = '{$values['id']}'
				AND `ignored` = 'n'
				AND `checklistItemId` IN ({$values['itemfilterquery']})"
				. $instQuery
				. $prioritiseQuery;
			//echo '<pre>' . $sql; die;
			break;

		case "clearitemlists":
			$sql="DELETE FROM `". $config['prefix'] ."lookuplist`
				WHERE `parentId` = '{$values['itemId']}'
				AND `listType` = '{$values['type']}'
				";
			break;

		case "delitemlist":
			$sql="DELETE FROM `". $config['prefix'] ."lookuplist`
				WHERE `parentId` = '{$values['parentId']}'
				AND `listType` = '{$values['listType']}'
				AND `listId` = '{$values['listId']}'
				";
			break;

		case "completeitem":
			$sql="UPDATE `". $config['prefix'] ."itemstatus`
				SET `dateCompleted`=" . $values['dateCompleted'].
				", `lastModified` = NULL
				WHERE `itemId`=" . $values['itemId'];
			break;

		case "completelistitem":
			$sql="UPDATE `". $config['prefix'] ."listitems`
				SET `dateCompleted`={$values['dateCompleted']}
				WHERE `listItemId` IN ({$values['itemfilterquery']})";
			break;

		case "copynextaction":
			$sql="INSERT INTO `". $config['prefix'] ."nextactions` (`parentId`,`nextaction`)
				VALUES ('{$values['parentId']}','{$values['newitemId']}')
				ON DUPLICATE KEY UPDATE `nextaction`='{$values['newitemId']}'";
			break;

		case "getchildlists":
			$sql="SELECT `parentId` as itemId, `listId` as id,
						`listType` as type
				FROM `". $config['prefix'] . "lookuplist`
				WHERE 1 = 1";
    			if (isset($values['parentId']))	$sql .= " AND `parentId` = {$values['parentId']}";
    			if (isset($values['type']))	$sql .= " AND `listType` = '{$values['type']}'";
    			if (isset($values['listId']))	$sql .= " AND `listId` = '{$values['listId']}'";
			break;

		case "getqualities":
			$sql="SELECT *
				FROM `". $config['prefix'] . "qualities`
				WHERE {$values['qQuery']} = '{$values['qValue']}' ";
    			if (isset($values['qSearch'])) $sql .= " AND {$values['qSearch']} LIKE '%{$values['qNeedle']}%' ";
    			//if (isset($values['qLimit'])) $sql .= " AND `disp` LIKE '%{$values['qLimit']}%' ";
				$sql .= "ORDER BY {$sort['getqualities']}";
            break;

		case "lookupqualities":
			$sql="SELECT *
				FROM `". $config['prefix'] . "lookupqualities`
				WHERE 1 = 1";
    			if (isset($values['visId'])) $sql .= "	AND `visId` = '{$values['visId']}'";
    			if (isset($values['itemId'])) $sql .= "	AND `itemId` = '{$values['itemId']}'";
    			if (isset($values['qId'])) $sql .= " AND `qId` = '{$values['qId']}'";
    			if (isset($values['itemType'])) $sql .= " AND `itemType` = '{$values['itemType']}'";
    			if (isset($values['qaId'])) $sql .= " AND `qaId` = '{$values['qaId']}'";
            break;

		case "deletequalities":
			$sql="DELETE
				FROM `". $config['prefix'] . "lookupqualities`
				WHERE `itemId` = '{$values['itemId']}'
				AND `itemType` = '{$values['itemType']}'";
			break;

		case "nullqualities":
			$sql="DELETE
				FROM `". $config['prefix'] . "lookupqualities`
				WHERE `value` = ''";
			break;

		case "countactions":
			$sql="SELECT COUNT(DISTINCT i.`itemId`) AS nactions
                    FROM `{$config['prefix']}items` as i
					JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
                    JOIN `{$config['prefix']}itemattributes` as ia USING (`itemId`)
                    LEFT OUTER JOIN `{$config['prefix']}lookup` AS lu USING (`itemId`)
                    LEFT OUTER JOIN ( SELECT
    						i.`itemId` AS parentId, ia.`isSomeday` AS pisSomeday,
    						ia.`deadline` AS pdeadline, ia.`suppress` AS psuppress,
    						ia.`suppressUntil` AS psuppressUntil,
    						its.`dateCompleted` AS pdateCompleted
					   FROM `{$config['prefix']}itemattributes` AS ia
							JOIN `{$config['prefix']}items` AS i USING (`itemId`)
							JOIN `{$config['prefix']}itemstatus` AS its  USING (`itemId`)
					) as y ON (y.`parentId` = lu.`parentId`) {$values['filterquery']}";
			break;

        case 'countactionsbycontext':
            $sql="SELECT cn.`name` AS cname,cn.`contextId`,COUNT(x.`itemId`) AS count
                    FROM `{$config['prefix']}itemattributes` as x
                    JOIN `{$config['prefix']}itemattributes` as ia USING (`itemId`)
                    JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
					LEFT OUTER JOIN `{$config['prefix']}context` AS cn
						ON (ia.`contextId` = cn.`contextId`)
                    JOIN (
                        SELECT DISTINCT nextAction FROM `{$config['prefix']}nextactions` AS na
                            JOIN (SELECT i.`itemId` AS parentId,
                                     ia.`isSomeday` AS pisSomeday,
                                     ia.`deadline` AS pdeadline,
						             ia.`suppress` AS psuppress,
						             ia.`suppressUntil` AS psuppressUntil,
						             its.`dateCompleted` AS pdateCompleted
            					   FROM `{$config['prefix']}itemattributes` as ia
            					   JOIN `{$config['prefix']}items` as i USING (`itemId`)
            					   JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
                                ) AS y USING (`parentId`)
                    ) AS nat ON (x.`itemId`=nat.`nextAction`)
                     {$values['filterquery']}
                     GROUP BY ia.`contextId` ORDER BY cn.`name`";
            break;

		case "countnextactions":
			$sql="SELECT INTERVAL(DATEDIFF(CURDATE(),ia.`deadline`),-6,0,1) AS `duecategory`,
			           COUNT(DISTINCT i.`itemId`) AS nnextactions
                    FROM `{$config['prefix']}items` as i
					JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
                    JOIN `{$config['prefix']}itemattributes` as ia USING (`itemId`)
                    JOIN (
                        SELECT DISTINCT nextAction FROM `{$config['prefix']}nextactions` AS na
                            LEFT OUTER JOIN (SELECT i.`itemId` AS parentId,
                                     ia.`isSomeday` AS pisSomeday,
                                     ia.`deadline` AS pdeadline,
						             ia.`suppress` AS psuppress,
						             ia.`suppressUntil` AS psuppressUntil,
						             its.`dateCompleted` AS pdateCompleted
            					   FROM `{$config['prefix']}itemattributes` as ia
            					   JOIN `{$config['prefix']}items` as i USING (`itemId`)
            					   JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
                                ) AS y USING (`parentId`)
                    ) AS nat ON (i.`itemId`=nat.`nextAction`)
                    {$values['filterquery']}
					GROUP BY `duecategory`";
			break;
		case "countselected":
			$sql="SELECT FOUND_ROWS()";
			break;
		case "countspacecontexts":
			$sql="SELECT COUNT(*)
				FROM `". $config['prefix'] ."context`";
			break;
		case "deleteinstance":
			$sql="DELETE FROM `". $config['prefix'] ."instance`
				WHERE `instanceId`='{$values['id']}'";
			break;
		case "deletecategory":
			$sql="DELETE FROM `". $config['prefix'] ."categories`
				WHERE `categoryId`='{$values['id']}'";
			break;
		case "deletechecklist":
			$sql="DELETE FROM `". $config['prefix'] ."checklist`
				WHERE `checklistId`='{$values['id']}'";
			break;
		case "deletechecklistitem":
			$sql="DELETE FROM `". $config['prefix'] ."checklistitems`
				WHERE `checklistItemId`='{$values['itemId']}'";
			break;
		case "deletechecklistiteminst":
			$sql="DELETE FROM `". $config['prefix'] ."checklistitemsinst`
				WHERE `checklistItemId`='{$values['itemId']}'";
			break;
		case "deletelist":
			$sql="DELETE FROM `". $config['prefix'] ."list`
				WHERE `listId`='{$values['id']}'";
			break;
		case "deletelistitem":
			$sql="DELETE FROM `". $config['prefix'] ."listitems`
				WHERE `listItemId`='{$values['itemId']}'";
			break;
		case "deletelistlookup":
			$sql="DELETE FROM `". $config['prefix'] ."lookuplist`
				WHERE `listId`='{$values['id']}'
				AND `listType`='{$values['type']}'";
			break;
		case "deleteitem":
			$sql="DELETE FROM `". $config['prefix'] ."items`
				WHERE `itemId`='{$values['itemId']}'";
			break;
		case "deleteitemattributes":
			$sql="DELETE FROM `". $config['prefix'] ."itemattributes`
				WHERE `itemId`='{$values['itemId']}'";
			break;
		case "deleteitemstatus":
			$sql="DELETE FROM `". $config['prefix'] ."itemstatus`
				WHERE `itemId`='{$values['itemId']}'";
			break;
		case "deletelookup":
			$sql="DELETE FROM `". $config['prefix'] ."lookup`
				WHERE `itemId` ='{$values['itemId']}'";
			break;
		case "checklookup":
			$sql="SELECT * FROM `". $config['prefix'] ."lookup`
				WHERE `itemId` ='{$values['itemId']}'
				AND `parentId` ='{$values['parentId']}'";
			break;
		case "lookuparray":
			$sql="SELECT * FROM `". $config['prefix'] ."lookup`
				";
			break;
		case "deletelookupparents":
			$sql="DELETE FROM `". $config['prefix'] ."lookup`
				WHERE `parentId` ='{$values['itemId']}'";
			break;
		case "deleteparlookup":
			$sql="DELETE FROM `". $config['prefix'] ."lookup`
				WHERE `itemId` ='{$values['itemId']}'
				AND `parentId` ='{$values['parentId']}'
				";
			break;
		case "deletenextaction":
			$sql="DELETE FROM `". $config['prefix'] ."nextactions`
				WHERE `nextAction`='{$values['itemId']}'";
			break;
		case "deletenextactionparents":
			$sql="DELETE FROM `". $config['prefix'] ."nextactions`
				WHERE `parentId` ='{$values['itemId']}'";
			break;
		case "deletenote":
			$sql="DELETE FROM `". $config['prefix'] ."tickler`
				WHERE `ticklerId`='{$values['noteId']}'";
			break;
		case "deletespacecontext":
			$sql="DELETE FROM `". $config['prefix'] ."context`
				WHERE `contextId`='{$values['id']}'";
			break;
		case "deletetimecontext":
			$sql="DELETE FROM `". $config['prefix'] ."timeitems`
				WHERE `timeframeId`='{$values['id']}'";
			break;

		case "getchecklistitems":
		    $table = 'cli';
		    $join = '';
		    $inst = '';
		    $sorts = $sort['getchecklistitems'];
		    if (isset($values['instanceId']) && is_numeric($values['instanceId'])) {
		        $table = 'i';
    		    $join = " LEFT JOIN `{$config['prefix']}checklistitemsinst` AS i
				ON (cli.`checklistitemId` = i.`checklistitemId`) ";
    		    $inst = " AND i.`instanceId` = '{$values['instanceId']}' ";
    		    $sorts = $sort['getchecklistitemsinst'];
		    }
			$sql="SELECT cli.`checklistitemId` AS `itemId`, cli.`item`, cli.`notes`, cli.`hyperlink`,
						cli.`checklistId` AS `id`, cli.`expect`, cli.`effort`,
						" . $table . ".`checked`,
						" . $table . ".`ignored`,
						" . $table . ".`score`,
						" . $table . ".`assessed`
				FROM `{$config['prefix']}checklistitems` AS cli
				" . $join . "
				WHERE cli.`checklistId` = '{$values['id']}'
				" . $inst . "
                ORDER BY {$sorts}";
            //echo '<pre>'. $sql;die;
			break;

		case "getchecklists":
			$sql="SELECT l.`checklistId` as id, l.`title`,
						l.`premiseA`,l.`premiseB`,l.`conclusion`,l.`behaviour`, l.`standard`, l.`conditions`, l.`metaphor`, l.`categoryId`, l.`hyperlink`, l.`sortBy`, c.`category`
				FROM `". $config['prefix'] ."checklist` as l
				LEFT OUTER JOIN `{$config['prefix']}categories` as c USING (`categoryId`) "
				.$values['filterquery']." ORDER BY {$sort['getchecklists']}";
			break;

		case "getchildren":
			$sql="SELECT i.`itemId`, i.`title`, i.`description`,
					i.`premiseA`,i.`premiseB`,i.`conclusion`,i.`behaviour`, i.`standard`, i.`conditions`, i.`metaphor`, i.`hyperlink`, i.`sortBy`, ia.`type`,
					ia.`isSomeday`, ia.`deadline`, ia.`repeat`,
					ia.`suppress`, ia.`suppressUntil`,
					its.`dateCreated`, its.`dateCompleted`,
					its.`lastModified`, ia.`categoryId`,
					c.`category`, ia.`contextId`,
					cn.`name` AS cname, ia.`timeframeId`, ti.`timeframe`
					, na.nextaction as NA
				FROM `". $config['prefix'] . "itemattributes` as ia
					JOIN `{$config['prefix']}lookup` AS lu USING (`itemId`)
					JOIN `". $config['prefix'] . "items` AS i USING (`itemId`)
					JOIN `". $config['prefix'] . "itemstatus` AS its USING (`itemId`)
					LEFT OUTER JOIN `". $config['prefix'] . "context` AS cn
						ON (ia.`contextId` = cn.`contextId`)
					LEFT OUTER JOIN `". $config['prefix'] ."categories` AS c
						ON (ia.`categoryId` = c.`categoryId`)
					LEFT OUTER JOIN `". $config['prefix'] . "timeitems` AS ti
						ON (ia.`timeframeId` = ti.`timeframeId`)
				LEFT JOIN (
						SELECT DISTINCT nextaction FROM {$config['prefix']}nextactions
					) AS na ON(na.nextaction=i.itemId)
				WHERE lu.`parentId`= '{$values['parentId']}' {$values['filterquery']}
				ORDER BY {$sort['getchildren']}";
			break;

		case "getgtdphpversion":
			//$sql="SELECT `version` FROM `{$config['prefix']}version`";
			break;

		case "getitems":
			$sql="SELECT i.`itemId`, i.`title`, i.`description`, i.`sortBy`, ia.`deadline`, ia.`isSomeday`, its.`dateCompleted`, c.`category`, c.`categoryId`, ia.`type`
				FROM `". $config['prefix'] . "itemattributes` as ia
					JOIN `". $config['prefix'] . "items` as i
						ON (ia.`itemId` = i.`itemId`)
					JOIN `". $config['prefix'] . "itemstatus` as its
						ON (ia.`itemId` = its.`itemId`)
					LEFT OUTER JOIN `". $config['prefix'] . "context` as cn
						ON (ia.`contextId` = cn.`contextId`)
					LEFT OUTER JOIN `". $config['prefix'] ."categories` as c
						ON (ia.`categoryId` = c.`categoryId`)
					LEFT OUTER JOIN `". $config['prefix'] . "timeitems` as ti
						ON (ia.`timeframeId` = ti.`timeframeId`) ".$values['filterquery']."
				ORDER BY {$sort['getitems']}";
			break;

		case "getitemsattr":
			$sql="SELECT itemId, type
				FROM `". $config['prefix'] . "itemattributes`
				";

			break;

		case "getitemsandparent":
			$sql="SELECT
    				x.`itemId`, x.`title`, x.`description`,
    				x.`premiseA`, x.`premiseB`, x.`conclusion`, x.`behaviour`, x.`standard`, x.`conditions`, x.`metaphor`, x.`hyperlink`, x.`sortBy`, x.`type`, x.`isSomeday`,
    				x.`deadline`, x.`repeat`, x.`suppress`,
    				x.`suppressUntil`, x.`dateCreated`, x.`dateCompleted`,
    				x.`lastModified`, x.`categoryId`, x.`category`,
    				x.`contextId`, x.`cname`, x.`timeframeId`,
    				x.`timeframe`,
    				GROUP_CONCAT(DISTINCT y.`parentId` ORDER BY y.`ptype`) as `parentId`,
    				GROUP_CONCAT(DISTINCT y.`ptitle` ORDER BY y.`ptype` SEPARATOR '{$config['separator']}') AS `ptitle`,
    				GROUP_CONCAT(DISTINCT y.`ptype` ORDER BY y.`ptype` SEPARATOR '{$config['separator']}') AS `ptype`
    				{$values['extravarsfilterquery']}
				FROM (
						SELECT
							i.`itemId`, i.`title`, i.`description`,
							i.`premiseA`, i.`premiseB`, i.`conclusion`, i.`behaviour`, i.`standard`, i.`conditions`, i.`metaphor`, i.`hyperlink`, i.`sortBy`, ia.`type`, ia.`isSomeday`,
							ia.`deadline`, ia.`repeat`, ia.`suppress`,
							ia.`suppressUntil`, its.`dateCreated`,
							its.`dateCompleted`, its.`lastModified`,
							ia.`categoryId`, c.`category`, ia.`contextId`,
							cn.`name` AS cname, ia.`timeframeId`,
							ti.`timeframe`, lu.`parentId`
						FROM
								`". $config['prefix'] . "itemattributes` as ia
							JOIN `". $config['prefix'] . "items` as i
								ON (ia.`itemId` = i.`itemId`)
							JOIN `". $config['prefix'] . "itemstatus` as its
								ON (ia.`itemId` = its.`itemId`)
							LEFT OUTER JOIN `". $config['prefix'] . "context` as cn
								ON (ia.`contextId` = cn.`contextId`)
							LEFT OUTER JOIN `". $config['prefix'] ."categories` as c
								ON (ia.`categoryId` = c.`categoryId`)
							LEFT OUTER JOIN `". $config['prefix'] . "timeitems` as ti
								ON (ia.`timeframeId` = ti.`timeframeId`)
							LEFT OUTER JOIN `". $config['prefix'] . "lookup` as lu
								ON (ia.`itemId` = lu.`itemId`)".$values['childfilterquery']."
				) as x
					LEFT OUTER JOIN
					(
						SELECT
							i.`itemId` AS parentId, i.`title` AS ptitle,
							i.`description` AS pdescription,
							i.`premiseA` AS ppremiseA,
							i.`premiseB` AS ppremiseB,
							i.`conclusion` AS pconclusion,
							i.`behaviour` AS pbehaviour,
							i.`standard` AS pstandard,
							i.`conditions` AS pconditions,
							i.`metaphor` AS pmetaphor,
							i.`hyperlink` AS phyperlink,
							i.`sortBy` AS psortBy,
							ia.`type` AS ptype, ia.`isSomeday` AS pisSomeday,
							ia.`deadline` AS pdeadline, ia.`repeat` AS prepeat,
							ia.`suppress` AS psuppress,
							ia.`suppressUntil` AS psuppressUntil,
							its.`dateCompleted` AS pdateCompleted
						FROM
								`". $config['prefix'] . "itemattributes` as ia
							JOIN `". $config['prefix'] . "items` as i
								ON (ia.`itemId` = i.`itemId`)
							JOIN `". $config['prefix'] . "itemstatus` as its
								ON (ia.`itemId` = its.`itemId`)
					) as y ON (y.parentId = x.parentId)
				{$values['filterquery']} GROUP BY x.`itemId`
				ORDER BY {$sort['getitemsandparent']}";
			break;

		case "getitembrief":
			$sql="SELECT `title`, `description`, `premiseA`, `premiseB`, `conclusion`, `behaviour`, `standard`, `conditions`, `metaphor`
				FROM  `". $config['prefix'] . "items`
				WHERE `itemId` = {$values['itemId']}";
			break;

		case "getlistitems":
			$sql="SELECT li.`listItemId` as itemId, li.`item`, li.`notes`, li.`hyperlink`,
                         li.`listId` as id, li.`dateCompleted`, li.`expect`
				FROM `". $config['prefix'] . "listitems` as li
					LEFT JOIN `". $config['prefix'] . "list` as l
						on li.`listId` = l.`listId`
				WHERE l.`listId` = '{$values['id']}' ".$values['filterquery']."
				ORDER BY {$sort['getlistitems']}";
			break;

		case "getlists":
			$sql="SELECT l.`listId` as id, l.`title`, l.`premiseA`,l.`premiseB`,l.`conclusion`,l.`behaviour`, l.`standard`, l.`conditions`, l.`metaphor`, l.`categoryId`, l.`hyperlink`, l.`sortBy`, c.`category`
				FROM `". $config['prefix'] . "list` as l
				LEFT OUTER JOIN `{$config['prefix']}categories` as c USING (`categoryId`) "
				.$values['filterquery']." ORDER BY {$sort['getlists']}";
			break;

		case "getnotes":
			$sql="SELECT `ticklerId`, `title`, `note`, `date`
				FROM `". $config['prefix'] . "tickler`  as tk".$values['filterquery']."
				ORDER BY {$sort['getnotes']}";
			break;

		case "getorphaneditems":
			$sql="SELECT ia.`itemId`, ia.`type`, i.`title`, i.`description`, i.`sortBy`, ia.`isSomeday`
				FROM `{$config['prefix']}itemattributes` AS ia
				JOIN `{$config['prefix']}items`		  AS i   USING (itemId)
				JOIN `{$config['prefix']}itemstatus`	 AS its USING (itemId)
				WHERE (ia.`type` NOT IN ({$values['notOrphansfilterquery']})
					       AND (ia.`itemId` NOT IN
						(SELECT lu.`itemId` FROM `". $config['prefix'] . "lookup` as lu)
                           ) OR ia.`type` IS NULL OR ia.`type`='')
				ORDER BY {$sort['getorphaneditems']}";
			break;

		case "getarrayorphans":
			/*$sql="SELECT i.`itemId`, ia.`type`, i.`title`, i.`description`, ia.`isSomeday`, its.`dateCompleted`
				FROM `{$config['prefix']}itemattributes` AS ia
				LEFT JOIN `{$config['prefix']}items` AS i USING (itemId)
				LEFT JOIN `{$config['prefix']}itemstatus` AS its USING (itemId)
				RIGHT JOIN `{$config['prefix']}lookup` AS lu USING (itemId)
				WHERE lu.parentId NOT IN ({$values['filterquery']})
				AND ia.type IN ({$values['filterquerytype']})
				ORDER BY {$sort['getitems']}";*/
			$sql="SELECT ia.`itemId`
				FROM `{$config['prefix']}itemattributes` AS ia
				WHERE ia.type IN ({$values['filterquerytype']})
				ORDER BY {$sort['getitems']}";
			break;

		case "getspacecontexts":
			$sql="SELECT `contextId`, `name`
				FROM `". $config['prefix'] . "context` ORDER BY `name` ASC";
			break;

		case "gettimecontexts":
			$sql="SELECT `timeframeId`, `timeframe`, `description`
				FROM `". $config['prefix'] . "timeitems` AS ti
				{$values['timefilterquery']} ORDER BY `timeframeId` ASC";
			break;


		case "lookupparent":
			$sql="SELECT lu.`parentId`,i.`title` AS `ptitle`,ia.`isSomeday`,ia.`type` AS `ptype`
				FROM `". $config['prefix'] . "lookup` AS lu
				JOIN `{$config['prefix']}items` AS i ON (lu.`parentId` = i.`itemId`)
				JOIN `{$config['prefix']}itemattributes` AS ia ON (lu.`parentId` = ia.`itemId`)
				WHERE lu.`itemId`='{$values['itemId']}'";
			break;

		case "lookupparentshort":
			$sql="SELECT `parentId`
				FROM `". $config['prefix'] . "lookup`
				WHERE `itemId`='{$values['itemId']}'";
			break;

		case "newinstance":
			$sql="INSERT INTO `". $config['prefix'] ."instance`
				VALUES ('', '{$values['name']}', '{$values['description']}')";
			break;

		case "newcategory":
			$sql="INSERT INTO `". $config['prefix'] ."categories`
				VALUES (NULL, '{$values['name']}', '{$values['description']}')";
			break;

		case "newchecklistitem":
			$sql="INSERT INTO `". $config['prefix'] . "checklistitems`
				VALUES (NULL, '{$values['item']}','{$values['notes']}','{$values['hyperlink']}',
                        '{$values['id']}', 'n', 'n', '', '', NULL, '')";
			break;

		case "selectchecklistiteminst":
			$sql="SELECT `checklistItemId`, `checklistId`
				FROM `". $config['prefix'] ."checklistitems`";
			break;

		case "newchecklistiteminst":
			$sql="INSERT INTO `". $config['prefix'] . "checklistitemsinst`
				VALUES ('{$values['lastId']}', '{$values['id']}', '{$values['instanceId']}',
                        'n', 'n', '', '')";
			break;

		case "newitem":
			$sql="INSERT INTO `". $config['prefix'] . "items`
						(`title`,`description`,`premiseA`,`premiseB`,`conclusion`,`behaviour`,`standard`,`conditions`,`metaphor`,`hyperlink`)
				VALUES ('{$values['title']}',
						'{$values['description']}','{$values['premiseA']}','{$values['premiseB']}','{$values['conclusion']}','{$values['behaviour']}','{$values['standard']}','{$values['conditions']}','{$values['metaphor']}','{$values['hyperlink']}')";
			break;

		case "newitemattributes":
			$sql="INSERT INTO `". $config['prefix'] . "itemattributes`
						(`itemId`,`type`,`isSomeday`,`categoryId`,`contextId`,
						`timeframeId`,`deadline`,`repeat`,`suppress`,`suppressUntil`)
				VALUES ('{$values['newitemId']}','{$values['type']}','{$values['isSomeday']}',
						'{$values['categoryId']}','{$values['contextId']}','{$values['timeframeId']}',
						{$values['deadline']},'{$values['repeat']}','{$values['suppress']}',
						'{$values['suppressUntil']}')";
			break;

		case "newitemstatus":
			$sql="INSERT INTO `". $config['prefix'] . "itemstatus`
						(`itemId`,`dateCreated`,`lastModified`,`dateCompleted`)
				VALUES ('{$values['newitemId']}',
						CURRENT_DATE,NULL,{$values['dateCompleted']})";
			break;

		case "newlist":
			$sql="INSERT INTO `". $config['prefix'] . "list`
				VALUES (NULL, '{$values['title']}',
						'{$values['categoryId']}', '{$values['premiseA']}', '{$values['premiseB']}', '{$values['conclusion']}', '{$values['behaviour']}', '{$values['standard']}', '{$values['conditions']}', '{$values['metaphor']}', '{$values['hyperlink']}', '{$values['sortBy']}')";
			break;

		case "newlistitem":
			$sql="INSERT INTO `". $config['prefix'] . "listitems`
				VALUES (NULL, '{$values['item']}',
						'{$values['notes']}','{$values['hyperlink']}', '{$values['id']}', NULL)";
			break;

		case "newnextaction":
			$sql="INSERT INTO `". $config['prefix'] . "nextactions`
						(`parentId`,`nextaction`)
				VALUES ('{$values['parentId']}','{$values['newitemId']}')
				ON DUPLICATE KEY UPDATE `nextaction`='{$values['newitemId']}'";
			break;

		case "newnote":
			$sql="INSERT INTO `". $config['prefix'] . "tickler`
						(`date`,`title`,`note`,`repeat`,`suppressUntil`)
				VALUES ('{$values['date']}','{$values['title']}',
						'{$values['note']}','{$values['repeat']}',
						'{$values['suppressUntil']}')";
			break;

		case "newparent":
			$sql="REPLACE INTO `". $config['prefix'] . "lookup`
						(`parentId`,`itemId`)
				VALUES ('{$values['parentId']}','{$values['newitemId']}')";
			break;

		case "newlistparent":
			$sql="REPLACE INTO `". $config['prefix'] . "lookuplist`
						(`parentId`,`listId`,`listType`)
				VALUES ('{$values['itemId']}','{$values['id']}','{$values['type']}')";
			break;

		case "newspacecontext":
			$sql="INSERT INTO `". $config['prefix'] . "context`
						(`name`,`description`)
				VALUES ('{$values['name']}', '{$values['description']}')";
			break;

		case "newtimecontext":
			$sql="INSERT INTO `". $config['prefix'] . "timeitems`
						(`timeframe`,`description`,`type`)
				VALUES ('{$values['name']}', '{$values['description']}', '{$values['type']}')";
			break;

		case "parentselectbox":
			$sql="SELECT i.`itemId`, i.`title`,
						i.`description`, ia.`isSomeday`,ia.`type`
				FROM `". $config['prefix'] . "items` as i
				JOIN `{$config['prefix']}itemattributes` as ia USING (`itemId`)
				JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
				WHERE (its.`dateCompleted` IS NULL) {$values['ptypefilterquery']}
				ORDER BY ia.`type`,i.`title`";
				#ORDER BY {$sort['parentselectbox']}";
			break;

		case "reassigncategory":
			$sql="UPDATE `". $config['prefix'] . "itemattributes`
				SET `categoryId`='{$values['newId']}'
				WHERE `categoryId`='{$values['id']}'";
			break;

		case "reassignspacecontext":
			$sql="UPDATE `". $config['prefix'] . "itemattributes`
				SET `contextId`='{$values['newId']}'
				WHERE `contextId`='{$values['id']}'";
			break;

		case "reassigntimecontext":
			$sql="UPDATE `". $config['prefix'] . "itemattributes`
				SET `timeframeId`='{$values['newId']}'
				WHERE `timeframeId`='{$values['id']}'";
			break;

		case "removechecklistitems":
			$sql="DELETE
				FROM `". $config['prefix'] . "checklistitems`
				WHERE `checklistId`='{$values['id']}'";
			break;

		case "deleteinstancerecords":
			$sql="DELETE
				FROM `". $config['prefix'] . "checklistitemsinst`
				WHERE `instanceId`='{$values['id']}'";
			break;

		case "removelistitems":
			$sql="DELETE
				FROM `". $config['prefix'] . "listitems`
				WHERE `listId`='{$values['id']}'";
			break;

		case "repeatnote":
			$sql="UPDATE `". $config['prefix'] . "tickler`
				SET `date` = DATE_ADD(`date`, INTERVAL ".$values['repeat']." DAY),
					`note` = '{$values['note']}', `title` = '{$values['title']}',
					`repeat` = '{$values['repeat']}',
					`suppressUntil` = '{$values['suppressUntil']}'
				WHERE `ticklerId` = '{$values['noteId']}'";
			break;

		case "selectcategory":
			$sql="SELECT `categoryId`, `category`, `description`
				FROM `". $config['prefix'] ."categories`
				WHERE `categoryId` = '{$values['categoryId']}'";
			break;

		case "selectchecklist":
			$sql="SELECT cl.`checklistId` as id, cl.`title`,
						cl.`premiseA`,cl.`premiseB`,cl.`conclusion`,cl.`behaviour`, cl.`standard`, cl.`conditions`, cl.`metaphor`, cl.`categoryId`, cl.`hyperlink`, cl.`sortBy`, cl.`frequency`, cl.`effort`, cl.`scored`, cl.`menu`, cl.`prioritise`, c.`category`
				FROM `". $config['prefix'] ."checklist` as cl
				LEFT OUTER JOIN `{$config['prefix']}categories` AS c USING (`categoryId`)
				WHERE cl.`checklistId`='{$values['id']}'
				ORDER BY {$sort['selectchecklist']}";
			break;

		case "selectchecklistitem":
			$sql="SELECT `checklistItemId` as itemId,
						`item`,
						`notes`,
						`hyperlink`,
						`checklistId` as id,
						`checked`,
						`ignored`,
						`score`,
						`assessed`,
						`expect`,
						`effort`
				FROM `". $config['prefix'] . "checklistitems`
				WHERE `checklistItemId` = '{$values['itemId']}'";
			break;

		case "selectcontext":
			$sql="SELECT `contextId`, `name`, `description`
				FROM `". $config['prefix'] . "context`
				WHERE `contextId` = '{$values['contextId']}'";
			break;

		case "selectitem":
			$sql="SELECT i.`itemId`, ia.`type`, i.`title`,
					i.`description`, i.`premiseA`, i.`premiseB`, i.`conclusion`, i.`behaviour`,i.`standard`,i.`conditions`,i.`metaphor`,i.`hyperlink`,i.`sortBy`,
					ia.`categoryId`, ia.`contextId`,
					ia.`timeframeId`, ia.`isSomeday`,
					ia.`deadline`, ia.`repeat`,
					ia.`suppress`, ia.`suppressUntil`,
					its.`dateCreated`, its.`dateCompleted`,
					its.`lastModified`, c.`category`, ti.`timeframe`,
					cn.`name` AS `cname`
				FROM `{$config['prefix']}items`          AS i
				JOIN `{$config['prefix']}itemattributes` AS ia  USING (`itemId`)
				JOIN `{$config['prefix']}itemstatus`     AS its USING (`itemId`)
					LEFT OUTER JOIN `". $config['prefix'] ."categories` as c
						ON (c.`categoryId` = ia.`categoryId`)
					LEFT OUTER JOIN `". $config['prefix'] . "context` as cn
						ON (cn.`contextId` = ia.`contextId`)
					LEFT OUTER JOIN `". $config['prefix'] . "timeitems` as ti
						ON (ti.`timeframeId` = ia.`timeframeId`)
				WHERE i.`itemId` = '{$values['itemId']}'";
			break;

		case "selectitemshort":
			$sql="SELECT i.`itemId`, i.`title`,
						i.`description`, ia.`isSomeday`,ia.`type`, its.`dateCompleted`
				FROM `". $config['prefix'] . "items` as i
				JOIN `{$config['prefix']}itemattributes` AS ia USING (`itemId`)
				JOIN `{$config['prefix']}itemstatus` AS its USING (`itemId`)
				WHERE i.`itemId` = '{$values['itemId']}'";
			break;

		case "selectitemstatus":
			$sql="SELECT ia.`itemId`, ia.`isSomeday`, its.`dateCompleted`
				FROM `{$config['prefix']}itemattributes` AS ia
				JOIN `{$config['prefix']}itemstatus`     AS its
				WHERE ia.`itemId` = '{$values['itemId']}'
				AND ia.`itemId` = its.`itemId`";
			break;

		case "selectitemtitle":
			$sql="SELECT i.`itemId`, i.`title`, i.`description`
				    FROM `". $config['prefix'] . "items` as i
				    WHERE i.`itemId` = '{$values['itemId']}'";
			break;

		case "selectlist":
			$sql="SELECT `listId` as id, l.`title`, l.`premiseA`, l.`premiseB`, l.`conclusion`, l.`behaviour`,l.`standard`,l.`conditions`,l.`metaphor`,l.`categoryId`, l.`hyperlink`, l.`sortBy`, l.`menu`, l.`prioritise`, c.`category`
				FROM `". $config['prefix'] . "list` AS l
                LEFT OUTER JOIN `{$config['prefix']}categories` AS c USING (`categoryId`)
				WHERE `listId` = '{$values['id']}'";
			break;

		case "selectlistitem":
			$sql="SELECT `listItemId` as itemId, `item`,
						`notes`, `hyperlink`, `listId` as id, `dateCompleted`, `expect`
				FROM `". $config['prefix'] . "listitems`
				WHERE `listItemId` = {$values['itemId']}";
			break;

		case "selectnote":
			$sql="SELECT `ticklerId`, `title`, `note`,
						`date`, `repeat`, `suppressUntil`
				FROM `". $config['prefix'] . "tickler`
				WHERE `ticklerId` = '{$values['noteId']}'";
			break;

		case "selecttimecontext":
			$sql="SELECT `timeframeId`, `timeframe`, `description`, `type`
				FROM `". $config['prefix'] . "timeitems`
				WHERE `timeframeId` = '{$values['tcId']}'";
			break;

		case "spacecontextselectbox":
			$sql="SELECT `contextId`, `name`, `description`
				FROM `". $config['prefix'] . "context` as cn
				ORDER BY {$sort['spacecontextselectbox']}";
			break;

		case "testitemrepeat":
			$sql="SELECT ia.`repeat`,its.`dateCompleted`
				FROM `{$config['prefix']}itemattributes` as ia
                JOIN `{$config['prefix']}itemstatus` as its USING (`itemId`)
				WHERE ia.`itemId`='{$values['itemId']}'";
			break;

		case "testnextaction":
			$sql="SELECT `parentId`, `nextaction`
				FROM `". $config['prefix'] . "nextactions`
				WHERE `nextaction`='{$values['itemId']}'";
			break;

		case "timecontextselectbox":
			$sql="SELECT `timeframeId`, `timeframe`, `description`, `type`
				FROM `". $config['prefix'] . "timeitems` as ti".$values['timefilterquery']."
				ORDER BY {$sort['timecontextselectbox']}";
			break;

		case "touchitem":
			$sql="UPDATE `". $config['prefix'] . "itemstatus`
				SET `lastModified` = NULL
				WHERE `itemId` = '{$values['itemId']}'";
			break;

		case "updatecategory":
			$sql="UPDATE `". $config['prefix'] ."categories`
				SET `category` ='{$values['name']}',
						`description` ='{$values['description']}'
				WHERE `categoryId` ='{$values['id']}'";
			break;

		case "updateinstance":
			$sql="UPDATE `". $config['prefix'] ."instance`
				SET `name` ='{$values['name']}',
						`description` ='{$values['description']}'
				WHERE `instanceId` ='{$values['id']}'";
			break;

		case "newlist":
			$sql="INSERT INTO `". $config['prefix'] . "list`
				VALUES (    NULL,
				            '{$values['title']}',
						    '{$values['categoryId']}',
						    '{$values['premiseA']}',
						    '{$values['premiseB']}',
						    '{$values['conclusion']}',
						    '{$values['behaviour']}',
						    '{$values['standard']}',
						    '{$values['conditions']}',
						    '{$values['metaphor']}',
						    '{$values['hyperlink']}',
						    '{$values['sortBy']}',
						    '{$values['menu']}',
						    '{$values['prioritise']}'
						    )";
			break;

		case "newchecklist":
			$sql="INSERT INTO `". $config['prefix'] ."checklist`
				VALUES (    '',
				            '{$values['title']}',
						    '{$values['categoryId']}',
						    '{$values['premiseA']}',
						    '{$values['premiseB']}',
						    '{$values['conclusion']}',
						    '{$values['behaviour']}',
						    '{$values['standard']}',
						    '{$values['conditions']}',
						    '{$values['metaphor']}',
						    '{$values['hyperlink']}',
						    '{$values['sortBy']}',
						    '{$values['frequency']}',
						    '',
						    '{$values['scored']}',
						    '{$values['menu']}',
						    '{$values['prioritise']}'
						    )";
			break;

		case "updatechecklist":
		    $sql="UPDATE `". $config['prefix'] ."checklist`
				SET     `title`         = '{$values['title']}',
						`categoryId`    = '{$values['categoryId']}',
						`premiseA`      = '{$values['premiseA']}',
						`premiseB`      = '{$values['premiseB']}',
						`conclusion`    = '{$values['conclusion']}',
						`behaviour`     = '{$values['behaviour']}',
						`standard`      = '{$values['standard']}',
						`conditions`    = '{$values['conditions']}',
						`metaphor`      = '{$values['metaphor']}',
						`hyperlink`     = '{$values['hyperlink']}',
						`sortBy`        = '{$values['sortBy']}',
						`frequency`     = '{$values['frequency']}',
						`effort`        = '{$values['effort']}',
						`scored`        = '{$values['scored']}',
						`menu`          = '{$values['menu']}',
						`prioritise`    = '{$values['prioritise']}'
				WHERE `checklistId` ='{$values['id']}'";
				//echo '<pre>';var_dump($sql);die;
			break;

		case "updatechecklistitem":
			$sql="UPDATE `". $config['prefix'] . "checklistitems`
				SET `notes` = '{$values['notes']}', `hyperlink` = '{$values['hyperlink']}', `item` = '{$values['item']}',
						`checklistId` = '{$values['id']}', `checked`='{$values['checked']}', `ignored`='{$values['ignored']}'
				WHERE `checklistItemId` ='{$values['itemId']}'";
			break;

		case "updatedeadline":
			$sql="UPDATE `{$config['prefix']}itemattributes`
				SET `deadline` ={$values['deadline']}
				WHERE `itemId` = '{$values['itemId']}'";
			break;

		case "updateitem":
			$sql="UPDATE `". $config['prefix'] . "items`
				SET `description` = '{$values['description']}',
						`title` = '{$values['title']}',
						`premiseA` = '{$values['premiseA']}',
						`premiseB` = '{$values['premiseB']}',
						`conclusion` = '{$values['conclusion']}',
						`behaviour` = '{$values['behaviour']}',
						`standard` = '{$values['standard']}',
						`conditions` = '{$values['conditions']}',
						`metaphor` = '{$values['metaphor']}',
						`hyperlink` = '{$values['hyperlink']}'
				WHERE `itemId` = '{$values['itemId']}'";
			break;

		case "updateitemattributes":
			$sql="UPDATE `". $config['prefix'] . "itemattributes`
				SET `type` = '{$values['type']}',
						`isSomeday`= '{$values['isSomeday']}',
						`categoryId` = '{$values['categoryId']}',
						`contextId` = '{$values['contextId']}',
						`timeframeId` = '{$values['timeframeId']}',
						`deadline` ={$values['deadline']},
						`repeat` = '{$values['repeat']}',
						`suppress`='{$values['suppress']}',
						`suppressUntil`='{$values['suppressUntil']}'
				WHERE `itemId` = '{$values['itemId']}'";
			break;

		case "updateitemtype":
			$sql="UPDATE `{$config['prefix']}itemattributes`
				SET `type` = '{$values['type']}',
					`isSomeday`= '{$values['isSomeday']}'
				WHERE `itemId` = '{$values['itemId']}'";
			break;

		case "updateitemtypequalities":
			$sql="UPDATE `{$config['prefix']}lookupqualities`
				SET `itemType` = '{$values['type']}'
				WHERE `itemId` = '{$values['itemId']}'
				AND `itemType` = '{$values['oldType']}'";
			break;

		case "updatelist":
			$sql="UPDATE `". $config['prefix'] . "list`
				SET `title` = '{$values['title']}',
						`categoryId` = '{$values['categoryId']}',
						`premiseA` = '{$values['premiseA']}',
						`premiseB` = '{$values['premiseB']}',
						`conclusion` = '{$values['conclusion']}',
						`behaviour` = '{$values['behaviour']}',
						`standard` = '{$values['standard']}',
						`conditions` = '{$values['conditions']}',
						`metaphor` = '{$values['metaphor']}',
						`hyperlink` = '{$values['hyperlink']}',
						`sortBy` = '{$values['sortBy']}',
						`menu` = '{$values['menu']}',
						`prioritise` = '{$values['prioritise']}'
				WHERE `listId` ='{$values['id']}'";
			break;

		case "updatelistitem":
			$sql="UPDATE `". $config['prefix'] . "listitems`
				SET `notes` = '{$values['notes']}', `hyperlink` = '{$values['hyperlink']}', `item` = '{$values['item']}',
						`listId` = '{$values['id']}',
						`dateCompleted`={$values['dateCompleted']}
				WHERE `listItemId` ='{$values['itemId']}'";
			break;

		case "updateparent":
			$sql="INSERT INTO `". $config['prefix'] . "lookup`
						(`parentId`,`itemId`)
				VALUES ('{$values['parentId']}','{$values['itemId']}')
				ON DUPLICATE KEY UPDATE `parentId`='{$values['parentId']}'";
			break;

		case "updatenextaction":
			$sql="INSERT INTO `". $config['prefix'] . "nextactions`
						(`parentId`,`nextaction`)
				VALUES ('{$values['parentId']}','{$values['itemId']}')
				ON DUPLICATE KEY UPDATE `nextaction`='{$values['itemId']}'";
			break;

		case "updatenote":
			$sql="UPDATE `". $config['prefix'] . "tickler`
				SET `date` = '{$values['date']}',
					`note` = '{$values['note']}',
					`title` = '{$values['title']}',
					`repeat` = '{$values['repeat']}',
					`suppressUntil` = '{$values['suppressUntil']}'
				WHERE `ticklerId` = '{$values['noteId']}'";
			break;

		case "updatespacecontext":
			$sql="UPDATE `". $config['prefix'] . "context`
				SET `name` ='{$values['name']}',
						`description`='{$values['description']}'
				WHERE `contextId` ='{$values['id']}'";
			break;

		case "updatetimecontext":
			$sql="UPDATE `". $config['prefix'] . "timeitems`
				SET `timeframe` ='{$values['name']}',
						`description`='{$values['description']}',
						`type`='{$values['type']}'
				WHERE `timeframeId` ='{$values['id']}'";
			break;

        default: // default to assuming that the label IS the query
            $sql=$querylabel;
            break;
    }
	return $sql;
}
/*
  ===============================================================
*/
function sqlparts($part,$config,$values) {

  if (is_array($values))
    foreach ($values as $key=>$value)
        $values[$key] = safeIntoDB($config, $value, $key);

  switch ($part) {
	case "activeitems":
		$sqlpart = " ((CURDATE()>=DATE_ADD(ia.`deadline`, INTERVAL -(ia.`suppressUntil`) DAY)) OR ia.`suppress`!='y') ";
		break;
	case "activelistitems":
		$sqlpart = " li.`dateCompleted` IS NULL ";
		break;
	case "categoryfilter":
		$sqlpart = " ia.`categoryId` = '{$values['categoryId']}' ";
		break;
	case "categoryfilter-parent":
		$sqlpart = " y.`pcategoryId` = '{$values['categoryId']}' ";
		break;
	case "checkchildren":
		$sqlpart = " LEFT JOIN (
                                        SELECT parentId as itemId,COUNT(DISTINCT nextaction) AS numNA
                                            FROM {$config['prefix']}nextactions GROUP BY itemId
                                        ) AS na ON(na.itemId=x.itemId)

                                      LEFT JOIN (
                                        SELECT cl.parentId AS itemId,count(DISTINCT cl.itemId) as numChildren
                                            FROM {$config['prefix']}lookup         AS cl
                                            JOIN {$config['prefix']}itemstatus     AS chis ON (cl.itemId=chis.itemId)
                                            JOIN {$config['prefix']}itemattributes AS chia ON (cl.itemId=chia.itemId)
                                            WHERE chis.dateCompleted IS NULL AND chia.type IN ('a','p','g','m','v','o','i','w')
                                            GROUP BY cl.parentId
                                        ) AS act ON (act.itemId=x.itemId) ";
		break;
	case "checklistcategoryfilter":
		$sqlpart = " cl.`categoryId`='{$values['categoryId']}' ";
		break;
	case "completeditems":
		$sqlpart = " its.`dateCompleted` IS NOT NULL ";
		break;
	case "completedlistitems":
		$sqlpart = " li.`dateCompleted` IS NOT NULL ";
		break;
	case "contextfilter":
		$sqlpart = " ia.`contextId` = '{$values['contextId']}' ";
		break;
	case "countchildren":
		$sqlpart = " ,na.numNA, act.numChildren";
		break;
	case "due":
		$sqlpart = " ((CURDATE() + INTERVAL 3 DAY)>=ia.`deadline` AND ia.`deadline` IS NOT NULL) ";
		break;
	case "getNA":
		$sqlpart = " , COUNT(DISTINCT na.nextaction) as NA ";
		break;
	case "hasparent":
		$sqlpart = " y.`parentId` = '{$values['parentId']}' ";
		break;
	case "isNA":
		$sqlpart = " LEFT JOIN ( SELECT nextaction FROM {$config['prefix']}nextactions
                               ) AS na ON(na.nextaction=x.itemId) ";
		break;
	case "isNAonly":
        $sqlpart = " INNER JOIN {$config['prefix']}nextactions AS na ON(na.nextaction=x.itemId) ";
		break;
	case "issomeday":
		$sqlpart = " ia.`isSomeday` = '{$values['isSomeday']}' ";
		break;
	case "limit":
		$sqlpart = " LIMIT {$values['maxItemsToSelect']} ";
		break;
	case "listcategoryfilter":
		$sqlpart = " l.`categoryId`='{$values['categoryId']}' ";
		break;
    case "liveparents":
        $sqlpart = "((CURDATE()>=DATE_ADD(y.`pdeadline`, INTERVAL -(y.`psuppressUntil`) DAY)) OR y.`psuppress`!='y' OR y.`psuppress` IS NULL)"
                    ." AND (y.`pdatecompleted` IS NULL) "
                    ." AND (y.`pisSomeday`='n' OR y.`pisSomeday` IS NULL)";
		break;
	case "matchall":
		$sqlpart = " (i.`title` LIKE '%{$values['needle']}%'
                                      OR i.`description` LIKE '%{$values['needle']}%'
                                      OR i.`premiseA` LIKE '%{$values['needle']}%'
                                      OR i.`premiseB` LIKE '%{$values['needle']}%'
                                      OR i.`conclusion` LIKE '%{$values['needle']}%'
                                      OR i.`behaviour` LIKE '%{$values['needle']}%'
                                      OR i.`standard` LIKE '%{$values['needle']}%'
                                      OR i.`conditions` LIKE '%{$values['needle']}%' )";
		break;
		/*

        $cols = array('i.`title`', 'i.`description`', 'i.`premiseA`', 'i.`premiseB`', 'i.`conclusion`', 'i.`behaviour`', 'i.`standard`', 'i.`conditions`');
        // Basic processing and controls
        //$searchParams = strip_tags($searchParams);

        // Start query
        $query = " (";

        // Explode search criteria taking into account the double quotes
        $searchParams = str_getcsv($values['needle'], ' ');

        // Query writing
        foreach($searchParams as $param) {
            // or operator
            if (strtolower($param) == 'or') {
                // Remove last ' AND ' sequence
                $query = substr($query, 0, strlen($query)-5);
                $query .= ' OR ';
                continue;
            }
            if(strpos($param, ' ') or (strlen($param)<4)) {
                // Elements with space were between double quotes and must be processed with LIKE.
                // Also for the elements with less than 4 characters. (red and "Florida 90210")
                $query .= "(";
                // Add each column
                foreach($cols as $col) {
                    if($col) {
                        $query .= $col." LIKE '%".$param."%' OR ";
                    }
                }
                // Remove last ' OR ' sequence
                $query = substr($query, 0, strlen($query)-4);
                // Following criteria will added with an AND
                $query .= ") AND ";
            } else {
                // Other criteria processed with MATCH AGAINST (mustang)
                $query .= "(MATCH (";
                foreach($cols as $col) {
                    if($col) {
                        $query .= $col.",";
                    }
                }
                // Remove the last ,
                $query = substr($query, 0, strlen($query)-1);
                // Following criteria will added with an AND
                $query .= ") AGAINST ('".$param."' IN NATURAL LANGUAGE MODE)) AND ";
            }
        }
        // Remove last ' AND ' sequence
        $query = substr($query, 0, strlen($query)-5);
        $sqlpart = $query . ') ';
        //echo $sqlpart;die;
        break;
*/
	case "notcategoryfilter":
		$sqlpart = " ia.`categoryId` != '{$values['categoryId']}' ";
		break;
	case "notcategoryfilter-parent":
		$sqlpart = " y.`pcategoryId` != '{$values['categoryId']}' ";
		break;
	case "notcontextfilter":
		$sqlpart = " ia.`contextId` != '{$values['contextId']}' ";
		break;
	case "notefilter":
		$sqlpart = " (`date` IS NULL) OR (CURDATE()>= `date`) ";
		break;
	case "nottimeframefilter":
		$sqlpart = " ia.`timeframeId` !='{$values['timeframeId']}' ";
		break;
	case "pendingitems":
		$sqlpart = " its.`dateCompleted` IS NULL ";
		break;
	case "repeating":
		$sqlpart = " ia.`repeat` >0 ";
		break;
	case "singleitem":
		$sqlpart = " i.`itemId`='{$values['itemId']}' ";
		break;
	case "suppresseditems":
		$sqlpart = " ia.`suppress`='y' AND (CURDATE()<=DATE_ADD(ia.`deadline`, INTERVAL -(ia.`suppressUntil`) DAY)) ";
		break;
	case "timeframefilter":
		$sqlpart = " ia.`timeframeId` ='{$values['timeframeId']}' ";
		break;
	case "timetype":
		$sqlpart = " ti.`type` = '{$values['type']}' ";
		break;
	case "typefilter":
		$sqlpart = " ia.`type` = '{$values['type']}' ";
		break;
/*
	case "ptypefilter":
		$sqlpart = " ia.`type` = '{$values['ptype']}' ";
		break;
*/
    default:
        if ($config['debug'] & _GTD_DEBUG) echo "<p class='error'>Failed to find sql component '$part'</p>'";
        $sqlpart=$part;
        break;
  }

  if ($config['debug'] & _GTD_DEBUG)
      echo "<pre>Sqlparts '$part': Result $sqlpart<br />Sanitised values in sqlparts: ",print_r($values,true),'</pre>';

  return $sqlpart;
}

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
