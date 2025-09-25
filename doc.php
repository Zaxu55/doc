<?php
// --- CONFIGURATION ---
// The URL of the page that contains the nonce.
$shop_page_url = "https://smartucshop.com/";

// The pattern that finds the nonce inside the JavaScript fetch command.
$nonce_pattern = '/&nonce=([a-zA-Z0-9]+)/';


// --- SCRIPT LOGIC ---

// Set headers to allow requests from any domain (CORS) and specify JSON content type.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Function to send a JSON error message and stop the script.
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// --- Step 1: Fetch the main shop page using cURL ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $shop_page_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$shop_page_html = curl_exec($ch);

// Check for cURL errors during the first fetch
if(curl_errno($ch)){
    send_error('cURL error on fetching shop page: ' . curl_error($ch));
}
curl_close($ch);

if (!$shop_page_html) {
    send_error('Could not fetch the shop page to find the nonce (empty response).');
}

// --- Step 2: Find and extract the nonce ---
preg_match($nonce_pattern, $shop_page_html, $matches);
if (empty($matches[1])) {
    send_error('Could not find a valid nonce on the page. The website may have changed.');
}
$dynamic_nonce = $matches[1];

// --- Step 3: Get Player ID and build final URL ---
$playerId = isset($_GET['player_id']) ? $_GET['player_id'] : null;
if (!$playerId) {
    send_error('Missing player_id parameter.');
}

$api_url = "https://smartucshop.com/wp-admin/admin-ajax.php?action=bgmi_api_check&player_id=" . urlencode($playerId) . "&nonce=" . urlencode($dynamic_nonce);

// --- Step 4: Call the smartucshop API using cURL ---
$ch_api = curl_init();
curl_setopt($ch_api, CURLOPT_URL, $api_url);
curl_setopt($ch_api, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch_api, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$response = curl_exec($ch_api);

// Check for cURL errors during the API call
if(curl_errno($ch_api)){
    send_error('cURL error on API call: ' . curl_error($ch_api));
}
curl_close($ch_api);


if (!$response) {
    send_error('Could not fetch from SmartUcShop API even with the new nonce (empty response).');
}

// --- Step 5: Validate and return the response ---
json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    send_error('Received an invalid (non-JSON) response from the SmartUcShop API.');
}

echo $response;

?>
