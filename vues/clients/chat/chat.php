<?php
error_reporting(E_ALL);

// Démarrer la session
session_start();

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
    // Vérifier la session et les paramètres
    if (!isset($_SESSION["user"])) {
        throw new Exception("Non autorisé", 401);
    }

    if (!isset($_GET["user"])) {
        throw new Exception("ID utilisateur requis", 400);
    }

    $user_id = $_SESSION["user"];
    $receiver_id = $_GET["user"];

    // Récupérer les détails du destinataire
    $stmt = $conn->prepare("SELECT id, username, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiver) {
        throw new Exception("Destinataire non trouvé", 404);
    }

    // Récupérer les messages de la conversation
    $stmt = $conn->prepare("
        SELECT id, sender_id, receiver_id, message, created_at, is_read
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les messages pour inclure le type (envoyé/reçu)
    $formatted_messages = [];
    foreach ($messages as $message) {
        $formatted_messages[] = [
            'id' => (int)$message['id'],
            'sender_id' => (int)$message['sender_id'],
            'receiver_id' => (int)$message['receiver_id'],
            'message' => $message['message'],
            'created_at' => $message['created_at'],
            'is_read' => (bool)$message['is_read'],
            'type' => ($message['sender_id'] == $user_id) ? 'sent' : 'received',
            'time_formatted' => date("h:i A", strtotime($message['created_at']))
        ];
    }

    // Réponse succès
    $response = [
        'status' => 'success',
        'data' => [
            'receiver' => [
                'id' => (int)$receiver['id'],
                'username' => $receiver['username'],
                'profile_picture' => $receiver['profile_picture'] ?: 'default_profile.jpg'
            ],
            'messages' => $formatted_messages,
            'conversation_info' => [
                'total_messages' => count($formatted_messages),
                'unread_count' => count(array_filter($formatted_messages, function($msg) use ($user_id) {
                    return $msg['receiver_id'] == $user_id && !$msg['is_read'];
                }))
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
