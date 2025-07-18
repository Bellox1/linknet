<?php
// config/db.php
$host     = 'sql203.infinityfree.com';
$dbname   = 'if0_39310327_linknet';
$username = 'if0_39310327';
$password = 'z3fMhkcseZyc';
/*
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
*/

try {
    /** @var PDO $conn */
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Connexion BD impossible : ' . $e->getMessage());
}
?>
