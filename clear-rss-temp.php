<?php
// Временный файл для очистки RSS источников
require_once __DIR__ . '/wp-load.php';

$settings = get_option('cf_ui_settings', []);
$settings['rss_sources'] = [];
update_option('cf_ui_settings', $settings);

echo "RSS источники очищены!\n";
echo "Текущие настройки: " . print_r(get_option('cf_ui_settings'), true);
