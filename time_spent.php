<?php

require_once 'header.php';

function timeSpentFormatDuration($seconds)
{
    $seconds = max(0, (int) $seconds);
    $hours = (int) floor($seconds / 3600);
    $minutes = (int) floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

function timeSpentFormatHours($seconds)
{
    return number_format(max(0, (int) $seconds) / 3600, 0);
}

function timeSpentFormatDate($date)
{
    $formattedDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);

    return $formattedDate === false ? (string) $date : $formattedDate->format('D d/m');
}

function timeSpentFormatPeriodEnd($date)
{
    $formattedDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);

    return $formattedDate === false ? (string) $date : $formattedDate->modify('-1 day')->format('D d/m');
}

function timeSpentRedirect($message, $activityId = null)
{
    $_SESSION['message'][] = $message;
    $redirectUrl = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $redirectUrl = is_string($redirectUrl) && $redirectUrl !== '' ? $redirectUrl : 'time_spent.php';
    if ($activityId !== null) {
        $redirectUrl .= '?activity=' . (int) $activityId;
    }
    nextScreen($redirectUrl);
}

function timeSpentEscape($value, $config)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, $config['charset']);
}

function timeSpentPeriodStart(DateTimeImmutable $date)
{
    $daysSinceSaturday = ((int) $date->format('N') + 1) % 7;

    return $date->setTime(0, 0, 0)->modify('-' . $daysSinceSaturday . ' days');
}

function timeSpentPeriodStatus($seconds, $lowerLimitHours, $upperLimitHours, $complete)
{
    $hours = (float) $seconds / 3600;
    if ($hours > (float) $upperLimitHours) {
        return 'Over upper limit';
    }
    if ($complete && $hours < (float) $lowerLimitHours) {
        return 'Below lower limit';
    }
    return $complete ? 'Complete' : '';
}

function timeSpentDaysTracked($startDate, DateTimeImmutable $now)
{
    $start = new DateTimeImmutable($startDate . ' 00:00:00', $now->getTimezone());
    $today = $now->setTime(0, 0, 0);

    return max(1, ((int) $start->diff($today)->format('%a')) + 1);
}

if (empty($_SESSION['time_spent_csrf'])) {
    $_SESSION['time_spent_csrf'] = bin2hex(random_bytes(32));
}

$timezone = new DateTimeZone($config['timezone']);
$now = new DateTimeImmutable('now', $timezone);
$periodStart = timeSpentPeriodStart($now);
$periodEnd = $periodStart->modify('+14 days');

$tablesReady = query('ensuretimespentactivities', $config) !== false;
if ($tablesReady) {
    $tablesReady = query('ensuretimespentactivityperiods', $config) !== false;
}
$activities = $tablesReady ? query('selecttimespentactivities', $config) : false;
if ($activities === 0) {
    query('newtimespent', $config, array(
        'activityName' => 'Activity',
        'startDate' => $now->format('Y-m-d'),
        'lowerLimitHours' => 10,
        'upperLimitHours' => 20,
        'periodStart' => $periodStart->format('Y-m-d'),
        'periodEnd' => $periodEnd->format('Y-m-d')
    ));
    $activities = query('selecttimespentactivities', $config);
}

$selectedActivityId = filter_var($_GET['activity'], FILTER_VALIDATE_INT);
$selectedActivityId = $selectedActivityId === false ? null : (int) $selectedActivityId;
$runningActivityId = null;
if (is_array($activities)) {
    foreach ($activities as $activity) {
        if ($activity['startedAt'] !== null) {
            $runningActivityId = (int) $activity['id'];
            break;
        }
    }
}
if (
    $selectedActivityId === null
    || !is_array($activities)
    || !array_reduce(
        $activities,
        function ($found, $activity) use ($selectedActivityId) {
            return $found || (int) $activity['id'] === $selectedActivityId;
        },
        false
    )
) {
    $selectedActivityId = is_array($activities) && count($activities) > 0
        ? (int) $activities[0]['id']
        : null;
}
$_SESSION['time_spent_activity_id'] = $selectedActivityId;

