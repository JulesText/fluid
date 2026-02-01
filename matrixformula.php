<?php

/*
res hours: assume repeat each year
hours: ok
travel: ok

years: adds to year start
year start: calculate what timeline years are committed
brainless: alters tally

items: separate brainless, update to timeline[] on fly, in final calc delimit brainless, push to result[]

visions: final calc, delimit brainless, push to result[]

summary: final calc, separate brainless, push to result[]
*/


// would having a single array built from the loops, then all calculations done at the end, be easier to maintain code than the current cross-cutting structure?


// start query time test
$starttime = microtime(true);

// declare time zone
date_default_timezone_set('Africa/Lagos');

require_once('headerDB.inc.php');

if (isset($_POST["qLimit"])) { $qLimit = $_POST["qLimit"]; } else { $qLimit = 'a'; }
if (isset($_POST["vLimit"]) && $_POST["vLimit"] != '') { $vLimit = $_POST["vLimit"]; } else { $vLimit = false; }


// initialise array used to collate calculation results
$result = array();
/* example result
$result[] = array(
    'type' => 'a', // ie goal, summary
    'visId' => '0',
    'id' => '0', // item id
    'attrId' => '0',
    'tline' => 'a', // timeline pair a or b for brainless or not
    'form' => 'a', // summary pair a or b for pos or neg
    'value' => '0');
*/

// initialise temporary array to collate value sums
$valuessum = array();

// initialise temporary array to collate context sums
$contextssum = array();

// initialise temporary array to collate scores for optimising
$scores = array();

// initialise temporary array to collate years for value scores
$yrsval = array();

// initialise temporary array to collate timeline results
$timeline = array();

// initialise temporary array for null item timeline values
$tlineNull = array();

// initialise temporary array to collate probability results
$certainties = array();

// call all visions
$values = array();
$values['filterquery'] = ' WHERE ia.type = "v" ';
if ($vLimit) $values['filterquery'] .= ' AND ia.itemId IN (' . $vLimit . ') ';
$visions = query("getitems",$config,$values,$sort);

// call values parent id
$values = array();
$values['qQuery'] = "`format`";
$values['qValue'] = 'unqvalues';
$qualities = query("getqualities",$config,$values,$sort);
foreach ((array) $qualities as $qual) {
    if ($qual['format'] == 'unqvalues') $valParId = $qual['qId'];
}

// call context parent id
$values = array();
$values['qQuery'] = "`format`";
$values['qValue'] = 'unqcontext';
$qualities = query("getqualities",$config,$values,$sort);
foreach ((array) $qualities as $qual) {
    if ($qual['format'] == 'unqcontext') $cntxtParId = $qual['qId'];
}

// call all attributes, exclude angles and qualities
$values = array();
$values['qQuery'] = "`qType`";
$values['qValue'] = 'attribute';
$values['qSearch'] = "`disp`";
$values['qNeedle'] = $qLimit;
$attributes = query("getqualities",$config,$values,$sort);


// if using unqhoursyear = 2500 unqhoursyearbrainless = 750
// for instance to show timeline where over or under committed
// need to recall above query with qValue = 'variable'


// set configuration variables and arrays relating to time

// holds ids for all variables that count hours
$unqhoursIds = array ();

// holds ids for all variables of years on timeline
$unqtimelineIds = array ();

// holds ids for all variables with scores
$scoreqIds = array();

// holds ids for any variables not to directly calculate
$skipqIds = array();

// optimisation array to capture item scores
$scoreweights = array();

// default not to calculate optimisation
$optim = false;

foreach ((array) $attributes as $attr) {

    // variable to flag when travel hours are being counted, and treat as brainless
    // attr: Travel / Year (int)
    if ($attr['format'] == 'unqhourstravel') { $travelId = $attr['qId']; $unqhoursIds[] = $travelId; }

    // attr: Effort / Year (int)
    if ($attr['format'] == 'unqhours') { $hoursId = $attr['qId']; $unqhoursIds[] = $attr['qId']; }

    // attr: Hours research (int)
    if ($attr['format'] == 'unqhoursresearch') { $reseId = $attr['qId']; $unqhoursIds[] = $attr['qId']; }

    // attr: Years (int)
    if ($attr['format'] == 'unqyears') $yearqId = $attr['qId'];

    // attr: Years probability (int)
    if ($attr['format'] == 'unqyearsprob') $yearsprobId = $attr['qId'];

    // attr: Brainless hours (boolean)
    if ($attr['format'] == 'unqbrainless') $brainlessId = $attr['qId'];

    // attr: [one of the n years duration of timeline] (int)
    if ($attr['format'] == 'unqtimeline') { $unqtimelineIds[$attr['title']] = $attr['qId']; $skipqIds[] = $attr['qId']; }

    // attr: Year Start (date)
    if ($attr['format'] == 'unqyearstart') $yearstqId = $attr['qId'];

    // attr: Year End (date)
    if ($attr['format'] == 'unqyearend') $yearenqId = $attr['qId'];

    // variable to flag optimise preferences attribute id
    if ($attr['format'] == 'unqoptimisepref') { $unqoptimiseprefId = $attr['qId']; $skipqIds[] = $attr['qId']; $optim = true; }

    // variable to flag optimise balance attribute id
    if ($attr['format'] == 'unqoptimisebala') { $unqoptimisebalaId = $attr['qId']; $skipqIds[] = $attr['qId']; $optim = true; }

    // variable to flag values sum attribute id
    if ($attr['format'] == 'unqvaluessum') { $unqvaluessumId = $attr['qId']; $skipqIds[] = $attr['qId']; }

    // attr: Value Sum / Hours (int)
    if ($attr['format'] == 'unqvaluessumhrs') { $unqtimelineIds[0] = $attr['qId']; $skipqIds[] = $attr['qId']; } // not clear why unqvaluessumhrs needs to be in $unqtimelineIds array

    // variable to flag contexts sum attribute id
    if ($attr['format'] == 'unqcontextssum') { $unqcontextssumId = $attr['qId']; $skipqIds[] = $attr['qId']; }

    // attr: Context Sum / Hours (int)
    if ($attr['format'] == 'unqcontextssumhrs') { $unqcontextssumhrs = $attr['qId']; $skipqIds[] = $attr['qId']; }

    // array to flag when scores are being used to check their probability, and to optimise weighting after calculations
    if ($attr['format'] == 'score') { $scoreqIds[$attr['qId']] = $attr['weight']; $scoreweights[$attr['qId']] = NULL; }

    // array to skip attributes not to calculate
    if ($attr['formulaSum1'] == null &&
        $attr['formulaSum2'] == null &&
        $attr['formulaVis1'] == null &&
        $attr['formulaSum2'] == null
        ) $skipqIds[] = $attr['qId'];

    // attr: Certainty of completion (int)
    if ($attr['format'] == 'unqprobability') { $skipqIds[] = $attr['qId']; $unqprobabilityId = $attr['qId']; }

    // skip all probability items as they are currently called from the attribute itself
    if ($attr['format'] == 'probability') $skipqIds[] = $attr['qId'];

    // attr: Career (boolean)
    if ($attr['format'] == 'unqcareer') $careerId = $attr['qId'];

}

// score optimisation
ksort($scoreqIds);
ksort($scoreweights);
if ($optim) {
    $scoreopt = array();
    $scoreopt[] = array('type' => 'x', 'form' => 'a', 'scores' => $scoreweights);
    $scoreopt[] = array('type' => 'x', 'form' => 'b', 'scores' => $scoreweights);
    foreach ((array) $visions as $visn) $scoreopt[] = array('type' => 'v', 'visId' => $visn['itemId'], 'scores' => $scoreweights);
}

