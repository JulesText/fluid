<?php

include('ai_require.php');

$comment_id = insert_comment($_POST['chat_id'], $_POST['msg'], $db);

$result = get_chat($_POST['chat_id'], $db);

// format for API
$history[] = ['role' => 'system', 'content' => ''];
foreach ($result as $row) {
  $history[] = ['role' => 'user', 'content' => $row['comment_human']];
  $history[] = ['role' => 'assistant', 'content' => $row['comment_ai']];
}
// remove the last empty assistant comment
array_pop($history);

$model_id = $_POST['model_id'];
if ($model_id == '4') $model_id = $config['air_model_4'];
if ($model_id == '3') $model_id = $config['air_model_3'];

$opts = [
  'temperature' => $config['air_temp']
  , 'frequency_penalty' => $config['air_freq_pen']
  , 'presence_penalty' => $config['air_pres_pen']
  , 'stream' => $config['stream']
];
$opts['model'] = $model_id;
$opts['messages'] = $history;

# max number of tokens in response
if ($_POST['word_count'] > 0) $max_tokens = $_POST['word_count'] * 2; // roughly 1.5 tokens / word
else $max_tokens = $config['air_max_tkn'];
$opts['max_tokens'] = $max_tokens;

// Set up the API parameters
$curly_tops['apiEndpoint'] = $config['air_endpoint'];
$curly_tops['method'] = 'POST';
$curly_tops['chat'] = $history;
$curly_tops['opts'] = $opts;
$curly_tops['headers'] = array(
    "Authorization: Bearer " . $config['openAI']
);

$curls = curlReq($curly_tops);

update_comment_ai($comment_id, $curls['data'], $db);

// Close the database connection
$db = NULL;

echo $curls['data'];
