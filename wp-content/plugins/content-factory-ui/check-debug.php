<?php
/**
 * Страница для просмотра debug логов
 * Открывать: /wp-content/plugins/content-factory-ui/check-debug.php
 */

// Загружаем WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Проверяем права
if (!current_user_can('manage_options')) {
    wp_die('Нет прав доступа');
}

$debug_log = get_option('cf_post_status_debug', []);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Logs - Post Status Sync</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .clear-btn { margin-bottom: 20px; padding: 10px 20px; background: #dc3545; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Post Status Sync - Debug Logs</h1>
    
    <form method="post">
        <button type="submit" name="clear_log" class="clear-btn">Очистить логи</button>
    </form>

    <?php
    if (isset($_POST['clear_log'])) {
        delete_option('cf_post_status_debug');
        echo '<p style="color: green;">Логи очищены! Обновите страницу.</p>';
        $debug_log = [];
    }
    ?>

    <h2>Всего записей: <?php echo count($debug_log); ?></h2>

    <?php if (empty($debug_log)): ?>
        <p>Логов пока нет. Попробуйте опубликовать статью.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Время</th>
                <th>Post ID</th>
                <th>Старый статус</th>
                <th>Новый статус</th>
                <th>Post Type</th>
            </tr>
            <?php foreach (array_reverse($debug_log) as $entry): ?>
                <tr>
                    <td><?php echo esc_html($entry['time']); ?></td>
                    <td><?php echo esc_html($entry['post_id']); ?></td>
                    <td><?php echo esc_html($entry['old_status']); ?></td>
                    <td><strong><?php echo esc_html($entry['new_status']); ?></strong></td>
                    <td><?php echo esc_html($entry['post_type']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <hr>
    <p><a href="<?php echo admin_url(); ?>">← Вернуться в админку</a></p>
</body>
</html>
