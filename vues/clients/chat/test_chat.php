<?php
error_reporting(E_ALL);

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU corrigé pour database.php
$db_path = __DIR__ . '/../../back-office/config/database.php';

// Vérifier que le fichier existe et est accessible
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

// Inclure le fichier de configuration
require_once $db_path;

// Vérifier si la connexion $conn existe
if (!isset($conn)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connexion à la base de données non initialisée'
    ]));
}

$response = ['status' => 'error', 'message' => ''];

try {
    // Test simple sans session
    if (!isset($_GET["user"])) {
        throw new Exception("ID utilisateur requis", 400);
    }

    $receiver_id = $_GET["user"];

    // Récupérer les détails du destinataire
    $stmt = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiver) {
        throw new Exception("Destinataire non trouvé", 404);
    }

    // Réponse succès simple
    $response = [
        'status' => 'success',
        'data' => [
            'receiver' => [
                'id' => (int)$receiver['id'],
                'username' => $receiver['username'],
                'profile_picture' => $receiver['profile_picture'] ?: 'default_profile.jpg'
            ],
            'test_message' => 'Fichier accessible !'
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 