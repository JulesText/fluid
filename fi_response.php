<?php

include('fi_require.php');

$comment_id = insert_comment($_POST['chat_id'], $_POST['msg'], $db);

$result = get_chat($_POST['chat_id'], $db);

$system_message = [
  'role' => 'system'
  , 'content' => 'Respond with plain text and no markdown, headings, bold text, emojis, or decorative formatting. Allow code blocks and plain tables.'
];

// Keep the newest complete exchanges within an approximate four-characters-per-token budget.
$history_char_limit = $config['fir_history_max_tkn'] * 4;
$history_chars = strlen($system_message['content']);
$recent_history = [];
foreach (array_reverse($result) as $row) {
    $row_chars = strlen((string) $row['comment_human']) + strlen((string) $row['comment_ai']);
    if (!empty($recent_history) && $history_chars + $row_chars > $history_char_limit) {
        break;
    }
    $recent_history[] = $row;
    $history_chars += $row_chars;
}

$history[] = $system_message;
foreach (array_reverse($recent_history) as $row) {
    $history[] = ['role' => 'user', 'content' => $row['comment_human']];
    if ($row['comment_ai'] !== null && $row['comment_ai'] !== '') {
        $history[] = ['role' => 'assistant', 'content' => $row['comment_ai']];
    }
}

$model_id = $_POST['model_id'];
if ($model_id == '5') {
    $model_id = $config['fir_model_5'];
    $token_word = $config['fir_model_5_token_word'];
    $model_temp = $config['fir_model_5_temp'];
}
if ($model_id == '4') {
    $model_id = $config['fir_model_4'];
    $token_word = $config['fir_model_4_token_word'];
    $model_temp = $config['fir_model_4_temp'];
}
if ($model_id == '3') {
    $model_id = $config['fir_model_3'];
    $token_word = $config['fir_model_3_token_word'];
    $model_temp = $config['fir_model_3_temp'];
}

$opts = [
  'temperature' => $model_temp
  , 'frequency_penalty' => $config['fir_freq_pen']
  , 'presence_penalty' => $config['fir_pres_pen']
  , 'stream' => $config['stream']
];
$opts['model'] = $model_id;
$opts['messages'] = $history;

# max number of tokens in response
if ($_POST['word_count'] > 0) {
    $max_tokens = $_POST['word_count'] * 2; // roughly 1.5 tokens / word
} else {
    $max_tokens = $config['fir_max_tkn'];
}
$opts[$token_word] = $max_tokens;

// Set up the API parameters
$curly_tops['apiEndpoint'] = $config['fir_endpoint'];
$curly_tops['method'] = 'POST';
$curly_tops['chat'] = $history;
$curly_tops['opts'] = $opts;
$curly_tops['headers'] = array(
    "Authorization: Bearer " . $config['openAI']
);

$curls = curlReq($curly_tops);

update_comment_fi($comment_id, $curls['data'], $db);

// Close the database connection
$db = null;

echo $curls['data'];