// call itemMeta types
$values = array();
$values['qQuery'] = "`qType`";
$values['qValue'] = 'itemMeta';
$qualMeta = query("getqualities",$config,$values,$sort);
foreach ((array) $qualMeta as $qualMet) {
    // attr: Checklist assigned to vision is someday
    if ($qualMet['format'] == 'someday' && $qualMet['typeReq'] == 'cl') $listSomedayId = $qualMet['qId'];
}

// forces json return for these vision attributes even if calculation finds no positive matches (ie if all items set to someday then recalc otherwise doesn't refresh vision)
// to do: move function to client side script
$resForce = array ('unqhoursresearch', 'score', 'integer', 'unqhours', 'unqhourstravel'/*, 'unqyears'*/);

// special case for years
$visYrs = array();
// loop visions
foreach ((array) $visions as $visn) {
    if ($visn['isSomeday'] == 'n' && $visn['dateCompleted'] == '') { $active = 1; } else { $active = 0; }
    $visYrs[$visn['itemId']] = array('yrs' => 0, 'yearst' => 0, 'active' => $active);
}

// loop attributes
foreach ((array) $attributes as $attr) {

    // skip attribute if not to calculate
    if (in_array($attr['qId'], $skipqIds, true)) continue;

    // variables to store grand totals, #1 is for row A, #2 for row B
		#$summary1 = '';
    #$summary2 = '';
    $summary1 = (float) 0;
    $summary2 = (float) 0;

    // special case effort/year
    $summaryBless1 = (float) 0;
    $summaryBless2 = (float) 0;

    // set variable for score weighting for attribute
    if (!is_null($attr['weight']) && isset($attr['weight'])) {
        $weight = (float) $attr['weight'];
    } else {
        $weight = (float) 10;
    }
    //$weight = $weight / 10; // original weighting, range 0 to 1.0
    //$weight = ($weight / 20) + 0.5; // moderated to not disfavour low weighting too much, set range 0.5 to 1.0
    $weight = (float) 1;

    // loop visions
    foreach ((array) $visions as $visn) {
        // initialise sql parameters
        $values = array();
        $values['visId'] = $visn['itemId'];
        $values['qId'] = $attr['qId'];

        // call the vision's child records from main table for current attribute
        $items = query("lookupqualities",$config,$values,$sort);

        // special cases of value for calculation held in other table
        // case: checklist hours from checklist table
        if ($attr['qId'] == $hoursId) {
            // query the list lookup table
            $values = array();
            $values['parentId'] = $visn['itemId'];
            $values['type'] = 'c';
            $itemVars = query("getchildlists",$config,$values,$sort);
            if (!empty($itemVars) && is_array($itemVars) && count($itemVars) > 0) {
                foreach ((array) $itemVars as $item) {
                    $values['listId'] = $item['listId'];
                    $res = query("selectchecklist",$config,$values,$sort);
                    if (count($res) > 0 && is_array($res)) {
                        if (!is_array($items)) $items = array(); // error handling where no child match in main table, but match from other table, ie checklist hours
                        $items[] = array('visId' => $visn['itemId'], 'itemId' => $item['listId'], 'qId' => $hoursId, 'itemType' => 'c', 'value' => $res[0]['effort']);
                    }
                }
            }
        }

        // initialise variables to store totals, #0 for combining text results from #1 and #2 after calculation
        // #1 for actual result, and #2 as supplementary paired result for #1
        #$output = '';
        #$output1 = '';
        #$output2 = '';
        #$pseudo2 = '';
				$output = (float) 0;
        $output1 = (float) 0;
        $output2 = (float) 0;
        $pseudo2 = (float) 0;

        // special case effort/year
        $bless1 = (float) 0;
        $bless2 = (float) 0;

        // special case for years
        $outputYrs = (float) 0;
				#$outputYear = false;
				$outputYear = (float) 0;

        // confirm the vision has child record(s) in main table for current attribute
        if (!is_array($items) || empty($items) || count($items) == 0) continue;

        // loop vision's child records
        foreach ((array) $items as $item) {

            // skip zero value items
            // except effort, as triggers year records
            // all probability items are currently called from the attribute itself
            if ($item['value'] == 0 && $attr['qId'] != $hoursId) {
                if ($optim && array_key_exists($attr['qId'], $scoreqIds)) $scores[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'attrId' => $attr['qId'], 'value' => $item['value']);
                continue;
            }

            // control for the case that it was once a child of the vision
            // however artifact records remain in the main table and must not be counted
            // to do: maybe update pages where lists are unassigned from items so that main table records are deleted

            // for lists
            if ($item['itemType'] == 'c' || $item['itemType'] == 'l') {

                // default to counted in case no record in lookup
                $someday = false;
                $complete = false;

                // query the list lookup table to check if vision is a parent
                $values = array();
                $values['parentId'] = $visn['itemId'];
                $values['type'] = $item['itemType'];
                $values['listId'] = $item['itemId'];
                $itemVars = query("getchildlists",$config,$values,$sort);
                if (!empty($itemVars) && is_array($itemVars) && count($itemVars) > 0) {
                    // if so look up qualities if someday
                    $values = array();
                    $values['visId'] = $visn['itemId'];
                    $values['qId'] = $listSomedayId;
                    $values['itemId'] = $item['itemId'];
                    $values['itemType'] = $item['itemType'];
                    $itemVars = query("lookupqualities",$config,$values,$sort);
										if (!empty($itemVars) && is_array($itemVars) && count($itemVars) > 0) {
                        if ($itemVars[0]['value'] == 'y') {
                            $someday = true;
                            $complete = true;
                        } else {
                            $someday = false;
                            $complete = false;
                        }
                    }
                } else {
                    // list is not a child
                    continue;
                }
            // else item is not a list
            } else {
                // query items parents
                // to do: see if this can be queried from the looping vision array
                $values = array();
                $values['itemId'] = $item['itemId'];
                $itemPars = query("lookupparentshort",$config,$values,$sort);
                // for false positives, prepare to escape
                $child = false;
                // confirms that item is child to vision parent
                if (!empty($itemPars) && is_array($itemPars) && count($itemPars) > 0) {
                    foreach ((array) $itemPars as $pars) if ($pars['parentId'] == $visn['itemId']) $child = true;
                }
                // item is not a child
                if (!$child) continue;
                // query item status to count or not
                $itemVars = query("selectitemstatus",$config,$values,$sort);
                ($itemVars[0]['isSomeday'] == 'y' ? $someday = true : $someday = false);
                ($itemVars[0]['dateCompleted'] == '' ? $complete = false : $complete = true);
            }

            // query years duration recorded against item, ie project, goal etc
            // for use in calculation of scores, ie project yielding x value and taking n years provides annual value of x/n
            // ignore if record is not a score or count of hours
            // estimate years if probability assigned
            // to do: put years/probs into array rather than requery to opmitise
            if (array_key_exists($attr['qId'], $scoreqIds) ||
                in_array($attr['qId'], $unqhoursIds, true)) {
                $values = array();
                $values['visId'] = $visn['itemId'];
                $values['qId'] = $yearqId;
                $values['itemId'] = $item['itemId'];
                $values['itemType'] = $item['itemType'];
                $itemVars = query("lookupqualities",$config,$values,$sort);
								if (empty($itemVars) || !is_array($itemVars) || $itemVars[0]['value'] == '' || $itemVars[0]['value'] == 0) {
									$years = (float) 1;
								} else {
									$years = (float) $itemVars[0]['value'];
								}

                $values['qId'] = $yearsprobId;
                $itemVars = query("lookupqualities",$config,$values,$sort);
								if (empty($itemVars) || !is_array($itemVars)) {
									$yearsprob = (float) 1;
								} else {
	                if ($itemVars[0]['value'] == '') {
										$yearsprob = (float) 1;
									} else {
										$yearsprob = (float) $itemVars[0]['value'];
										$yearsprob /= 10;
									}
								}

                // estimate years
                $years = (float) ceil($years * ($yearsprob > 0 ? 1/$yearsprob : 0));

            }

            // query if project, goal etc can be done during brain down time (brainless)
            // for counting time separately
            if ($item['qId'] == $travelId) { // override travel
                $brainless = 'y';
            } elseif ($item['qId'] == $reseId) { // override research
                $brainless = 'n';
            } else {
                $values = array();
                $values['visId'] = $visn['itemId'];
                $values['qId'] = $brainlessId;
                $values['itemId'] = $item['itemId'];
                $values['itemType'] = $item['itemType'];
                $itemVars = query("lookupqualities",$config,$values,$sort);
								if (empty($itemVars) || !is_array($itemVars)) {
									$brainless = 'n';
								} else {
									$brainless = $itemVars[0]['value'];
	                // default not brainless
	                if ($brainless == '') $brainless = 'n';
								}
            }

            // default probability
            $prob = 1;
            // check if attribute has probabilities
            if (!is_null($attr['probId'])) {
                $values = array();
                $values['visId'] = $visn['itemId'];
                $values['qId'] = $attr['probId'];
                $values['itemId'] = $item['itemId'];
                $values['itemType'] = $item['itemType'];
                $itemVars = query("lookupqualities",$config,$values,$sort); // this could just be checked in $items array
								if (!empty($itemVars) && is_numeric($itemVars[0]['value'])) {
									$prob = (float) $itemVars[0]['value'];
									$prob /= 10;
									// capture p for calculating Certainty
									// record active status
									if (!$someday && !$complete) { $active = 1; } else { $active = 0; }
									// update value sums array value
									$certainties[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'value' => $prob, 'active' => $active);
								} else {
									$prob = (float) 1;
								}
            }

            // finish setting the variables for calculation
            // $value, $prob, $weight, $years, $someday, $complete, $brainless, $output1, $output2, $summary1, $summary2
						$value = (float) $item['value'];
            // eval to execute as script value for attribute's formula for vision
            // updates the $output1 and $output2 variables, formulae defined in gtdfuncs.php
            if (!$someday && !$complete) {
							eval(formula($attr['formulaVis1']));
							/* file_put_contents ('_response.txt',
									formula($attr['formulaVis1']) . "\n" .
			            print_r($attr,true) . "\n" .
									gettype($output1) . "\n" .
									'$output1 ' . $output1 ."\n" ."\n" .
									gettype($output1) . "\n" .
									'$pseudo2 ' . $pseudo2 ."\n" ."\n" .
									gettype($value) . "\n" .
									'$value ' . $value . "\n" ."\n" .
									gettype($prob) . "\n" .
									'$prob ' . $prob . "\n" ."\n" .
									gettype($weight) . "\n" .
									'$weight ' . $weight . "\n" ."\n" .
									gettype($years) . "\n" .
									'$years ' . $years . "\n"
								);*/
							eval(formula($attr['formulaVis2']));
            }

            // calculate value
            $sum = 0;
            eval(formula('cubeYrItem'));

            // update scores array
            if (array_key_exists($attr['qId'], $scoreqIds)) $scores[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'attrId' => $attr['qId'], 'value' => $sum);

            // special case for attributes scoring values
            if ($attr['parId'] == $valParId) {

                // search if sum captured already from another value attribute, if so reuse sum
                $i = 0;
                foreach ((array) $valuessum as $valuesum) {
                    if ($valuesum['type'] == $item['itemType'] &&
                        $valuesum['visId'] == $visn['itemId'] &&
                        $valuesum['id'] == $item['itemId']
                        ) {
                            $sum += $valuesum['value'];
                            // remove record
                            unset($valuessum[$i]);
                            // reset array keys
                            $valuessum = array_values($valuessum);
                            // stop searching
                            break;
                    }
                    $i++;
                }

                // record active status
                if (!$someday && !$complete) { $active = 1; } else { $active = 0; }

                // update value sums array value
                $valuessum[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'value' => $sum, 'active' => $active);
            }

            // special case for attributes scoring context
            if ($attr['parId'] == $cntxtParId) {
//            if ($attr['qId'] == 41) {

                // search if sum captured already from another context attribute, if so reuse sum
                $i = 0;
                foreach ((array) $contextssum as $contextsum) {
                    if ($contextsum['type'] == $item['itemType'] &&
                        $contextsum['visId'] == $visn['itemId'] &&
                        $contextsum['id'] == $item['itemId']
                        ) {
                            $sum += $contextsum['value'];
                            // remove record
                            unset($contextssum[$i]);
                            // reset array keys
                            $contextssum = array_values($contextssum);
                            // stop searching
                            break;
                    }
                    $i++;
                }

                // record active status
                if (!$someday && !$complete) { $active = 1; } else { $active = 0; }

                // update context sums array value
                $contextssum[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'value' => $sum, 'active' => $active);
            }

            // special case for attributes counting hours
            // to do: incorporate formulae, and prob formula, into eval
            if (in_array($item['qId'], $unqhoursIds, true)) {

                // query year start
                $values = array();
                $values['visId'] = $visn['itemId'];
                $values['qId'] = $yearstqId;
                $values['itemId'] = $item['itemId'];
                $values['itemType'] = $item['itemType'];
                $itemVars = query("lookupqualities",$config,$values,$sort);
								if (!empty($itemVars) && is_numeric($itemVars[0]['value'])) {
									$yearst = (float) $itemVars[0]['value'];
									$tlineNull[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'itemId' => $item['itemId']);
								} else {
									// default this year
									$yearst = (float) date("Y");
								}

                // query year end
                $values = array();
                $values['visId'] = $visn['itemId'];
                $values['qId'] = $yearenqId;
                $values['itemId'] = $item['itemId'];
                $values['itemType'] = $item['itemType'];
                $itemVars = query("lookupqualities",$config,$values,$sort);
								// default item years
								$in = (float) 0; // inclusive of year itself
								if (!empty($itemVars) && is_numeric($itemVars[0]['value'])) {
									$yearen = (float) $itemVars[0]['value'];
									$in++;
									$tlineNull[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'itemId' => $item['itemId']);
								} else {
									$yearen = $yearst + $years;
								}
                // set years duration
                $yearsdur = $yearen + $in - $yearst;

                // calculate number of years diff: current year - year start
                $d1 = DateTime::createFromFormat("Y",$yearst);
                $d2 = new DateTime("Y");
                // add 1 minute difference
                $d2->add(new DateInterval('PT1M'));
                $diff = $d2->diff($d1);
                $yrdiff = $diff->y;

                /* cases:
                item year start in future, starts within timeline and ends before timeline end
                2020 2 yr
                item year start in future, starts within timeline and ends after timeline end
                2020 4 yr
                item year start in future, starts after timeline end
                2023 1 yr
                item year start this year, and ends before timeline end
                '' 4 yr
                item year start this year, and ends after timeline end
                '' 6 yr
                item year start in past, and ends before timeline start
                2010 9 yr
                item year start in past, and ends before timeline end
                2010 12 yr
                item year start in past, and ends after timeline end
                2010 14 yr
                brainless
                not brainless
                probability applied to research
                probability applied to hours
                probability applied to travel
                p applied to years
                */

                // item year start in future
                if ($d1 > $d2) {
                    // year to begin counting, diff incremented to allow for 1 minute difference
                    $yrbegin = $yrdiff + 2;
                    // year to end counting
                    $yrend = $yrbegin + $yearsdur - 1;
                // else item year start this year or in past
                } else {
                    // year to begin counting
                    $yrbegin = 1;
                    // year to end counting
                    $yrend = $yearsdur - $yrdiff;
                }

                // update max years remaining
                if (!$someday && !$complete) eval(formula('maxYrs'));

                // limit to number of timeline years
                if ($yrend > count($unqtimelineIds) - 1) $yrend = count($unqtimelineIds) - 1; // - 1 to ignore $unqtimelineIds[0]

                // years for value product
                $yrsval[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'active' => $active, 'yrs' => $yrend - $yrbegin + 1);

                // process value for each year item on timeline
                // 0 is base value used for sum of weighted value results per hour per year
                $yr = 0;
                while ($yr <= $yrend || $yr == 0) {

                    // skip years if start is in future
                    // except for first loop of yr = 0 always proceed
                    if ($yr < $yrbegin && $yr !== 0) { $yr++; continue; }

                    // initialise timeline item values
                    $tlinevala = 0;
                    $tlinevalb = 0;

                    // search if value captured already from another time attribute, if so reuse value
                    $i = 0;
                    foreach ((array) $timeline as $res) {
                        if ($res['type'] == $item['itemType'] &&
                            $res['visId'] == $visn['itemId'] &&
                            $res['id'] == $item['itemId'] &&
                            $res['attrId'] == $unqtimelineIds[$yr]
                            ) {
                                $tlinevala = $res['valuea'];
                                $tlinevalb = $res['valueb'];
                                // remove record
                                unset($timeline[$i]);
                                // reset array keys
                                $timeline = array_values($timeline);
                                // stop searching
                                break;
                        }
                        $i++;
                    }

                    // increment hours value
                    if ($brainless == 'n') {
                        $tlinevala += ceil($value * ($prob > 0 ? 1/$prob : 0));
                    } else {
                        $tlinevalb += ceil($value * ($prob > 0 ? 1/$prob : 0));
                    }

                    // record active status
                    if (!$someday && !$complete) { $active = 1; } else { $active = 0; }

                    // update timeline array value, ignoring nil values
                    if ($tlinevala !== 0 || $tlinevalb !== 0) $timeline[] = array('type' => $item['itemType'], 'visId' => $visn['itemId'], 'id' => $item['itemId'], 'attrId' => $unqtimelineIds[$yr], 'valuea' => $tlinevala, 'valueb' => $tlinevalb, 'active' => $active);

                    // loop to next year on timeline
                    $yr++;
                }
            }
        }

        // if paired output A obtained, store in text variable $output
        if ($output1 !== '') $output = $output1;
        // if paired output B obtained, assum A was obtained, separate A and B with character, and store in text variable $output
        if ($output2 !== '') { if ($output !== '') $output .= ", ";  $output .= $output2; }
        // if output obtained, or the current attribute must force a response to the matrix, push the record onto the final result
        if ($output !== '' || in_array($attr['format'],$resForce)) {
            if ($output == '') $output = 0;
            //$result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $attr['qId'], 'value' => $output);

            // if score item and optimising, place in array
            if ($optim && array_key_exists($attr['qId'], $scoreqIds)) {
                foreach ((array) $scoreopt as $key => $opt) {
                    if ($opt['type'] == 'v' && $opt['visId'] == $visn['itemId']) {
                        $scoreopt[$key]['scores'][$attr['qId']] = $output;
                        break;
                    }
                }
            }
        }

        // special case $brainless for effort/year
        if ($attr['qId'] == $hoursId) {
            // check calculation variable
            $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $brainlessId, 'value' => $bless1 + $bless2);
            if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
                $summaryBless1 += $bless1;
                $summaryBless2 += $bless2;
            }
        }
        // special case $outputYrs for years
        if ($outputYrs > 0) {
            foreach ((array) $visYrs as $key=>$visYr) {
                if ($visn['itemId'] == $key) {
                    if ($outputYrs > $visYr['yrs']) $visYrs[$key]['yrs'] = $outputYrs;
                    break;
                }
            }
        }
        if ($outputYear) {
            foreach ((array) $visYrs as $key=>$visYr) {
                if ($visn['itemId'] == $key) {
                    if (
                        $outputYear < $visYr['yearst']
                        || !$visYr['yearst']
                        ) $visYrs[$key]['yearst'] = $outputYear;
                    break;
                }
            }
        }

        // set variables for vision active or not to affect following summary calculation
        if ($visn['isSomeday'] == 'y') { $someday = true; } else { $someday = false; }
        if ($visn['dateCompleted'] == '') { $complete = false; } else { $complete = true; }

        // eval to execute as script value for attribute's formula for summary
        // updates the $summary1 and $summary2 variables, formulae defined in gtdfuncs.php
        if (!$someday && !$complete) {
            eval(formula($attr['formulaSum1']));
            eval(formula($attr['formulaSum2']));
        }

    }
    // if output obtained, or the current attribute must force a response to the matrix, push the record onto the final result
    // if ($summary1 !== '' || in_array($attr['format'],$resForce)) $result[] = array('type' => 'x', 'attrId' => $attr['qId'], 'form' => 'a', 'value' => $summary1);
    // if ($summary2 !== '' || in_array($attr['format'],$resForce)) $result[] = array('type' => 'x', 'attrId' => $attr['qId'], 'form' => 'b', 'value' => $summary2);

    // if score item and optimising, place in array
    if ($optim && array_key_exists($attr['qId'], $scoreqIds)) {
        $i = 0;
        foreach ((array) $scoreopt as $key => $opt) {
            if ($opt['type'] == 'x' && $opt['form'] == 'a') {
                $scoreopt[$key]['scores'][$attr['qId']] = $summary1;
                $i++;
            }
            if ($opt['type'] == 'x' && $opt['form'] == 'b') {
                $scoreopt[$key]['scores'][$attr['qId']] = $summary2;
                $i++;
            }
            if ($i == 2) break;
        }
    }

    // special case $brainless for effort/year
    if ($attr['qId'] == $hoursId) {
        $result[] = array('type' => 'x', 'attrId' => $brainlessId, 'form' => 'a', 'value' => $summaryBless1);
        $result[] = array('type' => 'x', 'attrId' => $brainlessId, 'form' => 'b', 'value' => $summaryBless2);
    }
}

