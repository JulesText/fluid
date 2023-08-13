<?php
include_once('header.php');
$item['title'] = 'open ai text';
# if chat_id isset and db record exists
# $values['title'] = summarise
require_once("headerHtml.inc.php");
#echo '<pre>';

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

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
#require_once('Orhanerday/OpenAi/OpenAi.php');
#require_once('Orhanerday/OpenAi/Url.php');

#echo '<div style="height: 100%">';
include_once('Orhanerday/ChatGPT/index.php');
#echo '</div><br><div>';

// $complete = $open_ai->image([
//    "prompt" => "A cat drinking milk",
//    "n" => 1,
//    "size" => "256x256",
//    "response_format" => "url",
// ]);
// var_dump($complete);

#include_once('footer.php');

?>
