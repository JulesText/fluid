const express = require('express');
const { exec } = require('child_process');
const app = express();

app.get('/fluid/dt', (req, res) => {
    exec('osascript /test.js', (error, stdout, stderr) => {
        if (error) {
            console.error(`Error: ${error.message}`);
            return res.status(500).send('Error executing JXA script');
        }
        console.log(`JXA Script Execution Result: ${stdout}`);
        res.send(stdout);
    });
});

app.listen(3000, () => {
    console.log('Server running on port 3000');
});
