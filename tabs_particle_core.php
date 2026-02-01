<html>
<body onload="particle()">
<script>
function particle() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
        'reportLists.php?listId=134&type=C', // last window
        'reportLists.php?listId=118&type=C&instanceId=11',
        'reportLists.php?listId=118&type=C',
        'reportLists.php?listId=116&type=C',
        'reportLists.php?listId=178&type=c',
        'reportLists.php?listId=184&type=c',
        'reportLists.php?listId=180&type=c',
        'reportLists.php?listId=179&type=c',
        'reportLists.php?listId=177&type=c',
        'reportLists.php?listId=172&type=c&instanceId=11',
        'reportLists.php?listId=172&type=c',
        'reportLists.php?listId=119&type=c&instanceId=11',
        'reportLists.php?listId=119&type=C',
        'reportLists.php?listId=176&type=c',
        'reportLists.php?listId=137&type=c',
        'matrix.php?vLimit=21236&calc=true', // second window etc.
        'matrix.php?vLimit=6030&calc=true' // first/current window
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
