<thead class="mx">
    <?php
    // matrix header
    if ($angles) {
        
        $col = 0;
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) $col++;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) { 
                foreach ((array) $valN['attributes'] as $valO) $col--;
            }
        }
        $transStr = '';
        $colHide = 1;
        while ($col > 0) {
            $transStr .= "<th class='trans ho' onClick='toggleCol([$colHide])'></th>"; 
            $col--;
            $colHide++;
        }
        
        $anglesStr = '';
        $anglesStr .= "<tr>\n" . $transStr;
        foreach ((array) $angles as $val) {
            $anglesStr .= "<th class='{$val['qType']} {$val['qId']} mx' title='{$val['description']}'>{$val['title']}</th>\n";
            $trans = -1;
            foreach ((array) $val['qualities'] as $valN) { foreach ((array) $valN['attributes'] as $valO) $trans++; }
            while ($trans > 0) { $anglesStr .= "<th class='{$val['qType']} {$val['qId']} trans'></th>"; $trans--; }
        }
        $anglesStr .= "\n</tr>\n";
        
        $qualsStr = "<tr>\n" . $transStr;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) {
                $qualsStr .= "<th class='{$valN['qType']} mx' title='{$valN['description']}'>{$valN['title']}</th>\n";
                $trans = -1;
                foreach ((array) $valN['attributes'] as $valO) $trans++; 
                while ($trans > 0) { $qualsStr .= "<th class='trans'></th>"; $trans--; }
            }
        }
        $qualsStr .= "\n</tr>\n";

        $attrStr = "\n<tr class='summary'>\n" . $transStr;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) { 
                foreach ((array) $valN['attributes'] as $valO) {
                    $attrStr .= "<th class='attr {$valO['qId']} a mx ho {$valO['style']}' onClick='toggleCol([$colHide])'>&nbsp;</th>\n";
                    $colHide++;
                }
            }
        }
        $attrStr .= "</tr>\n";
        
        $attrStr .= "\n<tr class='summary'>\n" . $transStr;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) { 
                foreach ((array) $valN['attributes'] as $valO) $attrStr .= "<th class='attr {$valO['qId']} b mx {$valO['style']}'>&nbsp;</th>\n";
            }
        }
        $attrStr .= "</tr>\n";
        
        $attrSrch .= "\n<tr class='summary'>\n";
        $filterCol = 0;
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) {
            if (strpos($key,'attr') > -1) break;
            $attrSrch .= "<th class='$key mx";
            switch ($key) {
                case 'toggle': 
                    $attrSrch .= " ho'>";
                    break;
                case 'item': 
                    $attrSrch .= "'><input id='txt$filterCol' name='$filterCol' class='fTxt' title='not, or, and, null, nnull'>";
                    break;
                case 'items': 
                    $attrSrch .= "'>";
                    break;
                case 'someday': 
                    $attrSrch .= "'>";
                    //$attrSrch .= "<input id='chk$filterCol' name='$filterCol' class='fChk' type='checkbox'>";
                    break;
                case 'complete': 
                    $attrSrch .= "'>";
                    //$attrSrch .= "<input id='chk$filterCol' name='$filterCol' class='fChk' type='checkbox'>";
                    break;
            }
            $attrSrch .= "\n</th>\n";
            $filterCol++;
        }
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) { 
                foreach ((array) $valN['attributes'] as $valO) {
                    $attrSrch .= "<th class='attr mx wh'>";
                    switch ($valO['filter']) {
                        case 'NULL': break;
                        case 'range':
                            $attrSrch .= "<input id='min$filterCol' name='$filterCol' class='fMin' title='int, null, nnull'>
                                        <input id='max$filterCol' name='$filterCol' class='fMax' title='int'>";
                            break;
                        case 'text': 
                            $attrSrch .= "<input id='txt$filterCol' name='$filterCol' class='fTxt' title='not, or, and, null, nnull'>";
                            break;
                        case 'empty': 
                            //$attrSrch .= "<input id='emp$filterCol' name='$filterCol' class='fEmp' type='checkbox'>";
                            break;
                        case 'check': 
                            //$attrSrch .= "<input id='chk$filterCol' name='$filterCol' class='fChk' type='checkbox'>";
                            break;
                    }
                    $attrSrch .= "\n</th>\n";
                    $filterCol++;
                }
            }
        }
        $attrSrch .= "</tr>\n";
        
        $attrTit = "\n<tr class='summary'>\n";
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) {
            if (strpos($key,'attr') > -1) break;
            $attrTit .= "<th class='$key mx'>$val</th>";
            // $attrTit .= "<th class='$key mx xm'>$val<div class='xm'>$val</div></th>"; // fixed header 
        }
        $di = 1;
        foreach ((array) $angles as $val) {
            foreach ((array) $val['qualities'] as $valN) { 
                foreach ((array) $valN['attributes'] as $valO) {
                    $title = $valO['title'];
                    if ($valO['format'] == 'unqtimeline') { $title += date("y") - 1; $title = "'" . $title; }
                    if ($data) {
                        if ($title == 'p') { 
                            $title .= $di; // differentiate p titles
                            $di++;
                        } else {
                            $title = preg_replace('/[^A-Za-z0-9 ]/', '', $title); // strip nonalpha
                            $title = preg_replace('!\s+!', ' ', $title); // strip double space
                            $title = str_replace(' ', '.', $title); // replace space
                        }
                    }
                    $attrTit .= "<th class='attr {$valO['qId']} mx' title='{$valO['description']}'>{$title}</th>\n";
                    if ($valO['title'] == '') { echo '<div style="position: relative; z-index: 10000;">disp mismatch attr ' . $valN['qId'] . '</div>'; }
                }
                // foreach ((array) $valN['attributes'] as $valO) $attrTit .= "<th class='attr {$valO['qId']} mx xm' title='{$valO['description']}'>{$valO['title']}<div class='xm'>{$valO['title']}</div></th>\n"; // fixed header 
            }
        }
        $attrTit .= "</tr>\n";
        
        if (!$data) {
            echo $anglesStr . $qualsStr . $attrStr . $attrSrch . $attrTit;
        } else {
            echo $attrTit;
        }
        
    }
    ?>