// process ouputYrs
$summaryYrs = 0;
$summaryYear = false;
foreach ((array) $visYrs as $key=>$visYr) {
    if (isset($yearqId)) $result[] = array('type' => 'v', 'visId' => $key, 'attrId' => $yearqId, 'value' => date("Y") - $visYr['yearst'] + $visYr['yrs']);
    if (isset($yearstqId) && $visYr['active']) $result[] = array('type' => 'v', 'visId' => $key, 'attrId' => $yearstqId, 'value' => $visYr['yearst']);
    if (isset($yearenqId) && $visYr['active']) $result[] = array('type' => 'v', 'visId' => $key, 'attrId' => $yearenqId, 'value' => date("Y") + $visYr['yrs'] - 1);
    if (!$visYr['active']) continue;
    if (!$summaryYear) $summaryYear = $visYr['yearst'];
    if ($summaryYrs < $visYr['yrs']) $summaryYrs = $visYr['yrs'];
    if ($summaryYear > $visYr['yearst'] && $visYr['active']) $summaryYear = $visYr['yearst'];
}
if (isset($yearqId)) $result[] = array('type' => 'x', 'attrId' => $yearqId, 'form' => 'a', 'value' => $summaryYrs);
if (isset($yearqId)) $result[] = array('type' => 'x', 'attrId' => $yearqId, 'form' => 'b', 'value' => '');
if (isset($yearstqId)) $result[] = array('type' => 'x', 'attrId' => $yearstqId, 'form' => 'a', 'value' => $summaryYear);
if (isset($yearstqId)) $result[] = array('type' => 'x', 'attrId' => $yearstqId, 'form' => 'b', 'value' => '');
if (isset($yearenqId)) $result[] = array('type' => 'x', 'attrId' => $yearenqId, 'form' => 'a', 'value' => $summaryYrs + date("Y") - 1);
if (isset($yearenqId)) $result[] = array('type' => 'x', 'attrId' => $yearenqId, 'form' => 'b', 'value' => '');


