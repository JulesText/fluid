<?php

##require __DIR__ . '/vendor/autoload.php'; // remove this line if you use a PHP Framework.

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

require_once('../../Orhanerday/OpenAi/OpenAi.php');
require_once('../../Orhanerday/OpenAi/Url.php');

use Orhanerday\OpenAi\OpenAi;

const ROLE = "role";
const CONTENT = "content";
const USER = "user";
const SYS = "system";
const ASSISTANT = "assistant";

$open_ai_key = getenv('OPENAI_API_KEY');
$open_ai = new OpenAi($open_ai_key);

$chat_id = $_GET['chat_id'];
$comment_id = $_GET['comment_id'];

// Retrieve the data in ascending order by the comment_id column
$stmt = $db->prepare('SELECT * FROM chat_history ORDER BY comment_id ASC');
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$history[] = [ROLE => SYS, CONTENT => "You are a helpful assistant."];
foreach ($result as $row) {
  $history[] = [ROLE => USER, CONTENT => $row['comment_human']];
  $history[] = [ROLE => ASSISTANT, CONTENT => $row['comment_ai']];
}

// remove the empty ai comment
array_pop($history);

/*
// Prepare a SELECT statement to retrieve the 'comment_human' field
$stmt = $db->prepare('SELECT comment_human FROM chat_history WHERE id = :id');
$stmt->bindValue(':id', $chat_history_id, PDO::PARAM_INT);

// Execute the SELECT statement and retrieve the 'comment_human' field
$stmt->execute();
$msg = $stmt->fetchAll(PDO::FETCH_ASSOC)['comment_human'];
$history[] = [ROLE => USER, CONTENT => $msg];
*/

$opts = [
    'model' => 'gpt-3.5-turbo'
    #'model' => 'gpt-4'
    , 'messages' => $history
    , 'temperature' => 1.0
    , 'max_tokens' => 1000 /* max number of words in response */
    , 'frequency_penalty' => 0
    , 'presence_penalty' => 0
    , 'stream' => TRUE
];

#file_put_contents('test.txt', PHP_EOL . json_encode($opts, JSON_PRETTY_PRINT), FILE_APPEND);

#file_put_contents('test.txt', PHP_EOL . 'x', FILE_APPEND);

#$chat = $open_ai->chat($opts);

header('Content-type: text/event-stream');
header('Cache-Control: no-cache');
/*
$obj = json_decode($chat);

if ($obj->error->message != "") {

  file_put_contents('test.txt', PHP_EOL . json_encode($obj->error->message), FILE_APPEND);

  error_log(json_encode($obj->error->message));

} else {

    echo($obj->choices[0]->message->content);

    file_put_contents('test.txt', PHP_EOL . $obj->choices[0]->message->content, FILE_APPEND);

}
*/
$txt = "";
$i = 0;
file_put_contents('test.txt', '');

$complete = $open_ai->chat(
  $opts,

  function ($curl_info, $data) use (&$txt, &$i) {

    if ($obj = json_decode($data) && $obj->error->message != "") {

      file_put_contents('test.txt', PHP_EOL . 'Error: ' . json_encode($obj->error->message), FILE_APPEND);

      error_log(json_encode($obj->error->message));

      $txt .= 'an error occurred';
      $data["choices"][0]["delta"]["content"] = 'an error occurred';
      echo $data;

    } else {

      // tries iterative printing result and cleaning

      echo $data; # send to js to print
      #file_put_contents('test.txt', PHP_EOL . $data, FILE_APPEND);

      # can have multiple results in one chat.completion.chunk
      # in first row
      # should separate to clean for db entry
      if ($i == 0) {
        $d = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"ran_str123",$data);
        $lines = explode("ran_str123", $d);
      } else {
        $lines = array(0 => $data);
      }
      $i++;

      foreach ($lines as $index => $row) {

        $clean = str_replace("data: ", "", $row);
        $arr = json_decode($clean, TRUE);
        $choice_content = $arr["choices"][0]["delta"]["content"];

        file_put_contents('test.txt', $index . ':' . $choice_content . PHP_EOL, FILE_APPEND);
        file_put_contents('test.txt', $clean . PHP_EOL, FILE_APPEND);

        if (!empty($choice_content)) {
          $txt .= $choice_content;
          #echo $row;
        }

      }

      #$clean = str_replace("data: ", "", $data);
      #$arr = json_decode($clean, TRUE);
      #if ($data != "data: [DONE]\n\n" &&
      #if (isset($arr["choices"][0]["delta"]["content"])) {

        #$txt .= $arr["choices"][0]["delta"]["content"];

        #$choice_content = $arr["choices"][0]["delta"]["content"];
        #$dump = 'v' . $index . PHP_EOL . $data . 'w' . PHP_EOL . $clean . 'x' . PHP_EOL . json_decode($arr) . PHP_EOL . 'y' . PHP_EOL . $choice_content . PHP_EOL . 'z' . PHP_EOL;
        #file_put_contents('test.txt', $dump, FILE_APPEND);

      #}

    }

    echo PHP_EOL;
    ob_flush();
    flush();
    return strlen($data);

});

file_put_contents('test.txt', PHP_EOL . $comment_id . ': ' . $chat_id . ': ' . $txt, FILE_APPEND);

// Prepare the UPDATE statement
$stmt = $db->prepare('UPDATE chat_history SET comment_ai = :comment_ai WHERE comment_id = :comment_id');
// Bind the parameters and execute the statement
$stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
$stmt->bindValue(':comment_ai', $txt, PDO::PARAM_STR);
$stmt->execute();

//
// Close the database connection
$db = NULL;
