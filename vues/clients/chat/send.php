<?php
session_start();
require_once "../../back-office/config/database.php";

if (!isset($_SESSION["user"]) || !isset($_POST["receiver_id"]) || !isset($_POST["message"])) {
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized access!"]);
        exit;
    }
    die("Unauthorized access!");
}

$user_id = $_SESSION["user"];
$receiver_id = $_POST["receiver_id"];
$message = trim($_POST["message"]);

if ($message !== "") {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $receiver_id, $message]);
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode(["success" => true]);
        exit;
    }
}

if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode(["success" => false]);
    exit;
}

echo "Message sent!";
?>
