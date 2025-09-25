<?php
// --- CONFIGURATION ---
// These values have been updated based on the source code you provided.

// The URL of the page that contains the nonce.
$shop_page_url = "https://smartucshop.com/";

// The pattern that finds the nonce inside the JavaScript fetch command.
$nonce_pattern = '/&nonce=([a-zA-Z0-9]+)/';


// --- SCRIPT LOGIC (No changes needed below this line) ---

// Set headers to allow requests from any domain (CORS) and specify JSON content type.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Function to send a JSON error message and stop the script.
function send_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Fetch the main shop page to find the nonce.
$shop_page_html = @file_get_contents($shop_page_url);
if (!$shop_page_html) {
    send_error('Could not fetch the shop page to find the nonce.');
}

// Find and extract the nonce from the HTML using the correct pattern.
preg_match($nonce_pattern, $shop_page_html, $matches);
if (empty($matches[1])) {
    send_error('Could not find a valid nonce on the page. The website may have changed.');
}
$dynamic_nonce = $matches[1]; // The freshly scraped nonce.

// Get the Player ID from the request.
$playerId = isset($_GET['player_id']) ? $_GET['player_id'] : null;
if (!$playerId) {
    send_error('Missing player_id parameter.');
}

// Build the final API URL with the Player ID and the dynamic nonce.
$api_url = "https://smartucshop.com/wp-admin/admin-ajax.php?action=bgmi_api_check&player_id=" . urlencode($playerId) . "&nonce=" . urlencode($dynamic_nonce);

// Call the smartucshop API.
$response = @file_get_contents($api_url);
if (!$response) {
    send_error('Could not fetch from SmartUcShop API even with the new nonce.');
}

// Check if the response is valid JSON before sending it back.
json_decode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    send_error('Received an invalid (non-JSON) response from the SmartUcShop API.');
}

// Send the final, valid JSON response back to your Wix form.
echo $response;

?>
