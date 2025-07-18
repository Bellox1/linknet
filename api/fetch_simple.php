<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Test simple sans base de données
$response = ['status' => 'error', 'message' => ''];

try {
    // Vérifier la session
    if (!isset($_SESSION["user"])) {
        throw new Exception("Non autorisé", 401);
    }

    if (!isset($_GET["receiver_id"])) {
        throw new Exception("ID destinataire requis", 400);
    }

    $user_id = $_SESSION["user"];
    $receiver_id = $_GET["receiver_id"];

    // Réponse de test
    $response = [
        'status' => 'success',
        'message' => 'fetch_simple.php fonctionne',
        'data' => [
            'user_id' => (int)$user_id,
            'receiver_id' => (int)$receiver_id,
            'messages' => [],
            'conversation_info' => [
                'total_messages' => 0,
                'unread_count' => 0
            ]
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 