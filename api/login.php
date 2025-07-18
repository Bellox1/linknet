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
    if (empty($input['username']) || empty($input['password'])) {
        throw new Exception("Nom d'utilisateur et mot de passe requis", 400);
    }

    $username = trim($input['username']);
    $password = $input['password'];

    // Vérifier si l'utilisateur existe
    $stmt = $conn->prepare("SELECT id, username, password, email, profile_picture FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception("Identifiants invalides", 401);
    }

    // Authentification réussie, démarrer la session
    $_SESSION['user'] = $user['id'];

    $response = [
        'status' => 'success',
        'message' => 'Connexion réussie',
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'profile_picture' => $user['profile_picture']
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
