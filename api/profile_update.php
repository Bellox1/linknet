<?php
error_reporting(E_ALL);
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

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

    // Lecture des infos utilisateur (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("SELECT username, email, birthday, bio, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'user' => $user
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Mise à jour du profil (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer les données POST
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : null;
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : null;
        $birthday = isset($_POST["birthday"]) ? trim($_POST["birthday"]) : null;
        $bio = isset($_POST["bio"]) ? trim($_POST["bio"]) : null;

        // Gestion de l'upload de la photo de profil
        $profile_picture = null;
        if (!empty($_FILES["profile_picture"]["name"])) {
            $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $target_dir = __DIR__ . "/../vues/back-office/uploads/";

            // Supprimer l'ancienne image si ce n'est pas l'image par défaut
            if (!empty($user["profile_picture"]) && $user["profile_picture"] !== "default_profile.jpg") {
                $old_file = $target_dir . $user["profile_picture"];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Nouveau nom unique pour la nouvelle image (timestamp)
            $ext = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
            $filename = $user["username"] . $user_id . "_" . time() . "." . $ext;
            $target_file = $target_dir . $filename;
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $filename;
        }

        // Construction dynamique de la requête SQL
        $fields = [];
        $params = [];

        if ($username !== null) {
            $fields[] = "username = ?";
            $params[] = $username;
        }
        if ($email !== null) {
            $fields[] = "email = ?";
            $params[] = $email;
        }
        if ($birthday !== null) {
            $fields[] = "birthday = ?";
            $params[] = $birthday;
        }
        if ($bio !== null) {
            $fields[] = "bio = ?";
            $params[] = $bio;
        }
        if ($profile_picture !== null) {
            $fields[] = "profile_picture = ?";
            $params[] = $profile_picture;
        }

        if (count($fields) > 0) {
            $params[] = $user_id;
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }

        $response = [
            'status' => 'success',
            'message' => 'Profil mis à jour avec succès'
        ];
    }

} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?> 