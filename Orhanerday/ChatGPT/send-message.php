<?php

// Create a new SQLite database connection
require_once('../../headerDB.inc.php');
$db = new PDO('mysql:host=' . $config["host"] . ';dbname=' . $config["db"], $config["user"], $config["pass"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Prepare the INSERT statement
    $stmt = $db->prepare('INSERT INTO chat_history (user_id, human) VALUES (:user_id, :human)');

    // Bind the parameters and execute the statement
    $stmt->bindValue(':user_id', $_POST['user_id'], PDO::PARAM_STR);
    $stmt->bindValue(':human', $_POST['msg'], PDO::PARAM_STR);
    $stmt->execute();

    // Set the HTTP response header to indicate that the response is JSON
    header('Content-Type: application/json');

    // data
    $data = [
        "id" => $db->lastInsertId()
    ];
#    file_put_contents('test.txt', PHP_EOL . $data['id'], FILE_APPEND);

    // Convert the chat history array to JSON and send it as the HTTP response body
    echo json_encode($data);

    // Close the database connection
    $db = NULL;

}
