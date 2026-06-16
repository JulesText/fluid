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

///////////////////
// populate vars //
///////////////////

//
/* Impact Scale */
//

create_variable_set('extinction_reduction', 771, 772);
create_variable_set('global_economy', 773, 774);
create_variable_set('poorest_income', 775, 776);
create_variable_set('healthy_years', 777, 778);

//
/* Impact Neglectedness */
//

create_variable_set('annual_spending', 781, 782);
create_variable_set('staff_numbers', 783, 784);
create_variable_set('supporter_numbers', 785, 786);

//
/* Impact Solvability */
//

create_variable_set('impact_solvability', 791, 792);

//
/* Personal Fit */
//

create_variable_set('desire_repeat', 51, 52, 3);
create_variable_set('desire_incomp', 53, 54, 3);
create_variable_set('align_abilities', 853, 854, 3);
create_variable_set('relative_ability', 855, 856, 3);

//
/* Career Capital */
//

create_variable_set('build_skills', 722, 723, 3);
create_variable_set('connections', 724, 725, 3);
create_variable_set('credentials', 726, 727, 3);

//
/* Conditions Locality */
//

create_variable_set('desirable_location', 831, 832, 3);
create_variable_set('allergy_tolerable', 833, 834, 3);
create_variable_set('fast_transport', 835, 836, 3);

//
/* Conditions Income */
//

create_variable_set('fair_pay', 841, 842, 3);
create_variable_set('job_security', 843, 844, 3);

//
/* Conditions Needs */
//

create_variable_set('manageable_hours', 822, 823, 3);

//
/* Conditions Sum */
//

create_variable_set('reasonable_demand', 812, 813, 3);
create_variable_set('harmony_values', 814, 815, 3);
create_variable_set('personal_life', 816, 817, 3);

//
/* Sum-Cr */
//

create_variable_set('unqyears', 521, 522);
if (is_null($unqyears)) $unqyears = 1;
create_variable_set('unqhours', 511, 512);

//////////////////
// calculations //
//////////////////

$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

//
/* Impact */
//

// select impact scale score, ignore null
$arr = clean_array([$extinction_reduction, $global_economy, $poorest_income, $healthy_years]);
$impact_scale = (count($arr) > 0) ? max($arr) : NULL;
$impact_scale_w = clean_mean([$extinction_reduction_w, $global_economy_w, $poorest_income_w, $healthy_years_w]);
// select impact neglectedness score
$arr = clean_array([$annual_spending, $staff_numbers, $supporter_numbers]);
$impact_neglectedness = (count($arr) > 0) ? min($arr) : NULL;
$impact_neglectedness_w = clean_mean([$annual_spending_w, $staff_numbers_w, $supporter_numbers_w]);
// calculate score (max raw 21 standardise to 99 * 3 units to sum not average)
$vars = ['impact_scale', 'impact_neglectedness', 'impact_solvability'];
calculate_score('impact', 761, $vars, 21, 99 * 3); // save impact Score unqimpactsum

// file_put_contents ('_response.txt', PHP_EOL . '$extinction_reduction = ' . $extinction_reduction);
// file_put_contents ('_response.txt', PHP_EOL . '$global_economy = ' . $global_economy, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$poorest_income = ' . $poorest_income, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$healthy_years = ' . $healthy_years, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$annual_spending = ' . $annual_spending, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$staff_numbers = ' . $staff_numbers, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$supporter_numbers = ' . $supporter_numbers, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$impact_solvability = ' . $impact_solvability, FILE_APPEND);

// calculate certainty
$idx = array_search($impact_scale, [$extinction_reduction, $global_economy, $poorest_income, $healthy_years]);
$impact_scale_p = [$extinction_reduction_p, $global_economy_p, $poorest_income_p, $healthy_years_p][$idx];
$idx = array_search($impact_scale, [$annual_spending, $staff_numbers, $supporter_numbers]);
$impact_neglectedness_p = [$annual_spending_p, $staff_numbers_p, $supporter_numbers_p][$idx];
$impact_score_p = clean_mean([$impact_scale_p, $impact_neglectedness_p, $impact_solvability_p]);

//
/* Personal Fit */
//

