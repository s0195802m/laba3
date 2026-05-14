<?php
// list.php - Страница со списком всех сохранённых анкет
require_once 'config.php';

// Получаем все анкеты с их языками программирования
$sql = "SELECT a.*, 
        GROUP_CONCAT(pl.name ORDER BY pl.name SEPARATOR ', ') as languages
        FROM applications a
        LEFT JOIN application_languages al ON a.id = al.application_id
        LEFT JOIN programming_languages pl ON al.language_id = pl.id
        GROUP BY a.id
        ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список сохранённых анкет — Лабораторная работа №3</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Дополнительные стили для таблицы */
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .applications-table th,
        .applications-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ffccd9;
            vertical-align: top;
        }
        
        .applications-table th {
            background: linear-gradient(135deg, #f8b0c0, #f48fb1);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .applications-table tr:hover {
            background-color: #fff5f7;
        }
        
        .applications-table tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            display: inline-block;
            background-color: #f06292;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            margin: 0.1rem;
        }
        
        .languages-cell {
            max-width: 250px;
        }
        
        .biography-preview {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #9b4b6e;
            background-color: #fff0f3;
            border-radius: 20px;
        }
        
        .stats {
            background-color: #fff0f3;
            padding: 1rem 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-view {
            background-color: #f06292;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background-color 0.2s;
        }
        
        .btn-view:hover {
            background-color: #d81b60;
        }
        
        .btn-delete {
            background-color: #ffebee;
            color: #f44336;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            border: 1px solid #f44336;
            transition: all 0.2s;
        }
        
        .btn-delete:hover {
            background-color: #f44336;
            color: white;
        }
        
        .table-wrapper {
            overflow-x: auto;
            border-radius: 20px;
        }
        
        .action-buttons {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #f06292, #d81b60);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 40px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.2s;
            display: inline-block;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .action-btn.secondary {
            background: linear-gradient(135deg, #b0bec5, #90a4ae);
        }
        
        /* Модальное окно для просмотра */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            padding: 2rem;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-close {
            position: absolute;
            right: 1.5rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #9b4b6e;
        }
        
        .modal-close:hover {
            color: #d81b60;
        }
        
        .modal h3 {
            color: #d81b60;
            margin-bottom: 1rem;
        }
        
        .modal-field {
            margin-bottom: 0.75rem;
        }
        
        .modal-field strong {
            color: #d81b60;
            display: inline-block;
            width: 120px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="subtitle">(с) Сергей Синица 2020</p>
            <h2>Задание 3. Список сохранённых анкет</h2>
            <p class="student-info">Выполнил: Дмитрий | Логин: u82461 | Группа: Web-бэкенд</p>
        </div>
    </header>

    <main class="container">
        <div class="stats">
            📊 Всего анкет в базе данных: <strong><?php echo count($applications); ?></strong>
        </div>

        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <p>😕 Пока нет ни одной сохранённой анкеты.</p>
                <a href="index.html" class="action-btn" style="margin-top: 1rem; display: inline-block;">📝 Заполнить первую анкету</a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Дата рождения</th>
                            <th>Пол</th>
                            <th>Любимые ЯП</th>
                            <th>Биография</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['id']); ?></td>
                                <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['phone']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($app['birth_date'])); ?></td>
                                <td>
                                    <?php 
                                    $gender_text = '';
                                    if ($app['gender'] == 'male') $gender_text = 'Мужской';
                                    if ($app['gender'] == 'female') $gender_text = 'Женский';
                                    echo $gender_text;
                                    ?>
                                </td>
                                <td class="languages-cell">
                                    <?php 
                                    $languages = explode(', ', $app['languages'] ?? '');
                                    foreach ($languages as $lang):
                                        if (trim($lang)):
                                    ?>
                                        <span class="badge"><?php echo htmlspecialchars(trim($lang)); ?></span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </td>
                                <td class="biography-preview">
                                    <?php 
                                    $bio = htmlspecialchars($app['biography'] ?? '');
                                    if (empty($bio)):
                                        echo '<em style="color: #9b4b6e;">— не указано —</em>';
                                    else:
                                        echo mb_strlen($bio) > 50 ? mb_substr($bio, 0, 50) . '…' : $bio;
                                    endif;
                                    ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i:s', strtotime($app['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="#" class="btn-view" onclick="showDetails(<?php echo htmlspecialchars(json_encode($app)); ?>, <?php echo htmlspecialchars(json_encode($languages ?? [])); ?>); return false;">👁️ Просмотр</a>
                                    <a href="delete.php?id=<?php echo $app['id']; ?>" class="btn-delete" onclick="return confirm('Удалить анкету №<?php echo $app['id']; ?>? Это действие нельзя отменить.');">🗑️ Удалить</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="index.html" class="action-btn">📝 Добавить новую анкету</a>
            <a href="export.php" class="action-btn secondary">📥 Экспорт в CSV</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №3 — Форма, валидация, база данных | Апрель 2026</p>
        </div>
    </footer>

    <!-- Модальное окно для просмотра деталей -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3>📄 Детали анкеты</h3>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function showDetails(application, languagesArray) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            const genderText = application.gender === 'male' ? 'Мужской' : 'Женский';
            const birthDate = new Date(application.birth_date).toLocaleDateString('ru-RU');
            const createdDate = new Date(application.created_at).toLocaleString('ru-RU');
            
            let languagesHtml = '';
            if (languagesArray && languagesArray.length > 0) {
                languagesArray.forEach(lang => {
                    if (lang.trim()) {
                        languagesHtml += `<span class="badge" style="margin-right: 5px;">${escapeHtml(lang.trim())}</span>`;
                    }
                });
            } else {
                languagesHtml = '<em style="color: #9b4b6e;">— не выбрано —</em>';
            }
            
            modalBody.innerHTML = `
                <div class="modal-field"><strong>ID:</strong> ${escapeHtml(application.id)}</div>
                <div class="modal-field"><strong>ФИО:</strong> ${escapeHtml(application.full_name)}</div>
                <div class="modal-field"><strong>Телефон:</strong> ${escapeHtml(application.phone)}</div>
                <div class="modal-field"><strong>Email:</strong> ${escapeHtml(application.email)}</div>
                <div class="modal-field"><strong>Дата рождения:</strong> ${birthDate}</div>
                <div class="modal-field"><strong>Пол:</strong> ${genderText}</div>
                <div class="modal-field"><strong>Любимые ЯП:</strong><br> ${languagesHtml}</div>
                <div class="modal-field"><strong>Биография:</strong><br> ${escapeHtml(application.biography) || '<em style="color: #9b4b6e;">— не указано —</em>'}</div>
                <div class="modal-field"><strong>Контракт:</strong> ${application.contract_accepted ? '✅ Ознакомлен' : '❌ Не ознакомлен'}</div>
                <div class="modal-field"><strong>Дата создания:</strong> ${createdDate}</div>
            `;
            
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>