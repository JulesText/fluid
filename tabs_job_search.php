<html>
<body onload="particle()">
<script>
function particle() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
        'itemReport.php?itemId=23020', // last window
        'reportLists.php?listId=122&type=c',
        'itemReport.php?itemId=6229',
        'reportLists.php?listId=108&type=c',
        'matrix.php?qLimit=e&nometa=true&career=true&vLimit=6022',
        'matrix.php?qLimit=e&nometa=true&career=true&vLimit=5282',
        'reportLists.php?listId=107&type=c',
        'reportLists.php?listId=87&type=c', // first/current window
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
