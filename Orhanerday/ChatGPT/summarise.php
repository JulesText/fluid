<?php

##require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

function countTokens($str) {
  // Replace special characters with spaces
  $pattern = "/[^\p{L}\p{Mn}\p{Pd}'’“”]/u";
  $str = preg_replace($pattern, " ", $str);

  // Split the string into words and count them
  $words = preg_split('/[\s]+/', $str);
  $count = 0;
  foreach ($words as $word) {
  // Check if the word is a whitespace or contains only punctuation
  if (strlen(trim($word)) == 0 || preg_match('/^[\p{P}\s]+$/u', $word)) {
  continue;
  }
  // Check if the word is an opening or closing punctuation mark
  if (preg_match('/^[\p{Pe}\p{Pf}]+$/u', $word)) {
  $count += 1;
  continue;
  }
  // Otherwise, count the word length
  $count += strlen($word);
  }
  return $count;
}

function limitTokens($str, $limit) {
  $count = countTokens($str);
  if ($count > $limit) {
  // Use substr_replace to get a string with the same number of tokens
  $str = substr_replace($str, "...", $limit);
  }
  return $str;
}

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);
/*
require_once('../../Orhanerday/OpenAi/OpenAi.php');
require_once('../../Orhanerday/OpenAi/Url.php');

use Orhanerday\OpenAi\OpenAi;
*/
$open_ai_key = getenv('OPENAI_API_KEY');
#$open_ai = new OpenAi($open_ai_key);

$chat_id = $_POST['chat_id'];

// Retrieve the data in ascending order by the comment_id column
$stmt = $db->prepare('SELECT * FROM chat_history WHERE chat_id = "' . $chat_id . '" ORDER BY comment_id ASC');
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chat = "";
foreach ($result as $row) {
  $chat .= $row['comment_human'] . '\n';
  $chat .= $row['comment_ai'] . '\n';
}

# limit to 3000 tokens
$chat = limitTokens($chat, 3000);

/*
header('Content-type: text/event-stream');
header('Cache-Control: no-cache');

$summary = $open_ai->summarize($opts);
*/

// Set up the API endpoint URL and parameters
$apiEndpoint = "https://api.openai.com/v1/completions";
// Set up the request data
$opts = array(
    "model" => "text-davinci-003",
    "prompt" => "Please summarize the following text in 5 words:\n\n" . $chat . "\n\n5-word summary:", # 5-word summarise is a particular style
    "max_tokens" => 15, # limits actual size of token response
    "temperature" => 0.8 # creativity = 1, none =  0 (deterministic)
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

file_put_contents('test.txt', PHP_EOL . $response);
file_put_contents('test.txt', PHP_EOL . '--' . PHP_EOL . $res, FILE_APPEND);
file_put_contents('test.txt', PHP_EOL . '--' . PHP_EOL . $responseData["choices"][0]["text"], FILE_APPEND);
file_put_contents('test.txt', PHP_EOL . '--' . PHP_EOL . $chat, FILE_APPEND);

#echo $responseData["choices"][0]["text"];
#var_dump( $responseData);
#echo $success . " " . $res; die;

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
