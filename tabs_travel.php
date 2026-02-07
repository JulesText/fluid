<html>
<body onload="pertinent()">
<script>
function pertinent() {

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    var windows = [
        'ical://x', // last window
        'media/flow.gif',
        'https://www.google.com/maps/@17.920191,132.4221915,3z?entry=ttu&g_ep=EgoyMDI1MDkwNy4wIKXMDSoASAFQAw%3D%3D',
        'https://www.atlasobscura.com/articles/all-places-in-the-atlas-on-one-map',
        'reportLists.php?listId=198&type=c',
        'reportLists.php?listId=197&type=c',
        'reportLists.php?listId=211&type=c',
        'itemReport.php?itemId=7161',
        'reportLists.php?listId=196&type=c',
        'matrix.php?&vLimit=5278&qLimit=k',
        'matrix.php?&vLimit=5258&qLimit=k',
        'matrix.php?&vLimit=6028&qLimit=k',
        'matrix.php?&vLimit=2196&qLimit=k',
        'matrix.php?&vLimit=10542&qLimit=k',
        'matrix.php?&vLimit=5252&qLimit=k',
        'matrix.php?&vLimit=21993&qLimit=k',
        'reportLists.php?listId=161&type=c', // second window
        'editListItems.php?itemId=8492&type=c', // first/current window
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
