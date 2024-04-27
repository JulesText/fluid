<?php

include_once('header.php');
$item['title'] = 'ai chat';
require_once("headerHtml.inc.php");

$api_file = 'ai.php';

include('ai_require.php');
$other_chats = get_other_chats($chat_id, $db);
$db = NULL; // Close the database connection

?>

<link rel="stylesheet" href="themes/default/ai_style.css">

<div class="conta <?php
  if (isMobile()) echo "contamob";
  else echo "contabro";
?>">

<section class="msger">

    <header class="msger-header">
        <div class="msger-header-title">
            <i class="fas fa-comment-alt"></i>
            <input type="text" id="chat_id" hidden>
            <span class="chat_summary"></span>
        </div>
        <div class="msger-header-options">
          <button id="summary-button">Summary</button>
          <button id="newchat-button">New</button>
          <button id="history-button">Hist</button>
          <button id="quit-button">Exit</button>
          <button id="delete-button">Del</button>
          <button id="model-button">Model</button>
          <span class="model_id"></span>
          <button id="word-button">Words</button>
          <span class="word_count" style="opacity:0"></span>
        </div>
    </header>

    <div id="popup-menu">
      <a href="">X</a>
      <?php
      foreach ($other_chats as $row) {
        $descrip = $row["chat_summary"];
        if ($descrip == NULL) $descrip = $row["chat_id"];
        echo '<br>
        <a class="history-link" onClick="summariseChat(
          \'' . $row["chat_id"] . '\',\'hist\'
          )">Summarise</a>&nbsp;
        <a class="history-link" onClick="deleteChatHistory(\'' . $row["chat_id"] . '\',\'hist\')">Delete</a>&nbsp;
        <a class="history-link" href="' . $api_file . '?chat_id=' . $row["chat_id"] . '"
          id="' . $row["chat_id"] . '">
          ' . $descrip . '
        </a>&nbsp;&nbsp;
        ';
      }
      ?>
    </div>

    <main class="msger-chat" id="area-scroll">
    </main>

    <form class="msger-inputarea">
        <textarea rows="2" class="msger-input" placeholder="Enter your message..." require></textarea>
        <button type="submit" class="msger-send-btn">Send</button>
    </form>
</section>
<script>
const api_file = '<?php echo $api_file; ?>';
</script>
<script src="ai_script.js"></script>
</div>
