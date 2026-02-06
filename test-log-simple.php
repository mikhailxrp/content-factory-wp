<?php
// Простой тест записи в файл
$log_file = __DIR__ . '/wp-content/debug.log';

$message = date('[Y-m-d H:i:s]') . " ТЕСТ: Запись напрямую в файл\n";
file_put_contents($log_file, $message, FILE_APPEND);

echo "Записано в: {$log_file}\n";
echo "Проверь файл!\n";

// Проверяем права
if (is_writable(dirname($log_file))) {
    echo "Директория wp-content доступна для записи: ДА\n";
} else {
    echo "Директория wp-content доступна для записи: НЕТ\n";
}

if (file_exists($log_file)) {
    echo "Файл debug.log существует: ДА\n";
    echo "Размер: " . filesize($log_file) . " байт\n";
} else {
    echo "Файл debug.log существует: НЕТ\n";
}