$result = $tablesReady && $selectedActivityId !== null
    ? query('selecttimespent', $config, array('id' => $selectedActivityId))
    : false;
if (!$tablesReady || $result === false) {
    $timer = false;
    $periods = false;
    $_SESSION['message'][] = 'The time tracker tables are not available. Import the time_spent tables from db.sql.';
} else {
    $timer = is_array($result) && count($result) > 0 ? $result[0] : false;
    $periods = $timer === false
        ? array()
        : query('gettimespentperiods', $config, array('activityId' => $selectedActivityId));
}

if ($timer !== false && $timer['startedAt'] === null) {
    $timerPeriodEnd = new DateTimeImmutable($timer['periodEnd'] . ' 00:00:00', $timezone);
    while ($now >= $timerPeriodEnd) {
        $values = array(
            'periodStart' => $timer['periodStart'],
            'periodEnd' => $timer['periodEnd'],
            'activityId' => $selectedActivityId,
            'totalSeconds' => (int) $timer['totalSeconds'],
            'lowerLimitHours' => $timer['lowerLimitHours'],
            'upperLimitHours' => $timer['upperLimitHours'],
            'status' => timeSpentPeriodStatus(
                $timer['totalSeconds'],
                $timer['lowerLimitHours'],
                $timer['upperLimitHours'],
                true
            ),
            'notes' => ''
        );
        query('newtimespentperiod', $config, $values);

        $nextStart = new DateTimeImmutable($timer['periodEnd'] . ' 00:00:00', $timezone);
        $values = array(
            'id' => $selectedActivityId,
            'periodStart' => $nextStart->format('Y-m-d'),
            'periodEnd' => $nextStart->modify('+14 days')->format('Y-m-d')
        );
        query('rollovertimespent', $config, $values);
        $result = query('selecttimespent', $config, array('id' => $selectedActivityId));
        $timer = is_array($result) && count($result) > 0 ? $result[0] : false;
        if ($timer === false) {
            break;
        }
        $timerPeriodEnd = new DateTimeImmutable($timer['periodEnd'] . ' 00:00:00', $timezone);
    }
    $periods = query('gettimespentperiods', $config, array('activityId' => $selectedActivityId));
}

