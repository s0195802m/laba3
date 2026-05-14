<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №3 — Форма с сохранением в БД</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h2>Задание 3. Форма с валидацией и сохранением в базу данных</h2>
        </div>
    </header>

    <main class="container">
        <section class="intro">
            <p>Заполните форму ниже. Все поля обязательны для заполнения, кроме биографии. После отправки данные будут проверены на сервере и сохранены в базу данных MySQL.</p>
        </section>

        <!-- Форма отправляется на process.php методом POST -->
        <form action="process.php" method="POST" class="application-form">
            <!-- 1. ФИО -->
            <div class="form-group">
                <label for="full_name">ФИО <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Иванов Иван Иванович" 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <small>Только буквы, пробелы и дефис, не более 150 символов</small>
            </div>

            <!-- 2. Телефон -->
            <div class="form-group">
                <label for="phone">Телефон <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" required 
                       placeholder="+7 (123) 456-78-90" 
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                <small>Формат: +7XXXXXXXXXX или 8XXXXXXXXXX</small>
            </div>

            <!-- 3. Email -->
            <div class="form-group">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" required 
                       placeholder="example@domain.ru" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <!-- 4. Дата рождения -->
            <div class="form-group">
                <label for="birth_date">Дата рождения <span class="required">*</span></label>
                <input type="date" id="birth_date" name="birth_date" required 
                       value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
            </div>

            <!-- 5. Пол  -->
<div class="form-group">
    <label>Пол <span class="required">*</span></label>
    <div class="radio-group">
        <label><input type="radio" name="gender" value="male" 
            <?php echo (($_POST['gender'] ?? '') == 'male') ? 'checked' : ''; ?>> Мужской</label>
        <label><input type="radio" name="gender" value="female" 
            <?php echo (($_POST['gender'] ?? '') == 'female') ? 'checked' : ''; ?>> Женский</label>
    </div>
</div>

            <!-- 6. Любимый язык программирования-->
            <div class="form-group">
                <label for="languages">Любимый язык программирования <span class="required">*</span></label>
                <select name="languages[]" id="languages" multiple size="6" required>
                    <option value="Pascal" <?php echo (in_array('Pascal', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Pascal</option>
                    <option value="C" <?php echo (in_array('C', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>C</option>
                    <option value="C++" <?php echo (in_array('C++', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>C++</option>
                    <option value="JavaScript" <?php echo (in_array('JavaScript', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>JavaScript</option>
                    <option value="PHP" <?php echo (in_array('PHP', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>PHP</option>
                    <option value="Python" <?php echo (in_array('Python', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Python</option>
                    <option value="Java" <?php echo (in_array('Java', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Java</option>
                    <option value="Haskell" <?php echo (in_array('Haskell', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Haskell</option>
                    <option value="Clojure" <?php echo (in_array('Clojure', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Clojure</option>
                    <option value="Prolog" <?php echo (in_array('Prolog', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Prolog</option>
                    <option value="Scala" <?php echo (in_array('Scala', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Scala</option>
                    <option value="Go" <?php echo (in_array('Go', $_POST['languages'] ?? [])) ? 'selected' : ''; ?>>Go</option>
                </select>
                <small>Зажмите Ctrl  для выбора нескольких языков</small>
            </div>

            <!-- 7. Биография  -->
            <div class="form-group">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography" rows="5" 
                          placeholder="Расскажите немного о себе..."><?php echo htmlspecialchars($_POST['biography'] ?? ''); ?></textarea>
            </div>

            <!-- 8. Чекбокс с контрактом -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" 
                        <?php echo (isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == 1) ? 'checked' : ''; ?>>
                    С контрактом ознакомлен(а) <span class="required">*</span>
                </label>
            </div>

            <!-- 9. Кнопка отправки -->
            <div class="form-group">
                <button type="submit" class="submit-btn"> Сохранить</button>
            </div>
        </form>
    </main>
<!-- Ссылка на список анкет -->
<div class="action-buttons" style="margin-top: 1.5rem; text-align: center;">
    <a href="list.php" class="action-btn"> Посмотреть все сохранённые анкеты</a>
    <a href="bd.html" class="action-btn secondary"> Структура БД</a>
</div>
</body>
</html>