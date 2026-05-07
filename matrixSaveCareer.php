<?php
require_once('headerDB.inc.php');

// get weights
$values['qQuery'] = 1;
$values['qValue'] = 1;
$values['qSearch'] = 'disp';
$values['qNeedle'] = 'e'; // for career
$result = query("getqualities",$config,$values,$sort);
$weights = array();
foreach ($result as $row)
    $weights[$row['qId']] = $row['weight'];

// check if Career flag true to recalculate career
// this code file is called from matrixAjax.js for some non-specifically career items like effort/year
// so only calculate Career if it's for a career item
$_POST["id4"] = 27;
include('matrixQuery.php');
if (!isset($value) || $value !== 'y') die();

//
// populate vars
//

/* Impact Scale */

$_POST["id4"] = 771; include('matrixQuery.php');
$extinction_reduction = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 772; include('matrixQuery.php');
$extinction_reduction_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($extinction_reduction_p > 0) $extinction_reduction *= $extinction_reduction_p / 10;

$_POST["id4"] = 773; include('matrixQuery.php');
$global_economy = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 774; include('matrixQuery.php');
$global_economy_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $global_economy *= $global_economy_p / 10;

$_POST["id4"] = 775; include('matrixQuery.php');
$poorest_income = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 776; include('matrixQuery.php');
$poorest_income_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $poorest_income *= $poorest_income_p / 10;

$_POST["id4"] = 777; include('matrixQuery.php');
$healthy_years = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 778; include('matrixQuery.php');
$healthy_years_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $healthy_years *= $healthy_years_p / 10;

/* Impact Neglectedness */

$_POST["id4"] = 781; include('matrixQuery.php');
$annual_spending = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 782; include('matrixQuery.php');
$annual_spending_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $annual_spending *= $annual_spending_p / 10;

$_POST["id4"] = 783; include('matrixQuery.php');
$staff_numbers = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 784; include('matrixQuery.php');
$staff_numbers_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $staff_numbers *= $staff_numbers_p / 10;

$_POST["id4"] = 785; include('matrixQuery.php');
$supporter_numbers = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 786; include('matrixQuery.php');
$supporter_numbers_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $supporter_numbers *= $supporter_numbers_p / 10;

/* Impact Solvability */

$_POST["id4"] = 791; include('matrixQuery.php');
$impact_solvability = (isset($value) && ctype_digit($value)) ? intval($value) : 0;
$_POST["id4"] = 792; include('matrixQuery.php');
$impact_solvability_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($ > 0) $impact_solvability *= $impact_solvability_p / 10;

/* Personal Fit */

$_POST["id4"] = 51; include('matrixQuery.php');
$desire_repeat = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$desire_repeat_w = $weights[51];
$_POST["id4"] = 52; include('matrixQuery.php');
$desire_repeat_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($desire_repeat_p > 0) $desire_repeat *= $desire_repeat_p / 10;

$_POST["id4"] = 53; include('matrixQuery.php');
$desire_incomp = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$desire_incomp_w = $weights[53];
$_POST["id4"] = 54; include('matrixQuery.php');
$desire_incomp_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($desire_incomp_p > 0) $desire_incomp *= $desire_incomp_p / 10;

$_POST["id4"] = 853; include('matrixQuery.php');
$align_abilities = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$align_abilities_w = $weights[853];
$_POST["id4"] = 854; include('matrixQuery.php');
$align_abilities_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($align_abilities_p > 0) $align_abilities *= $align_abilities_p / 10;

/* Career Capital */

$_POST["id4"] = 722; include('matrixQuery.php');
$build_skills = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$build_skills_w = $weights[722];
$_POST["id4"] = 723; include('matrixQuery.php');
$build_skills_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($build_skills_p > 0) $build_skills *= $build_skills_p / 10;

$_POST["id4"] = 724; include('matrixQuery.php');
$connections = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$connections_w = $weights[724];
$_POST["id4"] = 725; include('matrixQuery.php');
$connections_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($connections_p > 0) $connections *= $connections_p / 10;

$_POST["id4"] = 726; include('matrixQuery.php');
$credentials = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$credentials_w = $weights[726];
$_POST["id4"] = 727; include('matrixQuery.php');
$credentials_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($credentials_p > 0) $credentials *= $credentials_p / 10;

/* Conditions Locality */

$_POST["id4"] = 831; include('matrixQuery.php');
$desirable_location = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$desirable_location_w = $weights[831];
$_POST["id4"] = 832; include('matrixQuery.php');
$desirable_location_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($desirable_location_p > 0) $desirable_location *= $desirable_location_p / 10;

$_POST["id4"] = 833; include('matrixQuery.php');
$allergy_tolerable = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$allergy_tolerable_w = $weights[833];
$_POST["id4"] = 834; include('matrixQuery.php');
$allergy_tolerable_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($allergy_tolerable_p > 0) $allergy_tolerable *= $allergy_tolerable_p / 10;

$_POST["id4"] = 835; include('matrixQuery.php');
$fast_transport = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$fast_transport_w = $weights[835];
$_POST["id4"] = 836; include('matrixQuery.php');
$fast_transport_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($fast_transport_p > 0) $fast_transport *= $fast_transport_p / 10;

/* Conditions Income */

