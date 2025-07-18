<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Headers JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Chemin ABSOLU corrigé pour database.php
$db_path = __DIR__ . '/../vues/back-office/config/database.php';

// Inclure le fichier de configuration
require_once $db_path;

$response = ['status' => 'error', 'message' => ''];

try {
    // Vérifier la session
    if (!isset($_SESSION["user"])) {
        throw new Exception("Non autorisé", 401);
    }

    // Accepter les données en GET pour les tests
    if (!isset($_GET["receiver_id"]) || !isset($_GET["message"])) {
        throw new Exception("ID destinataire et message requis", 400);
    }

    $user_id = $_SESSION["user"];
    $receiver_id = $_GET["receiver_id"];
    $message = trim($_GET["message"]);

    // Vérifier que le message n'est pas vide
    if ($message === "") {
        throw new Exception("Le message ne peut pas être vide", 400);
    }

    // Vérifier que le destinataire existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Destinataire non trouvé", 404);
    }

    // Insérer le message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $message]);
    
    $message_id = $conn->lastInsertId();

    // Récupérer le message créé avec les détails
    $stmt = $conn->prepare("
        SELECT id, sender_id, receiver_id, message, created_at, is_read
        FROM messages 
        WHERE id = ?
    ");
    $stmt->execute([$message_id]);
    $new_message = $stmt->fetch(PDO::FETCH_ASSOC);

    // Réponse succès
    $response = [
        'status' => 'success',
        'message' => 'Message envoyé avec succès',
        'data' => [
            'message' => [
                'id' => (int)$new_message['id'],
                'sender_id' => (int)$new_message['sender_id'],
                'receiver_id' => (int)$new_message['receiver_id'],
                'message' => $new_message['message'],
                'created_at' => $new_message['created_at'],
                'is_read' => (bool)$new_message['is_read'],
                'type' => 'sent',
                'time_formatted' => date("h:i A", strtotime($new_message['created_at']))
            ]
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