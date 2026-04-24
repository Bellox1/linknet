<?php

/* -----------------------------------------------------------------
   Nouvelle connexion (Active) – InfinityFree sql309
------------------------------------------------------------------*/
$host     = 'sql309.infinityfree.com';
$dbname   = 'if0_39453622_linknet'; // ← Ton vrai nom de base ici
$username = 'if0_39453622';
$password = 'bellox123';

try {
    // DSN : on précise le port pour être explicite, mais il est facultatif
    $dsn  = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Vous pouvez maintenant utiliser $conn
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
