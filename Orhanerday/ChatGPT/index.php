<?
# todo:
# request response in markdown and format (in js?)
# fix summarise.php calls
# allow select chat from history (db > chat_summary)
# auto scroll down as response is incoming
# delete redundant composer packages (only one is Orhanerday)
# delete redundant code here
# lighten css

#https://github.com/orhanerday/open-ai
$api_path = 'Orhanerday/ChatGPT/';
?>
<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chat GPT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <link rel="stylesheet" href="<?php echo $api_path; ?>style.css">
<!-- </head>

<body> -->

<!--
<div class="sidebar">
  <p class="tablink sidebar-header">Create Chat</h2>
    <button class="tablink" onclick="openTab(event, 'tab1')">Lorem ipsum</button>
    <button class="tablink" onclick="openTab(event, 'tab2')">Dolor sit amet</button>
</div>
-->

<div class="conta <?php
  if (isMobile()) echo "contamob";
  else echo "contabro";
?>">
<section class="msger">

    <header class="msger-header">
        <div class="msger-header-title">
            <i class="fas fa-comment-alt"></i>
            <input type="text" id="chat_id" hidden>
            <span class="id_session"></span>
        </div>
        <div class="msger-header-options">
          <button id="summary-button">Summarise</button>
          <button id="quit-button">Exit</button>
          <button id="chat-button">New</button>
          <button id="delete-button">Delete Chat</button>
        </div>
    </header>

    <main class="msger-chat">
    </main>

    <form class="msger-inputarea">
        <!--<input class="msger-input" placeholder="Enter your message..." require>-->
        <textarea rows="2" class="msger-input" placeholder="Enter your message..." require></textarea>
        <button type="submit" class="msger-send-btn">Send</button>
    </form>
</section>
<script src="<?php echo $api_path; ?>script.js"></script>
</div>

<!--
<script>
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
-->
<!--
</body>

</html>
-->
