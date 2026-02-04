<?php
/**
 * Plugin Name: Content Factory UI
 * Description: UI для управления контент-фабрикой через n8n
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: content-factory-ui
 * Domain Path: /languages
 */

namespace ContentFactoryUI;

defined('ABSPATH') || exit;

// Константы плагина
define('CF_UI_VERSION', '1.0.4');
define('CF_UI_FILE', __FILE__);
define('CF_UI_DIR', plugin_dir_path(__FILE__));
define('CF_UI_URL', plugin_dir_url(__FILE__));
define('CF_UI_BASENAME', plugin_basename(__FILE__));

// Автозагрузка классов
spl_autoload_register(function ($class) {
  $prefix = 'ContentFactoryUI\\';
  $base_dir = CF_UI_DIR . 'src/';

  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }

  $relative_class = substr($class, $len);
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});

// Инициализация плагина
add_action('plugins_loaded', function () {
  load_plugin_textdomain('content-factory-ui', false, dirname(CF_UI_BASENAME) . '/languages');
  Plugin::instance()->init();
});

// Активация/деактивация
register_activation_hook(__FILE__, function () {
  // Создаем опции по умолчанию
  add_option('cf_ui_settings', [
    'n8n_url' => '',
    'endpoints' => []
  ]);
  
  // Сброс permalinks для регистрации REST API маршрутов
  flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
  // Очистка транзиентов при деактивации
  global $wpdb;
  $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cf_ui_%'");
  $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_cf_ui_%'");
});
