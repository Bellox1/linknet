<?php
error_reporting(E_ALL);
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU vers la config
$db_path = __DIR__ . '/../vues/back-office/config/database.php';
if (!file_exists($db_path)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Fichier de configuration introuvable'
    ]));
}
require_once $db_path;

if (!isset($conn)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connexion à la base de données non initialisée'
    ]));
}

$response = ['status' => 'error', 'message' => ''];

try {
    if (!isset($_SESSION["user"])) {
        throw new Exception("Non autorisé", 401);
    }
    $user_id = $_SESSION["user"];

    // Récupérer tous les amis (status = accepted, et exclure soi-même)
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.profile_picture, u.birthday
        FROM friends f
        JOIN users u ON (u.id = f.sender_id OR u.id = f.receiver_id)
        WHERE (f.sender_id = :uid OR f.receiver_id = :uid)
          AND f.status = 'accepted'
          AND u.id != :uid
        ORDER BY u.username ASC
    ");
    $stmt->execute(['uid' => $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => $friends
    ];
} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>