<?php

function req($method, $url, $data = [], $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

$baseUrl = 'http://127.0.0.1:8000/api';

echo "1. Testing Login...\n";
$login = req('POST', "$baseUrl/auth/login", [
    'email' => 'test@example.com',
    'password' => 'password'
]);

if ($login['code'] !== 200) {
    echo "Login failed: " . json_encode($login['body']) . "\n";
    exit(1);
}

$token = $login['body']['data']['token'] ?? $login['body']['token'] ?? null;
if (!$token) {
    // Try to find token in structure
    // Based on AuthController typically:
    $token = $login['body']['access_token'] ?? null;
}

if (!$token) {
     echo "Token not found in response: " . json_encode($login['body']) . "\n";
     exit(1);
}

echo "Login Success. Token: " . substr($token, 0, 10) . "...\n";

echo "\n2. Testing Profile (should be 200)...\n";
$profile = req('GET', "$baseUrl/user/profile", [], $token);
echo "Status: " . $profile['code'] . "\n";
if ($profile['code'] !== 200) {
    echo "Profile check failed.\n";
    exit(1);
}

echo "\n3. Testing Logout (should be 200)...\n";
$logout = req('POST', "$baseUrl/auth/logout", [], $token);
echo "Status: " . $logout['code'] . "\n";
echo "Response: " . json_encode($logout['body']) . "\n";

echo "\n4. Testing Profile after Logout (should be 401)...\n";
$profileAfter = req('GET', "$baseUrl/user/profile", [], $token);
echo "Status: " . $profileAfter['code'] . "\n";

if ($profileAfter['code'] === 401) {
    echo "\nProfile correctly revoked (401).\n";
} else {
    echo "\nWARNING: Profile still accessible.\n";
}

echo "\n5. Testing Repeated Logout (should be 200 now)...\n";
$logout2 = req('POST', "$baseUrl/auth/logout", [], $token);
echo "Status: " . $logout2['code'] . "\n";

if ($logout2['code'] === 200) {
    echo "\nVERIFICATION SUCCESSFUL: Logout is idempotent.\n";
} else {
    echo "\nVERIFICATION FAILED: Logout returned error: " . $logout2['code'] . "\n";
}
