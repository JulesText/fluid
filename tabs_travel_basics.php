<html>
<body onload="pertinent()">
<script>
function pertinent() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
        // last window
        // 'reportLists.php?listId=51&type=c',
        // 'reportLists.php?listId=50&type=c',
        // 'reportLists.php?listId=49&type=c',
        // 'reportLists.php?listId=48&type=c',
        // 'reportLists.php?listId=47&type=c',
        // 'reportLists.php?listId=46&type=c',
        'index.php',
        'reportLists.php?listId=13&type=C&content=bulk',
        'reportLists.php?listId=168&type=C&content=bulk',
        'reportLists.php?listId=170&type=c&content=bulk',
        'reportLists.php?listId=145&type=c&content=bulk',
        'reportLists.php?listId=209&type=c&content=bulk',
        'reportLists.php?listId=120&type=c&content=bulk',
        // 'reportLists.php?listId=198&type=c',
        'reportLists.php?listId=56&type=c&content=bulk',
        // 'reportLists.php?listId=197&type=c',
        'reportLists.php?listId=161&type=c&content=bulk',
        'matrix.php?&vLimit=21993&qLimit=k'
        // first/current window
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
