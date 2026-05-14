<?php
// process.php - Обработчик формы с валидацией и записью в БД
require_once 'config.php';

// Функция для вывода сообщения об ошибке
function showError($message) {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Ошибка валидации</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h2>Задание 3. Ошибка при заполнении формы</h2>
            </div>
        </header>
        <main class="container">
            <div class="error-message">
                <strong>❌ Ошибка:</strong> ' . htmlspecialchars($message) . '
            </div>
            <a href="index.php" class="back-link">← Вернуться к форме</a>
        </main>
        <footer><div class="container"><p>Лабораторная работа №3</p></div></footer>
    </body>
    </html>';
    exit;
}

// Функция для вывода сообщения об успехе
function showSuccess($message, $id = null) {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Успешное сохранение</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h2>Задание 3. Данные успешно сохранены</h2>
            </div>
        </header>
        <main class="container">
            <div class="success-message">
                <strong>✅ ' . htmlspecialchars($message) . '</strong><br>' .
                ($id ? "ID записи: " . htmlspecialchars($id) : "") . '
            </div>
            <a href="index.php" class="back-link">← Заполнить новую форму</a>
        </main>
        <footer><div class="container"><p>Лабораторная работа №3</p></div></footer>
    </body>
    </html>';
    exit;
}

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showError("Форма не была отправлена.");
}

// ==================== ВАЛИДАЦИЯ ПОЛЕЙ ====================

// 1. ФИО: только буквы, пробелы, дефис, длина ≤ 150
$full_name = trim($_POST['full_name'] ?? '');
if (empty($full_name)) {
    showError("Поле 'ФИО' обязательно для заполнения.");
}
if (strlen($full_name) > 150) {
    showError("Поле 'ФИО' не должно превышать 150 символов.");
}
if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $full_name)) {
    showError("Поле 'ФИО' должно содержать только буквы, пробелы и дефис.");
}

// 2. Телефон: российский формат +7 или 8, далее 10 цифр
$phone = trim($_POST['phone'] ?? '');
if (empty($phone)) {
    showError("Поле 'Телефон' обязательно для заполнения.");
}
$phone_clean = preg_replace('/[^0-9+]/', '', $phone);
if (!preg_match('/^(\+7|8)[0-9]{10}$/', $phone_clean)) {
    showError("Поле 'Телефон' должно быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода).");
}
// Приводим к единому формату +7...
if (preg_match('/^8([0-9]{10})$/', $phone_clean, $matches)) {
    $phone = '+7' . $matches[1];
} else {
    $phone = $phone_clean;
}

// 3. Email: валидный email
$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    showError("Поле 'E-mail' обязательно для заполнения.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showError("Поле 'E-mail' содержит некорректный адрес.");
}
if (strlen($email) > 100) {
    showError("Поле 'E-mail' не должно превышать 100 символов.");
}

// 4. Дата рождения: не в будущем, не старше 120 лет
$birth_date = trim($_POST['birth_date'] ?? '');
if (empty($birth_date)) {
    showError("Поле 'Дата рождения' обязательно для заполнения.");
}
$date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
if (!$date_obj || $date_obj->format('Y-m-d') !== $birth_date) {
    showError("Поле 'Дата рождения' имеет некорректный формат.");
}
$today = new DateTime();
$age = $today->diff($date_obj)->y;
if ($date_obj > $today) {
    showError("Дата рождения не может быть в будущем.");
}
if ($age > 120) {
    showError("Возраст не может превышать 120 лет.");
}

// 5. Пол: только допустимые значения (male/female)
$gender = $_POST['gender'] ?? '';
$allowed_genders = ['male', 'female'];
if (!in_array($gender, $allowed_genders)) {
    showError("Поле 'Пол' содержит недопустимое значение. Выберите 'Мужской' или 'Женский'.");
}

// 6. Любимые языки программирования 
$languages = $_POST['languages'] ?? [];
if (empty($languages)) {
    showError("Выберите хотя бы один любимый язык программирования.");
}
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
$invalid_langs = array_diff($languages, $allowed_languages);
if (!empty($invalid_langs)) {
    showError("Выбраны недопустимые языки программирования: " . implode(', ', $invalid_langs));
}

// 7. Биография: не обязательна, но если есть, проверим длину (макс 5000)
$biography = trim($_POST['biography'] ?? '');
if (strlen($biography) > 5000) {
    showError("Поле 'Биография' не должно превышать 5000 символов.");
}

// 8. Чекбокс с контрактом
$contract_accepted = isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == 1 ? 1 : 0;
if (!$contract_accepted) {
    showError("Вы должны ознакомиться с контрактом и подтвердить согласие.");
}

// ==================== СОХРАНЕНИЕ В БАЗУ ДАННЫХ ====================

try {
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    // Вставка основной записи
    $sql = "INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':email' => $email,
        ':birth_date' => $birth_date,
        ':gender' => $gender,
        ':biography' => $biography,
        ':contract_accepted' => $contract_accepted
    ]);
    
    // Получаем ID последней вставленной записи
    $application_id = $pdo->lastInsertId();
    
    // Получаем ID языков программирования из БД и вставляем связи
    $lang_sql = "SELECT id, name FROM programming_languages WHERE name = :name";
    $lang_stmt = $pdo->prepare($lang_sql);
    
    $insert_link_sql = "INSERT INTO application_languages (application_id, language_id) VALUES (:app_id, :lang_id)";
    $link_stmt = $pdo->prepare($insert_link_sql);
    
    foreach ($languages as $lang_name) {
        $lang_stmt->execute([':name' => $lang_name]);
        $lang_row = $lang_stmt->fetch();
        if ($lang_row) {
            $link_stmt->execute([
                ':app_id' => $application_id,
                ':lang_id' => $lang_row['id']
            ]);
        } else {
            // На случай, если язык отсутствует в таблице languages (хотя мы заполнили)
            throw new Exception("Язык '$lang_name' не найден в справочнике.");
        }
    }
    
    // Подтверждаем транзакцию
    $pdo->commit();
    
    showSuccess("Данные успешно сохранены в базу данных!", $application_id);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    showError("Ошибка при сохранении в базу данных: " . $e->getMessage());
} catch (Exception $e) {
    $pdo->rollBack();
    showError("Ошибка: " . $e->getMessage());
}
?>