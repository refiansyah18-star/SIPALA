<?php
$mmd_path = __DIR__ . '/sipala_erd.mmd';
$output_path = __DIR__ . '/sipala_erd.png';

if (!file_exists($mmd_path)) {
    die("Error: sipala_erd.mmd not found\n");
}

$data = file_get_contents($mmd_path);
// Kroki requires zlib deflated, then base64 url-safe encoded string
$compressed = gzcompress($data, 9);
$encoded = strtr(base64_encode($compressed), '+/', '-_');

$url = "https://kroki.io/mermaid/png/" . $encoded;

echo "Downloading from Kroki...\n";

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
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode === 200 && strpos($contentType, 'image') !== false) {
    file_put_contents($output_path, $response);
    echo "Successfully downloaded ERD to $output_path\n";
} else {
    echo "Error generating ERD. HTTP Code: $httpCode\n";
    if ($response) {
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
}