// process value year scores
$yrsval = array_values(array_unique($yrsval, SORT_REGULAR));

// initialise temp vision array
$valuessumvisn = array();
foreach ((array) $visions as $visn) {
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') { $active = 1; } else { $active = 0; }
    $valuessumvisn[$visn['itemId']] = array('active' => $active);
}
// process items
foreach ((array) $scores as $score) {
    if (!isset($valuessumvisn[$score['visId']][$score['attrId']])) $valuessumvisn[$score['visId']][$score['attrId']] = 0;
    foreach ((array) $yrsval as $item) {
        if (
            $item['type'] == $score['type'] &&
            $item['visId'] == $score['visId'] &&
            $item['id'] == $score['id']
        ) {
            $valuessumvisn[$score['visId']][$score['attrId']] += $item['yrs'] * $score['value'] * $item['active'];
            break;
        }
    }
}

// push vision value sums onto the final result
$formsummary = array('a' => 0, 'b' => 0);
$valuessummaryn = array();
foreach ((array) $valuessumvisn as $key => $visn) {

    foreach ((array) $visn as $valkey => $value) {

        if ($valkey == 'active') { $active = $value; continue; }
        if (!isset($valuessummaryn[$valkey])) $valuessummaryn[$valkey] = $formsummary;

        if ($value >= 0) { $value = ceil($value); } else { $value = floor($value); }
        $result[] = array('type' => 'v', 'visId' => $key, 'attrId' => $valkey, 'value' => $value);

        // if vision is live then increment summary value sums
        if ($active) {
            if ($value >= 0) {
                $valuessummaryn[$valkey]['a'] += $value;
            } else {
                $valuessummaryn[$valkey]['b'] += $value;
            }
        }
    }
}

