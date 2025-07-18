<?php
// session_start(); // Décommente si la session n’est pas déjà démarrée ailleurs
require_once __DIR__ . '/../back-office/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION["user"])) {
    http_response_code(401);
    echo json_encode(["error" => "Non authentifié"]);
    exit;
}

$user_id = $_SESSION["user"];

$stmt = $conn->prepare("
    SELECT notifications.id, notifications.type, notifications.post_id, notifications.is_read, users.username 
    FROM notifications 
    JOIN users ON notifications.sender_id = users.id 
    WHERE notifications.user_id = ?
    ORDER BY notifications.created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["notifications" => $notifications]);
