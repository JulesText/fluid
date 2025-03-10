<?php

include('fi_require.php');

$chat_id = $_POST['chat_id'];

$result = get_chat($chat_id, $db);

$chat = "";
foreach ($result as $row) {
  $chat .= $row['comment_human'] . '\n';
  $chat .= $row['comment_ai'] . '\n';
}

// Set up the API endpoint URL and parameters
$curly_tops['apiEndpoint'] = $config['fis_endpoint'];
$curly_tops['chat'] = limitTokens($chat, $config['fis_input_length']);

$opts = array(
    "model" => $config['fis_model'],
    "temperature" => $config['fis_temp']
);
$words = $config['fis_words'];
$opts["prompt"] = "Please summarize the following text in " . $words . " words:\n\n" . $curly_tops['chat'] . "\n\n" . $words . "-word summary:";
$opts['max_tokens'] = $words * 2; # limits actual size of token response
$curly_tops['method'] = 'POST';
$curly_tops['opts'] = $opts;
$curly_tops['headers'] = array(
    "Authorization: Bearer " . $config['openAI']
);

$curls = curlReq($curly_tops);

if ($curls['success']) {

  update_chat_summary($chat_id, $curls['data'], $db);

  file_put_contents('_response.txt', PHP_EOL . '--' . PHP_EOL . 'attempted to write to db:', FILE_APPEND);
  file_put_contents('_response.txt', PHP_EOL . '$chat_id: ' . $chat_id . ' text: ' . $curls['data'], FILE_APPEND);

}

echo $curls['data'];

// Close the database connection
$db = NULL;