// push summary value sums onto final result
foreach ((array) $valuessummaryn as $key => $summary) {
    $result[] = array('type' => 'x', 'attrId' => $key, 'form' => 'a', 'value' => $summary['a']);
    $result[] = array('type' => 'x', 'attrId' => $key, 'form' => 'b', 'value' => $summary['b']);
}


// process optimsing scores
if ($optim) {

    // process items
    foreach ((array) $scores as $score) {
        $exist = false;
        foreach ((array) $scoreopt as $item) {
            if (
                $item['type'] == $score['type'] &&
                $item['visId'] == $score['visId'] &&
                $item['id'] == $score['id']
            ) {
                $exist = true;
                break;
            }
        }
        if (!$exist) $scoreopt[] = array('type' => $score['type'], 'visId' => $score['visId'], 'id' => $score['id'], 'scores' => $scoreweights);
        foreach ((array) $scoreopt as $key => $item) {
            if (
                $item['type'] == $score['type'] &&
                $item['visId'] == $score['visId'] &&
                $item['id'] == $score['id']
            ) {
                $scoreopt[$key]['scores'][$score['attrId']] = $score['value'];
                break;
            }
        }
    }

    // calculate items, values, and summary
    foreach ((array) $scoreopt as $opt) {
        // valid array
        $cont = true;
        $zeros = true;
        foreach ((array) $opt['scores'] as $key => $score) {
            if (strlen($score) == 0) $cont = false;
            if ($score !== '0') $zeros = false;
        }
        if (!$cont || $zeros) continue;

        // correlations
        if (stDev($opt['scores']) !== 0 && stDev($scoreqIds) !== 0) {
            $pearson = corr($scoreqIds, $opt['scores']);
            $pearson = round($pearson, 1);
            if ($pearson == '0') $pearson = '0.0';
        } else {
            $pearson = '';
        }
        $uniformity = uniformity($opt['scores']);
        $uniformity = round($uniformity, 1);
        if ($uniformity < 0 || $uniformity == '0') $uniformity = '0.0';

        // push to result
        if ($opt['type'] == 'x') {
            if (isset($unqoptimiseprefId)) $result[] = array('type' => 'x', 'attrId' => $unqoptimiseprefId, 'form' => $opt['form'], 'value' => $pearson);
            if (isset($unqoptimisebalaId)) $result[] = array('type' => 'x', 'attrId' => $unqoptimisebalaId, 'form' => $opt['form'], 'value' => $uniformity);
        } elseif ($opt['type'] == 'v') {
            if (isset($unqoptimiseprefId)) $result[] = array('type' => 'v', 'visId' => $opt['visId'], 'attrId' => $unqoptimiseprefId, 'value' => $pearson);
            if (isset($unqoptimisebalaId)) $result[] = array('type' => 'v', 'visId' => $opt['visId'], 'attrId' => $unqoptimisebalaId, 'value' => $uniformity);
        } else {
            if (isset($unqoptimiseprefId)) $result[] = array('type' => $opt['type'], 'visId' => $opt['visId'], 'itemId' => $opt['id'], 'attrId' => $unqoptimiseprefId, 'value' => $pearson);
            if (isset($unqoptimisebalaId)) $result[] = array('type' => $opt['type'], 'visId' => $opt['visId'], 'itemId' => $opt['id'], 'attrId' => $unqoptimisebalaId, 'value' => $uniformity);
        }
    }
}


// process value sums

// initialise temp vision array
$valuessumvis = array();
foreach ((array) $visions as $visn) {
    $valuessumvis[$visn['itemId']] = 0;
}
foreach ((array) $valuessum as $valuesum) {
    // push item value sums onto the final result
    $result[] = array('type' => $valuesum['type'], 'visId' => $valuesum['visId'], 'itemId' => $valuesum['id'], 'attrId' => $unqvaluessumId, 'value' => round($valuesum['value']));

    // increment vision value sums
    if ($valuesum['active']) $valuessumvis[$valuesum['visId']] += $valuesum['value'];
}

// push vision value sums onto the final result
$valuessummary = array('a' => 0, 'b' => 0);
foreach ((array) $visions as $visn) {
		// crude error check here
		if (!isset($unqvaluessumId)) continue;
    if ($valuessumvis[$visn['itemId']] >= 0) {
        $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqvaluessumId, 'value' => ceil($valuessumvis[$visn['itemId']]));
    } else {
        $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqvaluessumId, 'value' => floor($valuessumvis[$visn['itemId']]));
    }

    // if vision is live then increment summary value sums
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
        if ($valuessumvis[$visn['itemId']] >= 0) {
            $valuessummary['a'] += ceil($valuessumvis[$visn['itemId']]);
        } else {
            $valuessummary['b'] += floor($valuessumvis[$visn['itemId']]);
        }
    }
}

// push summary value sums onto final result
if (isset($unqvaluessumId)) {
	$result[] = array('type' => 'x', 'attrId' => $unqvaluessumId, 'form' => 'a', 'value' => $valuessummary['a']);
	$result[] = array('type' => 'x', 'attrId' => $unqvaluessumId, 'form' => 'b', 'value' => $valuessummary['b']);
}


// calculate and push item value sum per hours