if (
    $timer !== false
    && (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        || isset($_GET['toggle'])
    )
) {
    $isUrlToggle = $_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['toggle']);
    if (
        !$isUrlToggle
        && (
            empty($_POST['csrf'])
            || !hash_equals($_SESSION['time_spent_csrf'], (string) $_POST['csrf'])
        )
    ) {
        http_response_code(403);
        exit('Invalid time tracker request.');
    }

    if ($isUrlToggle) {
        $_POST['action'] = 'toggle';
    }

    $action = isset($_POST['action']) ? (string) $_POST['action'] : 'toggle';
    if ($action === 'create-activity') {
        $activityName = trim((string) $_POST['newActivityName']);
        if ($activityName === '' || strlen($activityName) > 255) {
            timeSpentRedirect('Enter an activity name up to 255 characters.', $selectedActivityId);
        }
        query('newtimespent', $config, array(
            'activityName' => $activityName,
            'startDate' => $now->format('Y-m-d'),
            'lowerLimitHours' => 10,
            'upperLimitHours' => 20,
            'periodStart' => $periodStart->format('Y-m-d'),
            'periodEnd' => $periodEnd->format('Y-m-d')
        ));
        $selectedActivityId = (int) $GLOBALS['lastinsertid'];
        $_SESSION['time_spent_activity_id'] = $selectedActivityId;
        timeSpentRedirect('Activity created.', $selectedActivityId);
    }
    if ($action === 'save-settings') {
        $lowerLimitHours = filter_var($_POST['lowerLimitHours'], FILTER_VALIDATE_FLOAT);
        $upperLimitHours = filter_var($_POST['upperLimitHours'], FILTER_VALIDATE_FLOAT);
        if (
            $lowerLimitHours === false
            || $upperLimitHours === false
            || $lowerLimitHours < 0
            || $upperLimitHours < $lowerLimitHours
        ) {
            timeSpentRedirect(
                'Enter valid limits, with the upper limit at least as high as the lower limit.',
                $selectedActivityId
            );
        }
        query('updatetimespentsettings', $config, array(
            'lowerLimitHours' => number_format($lowerLimitHours, 2, '.', ''),
            'upperLimitHours' => number_format($upperLimitHours, 2, '.', ''),
            'id' => $selectedActivityId
        ));
        timeSpentRedirect('Tracker settings saved.', $selectedActivityId);
    }

    if ($action === 'correct-current') {
        if ($timer['startedAt'] !== null) {
            timeSpentRedirect('Stop the timer before correcting the current period.', $selectedActivityId);
        }
        $totalHours = filter_var($_POST['totalHours'], FILTER_VALIDATE_FLOAT);
        if ($totalHours === false || $totalHours < 0) {
            timeSpentRedirect('Enter a valid non-negative total.', $selectedActivityId);
        }
        query('updatetimespentcurrent', $config, array(
            'id' => $selectedActivityId,
            'totalSeconds' => (int) round($totalHours * 3600)
        ));
        timeSpentRedirect('Current period total corrected.', $selectedActivityId);
    }

    if ($action === 'correct-period') {
        $periodId = filter_var($_POST['periodId'], FILTER_VALIDATE_INT);
        $totalHours = filter_var($_POST['totalHours'], FILTER_VALIDATE_FLOAT);
        $period = false;
        if (is_array($periods)) {
            foreach ($periods as $periodRow) {
                if ((int) $periodRow['id'] === (int) $periodId) {
                    $period = $periodRow;
                    break;
                }
            }
        }
        if ($period === false || $totalHours === false || $totalHours < 0) {
            timeSpentRedirect('Enter a valid period total.', $selectedActivityId);
        }
        $totalSeconds = (int) round($totalHours * 3600);
        query('updatetimespentperiod', $config, array(
            'id' => $periodId,
            'activityId' => $selectedActivityId,
            'totalSeconds' => $totalSeconds,
            'status' => timeSpentPeriodStatus(
                $totalSeconds,
                $period['lowerLimitHours'],
                $period['upperLimitHours'],
                true
            ),
            'notes' => trim((string) $_POST['notes'])
        ));
        timeSpentRedirect('Historical period corrected.', $selectedActivityId);
    }

    $now = new DateTimeImmutable('now', $timezone);
    if ($timer['startedAt'] === null) {
        if ($runningActivityId !== null && $runningActivityId !== $selectedActivityId) {
            timeSpentRedirect('Stop the running activity before starting another one.', $selectedActivityId);
        }
        $updated = query('updatetimespentstart', $config, array(
            'id' => $selectedActivityId,
            'startedAt' => $now->format('Y-m-d H:i:s')
        ));
        if ($updated !== 1) {
            timeSpentRedirect('The timer changed before it could start; please try again.', $selectedActivityId);
        }
        $_SESSION['time_spent_last_session_seconds'] = 0;
        timeSpentRedirect($timer['activityName'] . ' started.', $selectedActivityId);
    }

    $startedAt = DateTimeImmutable::createFromFormat(
        'Y-m-d H:i:s',
        $timer['startedAt'],
        $timezone
    );
    if ($startedAt === false) {
        timeSpentRedirect('The saved start time is invalid; the timer was not stopped.', $selectedActivityId);
    }

    $elapsedSeconds = max(0, $now->getTimestamp() - $startedAt->getTimestamp());
    $updatedTotalSeconds = (int) $timer['totalSeconds'] + $elapsedSeconds;
    $updated = query('updatetimespentstop', $config, array(
        'id' => $selectedActivityId,
        'totalSeconds' => $updatedTotalSeconds
    ));
    if ($updated !== 1) {
        timeSpentRedirect('The timer changed before it could stop; please try again.', $selectedActivityId);
    }

    $message = $timer['activityName'] . ' stopped. Added ' . timeSpentFormatDuration($elapsedSeconds)
        . '; ' . timeSpentFormatHours($updatedTotalSeconds) . ' hours this fortnight.';
    if (
        (float) $updatedTotalSeconds / 3600 > (float) $timer['upperLimitHours']
        && !(int) $timer['upperLimitNotified']
    ) {
        query('marktimespentnotified', $config, array('id' => $selectedActivityId));
        $message .= ' Upper fortnightly limit exceeded.';
    }
    $_SESSION['time_spent_last_session_seconds'] = $elapsedSeconds;
    timeSpentRedirect($message, $selectedActivityId);
}

