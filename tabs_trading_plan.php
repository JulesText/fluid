<html>
<body onload="pertinent()">
<script>
function pertinent() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
        'itemReport.php?itemId=20175', // last window
        'itemReport.php?itemId=19269',
        'itemReport.php?itemId=19271',
        'itemReport.php?itemId=19276',
        'reportLists.php?listId=191&type=c',
        'itemReport.php?itemId=19268',
        'reportLists.php?listId=188&type=c',
        'itemReport.php?itemId=19266',
        'itemReport.php?itemId=19275',
        'itemReport.php?itemId=19267', // second window
        'matrix.php?vLimit=5233&qLimit=c&live=true&nometa=true&live=true', // first/current window
    ];

    var arrayLength = windows.length;

    async function display() {
        for (var i = 0; i < arrayLength - 1; i++) { // skip the last array item
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
