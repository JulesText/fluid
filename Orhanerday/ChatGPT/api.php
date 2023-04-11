<?php

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

// Get the user ID from the request data
$user_id = $_POST['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Prepare and execute a SELECT statement to retrieve the chat history data
    $stmt = $db->prepare('SELECT human, ai, date FROM chat_history WHERE user_id = "'. $user_id . '" ORDER BY id ASC');
    $stmt->execute();

    // Fetch the results and store them in an array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $chat_history = array();
    foreach ($result as $row) {
      $chat_history[] = $row;
    }

    // Set the HTTP response header to indicate that the response is JSON
    header('Content-Type: application/json');

    // Convert the chat history array to JSON and send it as the HTTP response body
    echo json_encode($chat_history);

}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    // Prepare and execute a DELETE statement to delete chat history records for the specified user ID
    $stmt = $db->prepare('DELETE FROM chat_history WHERE user_id = "'. $user_id . '"');
    $result = $stmt->execute();

    // Set the HTTP response status code to indicate success
    http_response_code(204); // No Content

}

// Close the database connection
$db = NULL;
