<?php include '../includes/header.php';
//session_start();
require_once "../../back-office/config/database.php";

if (!isset($_SESSION["user"])) {
    die("Access non autorisé!");
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/notifications.css">
   
</head>
<body>

<div class="notifications-container">
    <h2>Notifications</h2>
    <?php if (count($notifications) > 0): ?>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification <?= !$notification['is_read'] ? 'unread' : '' ?>">
                <i class="notification-icon">
                    <?php
                        if ($notification["type"] == "friend_request") echo '<i class="fas fa-user-plus"></i>';
                        if ($notification["type"] == "follow") echo '<i class="fas fa-user-friends"></i>';
                        if ($notification["type"] == "like") echo '<i class="fas fa-heart"></i>';
                        if ($notification["type"] == "comment") echo '<i class="fas fa-comment"></i>';
                    ?>
                </i>
                <p>
                    <a href="
                        <?php
                            if ($notification["type"] == "friend_request") echo "../friends/requests.php";
                            elseif ($notification["type"] == "follow") echo "profile.php?user=" . urlencode($notification['username']);
                            elseif ($notification["type"] == "like" || $notification["type"] == "comment") echo "post.php?id=" . $notification["post_id"];
                            else echo "#";
                        ?>
                    " class="notification-link">
                        <?php echo ucfirst(strtolower(htmlspecialchars($notification["username"]))); ?> 
                        <?php
                            if ($notification["type"] == "friend_request") echo "vous a envoyé une demande d'ami.";
                            if ($notification["type"] == "follow") echo "a commencé à vous suivre.";
                            if ($notification["type"] == "like") echo "a aimé votre publication.";
                            if ($notification["type"] == "comment") echo "a commenté votre publication.";
                        ?>
                    </a>
                </p>
                <?php if (!$notification['is_read']): ?>
                    <button class="mark-read" data-id="<?= $notification['id'] ?>">Marquer comme lue</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune nouvelle notification.</p>
    <?php endif; ?>
</div>


<script>
    document.querySelectorAll('.mark-read').forEach(button => {
        button.addEventListener('click', function() {
            let notificationId = this.getAttribute('data-id');
            fetch('mark_notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + notificationId
            }).then(response => response.text())
              .then(data => {
                if (data === "success") {
                    this.parentElement.classList.remove("unread");
                    this.remove();
                }
              });
        });
    });
</script>
</*?php include '../includes/footer.php'; ?*/>

</body>
</html>
