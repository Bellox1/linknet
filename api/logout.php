<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$response = ['status' => 'error', 'message' => ''];

try {
    // Détruire la session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    $response = [
        'status' => 'success',
        'message' => 'Déconnexion réussie'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 