// initialise temp vision array
$valuessumvishours = array();
foreach ((array) $visions as $visn) {
    $valuessumvishours[$visn['itemId']] = array('value' => 0, 'hours' => 0);
}

foreach ((array) $timeline as $tline) {
    // ignore non-base timeline
    if ($tline['attrId'] == $unqtimelineIds[0]) {
        foreach ((array) $valuessum as $valuesum) {
            if (
                $valuesum['type'] == $tline['type'] &&
                $valuesum['visId'] == $tline['visId'] &&
                $valuesum['id'] == $tline['id']
                ) {
                // push item onto the final result
                $denominator = $tline['valuea'] + $tline['valueb'];
                // attribute conflict handling if hours for item = 0 or negative, assume no comparable value
                if ($denominator > 1) {
                    $value = number_format($valuesum['value'] / $denominator, 1);
                    $result[] = array('type' => $valuesum['type'], 'visId' => $valuesum['visId'], 'itemId' => $valuesum['id'], 'attrId' => $unqtimelineIds[0], 'value' => $value);

                    // increment vision value sums
                    if ($valuesum['active']) {
                        $valuessumvishours[$valuesum['visId']]['value'] += $valuesum['value'];
                        $valuessumvishours[$valuesum['visId']]['hours'] += $denominator;
                    }
                }

                // stop searching
                break;
            }
        }
    }
}

// push vision value sums hours onto the final result
$valuessummaryhours = array('value' => 0, 'hours' => 0);
// crude error check
if (isset($unqtimelineIds[0]))
foreach ((array) $visions as $visn) {
    foreach ((array) $valuessumvishours as $key=>$sumv) {
        if ($visn['itemId'] == $key) {
            if ($sumv['hours'] <= 0) $sumv['hours'] = 1; // error handling if hours for item = 0 or negative, assume no division
            $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqtimelineIds[0], 'value' => number_format($sumv['value'] / $sumv['hours'], 1));
            break;
        }
    }

    // if vision is live then increment summary value sums
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
        $valuessummaryhours['value'] += $valuessumvishours[$visn['itemId']]['value'];
        $valuessummaryhours['hours'] += $valuessumvishours[$visn['itemId']]['hours'];
    }
}

// push summary value sums hours onto final result
// crude error check
if (isset($unqtimelineIds[0]) && isset($valuessummaryhours['hours']) && $valuessummaryhours['hours'] > 0)
	$result[] = array('type' => 'x', 'attrId' => $unqtimelineIds[0], 'form' => 'a', 'value' => number_format($valuessummaryhours['value'] / $valuessummaryhours['hours'], 1));


// process context sums

// initialise temp vision array
$contextssumvis = array();
foreach ((array) $visions as $visn) {
    $contextssumvis[$visn['itemId']] = 0;
}
foreach ((array) $contextssum as $contextsum) {
		// crude error catch here, maybe should not be calculating $contextsum to begin with unless called in qLimit?
		if (!isset($unqcontextssumId)) continue;
    // push item context sums onto the final result
    $result[] = array('type' => $contextsum['type'], 'visId' => $contextsum['visId'], 'itemId' => $contextsum['id'], 'attrId' => $unqcontextssumId, 'value' => round($contextsum['value']));

    // increment vision context sums
    if ($contextsum['active']) $contextssumvis[$contextsum['visId']] += $contextsum['value'];
}

// push vision context sums onto the final result
$contextssummary = array('a' => 0, 'b' => 0);
foreach ((array) $visions as $visn) {
		// crude error catch here, maybe should not be calculating $contextsum to begin with unless called in qLimit?
		if (!isset($unqcontextssumId)) continue;

    if ($contextssumvis[$visn['itemId']] >= 0) {
        $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqcontextssumId, 'value' => ceil($contextssumvis[$visn['itemId']]));
    } else {
        $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqcontextssumId, 'value' => floor($contextssumvis[$visn['itemId']]));
    }

    // if vision is live then increment summary context sums
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
        if ($contextssumvis[$visn['itemId']] >= 0) {
            $contextssummary['a'] += ceil($contextssumvis[$visn['itemId']]);
        } else {
            $contextssummary['b'] += floor($contextssumvis[$visn['itemId']]);
        }
    }
}

// push summary context sums onto final result
if (isset($unqcontextssumId)) {
	$result[] = array('type' => 'x', 'attrId' => $unqcontextssumId, 'form' => 'a', 'value' => $contextssummary['a']);
	$result[] = array('type' => 'x', 'attrId' => $unqcontextssumId, 'form' => 'b', 'value' => $contextssummary['b']);
}

// calculate and push item context sum per hours

// initialise temp vision array
$contextssumvishours = array();
foreach ((array) $visions as $visn) {
    $contextssumvishours[$visn['itemId']] = array('value' => 0, 'hours' => 0);
}

foreach ((array) $timeline as $tline) {
    // ignore non-base timeline
    if ($tline['attrId'] == $unqtimelineIds[0]) {
        foreach ((array) $contextssum as $contextsum) {
            if (
                $contextsum['type'] == $tline['type'] &&
                $contextsum['visId'] == $tline['visId'] &&
                $contextsum['id'] == $tline['id']
                ) {
                // push item onto the final result
                $denominator = $tline['valuea'] + $tline['valueb'];
                // attribute conflict handling if hours for item = 0 or negative, assume no comparable value
                if ($denominator > 1 && isset($unqcontextssumhrs)) {
                    $value = number_format($contextsum['value'] / $denominator, 1);
										// in cases when GET variables are not carefully requested,
										// $unqcontextssumhrs might not have returned the relevant quality with format = 'unqcontextssumhrs' because it is not called for Qual: Defult
										// which stems from the GET variable $qLimit (which is set default = a in matrix.php)
                    $result[] = array('type' => $contextsum['type'], 'visId' => $contextsum['visId'], 'itemId' => $contextsum['id'], 'attrId' => $unqcontextssumhrs, 'value' => $value);

                    // increment vision context sums
                    if ($contextsum['active']) {
                        $contextssumvishours[$contextsum['visId']]['value'] += $contextsum['value'];
                        $contextssumvishours[$contextsum['visId']]['hours'] += $denominator;
                    }
                }

                // stop searching
                break;
            }
        }
    }
}

// push vision context sums hours onto the final result
$contextssummaryhours = array('value' => 0, 'hours' => 0);
foreach ((array) $visions as $visn) {
    foreach ((array) $contextssumvishours as $key=>$sumv) {
        if ($visn['itemId'] == $key) {
            if ($sumv['hours'] <= 0) $sumv['hours'] = 1; // error handling if hours for item = 0 or negative, assume no division
						// crude error catch here, maybe should not be calculating $contextsum to begin with unless called in qLimit?
						if (isset($unqcontextssumhrs))
            	$result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqcontextssumhrs, 'value' => number_format($sumv['value'] / $sumv['hours'], 1));
            break;
        }
    }

    // if vision is live then increment summary context sums
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
        $contextssummaryhours['value'] += $contextssumvishours[$visn['itemId']]['value'];
        $contextssummaryhours['hours'] += $contextssumvishours[$visn['itemId']]['hours'];
    }
}

// push summary context sums hours onto final result
// crude error catch here, maybe should not be calculating $contextsum to begin with unless called in qLimit?
if (isset($unqcontextssumhrs))
	$result[] = array('type' => 'x', 'attrId' => $unqcontextssumhrs, 'form' => 'a', 'value' => number_format($contextssummaryhours['value'] / $contextssummaryhours['hours'], 1));


// timeline summary

