<?php
// 0. setup router with static ip $home_ip and map port 8443 to fixed local ip of machine with different port running DEVONthink server. Use a self-signed SSL certificate on the DEVONthink server and import it into your office browser to avoid SSL warnings. This script will act as a secure gateway to your home DEVONthink server

// 1. CONFIGURE YOUR SECURITY PASSPHRASE
$secret_password = "xxx";

// 2. CONFIGURATION PARAMETERS FOR YOUR HOME MACHINE
$home_ip = "x.x.x.x"; // Update this to your DDNS address later if needed
$home_port = 8443;

// 3. SECURE COOKIE SESSION MANAGEMENT (30 DAYS)
$cookie_name = "devon_gate_session";
$authenticated = false;

// Check if a valid 30-day cookie already exists in the browser
if (isset($_COOKIE[$cookie_name])) {
    // Verify the cookie content matches a secure hash of your password
    $expected_hash = hash('sha256', $secret_password . $_SERVER['HTTP_USER_AGENT']);
    if ($_COOKIE[$cookie_name] === $expected_hash) {
        $authenticated = true;
    }
}

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gate_password'])) {
    if ($_POST['gate_password'] === $secret_password) {
        $authenticated = true;
        // Generate a fingerprint hash locked to your specific office browser app
        $cookie_value = hash('sha256', $secret_password . $_SERVER['HTTP_USER_AGENT']);
        $expiry_time = time() + (30 * 24 * 60 * 60); // 30 days in seconds
        
        // Set a secure, HTTP-only cookie that JavaScript cannot steal
        setcookie($cookie_name, $cookie_value, [
            'expires' => $expiry_time,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        // Refresh the page to clear the POST data and load the interface
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_msg = "Incorrect access password.";
    }
}

// 4. DISPLAY THE LOGIN SCREEN IF NOT AUTHENTICATED
if (!$authenticated) {
    header('HTTP/1.0 401 Unauthorized');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Gateway</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 320px; text-align: center; }
            h2 { color: #1d1d1f; margin-bottom: 20px; font-size: 22px; font-weight: 600; }
            input[type="password"] { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #d2d2d7; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
            button { width: 100%; padding: 12px; background-color: #0071e3; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 500; cursor: pointer; }
            button:hover { background-color: #0077ed; }
            .error { color: #ff3b30; font-size: 14px; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>System Gateway</h2>
            <?php if (isset($error_msg)) echo "<div class='error'>$error_msg</div>"; ?>
            <form method="POST">
                <input type="password" name="gate_password" placeholder="Enter passphrase" required autofocus>
                <button type="submit">Unlock for 30 Days</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 5. THE PROXY ENGINE (Only runs if the 30-day cookie validation passes)
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

$headers = [];
foreach (getallheaders() as $key => $value) {
    if (strcasecmp($key, 'Host') !== 0 && strcasecmp($key, 'Cookie') !== 0) {
        $headers[] = "$key: $value";
    }
}

// Rebuild cookies to isolate your work browser from your home DEVONthink server cookies
$devon_cookies = [];
foreach ($_COOKIE as $key => $value) {
    if ($key !== $cookie_name) {
        $devon_cookies[] = "$key=$value";
    }
}
if (!empty($devon_cookies)) {
    $headers[] = "Cookie: " . implode('; ', $devon_cookies);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$home_ip:$home_port$request_uri");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

if ($request_method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Gateway Connection Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$response_headers = substr($response, 0, $header_size);
$response_body = substr($response, $header_size);

foreach (explode("\r\n", $response_headers) as $header) {
    if (!empty($header) && stripos($header, 'Transfer-Encoding') === false) {
        header($header);
    }
}

echo $response_body;
?>
