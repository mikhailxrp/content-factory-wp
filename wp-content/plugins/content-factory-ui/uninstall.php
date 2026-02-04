<?php
/**
 * Удаление данных при полном удалении плагина
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

// Удаляем опции
delete_option('cf_ui_settings');

// Удаляем все транзиенты плагина
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cf_ui_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_cf_ui_%'");

// Удаляем мета-данные постов (если есть связи с n8n)
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'cf_ui_%'");