// initialise temp vision array
$tlinevis = array();
$tlineviskey = array();
foreach ((array) $unqtimelineIds as $key=>$val) {
    if ($key !== 0) $tlineviskey[$val] = array('a' => 0, 'b' => 0);
}
foreach ((array) $visions as $visn) {
    $tlinevis[$visn['itemId']] = $tlineviskey;
}

// loop item timeline results for incrementing visions
foreach ((array) $timeline as $tline) {
    // skip inactive items, ignore base year
    if (!$tline['active'] || $tline['attrId'] == $unqtimelineIds[0]) continue;
    // search visions
    foreach ((array) $tlinevis as $visId=>$varray) {
        // match $timeline record to vision
        if ($visId == $tline['visId']) {
            // search vision records
            foreach ((array) $varray as $tlineId=>$tarray) {
                // match $timeline record to year id
                if ($tlineId == $tline['attrId']) {
                    // search timeline pair
                    $i = 1;
                    foreach ((array) $tarray as $tlinepair=>$value) {
                        // increment vision timeline value
                        if ($tlinepair == 'a') $tlinevis[$visId][$tlineId][$tlinepair] = $value + $tline['valuea'];
                        if ($tlinepair == 'b') $tlinevis[$visId][$tlineId][$tlinepair] = $value + $tline['valueb'];
                        $i++;
                        if ($i > 2) break 3;
                    }
                }
            }
        }
    }
}

// loop vision timeline results for incrementing summary
$tmpsummary = $tlineviskey;
// push vision timeline onto the final result
foreach ((array) $tlinevis as $visId=>$varray) {
    // loop vision records
    foreach ((array) $varray as $tlineId=>$tarray) {
        $outputa = '';
        $outputb = '';
        // loop timeline pair
        foreach ((array) $tarray as $tlinepair=>$value) {
            // set value
            if ($tlinepair == 'a') $outputa = $value;
            if ($tlinepair == 'b') $outputb = $value;
        }

        // if vision is live then increment summary timeline
        foreach ((array) $visions as $visn) {
            if ($visn['itemId'] == $visId &&
                $visn['isSomeday'] == 'n' &&
                $visn['dateCompleted'] == ''
                ) {
                $tmpsummary[$tlineId]['a'] += $outputa;
                $tmpsummary[$tlineId]['b'] += $outputb;
            }
        }

        // push vision timeline onto the final result
        if ($outputa == '' && $outputb == '') { $output = ''; } else { $output = $outputa + $outputb; }
/*
        if ($outputa !== '' && $outputb == '') $output = $outputa . ', 0';
        if ($outputa == '' && $outputb !== '') $output = '0, ' . $outputb;
        if ($outputa !== '' && $outputb !== '') $output = $outputa . ', ' . $outputb;
*/
        $result[] = array('type' => 'v', 'visId' => $visId, 'attrId' => $tlineId, 'value' => $output);
    }
}

// push summary timeline onto final result
foreach ((array) $tmpsummary as $tlineId=>$tarray) {
    $tlinesum = 0;
    foreach ((array) $tarray as $tlinepair=>$value) {
        $result[] = array('type' => 'x', 'attrId' => $tlineId, 'form' => $tlinepair, 'value' => $value);
        $tlinesum += $value;
    }
    $result[] = array('type' => 'x', 'attrId' => $tlineId, 'form' => 'c', 'value' => $tlinesum);
}

// push item timeline onto the final result
// temp array for item timeline values
$resTemp = array();
foreach ((array) $timeline as $tline) {
    // ignore base timeline
    if ($tline['attrId'] == $unqtimelineIds[0]) continue;

    if ($tline['valuea'] == '' && $tline['valueb'] == '') { $output = ''; } else { $output = $tline['valuea'] + $tline['valueb']; }
/*
    if ($tline['valuea'] !== '' && $tline['valueb'] == '') $output = $tline['valuea'] . ', 0';
    if ($tline['valuea'] == '' && $tline['valueb'] !== '') $output = '0, ' . $tline['valueb'];
    if ($tline['valuea'] !== '' && $tline['valueb'] !== '') $output = $tline['valuea'] . ', ' . $tline['valueb'];
*/
    $result[] = array('type' => $tline['type'], 'visId' => $tline['visId'], 'itemId' => $tline['id'], 'attrId' => $tline['attrId'], 'value' => $output);
    $resTemp[] = array('type' => $tline['type'], 'visId' => $tline['visId'], 'itemId' => $tline['id'], 'attrId' => $tline['attrId']);
}

// push empty item timeline onto the final result to clear browser display
// temp array for item empty timeline values, with dupes removed
$tlineNull = array_values(array_unique($tlineNull, SORT_REGULAR));
foreach ((array) $tlineNull as $ren) {
    foreach ((array) $unqtimelineIds as $attrId) {
        // ignore non-base timeline
        if ($attrId == $unqtimelineIds[0]) continue;
        // search temp array for match
        $match = false;
        foreach ((array) $resTemp as $temp) {
            if (
                $temp['type'] == $ren['type'] &&
                $temp['visId'] == $ren['visId'] &&
                $temp['itemId'] == $ren['itemId'] &&
                $temp['attrId'] == $attrId
            ) {
                $match = true;
                break;
            }
        }
        // if no match then write null
        if (!$match) $result[] = array('type' => $ren['type'], 'visId' => $ren['visId'], 'itemId' => $ren['itemId'], 'attrId' => $attrId, 'value' => '');
    }
}


// calculate certainty

// initialise temp vision array
$certaintiesvis = array();
$certaintiesitem = array();
$certaintiesitemtemp = array();
$certaintyMax = 0;
$certaintyMin = 9;
foreach ((array) $visions as $visn) {
    $certaintiesvis[$visn['itemId']] = array('value' => 0, 'count' => 0);
}

// process certainty sums
foreach ((array) $certainties as $certainty) {
    // collate items array
    $certaintiesitem[] = array('type' => $certainty['type'], 'visId' => $certainty['visId'], 'itemId' => $certainty['id'], 'count' => 0, 'value' => 0, 'active' => $certainty['active']);
}

// remove dupes
$certaintiesitem = array_values(array_unique($certaintiesitem, SORT_REGULAR));
$certaintiesitemtemp = $certaintiesitem;

// increment sums
foreach ((array) $certainties as $certainty) {
    // sum
    $i = 0;
    foreach ((array) $certaintiesitemtemp as $item) {
        if (
            $certainty['id'] == $item['itemId'] &&
            $certainty['visId'] == $item['visId'] &&
            $certainty['type'] == $item['type']
        ) {
            $certaintiesitem[$i]['count']++;
            $certaintiesitem[$i]['value'] += $certainty['value'];
            break;
        }
        $i++;
    }
}

// calculate and push item certainty average
foreach ((array) $certaintiesitem as $certainty) {
    $value = $certainty['value'] * 10 / $certainty['count'];
    if (floor($value) > $certaintyMax) $certaintyMax = floor($value);
    if (floor($value) < $certaintyMin) $certaintyMin = floor($value);

		// crude error catch here, maybe should not be calculating to begin with unless called in qLimit?
		if (!isset($unqprobabilityId)) continue;

    $result[] = array('type' => $certainty['type'], 'visId' => $certainty['visId'], 'itemId' => $certainty['itemId'], 'attrId' => $unqprobabilityId, 'value' => floor($value));
    if ($certainty['active']) {
        $certaintiesvis[$certainty['visId']]['count']++;
        $certaintiesvis[$certainty['visId']]['value'] += $value;
    }
}

