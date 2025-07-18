<?php
// ✅ Allow requests from any domain (for fetch)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST");

$playerId = isset($_GET['player_id']) ? $_GET['player_id'] : null;
if (!$playerId) {
    echo json_encode(['error' => '❌ Missing player_id']);
    exit;
}

$nonce = 'f0865f39b8';  // 🔁 Daily update karna
$url = "https://smartucshop.com/wp-admin/admin-ajax.php?action=bgmi_api_check&player_id=" . urlencode($playerId) . "&nonce=" . $nonce;

$response = @file_get_contents($url);
if (!$response) {
    echo json_encode(['error' => '❌ Could not fetch from SmartUcShop']);
    exit;
}

header('Content-Type: application/json');
echo $response;
