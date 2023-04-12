<?php

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Prepare the INSERT statement
    $stmt = $db->prepare('INSERT INTO chat_history (chat_id, comment_human) VALUES (:chat_id, :comment_human)');

    // Bind the parameters and execute the statement
    $stmt->bindValue(':chat_id', $_POST['chat_id'], PDO::PARAM_STR);
    $stmt->bindValue(':comment_human', $_POST['msg'], PDO::PARAM_STR);
    $stmt->execute();

    // Set the HTTP response header to indicate that the response is JSON
    header('Content-Type: application/json');

    // data
    $data = [
        "comment_id" => $db->lastInsertId()
    ];
#    file_put_contents('test.txt', PHP_EOL . $data['id'], FILE_APPEND);

    // Convert the chat history array to JSON and send it as the HTTP response body
    echo json_encode($data);

    // Close the database connection
    $db = NULL;

}