<!--    <tr> -->
    <?php /*
        // header
        foreach ((array) $dispArray as $key=>$val) if ($show[$key]) {
            echo "<th class='$key";
            if ($angles) echo " mx";
            echo "'>$val</th>"; 
        } */
        ?>
<!--    </tr> -->
</thead>
<tbody>
<?php
$et2 = 0;
$et3 = 0;
$et4 = 0;
$arrowKeys = "id='start' ";
foreach ((array) $maintable as $row) {
    echo '<tr'
        ,(!empty($row['row.class']))?" class='{$row['row.class']}'":''
        ,">\n";
        if (strpos($row['item'], '~') > -1) { $tilda = true; } else { $tilda = false; }
    foreach ((array) $dispArray as $key=>$val) if ($show[$key]) {
        $et2a = microtime(true);   
        if (strpos($key,'attr') > -1) { $isAttr = str_replace("attr ", "", $key); } else { $isAttr = false; }
        echo "<td " . $arrowKeys . "class='$key"
            ,(isset($row["$key.class"]))?" ".$row["$key.class"]:''
            ,(isset($row["mx"]))?" mx":''
            ,(isset($row["indent"]) && $key == 'item')?" indent" . $row["indent"]:''
            ,(isset($row["type"]))?" " . $row["type"]:''
            ,($row[$key] == '0')?" nl":'';
        $arrowKeys = '';
        $mxlink = false;
        if ($isAttr) {
            foreach ((array) $angles as $val) {
                foreach ((array) $val['qualities'] as $valN) { 
                    foreach ((array) $valN['attributes'] as $valO) {
                        if ($isAttr == $valO['qId']) {
                            if (strpos($valO['typeReq'],'~') > -1 && $tilda) {
                                echo " req";
                            } elseif (strpos($valO['typeReq'],$row["type"]) > -1 && !$tilda) {
                                echo " req";
                            }
                            echo " " . $valO['style'];
                            if ($valO['style'] == 'hlnk') $mxlink = true; 
                            break 3;
                        }
                    }
                }
            }
        }
        $et2 += microtime(true) - $et2a;
        $et3a = microtime(true);    
        echo "'"
            ,(isset($row["$key.title"]))?(' title="'.$row["$key.title"].'"'):'';
        
        // default db query value
        $existKeyVal = $row['itemId'];
        // check if this item is called from existing table
        $existItem = false;
        $existXRef = false;
        // if this attribute calls from existing table, and not for this item type, will the value be non editable? default is no
        $existOnly = false;
        $checkbox = false;
        if ($isAttr) {
            $qId = $isAttr;

            foreach ((array) $attrStat as $attr) {
                if ($attr['qId'] == $qId && !is_null($attr['existTable'])) {
                    foreach ((array) $attr['existTable'] as $crit) {
                        if (isset($crit['limit']) && $crit['limit'] == 1) $existOnly = true;
                        if (strpos($crit['type'],$row['type']) > -1) {
                            $existItem = true;
                            $existTable = $crit['existTable'];
                            $existKey = $crit['existKey'];
                            $existField = $crit['existField'];
                            $existOnly = false;
                        }
                        if (strpos($crit['type'],'z') > -1) {
                            $existXTable = $crit['existTable'];
                            $existXKey = $crit['existKey'];
                            $existXField = $crit['existField'];
                            if (isset($crit['keyTrim'])) { $existXTrim = $crit['keyTrim']; } else { $existXTrim = false; }
                            $existXRef = true;
                        }
                    }
                    // special case x-linked
                    if ($existXRef) {
                        $query = "SELECT " . $existField . " FROM " . $existTable . " WHERE " . $existKey . " = '" . $existKeyVal . "'";
                        $res = doQuery($query);
                        foreach ((array) $res as $r) $existKeyVal = $r[$existField]; // call value before rewriting $existField
                        $existTable = $existXTable;
                        $existKey = $existXKey;
                        $existField = $existXField;
                        if ($existXTrim) $existKeyVal = substr($existKeyVal, 0, $existXTrim); // if only limited variable
                        $existXRef = false; // do not call again
                    }
                }
                if ($attr['qId'] == $qId && $attr['style'] == 'cb' && $row['type'] != 'v') {
                    $checkbox = true;
                    $checkboxqaId = $row[$key . " qaId"];
                    $checkboxval = $row[$key];
                }
            }
        }
        if (!$isAttr) {
            foreach ((array) $attrMeta as $attrMet) {
                if ($attrMet['format'] == $key && $attrMet['style'] == 'cb' && strpos($attrMet['typeReq'],$row['type']) > -1) {
                    $checkbox = true;
                    $qId = $attrMet['qId'];
                    $checkboxqaId = $row["attr " . $qId . " qaId"];
                    $checkboxval = $row["attr " . $qId];
                    break;
                }
            }
        }
        if ($key == 'item') {
            switch($row["type"]) {
                case 'p' :
                case 'o' :
                case 'g' :
                case 'v' : 
                    $existTable = 'items';
                    $existKey = 'itemId';
                    $existField = 'title';
                    break;
                case 'c' :
                    $existTable = 'checklist';
                    $existKey = 'checklistId';
                    $existField = 'title';
                    break;
                case 'l' :
                    $existTable = 'list';
                    $existKey = 'listId';
                    $existField = 'title';
                    break;
            }
            $existItem = true;
        }
        $et3 += microtime(true) - $et3a;
        $et4a = microtime(true);
        
        $editable = ' contenteditable="true"';
        foreach ((array) $nonEdit as $non) {
            if ($non['qId'] == $qId && (strpos($non['typeNoEd'],$row["type"]) > -1)) {
                $editable = '';
                break;
            }
        }
        
        if ($key == 'toggle' && $row['type'] == 'v') {
                echo ' onClick="toggleRow([\'v.' . $row["itemId"] . '\'])"';
        } elseif ($checkbox) {
            $tdstr = '';
            // only for lookupqualities table 'lq'
            if (is_numeric($row["itemId"])) { // not blank
                $tdstr = checkerB ('lq','qaId',$checkboxqaId,'val',$checkboxval,'vId',$row["visId"],'iId',$row["itemId"],'qId',$qId,'iT',$row["type"]);
            }
        } elseif (!$editable || $key == 'item' || $data) { // by default item titles are not editable. data cells not editable as causes export problems with line breaks.
        } elseif ($existItem) {
            $saveId = $row[$existKey];
            if (in_array($row["type"], array('c','l'))) $saveId = $row['itemId']; 
            if (is_numeric($saveId)) echo $editable . ' onBlur="sT(this,\'' . $existTable . '\',\'' . $existField . '\',\'' . $existKey . '\',\'' . $saveId . '\')" onFocus="sE(this)"';
        } elseif (
                isset($row[$key]) &&
                strpos($key,'attr') > -1 && 
                /* && $row['type'] !== 'v' */
                !$existOnly
                ) {
                echo $editable . ' onBlur="sT(this,\'lq\',\'val\',\'qaId\',\'' . $row[$key . " qaId"] . '\',\'vId\',\'' . $row["visId"] . '\',\'iId\',\'' . $row["itemId"] . '\',\'qId\',\'' . $qId . '\',\'iT\',\'' . $row["type"] . '\')" onFocus="sE(this)"';
        }
        $et4 += microtime(true) - $et4a;
        $et5a = microtime(true);    

        echo '>';
        if (!$checkbox) {
            if ($key == 'item' && $data) $row[$key] = preg_replace('/[^A-Za-z0-9 ]/', '', $row[$key]);
            if (!$existItem || $key == 'item') {
                $tdstr = ajaxLineBreak($row[$key]);
            } else {
                $query = "SELECT " . $existField . " FROM " . $existTable . " WHERE " . $existKey . " = '" . $existKeyVal . "'";
                $res = doQuery($query);
                $tdstr = ajaxLineBreak($res[0][$existField]);
            }
            if ($mxlink && strlen($tdstr) > 0) $tdstr = faLink($tdstr, true);
        }
        if ($data) {
            if (strpos($tdstr,'checkbox') !== false) { 
                if (strpos($tdstr,'checked') === false) { $tdstr = 0; } else { $tdstr = 1; }
            }
            $tdstr = trim($tdstr);
        }
        echo $tdstr;
        echo "</td>\n";
        $et5 += microtime(true) - $et5a;
    }
    echo "</tr>\n";
} 
?>
</tbody>
