<thead>
    <tr>
    <?php foreach ($dispArray as $key=>$val) if ($show[$key]) echo "<th class='col-$key'>$val</th>"; ?>
    </tr>
</thead>
<?php if (!empty($tfoot)) echo $tfoot; ?>
<tbody>
<?
// echo '<pre>';var_dump($maintable);die;
foreach ($maintable as $row) {
    if (!isset($row['ptype'])) $row['ptype'] = '';
    echo '<tr'
        ,(!empty($row['row.class']))?" class='{$row['row.class']}' ":''
        ,">\n";
    foreach ($dispArray as $key=>$val) if ($show[$key]) {
        echo "<td class='col-$key"
            ,(isset($row["$key.class"]))?" ".$row["$key.class"]:''
            ,"'"
            ,(isset($row["$key.title"]))?(' title="'.$row["$key.title"].'"'):''
            ,'>';
        switch ($key) {
            case 'title':

				if(!is_array($linkedItems)) $linkedItems = array();
				$isProject = false;
				$isActiveProject = false;
				$hasTickler = false;
				$childString = '';
				$tempValues = $values;
				$values['itemId'] = $row['itemId'];
				$values['extravarsfilterquery'] = sqlparts("countchildren",$config,$values);
				$result = query("selectitem",$config,$values,$sort);
				if($result[0]['type'] == 'p') $isProject = true;
				if($result[0]['type'] == 'p' && $result[0]['isSomeday'] == 'n') {
					$arr = array('a','w','p');
					foreach ($arr as $val) {
						$values['type']=$val;
						$values['isSomeday']='n';
						$values['filterquery'] = " AND ".sqlparts("typefilter",$config,$values);
						$values['filterquery'] .= " AND ".sqlparts("issomeday",$config,$values);
						$q=($comp==='y')?'completeditems':'pendingitems';  //suppressed items will be shown on report page
						$values['filterquery'] .= " AND ".sqlparts($q,$config,$values);
						$values['parentId']=$values['itemId'];
						$result = query("getchildren",$config,$values,$sort);
						if ($result) {
							foreach ($result as $child) {
								if($child['NA'] || $isProject) {
									if ($child['suppress'] == 'y' && strtotime($child['deadline'] . '- ' . $child['suppressUntil'] . ' day') > strtotime("now")) {
										$hasTickler = true;
										continue;
									}
								}
								if($child['NA']) {
									if(!in_array($child['itemId'], $linkedItems, true)) {
										$childString .= '<br>' . strtoupper($child['type']) . ':&nbsp;<a href="item.php?itemId=' . $child['NA'] . '">'. str_replace(' ', '&nbsp;', substr($child['title'], 0, 30))  . '</a>';
										array_push($linkedItems, $child['itemId']);

									} else {
										if ($val == 'a') $childString .= '<span style="opacity: 0.5"><br>SEE ACTIONS</span>';
										if ($val == 'w') $childString .= '<span style="opacity: 0.5"><br>SEE WAITING ONS</span>';
									}
									$isActiveProject = true;
								}
								/* if($val == 'p') {
									$values['parentId']=$child['itemId'];
									$values['type']='w';
									$result = query("getchildren",$config,$values,$sort);
									$childString .= '<br>' . $child['title'];
									$values['parentId']=$values['itemId'];
								} */
							}
						}
					}
				}

				$values = $tempValues;

                $cleaned=$row[$key];
                echo /* "<a href='itemReport.php?itemId={$row['itemId']}'>"
                    ,"<img src='themes/{$config['theme']}/report.gif' class='noprint' alt='Report' title='View Report' /></a>"
                    ,"<a href='item.php?itemId={$row['itemId']}'>"
                    ,"<img src='themes/{$config['theme']}/edit.gif' class='noprint' alt='Edit ' title='Edit' /></a>"
                    ,*/"<a ",(empty($row['NA']) && !$isActiveProject)?'':"class='nextactionlink'"
                    ," title='"
                    ,(empty($row['doreport']))?'Edit':'View Report'
                    ,"' href='";
                if (empty($row['doreport']) && $isProject) echo "itemReport.php?itemId={$row['itemId']}'";
                else if (empty($row['doreport']) && !$isProject) echo "item.php?itemId={$row['itemId']}'";
                else if ($row['doreport'] == 'item') echo "item.php?itemId={$row['itemId']}'";
                else if ($row['doreport'] == 'parent') echo "itemReport.php?itemId={$row['itemId']}'";
                else if ($row['doreport'] == 'cl') echo "reportLists.php?listId={$row['itemId']}&type=c'";
                else if ($row['doreport'] == 'cli') echo "editListItems.php?itemId={$row['itemId']}&type=c'";
                else if ($row['doreport'] == 'l') echo "reportLists.php?listId={$row['itemId']}&type=l'";
                else if ($row['doreport'] == 'li') echo "editListItems.php?itemId={$row['itemId']}&type=l'";
                else if ($row['doreport'] == 'ai') echo "fi.php?chat_id={$row['itemId']}'";
                echo ">$cleaned</a>";
				echo $childString . ($hasTickler ? '<span style="opacity: 0.5"><br>TICKLED ITEM(S)</span>' : '');
				if (isset($item['title'])) {
					$values['itemId'] = $row['itemId'];
					$presult = query("lookupparent",$config,$values,$sort);
					foreach ($presult as $prow) {
//						if (preg_match("/~/i", $prow['ptitle']) && $item['title'] != $prow['ptitle']) echo '<br>'.$prow['ptitle'];
						if ($item['title'] != $prow['ptitle'] && $prow['ptype'] == 'p') {
							$svalues['itemId'] = $row['itemId'];
							$sresult = query("selectitem",$config,$svalues,$sort);
							$brk = false;
							foreach ($sresult as $temp) { if ($temp['type'] == 'r') $brk = true; }
							if ($brk) break;

							if (!$isProject) {
								echo '<br><span style="opacity: .5">Pr:&nbsp;<a href="itemReport.php?itemId=' . $prow['parentId'] . '">' . str_replace(' ', '&nbsp;', substr($prow['ptitle'] . ' ', 0, 30)) . '</a></span>';
								array_push($linkedItems, $row['itemId']);
							} else {
								if(!in_array($prow['parentId'], $linkedItems, true)) {
									$skip = false;
									foreach ($maintable as $blunt) {
										if (in_array($prow['parentId'], $blunt, true)) {
											$skip = true;
											break;
										}
									}

									if (!$skip) {

										echo '<br><br><span style="opacity: .5">Pr:&nbsp;</span><span style="opacity: 1"><a href="itemReport.php?itemId=' . $prow['parentId'] . '">' . str_replace(' ', '&nbsp;', substr($prow['ptitle'] . ' ', 0, 30)) . '</a></span>';
										array_push($linkedItems, $prow['parentId']);
/*
										$gprow = array(
												"itemId"=>$prow['parentId'],
												"title"=>$prow['ptitle'],
												"fulldesc"=>"",
												"fulloutcome"=>"",
												"categoryId"=>"1",
												"category"=>"Professional",
												"contextId"=>"10",
												"context"=>"AGENDAS",
												"context.title"=>"Go to AGENDAS context report",
												"timeframeId"=>"0",
												"timeframe"=>"",
												"timeframe.title"=>"Go to  time-context report",
												"deadline"=>
												NULL,
												"checkbox.title"=>
												"Mark ~ ALLISON BRILL complete",
												"checkboxname"=>
												"isMarked[]",
												"checkboxvalue"=>
												"4667"
												);
										array_push($maintable, $gprow);
										*/

				$isActiveProject = false;
				$hasTickler = false;
				$childString = '';
				$tempValues = $values;
				$values['itemId'] = $prow['parentId'];
				$values['extravarsfilterquery'] = sqlparts("countchildren",$config,$values);
				$result = query("selectitem",$config,$values,$sort);
				if($result[0]['type'] == 'p' && $result[0]['isSomeday'] == 'n') {
					$arr = array('a','w','p');
					foreach ($arr as $val) {
						$values['type']=$val;
						$values['isSomeday']='n';
						$values['filterquery'] = " AND ".sqlparts("typefilter",$config,$values);
						$values['filterquery'] .= " AND ".sqlparts("issomeday",$config,$values);
						$q=($comp==='y')?'completeditems':'pendingitems';  //suppressed items will be shown on report page
						$values['filterquery'] .= " AND ".sqlparts($q,$config,$values);
						$values['parentId']=$values['itemId'];
						$result = query("getchildren",$config,$values,$sort);
						if ($result) {
							foreach ($result as $child) {
								if($child['NA']) {
									if ($child['suppress'] == 'y' && strtotime($child['deadline'] . '- ' . $child['suppressUntil'] . ' day') > strtotime("now")) {
										$hasTickler = true;
										continue;
									}
									if(!in_array($child['itemId'], $linkedItems, true)) {
										$childString .= '<br>' . strtoupper($child['type']) . ':&nbsp;<a href="item.php?itemId=' . $child['NA'] . '">'. str_replace(' ', '&nbsp;', substr($child['title'], 0, 30))  . '</a>';
									} else {
										if ($val == 'a') $childString .= '<span style="opacity: 0.5"><br>SEE ACTIONS</span>';
										if ($val == 'w') $childString .= '<span style="opacity: 0.5"><br>SEE WAITING ONS</span>';
									}
									$isActiveProject = true;
								}
							}
						}
					}
				}
				echo '<span style="opacity: .5">' . $childString . '</span>';
				$values = $tempValues;

									}
								}
							}
						}
					}
				}
                break;
            case 'assignType':
                // echo "<a href='assignType.php?itemId={$row['itemId']}";
                echo "<a href='item.php?itemId={$row['itemId']}&convert=true";
/*                if (!empty($afterTypeChange))
                    echo "&amp;referrer=$afterTypeChange";
                elseif (!empty($referrer))
                    echo "&amp;referrer=$referrer";
*/                echo "&amp;referrer=$referrer'>Set type</a>";
                break;
            case 'checkbox':
                echo "<input name='{$row['checkboxname']}' value='{$row['checkboxvalue']}' type='checkbox' class='unchecked'"
                      . ajaxUpd('itemComplete',$row['itemId'])
                      . "/>";
/*				echo ' <a title="Set to action" href="processItems.php?action=changeType&type=a&safe=1&itemId=' . $row['itemId'] . '">A</a>';
				echo ' <a title="Set to waiting on" href="processItems.php?action=changeType&type=w&safe=1&itemId=' . $row['itemId'] . '">W</a>';
				echo ' <a title="Set to reference" href="processItems.php?action=changeType&type=r&safe=1&itemId=' . $row['itemId'] . '">R</a>';
*/
                break;
            case 'NA':
                echo "<input name='isNAs[]' value='{$row['itemId']}' type='checkbox' ";
                if ($row[$key]) echo " checked='checked' class='checked' ";
                else echo " class='unchecked'";
                echo ajaxUpd('itemNA',$row['itemId'])
                    . "/>";
                break;
            case 'flags':
                if ($row[$key]==='')
                    echo '&nbsp;';
                else
                    echo "<a class='noNextAction' title='"
                        ,($row[$key]==='noNA')?
                            "No next action - click to assign one' href='itemReport.php?itemId="
                            :("No children - click to create one' href='item.php?type=".$row['childtype'].'&amp;parentId=')
                        ,$row['itemId'],"'>!"
                        ,($row[$key]==='noChild')?'!':'&nbsp;'
                        ,"</a>";
                break;
            case 'category':
                if ($row[$key.'Id'])
//                    echo "<a href='editCat.php?field=category&amp;id=",$row[$key.'Id'],"' title='Edit the {$row[$key]} category'>{$row[$key]}</a>";
                    echo "{$row[$key]}";
                else
                    echo '&nbsp;';
                break;
            case 'parent':
                if (empty($row[$key.'Id']))
                    echo '&nbsp;';
                else {
                    $out='';
                    $brk='';
                    $pids=explode(',',$row['parentId']);
                    $ptypes=explode($config['separator'],$row['ptype']);
                    $pnames=explode($config['separator'],$row['ptitle']);
                    foreach ($pids as $pkey=>$pid) {
						echo "$brk";
						if (isset($ptypes[$pkey]) && strlen($ptypes[$pkey]) > 0) echo "<span style='opacity: 0.5'>" , strtoupper(makeclean($ptypes[$pkey])),":</span>";
                        echo "<a href='itemReport.php?itemId=$pid' title='View report'>"
                            ,makeclean($pnames[$pkey])
                            ,"</a> ";
                        $brk="<br />\n<br />\n";
                    }
                }
                break;
            case 'context':
                if ($row[$key]=='')
                    echo '&nbsp;';
                else
//                    echo "<a href='reportContext.php#c",$row[$key.'Id'],"' title='Go to the ",$row[$key]," context report'>{$row[$key]}</a>";
                    echo "{$row[$key]}";
                break;
            case 'spatialcontext':
                if ($row[$key]=='')
                    echo '&nbsp;';
                else
//                    echo "<a href='editCat.php?field=context&amp;id=",$row[$key.'Id'],"' title='Go to the ",$row[$key]," context report'>{$row[$key]}</a>";
                    echo "{$row[$key]}";
                break;
            case 'timeframe':
                if ($row[$key.'Id'])
//                    echo "<a href='editCat.php?field=time-context&amp;id=",$row[$key.'Id'],"' title='Edit the {$row[$key]} time context'>{$row[$key]}</a>";
                    echo "{$row[$key]}";
                else
                    echo '&nbsp;';
                break;
            case 'type': // TOFIX - if type is blank, offer 'assign type' link
                if (empty($row[$key]))
                    echo "<a href='assignType.php?itemId={$row['itemId']}'>Set type</a>";
                elseif (isset($row['isSomeday']) && $row['isSomeday']==='y')
                    echo 'Someday';
                else
                    echo getTypes($row[$key]);
                break;
            case 'description': // flows through to case 'outcome' deliberately
            case 'hyperlink':
            case 'premiseA':
            case 'premiseB':
            case 'conclusion':
            case 'behaviour':
            case 'standard':
            case 'conditions':
            case 'metaphor':
                echo trimTaggedString($row[$key],$config['trimLength']);
                break;
            case 'fulldesc': // flows through to case 'fulloutcome' deliberately
            case 'fulloutcome':
                echo trimTaggedString($row[$key],$config["trimLengthInReport"]);
                break;
			case 'deadline':
				$values['itemId'] = $row['itemId'];
				$result = query("selectitem",$config,$values,$sort);
				echo $result[0]['deadline'];
				break;
            default:
                echo $row[$key];
                break;
        }
        echo "</td>\n";
    }
    echo "</tr>\n";
} ?>
</tbody>