// calculate and push value certainty average
$certaintysummary = 0;
$certaintyviscount = 0;
foreach ((array) $visions as $visn) {
    if ($certaintiesvis[$visn['itemId']]['count'] == 0) break;
    $value = $certaintiesvis[$visn['itemId']]['value'] / $certaintiesvis[$visn['itemId']]['count'];
		// crude error catch here, maybe should not be calculating to begin with unless called in qLimit?
		if (!isset($unqprobabilityId)) continue;
    $result[] = array('type' => 'v', 'visId' => $visn['itemId'], 'attrId' => $unqprobabilityId, 'value' => floor($value));

    // if vision is live then increment summary value sums
    if ($visn['isSomeday'] !== 'y' && $visn['dateCompleted'] == '') {
        $certaintysummary += $value;
        $certaintyviscount++;
    }
}

// push summary value certainty onto final result
// crude error catch here, maybe should not be calculating to begin with unless called in qLimit?
if (isset($unqprobabilityId)) {
	if ($certaintyviscount > 0) $result[] = array('type' => 'x', 'attrId' => $unqprobabilityId, 'form' => 'a', 'value' => $certaintyMax);
	if ($certaintyviscount > 0) $result[] = array('type' => 'x', 'attrId' => $unqprobabilityId, 'form' => 'b', 'value' => $certaintyMin);
	//if ($certaintyviscount > 0) $result[] = array('type' => 'x', 'attrId' => $unqprobabilityId, 'form' => 'a', 'value' => floor($certaintysummary / $certaintyviscount));
}

// result

// remove empty elements from result array
$result = array_values($result);
// stop query time test
$endtime = microtime(true);
$duration = round($endtime - $starttime,1);
// save query time test
$result[] = array('type' => 't', 'duration' => $duration);
// pass result array to ajax listener
echo json_encode($result);

// Kelty's uniformity
function uniformity($x){
    // reset keys
    $x = array_values($x);

		if (empty($x) || count($x) == 0 || array_sum($x) == 0) return 0;

    $length = count($x);
    $mean = (float) array_sum($x) / $length;

    $a = (float) 0;
    $a2 = (float) 0;

    for ($i=0; $i<$length; $i++)
    {
        $a = (float) $x[$i] - $mean;
        $a2 = $a2 + (float) pow($a,2);
    }

    $uniformity = 1 - $a2 / (pow($mean,2) * $length);

    return $uniformity;
}

// Pearson's correlation
function corr($x, $y){
    // reset keys
    $x = array_values($x);
    $y = array_values($y);

    $length = count($x);
    $mean1 = (float) array_sum($x) / $length;
    $mean2 = (float) array_sum($y) / $length;

    $a = (float) 0;
    $b = (float) 0;
    $axb = (float) 0;
    $a2 = (float) 0;
    $b2 = (float) 0;

    for ($i=0; $i<$length; $i++)
    {
        $a = (float) $x[$i] - $mean1;
        $b = (float) $y[$i] - $mean2;
        $axb = $axb + ($a * $b);
        $a2 = $a2 + pow($a,2);
        $b2 = $b2 + pow($b,2);
    }

		if ($a2 > 0 && $b2 > 0) {
    	$corr = $axb / sqrt($a2*$b2);
		} else {
			$corr = (float) 0;
		}

    return $corr;
}

// standard deviation
function stDev ($arr)
    {
        $num_of_elements = count($arr);

        $variance = (float) 0.0;

        // calculating mean using array_sum() method
        $average = (float) array_sum($arr) / $num_of_elements;

        foreach($arr as $i)
        {
            // sum of squares of differences between
            // all numbers and means.
            $variance += pow(((float)$i - $average), 2);
        }

        return (float) sqrt($variance / $num_of_elements);
    }

$debugSave = 'vsumvisn';

// debug scripts
switch ($debugSave) {
    case 'yrsval':
        file_put_contents ('_response.txt',
            '$yrsval: ' . print_r($yrsval,true) . "\n"
            );
        break;
    case 'vsumvisn':
        file_put_contents ('_response.txt',
            '$valuessummaryn: ' . print_r($valuessummaryn,true) . "\n" .
            '$valuessumvisn: ' . print_r($valuessumvisn,true) . "\n"
            );
        break;
    case 'certainties':
        file_put_contents ('_response.txt',
            '$certaintiesitem: ' . print_r($certaintiesitem,true) . "\n" .
            '$certaintiesitemtemp: ' . print_r($certaintiesitemtemp,true) . "\n" .
            '$certainties: ' . print_r($certainties,true) . "\n"
            );
        break;
    case 'visions':
        file_put_contents ('_response.txt',
            '$visions: ' . print_r($visions,true) . "\n"
            );
        break;
    case 'result':
        file_put_contents ('_response.txt',
            '$result: ' . print_r($result,true) . "\n"
            );
        break;
    case 'valuessumhrs':
        file_put_contents ('_response.txt',
            '$unqtimelineIds[0]: ' . $unqtimelineIds[0] . "\n" .
            '$valuessum: ' . print_r($valuessumvishours,true) . "\n"
            );
        break;
    case 'contextssum':
        file_put_contents ('_response.txt',
            '$valParId: ' . $cntxtParId . "\n" .
            '$unqvaluessumId: ' . $unqcontextssumId . "\n" .
            '$scoreqIds: ' . print_r($scoreqIds,true) . "\n" .
            '$valuessum: ' . print_r($contextssum,true) . "\n"
            );
        break;
    case 'valuessum':
        file_put_contents ('_response.txt',
            '$valParId: ' . $valParId . "\n" .
            '$unqvaluessumId: ' . $unqvaluessumId . "\n" .
            '$valuessum: ' . print_r($valuessum,true) . "\n"
            );
        break;
    case 'optimise':
        file_put_contents ('_response.txt',
            '$scoreqIds: ' . print_r($scoreqIds,true) . "\n" .
            '$scoreweights: ' . print_r($scoreweights,true) . "\n" .
            '$scoreopt: ' . print_r($scoreopt,true) . "\n" .
            '$scores: ' . print_r($scores,true) . "\n"
            );
        break;
    case 'timeline':
        file_put_contents ('_response.txt',
            '$yearqId: ' . $yearqId . "\n" .
            '$brainlessId: ' . $brainlessId . "\n" .
            '$hoursId: ' . $hoursId . "\n" .
            '$unqhoursIds: ' . print_r($unqhoursIds,true) . "\n" .
            //'$sumHours: ' . print_r($sumHours,true) . "\n" .
            '$unqtimelineIds: ' . print_r($unqtimelineIds,true) . "\n" .
            '$skipqIds: ' . print_r($skipqIds,true) . "\n" .
            'test : ' . "\n" .
            '$timeline: ' . print_r($timeline,true) . "\n"
            );
        break;
    case 'tlinevis':
        file_put_contents ('_response.txt',
            '$tlineviskey: ' . print_r($tlineviskey,true) . "\n" .
            '$tlinevis: ' . print_r($tlinevis,true)
            );
        break;
    case 'childitems':
        file_put_contents ('_response.txt',
            'attr count: ' . count($attributes) .
            ', item count: ' . count($items) .
            ', $itemVar[id]: ' . $itemVar['id'] .
            ', $item[itemId]: ' . $item['itemId'] .
            ', $itemVar[type]: ' . $itemVar['type'] .
            ', $item[itemType]: ' . $item['itemType'] .
            PHP_EOL, FILE_APPEND
            );
        break;
    case 'inactiveitems':
        file_put_contents ('_response.txt',
            'attr[qId]: ' . $attr['qId'] .
            ', value: ' . $value .
            ', someday: ' . $someday .
            ', complete: ' . $complete .
            ', output1: ' . $output1 .
            ', output2: ' . $output2 .
            PHP_EOL . PHP_EOL, FILE_APPEND);
        break;
}

?>