// calculate score (max raw each +/- 64 standardise to 99)
$vars = ['desire_repeat', 'desire_incomp', 'align_abilities', 'relative_ability'];
calculate_score('personal_fit', 857, $vars, 64, 99); // save Score unqcareerfitsum

// calculate certainty
$personal_fit_score_p = clean_mean([$desire_repeat_p, $desire_incomp_p, $align_abilities_p, $relative_ability_p]);

//
/* Career Capital */
//

// calculate score (max raw each +/- 64 standardise to 99)
$vars = ['build_skills', 'connections', 'credentials'];
calculate_score('career_capital', 721, $vars, 64, 99); // save Score unqcareercapsum

// calculate certainty
$career_capital_score_p = clean_mean([$build_skills_p, $connections_p, $credentials_p]);

//
/* Conditions Locality */
//

// calculate score (max raw each +/- 64 standardise to 99)
$vars = ['desirable_location', 'allergy_tolerable', 'fast_transport'];
calculate_score('conditions_locality', 824, $vars, 64, 99); // save Score unqcareercondlocality

// calculate certainty
$conditions_locality_p = clean_mean([$desirable_location_p, $allergy_tolerable_p, $fast_transport_p]);

//
/* Conditions Income */
//

// calculate score (max raw each +/- 64 standardise to 99)
$vars = ['fair_pay', 'job_security'];
calculate_score('conditions_income', 825, $vars, 64, 99); // save Score unqcareercondincome

// calculate certainty
$conditions_income_p = clean_mean([$fair_pay_p, $job_security_p]);

//
/* Conditions Needs */
//

// calculate score (max raw each +/- 99 standardise to 99)
if (!is_null($manageable_hours)) $manageable_hours *= 99/64;
$vars = ['manageable_hours', 'conditions_locality_score', 'conditions_income_score'];
calculate_score('conditions_needs', 821, $vars, 99, 99); // save score unqcareercondneeds

// calculate certainty
$conditions_needs_p = clean_mean([$manageable_hours_p, $conditions_locality_p, $conditions_income_p]);

//
/* Conditions Sum */
//

// calculate score (max raw each +/- 64 standardise to 99)
if (!is_null($conditions_needs_score)) $conditions_needs_score *= 64/99;
$vars = ['reasonable_demand', 'harmony_values', 'personal_life', 'conditions_needs_score'];
calculate_score('conditions_sum', 811, $vars, 64, 99); // save Score unqcareercondsum

// calculate certainty
$conditions_sum_p = clean_mean([$reasonable_demand_p, $harmony_values_p, $personal_life_p, $conditions_needs_p]);

//
/* Career Sum Score */
//

// calculate overall Certainty
$certainty = clean_mean([$impact_score_p, $personal_fit_score_p, $career_capital_score_p, $conditions_sum_p]);
$_POST["id4"] = 611; // Certainty unqprobability
$_POST["updVal"] = intval($certainty);
include('matrixSave.php');

// calculate career score
// we've already applied certainty adjustments so do not readjust for certainty
// Career Sum Score = Personal Fit * (Impact + Career Capital + Conditions)
$career_sum = ($personal_fit_score / 99) * clean_mean([$impact_score / $unqyears, $career_capital_score, $conditions_sum_score]);
$_POST["id4"] = 711; // unqcareersum
$_POST["updVal"] = intval($career_sum);
include('matrixSave.php');

// file_put_contents ('_response.txt', PHP_EOL . '$personal_fit_score = ' . $personal_fit_score);
// file_put_contents ('_response.txt', PHP_EOL . '$impact_score = ' . $impact_score, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$unqyears = ' . $unqyears, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$career_capital_score = ' . $career_capital_score, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$conditions_sum_score = ' . $conditions_sum_score, FILE_APPEND);
// file_put_contents ('_response.txt', PHP_EOL . '$career_sum = ' . $career_sum, FILE_APPEND);

// calculate effort
// we've already applied certainty adjustments so do not readjust for certainty
$career_hours = $unqhours * $unqyears;
$_POST["id4"] = 712; // unqcareerhrs
$_POST["updVal"] = intval($career_hours);
include('matrixSave.php');

//
// destroy connection
//

$db = NULL;

?>
