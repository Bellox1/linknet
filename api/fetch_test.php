<?php
// Test simple pour diagnostiquer le problème 404
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

echo json_encode([
    'status' => 'success',
    'message' => 'fetch_test.php fonctionne correctement',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 