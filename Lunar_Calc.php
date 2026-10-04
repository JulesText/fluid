<?php

// Return the Julian day for a Unix timestamp.
function lunarJulianDay($timestamp)
{
    return ($timestamp / 86400) + 2440587.5;
}

// Return the Julian ephemeris day of lunation number $k.
// This is the Meeus approximation for a new moon and needs no external data.
function lunarNewMoon($k)
{
    $t = $k / 1236.85;
    $t2 = $t * $t;
    $t3 = $t2 * $t;
    $t4 = $t3 * $t;

    $jde = 2451550.09765
        + 29.530588853 * $k
        + 0.0001337 * $t2
        - 0.000000150 * $t3
        + 0.00000000073 * $t4;

    $m = deg2rad(2.5534 + 29.10535670 * $k - 0.0000014 * $t2);
    $mPrime = deg2rad(201.5643 + 385.81693528 * $k + 0.0107582 * $t2);
    $f = deg2rad(160.7108 + 390.67050284 * $k - 0.0016118 * $t2);
    $omega = deg2rad(124.7746 - 1.56375580 * $k + 0.0020672 * $t2);
    $e = 1 - 0.002516 * $t - 0.0000074 * $t2;

    $correction = -0.40720 * sin($mPrime)
        + 0.17241 * $e * sin($m)
        + 0.01608 * sin(2 * $mPrime)
        + 0.01039 * sin(2 * $f)
        + 0.00739 * $e * sin($mPrime - $m)
        - 0.00514 * $e * sin($mPrime + $m)
        + 0.00208 * $e * $e * sin(2 * $m)
        - 0.00111 * sin($mPrime - 2 * $f)
        - 0.00057 * sin($mPrime + 2 * $f)
        + 0.00056 * $e * sin(2 * $mPrime + $m)
        - 0.00042 * sin(3 * $mPrime)
        + 0.00042 * $e * sin($m + 2 * $f)
        + 0.00038 * $e * sin($m - 2 * $f)
        - 0.00024 * $e * sin(2 * $mPrime - $m)
        - 0.00017 * sin($omega)
        - 0.00007 * sin($mPrime + 2 * $m)
        + 0.00004 * sin(2 * $mPrime - 2 * $f)
        + 0.00004 * sin(3 * $m)
        + 0.00003 * sin($mPrime + $m - 2 * $f)
        + 0.00003 * sin(2 * $mPrime + 2 * $f)
        - 0.00003 * sin($mPrime + $m + 2 * $f)
        + 0.00003 * sin($mPrime - $m + 2 * $f)
        - 0.00002 * sin($mPrime - $m - 2 * $f)
        - 0.00002 * sin(3 * $mPrime + $m)
        + 0.00002 * sin($mPrime + $m - 2 * $f)
        + 0.00002 * sin($mPrime - $m + 2 * $f)
        - 0.00002 * sin(3 * $mPrime - $m);

    return $jde + $correction;
}

// Calculate the current lunar age and cycle without an external service.
$date = time();
$julianDay = lunarJulianDay($date);
$lunation = floor(($julianDay - 2451550.09765) / 29.530588853);
$newMoon = lunarNewMoon($lunation);

if ($newMoon > $julianDay) {
    $lunation--;
    $newMoon = lunarNewMoon($lunation);
} elseif (lunarNewMoon($lunation + 1) <= $julianDay) {
    $lunation++;
    $newMoon = lunarNewMoon($lunation);
}

$nextNewMoon = lunarNewMoon($lunation + 1);
$currentDay = $julianDay - $newMoon;
$cycleLength = $nextNewMoon - $newMoon;
$currentProgress = $currentDay / $cycleLength;

// Six checklist windows centered on the six broad lunar phases. The quarter
// points remain at 0.25, 0.50, and 0.75 of the synodic cycle.
if ($currentProgress < (1 / 12) || $currentProgress >= (11 / 12)) {
    $lunarChecklistId = 46;
    $lunarChecklistName = 'New Moon';
} elseif ($currentProgress < (1 / 4)) {
    $lunarChecklistId = 47;
    $lunarChecklistName = 'Waxing Crescent';
} elseif ($currentProgress < (5 / 12)) {
    $lunarChecklistId = 48;
    $lunarChecklistName = 'Waxing Gibbous';
} elseif ($currentProgress < (7 / 12)) {
    $lunarChecklistId = 49;
    $lunarChecklistName = 'Full Moon';
} elseif ($currentProgress < (3 / 4)) {
    $lunarChecklistId = 50;
    $lunarChecklistName = 'Waning Gibbous';
} else {
    $lunarChecklistId = 51;
    $lunarChecklistName = 'Waning Crescent';
}

// Global vars:
$current = $currentProgress;
$day = floor($currentDay) + 1;
$lunarCycleDays = ceil($cycleLength);
$moonTerminatorRadius = abs(cos(2 * pi() * $currentProgress)) * 45;
if ($currentProgress <= 0.5) {
    $moonLightPath = 'M 50 5 A 45 45 0 0 0 50 95 A '
        . number_format($moonTerminatorRadius, 2, '.', '')
        . ' 45 0 0 1 50 5 Z';
} else {
    $moonLightPath = 'M 50 5 A 45 45 0 0 1 50 95 A '
        . number_format($moonTerminatorRadius, 2, '.', '')
        . ' 45 0 0 0 50 5 Z';
}
$month = fmod($lunation, 13);
if ($month < 0) {
    $month += 13;
}

// End lunar calculation.
