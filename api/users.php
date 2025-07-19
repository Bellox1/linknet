<?php
error_reporting(E_ALL);

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU vers la config DB
$db_path = __DIR__ . '/../vues/back-office/config/database.php';
if (!file_exists($db_path)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Fichier de configuration introuvable',
        'debug' => [
            'path_attempted' => $db_path,
            'allowed_paths' => ini_get('open_basedir'),
            'current_dir' => __DIR__
        ]
    ]));
}
require_once $db_path;

if (!isset($conn)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connexion à la base de données non initialisée'
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

$response = ['status' => 'error', 'message' => ''];

try {
    // Récupérer tous les utilisateurs (infos complètes sauf password)
    $stmt = $conn->prepare("SELECT id, username, email, profile_picture, bio, created_at, birthday FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => [
            'users' => $users
        ]
    ];
} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
