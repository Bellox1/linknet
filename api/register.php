<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Inclure la connexion à la base de données
$db_path = __DIR__ . '/../vues/back-office/config/database.php';
require_once $db_path;

$response = ['status' => 'error', 'message' => ''];

try {
    // Récupérer les données POST (JSON ou formulaire)
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    // Vérifier les paramètres requis
    if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
        throw new Exception("Nom d'utilisateur, email et mot de passe requis", 400);
    }

    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];

    // Vérifier si l'utilisateur ou l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        throw new Exception("Nom d'utilisateur ou email déjà utilisé", 409);
    }

    // Hasher le mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le nouvel utilisateur
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password_hash, 'default_profile.jpg']);
    $user_id = $conn->lastInsertId();

    // Démarrer la session
    $_SESSION['user'] = $user_id;

    $response = [
        'status' => 'success',
        'message' => "Inscription réussie",
        'user' => [
            'id' => (int)$user_id,
            'username' => $username,
            'email' => $email,
            'profile_picture' => 'default_profile.jpg'
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 