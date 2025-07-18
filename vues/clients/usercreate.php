<?php
require_once "../back-office/config/database.php";

$username = "user";
$email = "user@gmail.com";
$password = password_hash("user123", PASSWORD_DEFAULT);
$birthday = null;
$profile_picture = null;
$bio = null;

$stmt = $conn->prepare("INSERT INTO users (username, email, password, birthday, profile_picture, bio) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$username, $email, $password, $birthday, $profile_picture, $bio]);

echo "Utilisateur 'user' créé !";
?>
