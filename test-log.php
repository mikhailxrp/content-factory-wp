<?php
// Тест логирования
require_once __DIR__ . '/wp-load.php';

error_log("===== ТЕСТ 1: Простой error_log =====");
error_log("Текущая директория: " . __DIR__);
error_log("WP_DEBUG: " . (defined('WP_DEBUG') ? (WP_DEBUG ? 'true' : 'false') : 'not defined'));
error_log("WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'true' : 'false') : 'not defined'));
error_log("error_log setting: " . ini_get('error_log'));

echo "Проверь wp-content/debug.log - там должны быть записи\n";
echo "Путь к логу: " . ini_get('error_log') . "\n";
