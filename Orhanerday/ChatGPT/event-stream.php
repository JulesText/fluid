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

#'model' => 'gpt-3.5-turbo-16k' # unclear what 16k does (still 4k tokens)
#'model' => 'gpt-3.5-turbo' # 4k tokens
#'model' => 'gpt-4'
$model_id = $_GET['model_id'];
if ($model_id == '4') $model_id = 'gpt-4';
if ($model_id == '3') $model_id = 'gpt-3.5-turbo';

// Retrieve the data in ascending order by the comment_id column
$stmt = $db->prepare('SELECT * FROM chat_history WHERE chat_id = "' . $chat_id . '" ORDER BY comment_id ASC');
#$stmt = $db->prepare('SELECT * FROM chat_history ORDER BY comment_id ASC');
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$history[] = [ROLE => SYS, CONTENT => "You are a helpful assistant."];
foreach ($result as $row) {
  $history[] = [ROLE => USER, CONTENT => $row['comment_human']];
  $history[] = [ROLE => ASSISTANT, CONTENT => $row['comment_ai']];
}

// remove the empty ai comment
array_pop($history);

$opts = [
  'model' => $model_id
  , 'messages' => $history
  , 'temperature' => 1.0
  , 'max_tokens' => 1000 /* max number of tokens in response */
  , 'frequency_penalty' => 0
  , 'presence_penalty' => 0
  , 'stream' => TRUE
  // , 'content_type' = 'text/markdown'
];

header('Content-type: text/event-stream');
header('Cache-Control: no-cache');

$txt = "";
file_put_contents('test.txt', '');

$complete = $open_ai->chat($opts,

  function ($curl_info, $data) use (&$txt) {

    $obj = json_decode($data);
    #file_put_contents('test.txt', PHP_EOL . json_encode($obj), FILE_APPEND);

    if ($obj->error->type != "") {

      $data = json_encode($obj);
      file_put_contents('test.txt', PHP_EOL . $data, FILE_APPEND);

      $txt .= $obj->error->message;
      #echo $obj->error->message;
      // $data = 'data: {"id":"0","object":"chat.completion.chunk","created":0,"model"'
      //   . ':"0","choices":[{"delta":{"content":"' . $txt . '"},"index":0,"finish_reason":null}]}';
      #echo $data;
      #file_put_contents('test.txt', PHP_EOL . $data, FILE_APPEND);die;

    } else {

      // tries iterative printing result and cleaning

      echo $data; # send to js to print
      #file_put_contents('test.txt', PHP_EOL . $data, FILE_APPEND);die;

      # can have multiple results in one chat.completion.chunk
      # the line breaks will prevent converting json to array
      # need to separate to clean for db entry
      # we can't do a blanket replace as sometimes the content includes line breaks as \n
      # so split either side of the content, then clean, then rejoin
      $returns = array("\r\n","\r","\n","\\r","\\n","\\r\\n");
      $str_left = '{"content":"'; # content start
      $str_right = '"},"finish_reason"'; # content end
      $d = str_replace(array($str_left, $str_right), "ran_str123", $data);
      $d = explode("ran_str123", $d);
      for ($i = 0; $i < count($d); $i++) {
        if ($i%2 == 1) continue; # skip odd strings as these will be actual content
        $d[$i] = str_replace($returns, "", $d[$i]);
        if ($i == 0) $d[$i] = $d[$i] . $str_left; # first iter
        else if ($i == count($d) - 1) $d[$i] = $str_right . $d[$i]; # last iter
        else $d[$i] = $str_right . $d[$i] . $str_left; # middle iter
      }
      # rejoin content
      $d = implode($d);
      # split correctly
      $lines = explode("data: ", $d); # this represents start of unique row

      foreach ($lines as $index => $row) {

        # file_put_contents('test.txt', $index . ': ' . $row . PHP_EOL, FILE_APPEND);

        $arr = json_decode($row, TRUE);
        $choice_content = $arr["choices"][0]["delta"]["content"];

        # file_put_contents('test.txt', $index . ': ' . count($arr) . ' rows in $row' . PHP_EOL, FILE_APPEND);

        file_put_contents('test.txt', $index . ':' . $choice_content . PHP_EOL, FILE_APPEND);
        file_put_contents('test.txt', $clean . PHP_EOL, FILE_APPEND);

        if (!empty($choice_content)) {
          $txt .= $choice_content;
          #echo $row;
        }

      }

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
