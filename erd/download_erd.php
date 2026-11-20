<?php
$mmd_path = __DIR__ . '/sipala_erd.mmd';
$output_path = __DIR__ . '/sipala_erd.png';

if (!file_exists($mmd_path)) {
    die("Error: sipala_erd.mmd not found\n");
}

$mmd = file_get_contents($mmd_path);

// The payload for mermaid ink should be a base64 encoded JSON string
// containing the 'code' and 'mermaid' configuration
$payload = json_encode([
    'code' => $mmd,
    'mermaid' => ['theme' => 'default']
]);

$base64 = base64_encode($payload);
$url = "https://mermaid.ink/img/" . $base64;

echo "Downloading from: " . substr($url, 0, 50) . "...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("cURL error: " . curl_error($ch) . "\n");
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    file_put_contents($output_path, $response);
    echo "Successfully downloaded ERD to $output_path\n";
} else {
    echo "Error generating ERD. HTTP Code: $httpCode\n";
    if ($response) {
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
}
