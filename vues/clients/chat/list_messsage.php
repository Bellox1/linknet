<?php
session_start();
require_once "../../back-office/config/database.php";

if (!isset($_SESSION["user"]) || !isset($_GET["user"])) {
    die("Unauthorized access!");
}

$user_id = $_SESSION["user"];
$receiver_id = $_GET["user"];

// Fetch receiver details
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->execute([$receiver_id]);
$receiver = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch messages
$stmt = $conn->prepare("
    SELECT m.sender_id, m.message, m.created_at, u.profile_picture, u.username
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Discussion avec <?php echo ucfirst(strtolower(htmlspecialchars($receiver['username']))); ?></title>
    <link rel="stylesheet" href="/assets/css/message.css">
    <script>
        window.USER_ID = <?php echo json_encode($user_id); ?>;
    </script>
    <script src="message.js" defer></script>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <img src="/vues/back-office/uploads/<?php echo $receiver['profile_picture'] ?: 'default_profile.jpg'; ?>" alt="Photo de profil">
        <h2><?php echo ucfirst(strtolower(htmlspecialchars($receiver['username']))); ?></h2>
    </div>

    <div id="chat-box">
        <?php foreach ($messages as $message): ?>
            <div class="chat-message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                <p><?php echo htmlspecialchars($message['message']); ?></p>
                <span class="chat-time"><?php echo date("H:i", strtotime($message['created_at'])); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <form id="chat-form">
        <input type="hidden" id="receiver_id" value="<?php echo $receiver_id; ?>">
        <input type="text" id="message" placeholder="Écrivez un message..." autocomplete="off">
        <button type="submit">Envoyer</button>
    </form>
</div>
<a class="back-button" href="/vues/clients/chat/"></a>



</body>
</html>