$_POST["id4"] = 841; include('matrixQuery.php');
$fair_pay = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$fair_pay_w = $weights[841];
$_POST["id4"] = 842; include('matrixQuery.php');
$fair_pay_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($fair_pay_p > 0) $fair_pay *= $fair_pay_p / 10;

$_POST["id4"] = 843; include('matrixQuery.php');
$job_security = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$job_security_w = $weights[843];
$_POST["id4"] = 844; include('matrixQuery.php');
$job_security_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($job_security_p > 0) $job_security *= $job_security_p / 10;

/* Conditions Needs */

$_POST["id4"] = 822; include('matrixQuery.php');
$manageable_hours = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$manageable_hours_w = $weights[822];
$_POST["id4"] = 823; include('matrixQuery.php');
$manageable_hours_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($manageable_hours_p > 0) $manageable_hours *= $manageable_hours_p / 10;

/* Conditions Sum */

$_POST["id4"] = 812; include('matrixQuery.php');
$reasonable_demand = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$reasonable_demand_w = $weights[812];
$_POST["id4"] = 813; include('matrixQuery.php');
$reasonable_demand_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($reasonable_demand_p > 0) $reasonable_demand *= $reasonable_demand_p / 10;

$_POST["id4"] = 814; include('matrixQuery.php');
$harmony_values = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$harmony_values_w = $weights[814];
$_POST["id4"] = 815; include('matrixQuery.php');
$harmony_values_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($harmony_values_p > 0) $harmony_values *= $harmony_values_p / 10;

$_POST["id4"] = 816; include('matrixQuery.php');
$personal_life = ((isset($value) && ctype_digit($value)) ? intval($value) : 0) ** 3;
$personal_life_w = $weights[816];
$_POST["id4"] = 817; include('matrixQuery.php');
$personal_life_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($personal_life_p > 0) $personal_life *= $personal_life_p / 10;

/* Sum-Cr */

$_POST["id4"] = 521; include('matrixQuery.php');
$unqyears = (isset($value) && ctype_digit($value)) ? intval($value) : 1;
$_POST["id4"] = 522; include('matrixQuery.php');
$unqyears_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($unqyears_p > 0) $unqyears *= $unqyears_p / 10;

$_POST["id4"] = 511; include('matrixQuery.php');
$unqhours = (isset($value) && ctype_digit($value)) ? intval($value) : 2000;
$_POST["id4"] = 512; include('matrixQuery.php');
$unqhours_p = (isset($value) && ctype_digit($value)) ? $value + 1 : 0;
if ($unqhours_p > 0) $unqhours *= $unqhours_p / 10;


//
// calculations
//

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

/* Impact */

// select impact scale score
$impact_scale = max($extinction_reduction, $global_economy, $poorest_income, $healthy_years);
// select impact neglectedness score
$impact_neglectedness = min($annual_spending, $staff_numbers, $supporter_numbers);
// calculate score, save
$impact_score = intval($impact_scale + $impact_neglectedness + $impact_solvability);
$_POST["id4"] = 761; // Impact Score unqimpactsum
$_POST["updVal"] = $impact_score;
include('matrixSave.php');
// calculate certainty
$idx = array_search($impact_scale, [$extinction_reduction, $global_economy, $poorest_income, $healthy_years]);
$impact_scale_p = [$extinction_reduction_p, $global_economy_p, $poorest_income_p, $healthy_years_p][$idx];
$idx = array_search($impact_scale, [$annual_spending, $staff_numbers, $supporter_numbers]);
$impact_neglectedness_p = [$annual_spending_p, $staff_numbers_p, $supporter_numbers_p][$idx];
$arr = [$impact_scale_p, $impact_neglectedness_p, $impact_solvability_p];
$arr = array_filter($arr, function ($val) { return $val !== 0; }); // assume values of 0 are set by default, remove them
if (count($arr) > 0) $impact_score_p = array_sum($arr) / count($arr);
else $impact_score_p = NULL;

/* Personal Fit */

$personal_fit_score_p

/* Career Capital */

$career_capital_score_p

/* Conditions Locality */

$conditions_locality = mean($desirable_location, $allergy_tolerable, $fast_transport);
// range is +/- 64, standardise
$conditions_locality *= 100 / 64;

// calculate Conditions Score
$sum = ;
$_POST["id4"] = 824; // Conditions Locality Score unqcareercondlocality
$_POST["updVal"] = intval($sum);
include('matrixSave.php');
// calculate p
$conditions_locality_p

/* Conditions Income */

/* Conditions Needs */

/* Conditions Sum */

$sum = ;
$_POST["id4"] = 811; // Conditions Score unqcareercondsum
$_POST["updVal"] = intval($sum);
include('matrixSave.php');
// calculate p
$conditions_sum_p

/* Sum-Cr */

// calculate overall Certainty
$certainty = mean($impact_score_p, $personal_fit_score_p, $career_capital_score_p, $conditions_sum_p);
$_POST["id4"] = 611; // Certainty unqprobability
$_POST["updVal"] = intval($certainty);
include('matrixSave.php');

// calculate Sum-Cr / Year
...
$_POST["id4"] = 711; // Sum-Cr / Year (unqcareersum)
$_POST["updVal"] = intval();
include('matrixSave.php');

// calculate Sum-Cr / Hrs
...
$_POST["id4"] = 712; // Sum-Cr / Hrs (unqcareersumhrs)
$_POST["updVal"] = intval();
include('matrixSave.php');


// destroy connection

$db = NULL;

?>
