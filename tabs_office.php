<html>
  <body onload="pertinent()">
    <script>
      function pertinent() {
        function sleep(ms) {
          return new Promise((resolve) => setTimeout(resolve, ms));
        }

        var windows = [
          "https://w.jules.net.au/", // last window
          "itemReport.php?itemId=7348",
          "itemReport.php?itemId=7346",
          "itemReport.php?itemId=7338",
          "itemReport.php?itemId=21270",
          "https://jira.infra.abs.gov.au/secure/RapidBoard.jspa?rapidView=56000&view=detail&selectedIssue=NVDA-8&quickFilter=97475#",
          "reportLists.php?listId=148&type=C",
          "https://absgovau-my.sharepoint.com/:x:/r/personal/jules_kelty_abs_gov_au/Documents/time%20sheet.xlsx?d=w01f1c6c2b46f4bd5a8bc2318cc9d4e90&csf=1&web=1&e=N8hh72", // second window
          "reportLists.php?listId=146&type=c", // first/current window
        ];

        var arrayLength = windows.length;

        async function display() {
          for (var i = 0; i < arrayLength - 1; i++) {
            // skip the last array item
            window.open(windows[i]);
            await sleep(200); // avoid windows from not opening due to server restrictions
          }
          window.location.assign(windows[i]); // first/current window
        }

        display();

        return true;
      }
    </script>
  </body>
</html>