$startedAt = null;
$displaySeconds = $timer === false
    ? 0
    : (int) ($_SESSION['time_spent_last_session_seconds'] ?? 0);
$currentTotalSeconds = 0;
$isRunning = $timer !== false && $timer['startedAt'] !== null;
if ($timer !== false) {
    $currentTotalSeconds = (int) $timer['totalSeconds'];
    if ($isRunning) {
        $startedAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $timer['startedAt'],
            $timezone
        );
        if ($startedAt !== false) {
            $displaySeconds = max(0, $now->getTimestamp() - $startedAt->getTimestamp());
            $currentTotalSeconds += $displaySeconds;
        }
    }
}

$currentHours = $timer === false ? 0 : $currentTotalSeconds / 3600;
$daysTracked = $timer === false ? 1 : timeSpentDaysTracked($timer['startDate'], $now);
$periodEndDate = $timer === false
    ? false
    : new DateTimeImmutable($timer['periodEnd'] . ' 00:00:00', $timezone);
$daysRemaining = $timer === false
    ? 0
    : (int) floor(max(0, $periodEndDate->getTimestamp() - $now->getTimestamp()) / 86400);
$averageHours = $currentHours / $daysTracked;
$currentStatus = $timer === false
    ? 'Unavailable'
    : timeSpentPeriodStatus(
        $currentTotalSeconds,
        $timer['lowerLimitHours'],
        $timer['upperLimitHours'],
        !$isRunning && $now >= new DateTimeImmutable($timer['periodEnd'] . ' 00:00:00', $timezone)
    );
$timeSpentUrl = 'time_spent.php?activity=' . (int) $selectedActivityId;

