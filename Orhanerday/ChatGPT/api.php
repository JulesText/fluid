<?php

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the chat ID from the request data
    $chat_id = $_POST['chat_id'];

    if (isset($_POST['last'])) $last = TRUE;
    else $last = FALSE;

    #$chat_id="%";

    // Prepare and execute a SELECT statement to retrieve the chat history data
    $stmt = $db->prepare('SELECT comment_human, comment_ai, comment_date FROM chat_history WHERE chat_id LIKE "'. $chat_id . '" ORDER BY comment_id ASC');
    $stmt->execute();

    // Fetch the results and store them in an array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $chat_history = array();
    foreach ($result as $row) {
      $chat_history[] = $row;
    }

    if ($last) {

      $chat_history = array_reverse($chat_history);
      $chat_history = $chat_history[0]['comment_ai'];
      echo $chat_history;
      #file_put_contents('test.txt', PHP_EOL . json_encode($chat_history));die;

    } else {

      // Set the HTTP response header to indicate that the response is JSON
      header('Content-Type: application/json');

      // Convert the chat history array to JSON and send it as the HTTP response body
      echo json_encode($chat_history);

    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!isset($_POST['chat_id'])) $chat_id = $_GET['chat_id'];

    // Prepare and execute a DELETE statement to delete chat history records for the specified user ID
    $stmt = $db->prepare('DELETE FROM chat_history WHERE chat_id = "'. $chat_id . '"');
    $result = $stmt->execute();
    file_put_contents('test.txt', PHP_EOL . 'DELETE FROM chat_history WHERE chat_id = "'. $chat_id . '"');

    // Set the HTTP response status code to indicate success
    http_response_code(204); // No Content

}

// Close the database connection
$db = NULL;
