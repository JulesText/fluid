<?php
#include_once('header.php');
#require_once("headerHtml.inc.php");
echo '<pre>';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function autoloader($class_name) {
    $class_name = str_replace('\\', '/', $class_name);
    $file_path = $class_name . '.php';
    if (file_exists($file_path)) {
      require_once $file_path;
    } else {
      echo $file_path;
    }
}
spl_autoload_register('autoloader');

$openaiClient = \Tectalic\OpenAi\Manager::build(
    new \GuzzleHttp\Client(),
    new \Tectalic\OpenAi\Authentication(getenv('OPENAI_API_KEY'))
);

/** @var \Tectalic\OpenAi\Models\ChatCompletions\CreateResponse $response */
$response = $openaiClient->chatCompletions()->create(
    new \Tectalic\OpenAi\Models\ChatCompletions\CreateRequest([
        'model' => 'gpt-4',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Will using a well designed and supported third party package save time?'
            ],
        ],
    ])
)->toModel();

echo $response->choices[0]->message->content;

include_once('footer.php');

?>
