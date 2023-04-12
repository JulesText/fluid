<?php

##require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);
/*
require_once('../../Orhanerday/OpenAi/OpenAi.php');
require_once('../../Orhanerday/OpenAi/Url.php');

use Orhanerday\OpenAi\OpenAi;
*/
$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);

$chat_id = $_POST['chat_id'];

// Retrieve the data in ascending order by the comment_id column
$stmt = $db->prepare('SELECT * FROM chat_history WHERE chat_id = "' . $chat_id . '" ORDER BY comment_id ASC');
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chat = "";
foreach ($result as $row) {
  $chat .= $row['comment_ai'] . '\n';
  $chat .= $row['comment_human'] . '\n';
}


/*
header('Content-type: text/event-stream');
header('Cache-Control: no-cache');

$summary = $open_ai->summarize($opts);
*/



// Set up the API endpoint URL and parameters
$apiEndpoint = "https://api.openai.com/v1/engines/davinci/completions";

// Set up the request data
$opts = array(
    #"prompt" => "Summarize the following chat into 5 words: " . $chat,
    "prompt" => $chat,
    "temperature" => 1,
    "max_tokens" => 5
);
$requestHeaders = array(
    "Content-Type: application/json",
    "Authorization: Bearer " . $open_ai_key
);

// Make the API request using cURL
$ch = curl_init($apiEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
$response = curl_exec($ch);

// Check for errors and decode the response data
$success = FALSE;
if(curl_errno($ch)) {
    $res = "Error: " . curl_error($ch);
} else {
    $responseData = json_decode($response, true);
    if(isset($responseData["choices"][0]["text"])) {
        $res = $responseData["choices"][0]["text"];
        $success = TRUE;
    } else {
        $res = "Error: Failed to retrieve summary";
    }
}

// Close the cURL session
curl_close($ch);

file_put_contents('test.txt', PHP_EOL . json_decode($responseData));
file_put_contents('test.txt', PHP_EOL . $res, FILE_APPEND);
file_put_contents('test.txt', PHP_EOL . $chat, FILE_APPEND);

if($success) {

  // Prepare the UPDATE statement
  $stmt = $db->prepare('UPDATE chat_history SET chat_summary = :chat_summary WHERE chat_id = :chat_id');
  // Bind the parameters and execute the statement
  $stmt->bindValue(':chat_summary', $res, PDO::PARAM_STR);
  $stmt->bindValue(':chat_id', $chat_id, PDO::PARAM_STR);
  $stmt->execute();

}

echo $res;

// Close the database connection
$db = NULL;
