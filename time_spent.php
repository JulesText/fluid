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

function timeSpentRedirect($message)
{
    $_SESSION['message'][] = $message;
    nextScreen('time_spent.php');
}

if (empty($_SESSION['time_spent_csrf'])) {
    $_SESSION['time_spent_csrf'] = bin2hex(random_bytes(32));
}

$result = query('selecttimespent', $config);
if ($result === false) {
    $timer = false;
    $_SESSION['message'][] = 'The time tracker table is not available. Import the time_spent table from db.sql.';
} elseif ($result === 0) {
    query('newtimespent', $config);
    $result = query('selecttimespent', $config);
    $timer = is_array($result) ? $result[0] : false;
} else {
    $timer = $result[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $timer !== false) {
    if (
        empty($_POST['csrf'])
        || !hash_equals($_SESSION['time_spent_csrf'], (string) $_POST['csrf'])
    ) {
        http_response_code(403);
        exit('Invalid time tracker request.');
    }

    $now = new DateTimeImmutable('now', new DateTimeZone($config['timezone']));
    if ($timer['startedAt'] === null) {
        $values['startedAt'] = $now->format('Y-m-d H:i:s');
        $updated = query('updatetimespentstart', $config, $values);
        if ($updated !== 1) {
            timeSpentRedirect('The timer changed before it could start; please try again.');
        }
        timeSpentRedirect('Timer started.');
    }

    $startedAt = DateTimeImmutable::createFromFormat(
        'Y-m-d H:i:s',
        $timer['startedAt'],
        new DateTimeZone($config['timezone'])
    );
    if ($startedAt === false) {
        timeSpentRedirect('The saved start time is invalid; the timer was not stopped.');
    }

    $elapsedSeconds = max(0, $now->getTimestamp() - $startedAt->getTimestamp());
    $values['totalSeconds'] = (int) $timer['totalSeconds'] + $elapsedSeconds;
    $updated = query('updatetimespentstop', $config, $values);
    if ($updated !== 1) {
        timeSpentRedirect('The timer changed before it could stop; please try again.');
    }
    timeSpentRedirect('Timer stopped. Added ' . timeSpentFormatDuration($elapsedSeconds) . '.');
}

$startedAt = null;
$displaySeconds = 0;
$isRunning = $timer !== false && $timer['startedAt'] !== null;
if ($timer !== false) {
    $displaySeconds = (int) $timer['totalSeconds'];
    if ($isRunning) {
        $startedAt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $timer['startedAt'],
            new DateTimeZone($config['timezone'])
        );
        if ($startedAt !== false) {
            $displaySeconds += max(0, time() - $startedAt->getTimestamp());
        }
    }
}

require_once 'headerHtml.inc.php';
?>
<div class="time-spent-page">
    <h1>Time spent</h1>
    <?php if ($timer !== false) { ?>
        <p class="time-spent-status">
            <?php echo $isRunning ? 'Timer running' : 'Timer stopped'; ?>
        </p>
        <p
            class="time-spent-total"
            id="time-spent-total"
            data-running="<?php echo $isRunning ? '1' : '0'; ?>"
            data-started-at="<?php echo $startedAt
                ? htmlspecialchars((string) $startedAt->getTimestamp(), ENT_QUOTES, $config['charset'])
                : ''; ?>"
            data-total-seconds="<?php echo (int) $timer['totalSeconds']; ?>"
        ><?php echo timeSpentFormatDuration($displaySeconds); ?></p>
        <form method="post" action="time_spent.php">
            <input
                type="hidden"
                name="csrf"
                value="<?php echo htmlspecialchars($_SESSION['time_spent_csrf'], ENT_QUOTES, $config['charset']); ?>"
            />
            <button class="time-spent-toggle" type="submit">
                <?php echo $isRunning ? 'Stop timer' : 'Start timer'; ?>
            </button>
        </form>
        <p class="time-spent-help">The timer uses the server clock and survives closing the page.</p>
    <?php } ?>
</div>
<style type="text/css">
html,
body {
    min-height: 100%;
    background: #101418;
    color: #e6edf3;
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
    max-width: 32em;
    min-height: 100vh;
    margin: 0 auto;
    padding: 3em 1.5em;
    text-align: center;
}
.time-spent-page h1 {
    margin: 0 0 1.5em;
    color: #f0f6fc;
    font-size: 2em;
    font-weight: 600;
}
.time-spent-status {
    margin: 0;
    color: #8b949e;
    font-size: 1.1em;
}
.time-spent-total {
    padding: 0.35em 0;
    margin: 0.35em 0 0.75em;
    color: #58a6ff;
    font-family: monospace;
    font-size: clamp(2.8em, 15vw, 4.5em);
    font-weight: 700;
    letter-spacing: 0.04em;
    text-shadow: 0 0 24px rgba(88, 166, 255, 0.2);
}
.time-spent-toggle {
    min-width: 11em;
    min-height: 3.25em;
    padding: 0.8em 1.2em;
    border: 1px solid #388bfd;
    border-radius: 0.5em;
    background: #1f6feb;
    color: #ffffff;
    cursor: pointer;
    font-size: 1.25em;
    font-weight: 600;
    -webkit-tap-highlight-color: transparent;
}
.time-spent-toggle:hover,
.time-spent-toggle:focus {
    background: #388bfd;
}
.time-spent-toggle:focus {
    outline: 2px solid #79c0ff;
    outline-offset: 3px;
}
.time-spent-help {
    max-width: 25em;
    margin: 2em auto 0;
    color: #8b949e;
    font-size: 0.95em;
    line-height: 1.5;
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
