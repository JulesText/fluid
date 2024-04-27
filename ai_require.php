<?php

require_once('headerDB.inc.php');

// Create a new SQLite database connection
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);



// ---- these are db functions ---- //

function get_other_chats($chat_id, $db) {

  $stmt = $db->prepare('SELECT DISTINCT chat_id, chat_summary FROM chat_history WHERE chat_id != "' . $_GET['chat_id'] . '" ORDER BY comment_id DESC');
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);

}

function get_chat($chat_id, $db) {

  // Retrieve the data in ascending order by the comment_id column
  $stmt = $db->prepare('SELECT * FROM chat_history WHERE chat_id = "' . $chat_id . '" ORDER BY comment_id ASC');
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  return $result;

}

function update_chat_summary($chat_id, $chat_summary, $db) {

  // Prepare the UPDATE statement
  $stmt = $db->prepare('UPDATE chat_history SET chat_summary = :chat_summary WHERE chat_id = :chat_id');
  // Bind the parameters and execute the statement
  $stmt->bindValue(':chat_summary', $chat_summary, PDO::PARAM_STR);
  $stmt->bindValue(':chat_id', $chat_id, PDO::PARAM_STR);
  $stmt->execute();

}

function insert_comment($chat_id, $msg, $db) {

  // prepare db for message
  $stmt = $db->prepare('INSERT INTO chat_history (chat_id, comment_human) VALUES (:chat_id, :comment_human)');
  $stmt->bindValue(':chat_id', $chat_id, PDO::PARAM_STR);
  $stmt->bindValue(':comment_human', $msg, PDO::PARAM_STR);
  $stmt->execute();

  return $db->lastInsertId();

}

function update_comment_ai($comment_id, $msg, $db) {

  // Prepare the UPDATE statement
  $stmt = $db->prepare('UPDATE chat_history SET comment_ai = :comment_ai WHERE comment_id = :comment_id');
  // Bind the parameters and execute the statement
  $stmt->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
  $stmt->bindValue(':comment_ai', $msg, PDO::PARAM_STR);
  $stmt->execute();

}

function delete_chat($chat_id, $db) {

  // Prepare and execute a DELETE statement to delete chat history records
  $stmt = $db->prepare('DELETE FROM chat_history WHERE chat_id = "'. $chat_id . '"');
  $result = $stmt->execute();

  // Set the HTTP response status code to indicate success
  return http_response_code(204); // No Content

}




// ---- these are utility functions ---- //


function curlReq($curly_tops) {

  // Make the API request using cURL
  $curl_info = [
      CURLOPT_URL            => $curly_tops['apiEndpoint'],
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 30,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => $curly_tops['method'],
      CURLOPT_POST           => TRUE,
      CURLOPT_POSTFIELDS     => json_encode($curly_tops['opts'])
  ];

  if ($curly_tops['opts'] == []) unset($curl_info[CURLOPT_POSTFIELDS]);


  if (array_key_exists('stream', $curly_tops['opts']) && $opts['stream']) {
      $curl_info[CURLOPT_WRITEFUNCTION] = TRUE;
      // not sure this works
      $curly_tops['headers'][] = "Content-Type: text/event-stream";
      $curly_tops['headers'][] = "Cache-Control: no-cache";
  } else {
      $curly_tops['headers'][] = "Content-Type: application/json";
  }

  $curl_info[CURLOPT_HTTPHEADER] = $curly_tops['headers'];

  $curl = curl_init();

  curl_setopt_array($curl, $curl_info);
  $response = curl_exec($curl);

  // Check for errors and decode the response data
  $curls = [];

  if (curl_errno($curl)) {
      $curls['success'] = FALSE;
      $curls['data'] = "Curl error: " . curl_error($curl)
        . PHP_EOL . print_r($curly_tops, TRUE);
  } else {
      $responseData = json_decode($response, true);
      if (isset($responseData["choices"][0]["text"])) {
          $curls['success'] = TRUE;
          $curls['data'] = $responseData["choices"][0]["text"];
      } else if (isset($responseData["choices"][0]["message"])) {
          $curls['success'] = TRUE;
          $curls['data'] = $responseData["choices"][0]["message"]["content"];
      } else {
          $curls['success'] = FALSE;
          $curls['data'] = "API error: Failed to retrieve expected result";
      }
  }

  curl_close($curl);

  if ($curls['success']) file_put_contents('test.txt', '$curls[success]: TRUE');
  else file_put_contents('test.txt', '$curls[success]: FALSE');

  file_put_contents('test.txt',
    PHP_EOL
    . '$curls[data]:' . print_r($curls['data'], TRUE)
    . PHP_EOL . '$responseData: ' . print_r($responseData, TRUE)
    . PHP_EOL . '$curly_tops: ' . print_r($curly_tops, TRUE)
    , FILE_APPEND);

  return $curls;

}

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


// ---- these are posts called from js ---- //


if ($_POST['query'] === 'get_chat') {

    // Get the chat ID from the request data
    $chat_id = $_POST['chat_id'];

    if (isset($_POST['last'])) $last = TRUE;
    else $last = FALSE;

    $result = get_chat($chat_id, $db);

    $chat_history = array();
    foreach ($result as $row) {
      $chat_history[] = $row;
    }

    if ($last) {

      $chat_history = array_reverse($chat_history);
      $chat_history = $chat_history[0]['comment_ai'];
      echo $chat_history;

    } else {

      // Set the HTTP response header to indicate that the response is JSON
      header('Content-Type: application/json');

      // Convert the chat history array to JSON and send it as the HTTP response body
      echo json_encode($chat_history);

    }
}

if ($_POST['query'] === 'delete_chat') {

    if (!isset($_POST['chat_id'])) $chat_id = $_GET['chat_id'];
    else $chat_id = $_POST['chat_id'];

    return delete_chat($chat_id, $db);

}



?>