$_SESSION['message'] = array();
require_once 'headerHtml.inc.php';
?>
<div class="time-spent-page">
    <?php if (is_array($activities) && count($activities) > 0) { ?>
        <form method="get" action="time_spent.php" class="time-spent-activity-picker">
            <label for="time-spent-activity">Activity</label>
            <select id="time-spent-activity" name="activity" onchange="this.form.submit()">
                <?php foreach ($activities as $activity) { ?>
                    <option
                        value="<?php echo (int) $activity['id']; ?>"
                        <?php echo (int) $activity['id'] === (int) $selectedActivityId ? ' selected="selected"' : ''; ?>
                    ><?php echo timeSpentEscape($activity['activityName'], $config); ?></option>
                <?php } ?>
            </select>
        </form>
    <?php } ?>
    <?php if ($timer !== false) { ?>
        <p class="time-spent-status">
            <?php echo $isRunning ? 'Timer running' : 'Timer stopped'; ?>
            <?php if ($currentStatus !== '') { ?>
                <span class="time-spent-status-detail"><?php echo timeSpentEscape($currentStatus, $config); ?></span>
            <?php } ?>
        </p>
        <p
            class="time-spent-total"
            id="time-spent-total"
            data-running="<?php echo $isRunning ? '1' : '0'; ?>"
            data-started-at="<?php echo $startedAt
                ? timeSpentEscape($startedAt->getTimestamp(), $config)
                : ''; ?>"
            data-total-seconds="<?php echo (int) $displaySeconds; ?>"
        ><?php echo timeSpentFormatDuration($displaySeconds); ?></p>
        <form method="post" action="<?php echo timeSpentEscape($timeSpentUrl, $config); ?>">
            <input type="hidden" name="csrf" value="<?php echo timeSpentEscape($_SESSION['time_spent_csrf'], $config); ?>" />
            <input type="hidden" name="action" value="toggle" />
            <button class="time-spent-toggle" type="submit">
                <?php echo $isRunning ? 'Stop timer' : 'Start timer'; ?>
            </button>
        </form>

        <div class="time-spent-summary">
            <div><strong><?php echo number_format($currentHours, 0); ?></strong><span>hours this fortnight</span></div>
            <div><strong><?php echo (int) $daysRemaining; ?></strong><span>days remaining</span></div>
            <div><strong><?php echo (int) $daysTracked; ?></strong><span>days tracked</span></div>
            <div><strong><?php echo number_format($averageHours, 0); ?></strong><span>hours / day</span></div>
        </div>
        <p class="time-spent-period">
            Target: <?php echo number_format((float) $timer['lowerLimitHours'], 0); ?>
            &ndash; <?php echo number_format((float) $timer['upperLimitHours'], 0); ?> hours
            &middot;
            <?php echo timeSpentEscape(timeSpentFormatDate($timer['periodStart']), $config); ?>
            to
            <?php echo timeSpentEscape(timeSpentFormatPeriodEnd($timer['periodEnd']), $config); ?>
        </p>

        <details class="time-spent-details">
            <summary>Tracker settings</summary>
            <form method="post" action="<?php echo timeSpentEscape($timeSpentUrl, $config); ?>" class="time-spent-settings">
                <input type="hidden" name="csrf" value="<?php echo timeSpentEscape($_SESSION['time_spent_csrf'], $config); ?>" />
                <input type="hidden" name="action" value="save-settings" />
                <label>Lower limit (hours)
                    <input type="number" name="lowerLimitHours" min="0" step="0.01" value="<?php echo number_format((float) $timer['lowerLimitHours'], 0, '.', ''); ?>" />
                </label>
                <label>Upper limit (hours)
                    <input type="number" name="upperLimitHours" min="0" step="0.01" value="<?php echo number_format((float) $timer['upperLimitHours'], 0, '.', ''); ?>" />
                </label>
                <button type="submit">Save settings</button>
            </form>
            <form method="post" action="<?php echo timeSpentEscape($timeSpentUrl, $config); ?>" class="time-spent-new-activity">
                <input type="hidden" name="csrf" value="<?php echo timeSpentEscape($_SESSION['time_spent_csrf'], $config); ?>" />
                <input type="hidden" name="action" value="create-activity" />
                <label>Activity name
                    <input type="text" name="newActivityName" maxlength="255" placeholder="Activity name" />
                </label>
                <button type="submit">Add activity</button>
            </form>
        </details>

        <details class="time-spent-details">
            <summary>Correct fortnight total</summary>
            <?php if (!$isRunning) { ?>
                <form method="post" action="<?php echo timeSpentEscape($timeSpentUrl, $config); ?>" class="time-spent-correction">
                    <input type="hidden" name="csrf" value="<?php echo timeSpentEscape($_SESSION['time_spent_csrf'], $config); ?>" />
                    <input type="hidden" name="action" value="correct-current" />
                    <label>Total hours
                        <input type="number" name="totalHours" min="0" step="0.01" value="<?php echo number_format($currentHours, 2, '.', ''); ?>" />
                    </label>
                    <button type="submit">Correct total</button>
                </form>
            <?php } ?>
        </details>

        <?php if (is_array($periods) && count($periods) > 0) { ?>
            <details class="time-spent-details time-spent-history">
                <summary>Previous fortnights</summary>
                <?php foreach ($periods as $period) { ?>
                    <form method="post" action="<?php echo timeSpentEscape($timeSpentUrl, $config); ?>" class="time-spent-period-row">
                        <input type="hidden" name="csrf" value="<?php echo timeSpentEscape($_SESSION['time_spent_csrf'], $config); ?>" />
                        <input type="hidden" name="action" value="correct-period" />
                        <input type="hidden" name="periodId" value="<?php echo (int) $period['id']; ?>" />
                        <span>
                            <?php echo timeSpentEscape(timeSpentFormatDate($period['periodStart']), $config); ?>
                            to
                            <?php echo timeSpentEscape(timeSpentFormatPeriodEnd($period['periodEnd']), $config); ?>
                        </span>
                        <input
                            type="number"
                            name="totalHours"
                            min="0"
                            step="0.01"
                            value="<?php echo number_format((int) $period['totalSeconds'] / 3600, 2, '.', ''); ?>"
                        />
                        <input
                            type="text"
                            name="notes"
                            value="<?php echo timeSpentEscape($period['notes'], $config); ?>"
                            placeholder="Notes"
                        />
                        <button type="submit">Save</button>
                    </form>
                <?php } ?>
            </details>
        <?php } ?>
        <p class="time-spent-help">The timer uses the server clock and survives closing the page.</p>
    <?php } else { ?>
        <p class="time-spent-help">Import the time_spent tables from db.sql to enable tracking.</p>
    <?php } ?>
