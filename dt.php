<?php
   header("Access-Control-Allow-Origin: http://localhost/");
   header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
   header("Access-Control-Allow-Headers: Content-Type");
   echo 'ok';
?>
<html>
  <body>
    <h1>Run JXA Script</h1>

    <button onclick="executeScript()">Run Script</button>
    <script>
      function executeScript() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "http://localhost/fluid/dt.js", true);
        xhr.send();
      }
    </script>
  </body>
</html>
