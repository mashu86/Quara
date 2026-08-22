<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `quara_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database quara_db created or already exists successfully.\n";
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
}
