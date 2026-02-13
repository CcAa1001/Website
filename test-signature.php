// test-signature.php (create in project root)
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Manual Signature Calculation Test\n";
echo "=====================================\n\n";

$merchantNo = 'U251121123128666000';
$signatureKey = '410ef46c12214d8cb41d2cea6787378c';

// Example from error message
$params = [
    'amount' => '1000',
    'callbackurl' => 'https://defrayable-disingenuously-annalisa.ngrok-free.dev/webhook/nusandana/payment',
    'clientip' => '180.242.198.19',
    'merchantno' => 'U251121123128666000',
    'morderno' => 'WEB1770501574',
    'paycode' => 'qrcode',
    'timestamp' => '1770501574870',
];

echo "Parameters:\n";
print_r($params);
echo "\n";

// Sort by key
ksort($params);

echo "After ksort():\n";
print_r($params);
echo "\n";

// Method 1: http_build_query (default)
$query1 = http_build_query($params);
$string1 = $query1 . '&key=' . $signatureKey;
$sign1 = md5($string1);

echo "Method 1 (http_build_query default):\n";
echo "Query: {$query1}\n";
echo "String to sign: {$string1}\n";
echo "Signature: {$sign1}\n\n";

// Method 2: http_build_query with RFC3986
$query2 = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
$string2 = $query2 . '&key=' . $signatureKey;
$sign2 = md5($string2);

echo "Method 2 (http_build_query RFC3986):\n";
echo "Query: {$query2}\n";
echo "String to sign: {$string2}\n";
echo "Signature: {$sign2}\n\n";

// Method 3: http_build_query with RFC1738
$query3 = http_build_query($params, '', '&', PHP_QUERY_RFC1738);
$string3 = $query3 . '&key=' . $signatureKey;
$sign3 = md5($string3);

echo "Method 3 (http_build_query RFC1738):\n";
echo "Query: {$query3}\n";
echo "String to sign: {$string3}\n";
echo "Signature: {$sign3}\n\n";

// Method 4: Manual build (no encoding)
$parts = [];
foreach ($params as $key => $value) {
    $parts[] = $key . '=' . $value;
}
$query4 = implode('&', $parts);
$string4 = $query4 . '&key=' . $signatureKey;
$sign4 = md5($string4);

echo "Method 4 (Manual - no encoding):\n";
echo "Query: {$query4}\n";
echo "String to sign: {$string4}\n";
echo "Signature: {$sign4}\n\n";

// What Nusandana says it should be:
echo "Expected from error message:\n";
echo "amount=1000&callbackurl=https://defrayable-disingenuously-annalisa.ngrok-free.dev/webhook/nusandana/payment&clientip=180.242.198.19&merchantno=U251121123128666000&morderno=WEB1770501574&paycode=qrcode&timestamp=1770501574870&key={md5key}\n";
echo "\n";

echo "✅ If any method matches the expected string exactly, use that method!\n";