</div>
<style type="text/css">
html,
body {
    min-height: 100%;
    background: #101418;
    color: #e6edf3;
}
.closeButton {
    display: none !important;
}
body {
    margin: 0;
}
#container,
#main {
    min-height: 100vh;
    background: #101418;
}
.time-spent-page {
    box-sizing: border-box;
    max-width: 38em;
    min-height: 100vh;
    margin: 0 auto;
    padding: 1.5em 1.5em;
    text-align: center;
}
.time-spent-page h1 {
    margin: 0 0 0.5em;
    color: #f0f6fc;
    font-size: 2em;
    font-weight: 600;
}
.time-spent-activity-picker {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5em;
    margin-bottom: 1em;
    color: #8b949e;
    font-size: 1.2em;
    font-weight: 600;
}
.time-spent-activity-picker select {
    max-width: 18em;
    padding: 0.45em;
    border: 1px solid #30363d;
    border-radius: 0.35em;
    background: #161b22;
    color: #e6edf3;
    font-size: 1em;
}
.time-spent-status {
    margin: 0;
    color: #8b949e;
    font-size: 1.1em;
}
.time-spent-status-detail {
    margin-left: 0.5em;
    color: #d29922;
    font-size: 0.85em;
}
.time-spent-total {
    padding: 0;
    margin: 0;
    color: #58a6ff;
    font-family: monospace;
    font-size: clamp(1.8em, 9vw, 3em);
    font-weight: 700;
    letter-spacing: 0.04em;
    line-height: 1;
    text-shadow: 0 0 24px rgba(88, 166, 255, 0.2);
}
.time-spent-toggle,
.time-spent-settings button,
.time-spent-new-activity button,
.time-spent-correction button,
.time-spent-period-row button {
    border: 1px solid #388bfd;
    border-radius: 0.5em;
    background: #1f6feb;
    color: #ffffff;
    cursor: pointer;
    font-weight: 600;
}
.time-spent-toggle {
    min-width: 11em;
    min-height: 3.25em;
    padding: 0.8em 1.2em;
    font-size: 1.25em;
    -webkit-tap-highlight-color: transparent;
}
.time-spent-toggle:hover,
.time-spent-toggle:focus,
.time-spent-settings button:hover,
.time-spent-settings button:focus,
.time-spent-new-activity button:hover,
.time-spent-new-activity button:focus,
.time-spent-correction button:hover,
.time-spent-correction button:focus,
.time-spent-period-row button:hover,
.time-spent-period-row button:focus {
    background: #388bfd;
}
.time-spent-toggle:focus,
.time-spent-settings button:focus,
.time-spent-new-activity button:focus,
.time-spent-correction button:focus,
.time-spent-period-row button:focus {
    outline: 2px solid #79c0ff;
    outline-offset: 3px;
}
.time-spent-summary {
    display: flex;
    justify-content: center;
    gap: 1em;
    margin: 1em 0 0.5em;
}
.time-spent-summary div {
    box-sizing: border-box;
    width: 4.9em;
    min-width: 4.9em;
    padding: 0.75em 0.4em;
    border: 1px solid #30363d;
    border-radius: 0.5em;
    background: #161b22;
    overflow-wrap: anywhere;
}
.time-spent-summary strong,
.time-spent-summary span {
    display: block;
}
.time-spent-summary strong {
    color: #f0f6fc;
    font-size: 1.25em;
}
.time-spent-summary span,
.time-spent-period,
.time-spent-help {
    color: #8b949e;
}
.time-spent-summary span {
    margin-top: 0.35em;
    font-size: 0.8em;
}
.time-spent-period {
    margin: 0.5em 0;
    line-height: 1.5;
}
.time-spent-details {
    box-sizing: border-box;
    max-width: 22.6em;
    margin: 1em auto;
    border: 1px solid #30363d;
    border-radius: 0.5em;
    background: #161b22;
    text-align: left;
}
.time-spent-details summary {
    padding: 0.9em 1em;
    color: #c9d1d9;
    cursor: pointer;
    font-weight: 600;
}
.time-spent-settings,
.time-spent-new-activity,
.time-spent-correction {
    display: grid;
    gap: 0.75em;
    padding: 0 1em 1em;
}
.time-spent-settings label,
.time-spent-new-activity label,
.time-spent-correction label {
    display: grid;
    gap: 0.3em;
    color: #8b949e;
    font-size: 0.9em;
}
.time-spent-settings input,
.time-spent-new-activity input,
.time-spent-correction input,
.time-spent-period-row input {
    box-sizing: border-box;
    min-height: 2.5em;
    padding: 0.45em 0.6em;
    border: 1px solid #484f58;
    border-radius: 0.3em;
    background: #0d1117;
    color: #e6edf3;
}
.time-spent-settings input[type="number"],
.time-spent-correction input[type="number"],
.time-spent-period-row input[type="number"] {
    font-size: 1.1em;
}
.time-spent-settings button,
.time-spent-new-activity button,
.time-spent-correction button {
    justify-self: start;
    padding: 0.65em 1em;
}
.time-spent-period-row {
    display: grid;
    grid-template-columns: 1fr 6em;
    gap: 0.6em;
    padding: 0 1em 1em;
}
.time-spent-period-row span {
    align-self: center;
    color: #8b949e;
    font-size: 0.9em;
}
.time-spent-period-row input[name="notes"] {
    grid-column: 1 / -1;
}
.time-spent-period-row button {
    padding: 0.5em;
}
.time-spent-help {
    max-width: 25em;
    margin: 2em auto 0;
    font-size: 0.95em;
    line-height: 1.5;
}
@media (max-width: 30em) {
    .time-spent-summary {
        gap: 0.4em;
    }
    .time-spent-summary div {
        width: auto;
        min-width: 0;
        flex: 1;
    }
    .time-spent-period-row {
        grid-template-columns: 1fr 5.5em;
    }
    .time-spent-details {
        max-width: none;
    }
}
</style>
<script type="text/javascript">
(function () {
    var display = document.getElementById("time-spent-total");
    if (!display || display.getAttribute("data-running") !== "1") {
        return;
    }

    var startedAt = parseInt(display.getAttribute("data-started-at"), 10);
    var totalSeconds = parseInt(display.getAttribute("data-total-seconds"), 10);
    if (!Number.isFinite(startedAt) || !Number.isFinite(totalSeconds)) {
        return;
    }

    function render() {
        var elapsed = totalSeconds + Math.max(0, Math.floor(Date.now() / 1000) - startedAt);
        var hours = Math.floor(elapsed / 3600);
        var minutes = Math.floor((elapsed % 3600) / 60);
        var seconds = elapsed % 60;
        display.textContent =
            String(hours).padStart(2, "0") + ":" +
            String(minutes).padStart(2, "0") + ":" +
            String(seconds).padStart(2, "0");
    }

    render();
    window.setInterval(render, 1000);
}());
</script>
<?php
echo "</div></div></body></html>";
