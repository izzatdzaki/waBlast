<?php
// Get CSRF token dari Laravel
$ch = curl_init('http://127.0.0.1:8000/whatsapp/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
$response = curl_exec($ch);

// Extract CSRF token from HTML
preg_match('/name="_token" content="([^"]+)"/', $response, $matches);
$csrf = $matches[1] ?? null;

if (!$csrf) {
    echo "Error: Could not find CSRF token\n";
    exit(1);
}

echo "✓ CSRF Token: " . substr($csrf, 0, 20) . "...\n\n";

// Now send test message
$ch = curl_init('http://127.0.0.1:8000/api/whatsapp/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: ' . $csrf,
    'Accept: application/json'
]);

$data = json_encode([
    'phone' => '6281234567890',
    'message' => 'Test Message from Script'
]);

echo "Sending request:\n";
echo json_encode(json_decode($data), JSON_PRETTY_PRINT) . "\n\n";

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);

echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
