<?php
require_once "../includes/header.php";
require_once __DIR__ . '/../../back-office/config/database.php';

if (!isset($_SESSION["user"])) {
    die("Unauthorized access!");
}

$user_id = $_SESSION["user"];

// Fetch friends list with last message
$stmt = $conn->prepare("
    SELECT users.id, users.username, users.profile_picture, 
        (SELECT message FROM messages 
         WHERE (sender_id = users.id AND receiver_id = ?) 
            OR (sender_id = ? AND receiver_id = users.id) 
         ORDER BY created_at DESC LIMIT 1) AS last_message
    FROM friends 
    JOIN users ON 
        (friends.sender_id = users.id OR friends.receiver_id = users.id) 
    WHERE (friends.sender_id = ? OR friends.receiver_id = ?) 
        AND friends.status = 'accepted' 
        AND users.id != ?
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Discussion</title>
    <link rel="stylesheet" href="/assets/css/message_dash.css">
    <script src="message.js"></script>
</head>
<body>
    <div class="chat-container">
        <h2>Discussion</h2>

        <h3>Amis</h3>
        <ul class="chat-user-list">
            <?php foreach ($friends as $friend): ?>
                <li>
                    <a href="list_messsage.php?user=<?php echo $friend['id']; ?>" class="chat-user">
                        <img src="/vues/back-office/uploads/<?php echo $friend['profile_picture'] ?: 'default_profile.jpg'; ?>" alt="Profil">
                        <div class="chat-user-info">
                            <span class="chat-username"><?php echo ucfirst(strtolower(htmlspecialchars($friend['username']))); ?></span>
                            <span class="chat-last-message">
                                <?php echo $friend['last_message'] ? htmlspecialchars($friend['last_message']) : "Aucun message pour l'instant"; ?>
                            </span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
