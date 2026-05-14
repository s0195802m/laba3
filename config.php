<?php
// config.php - Конфигурация подключения к базе данных

$host = 'localhost';
$dbname = 'u82461';        // Имя БД совпадает с логином
$username = 'u82461';      // Логин от сервера
$password = '3874492';  // Пароль от сервера 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>