<?php
session_start();
require_once "../../back-office/config/database.php";

if (!isset($_SESSION["user"]) || !isset($_GET["receiver_id"])) {
    die("Unauthorized access!");
}

$user_id = $_SESSION["user"];
$receiver_id = $_GET["receiver_id"];

$stmt = $conn->prepare("
    SELECT sender_id, message, created_at 
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC
");
$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    // Ajoute l'heure formatée pour l'affichage
    foreach ($messages as &$msg) {
        $msg['time'] = date("H:i", strtotime($msg['created_at']));
    }
    echo json_encode(["messages" => $messages]);
    exit;
}

foreach ($messages as $message) {
    echo "<p><strong>" . (($message['sender_id'] == $user_id) ? "You" : "Them") . ":</strong> " . htmlspecialchars($message['message']) . "</p>";
}
?>
