<?php

namespace ContentFactoryUI;

use ContentFactoryUI\Admin\Menu;
use ContentFactoryUI\Rest\Router;
use ContentFactoryUI\Support\Assets;
use ContentFactoryUI\Support\EditorAssets;
use ContentFactoryUI\WP\PostStatusSync;

/**
 * Основной класс плагина - инициализация модулей
 */
class Plugin {
  private static $instance = null;

  public static function instance() {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {}

  /**
   * Инициализация всех модулей плагина
   */
  public function init() {
    error_log("[Plugin] ===== ПЛАГИН ИНИЦИАЛИЗИРУЕТСЯ =====");
    
    // REST API маршруты (всегда)
    add_action('rest_api_init', [Router::class, 'register']);
    
    // Синхронизация статуса постов с n8n
    error_log("[Plugin] Вызываем PostStatusSync::register()");
    PostStatusSync::register();
    error_log("[Plugin] PostStatusSync::register() выполнен");
    
    // ВРЕМЕННО: Принудительная очистка кэша REST API при каждой загрузке
    add_action('init', function() {
      if (isset($_GET['cf_flush'])) {
        delete_transient('rest_api_routes');
        wp_die('REST API routes cache cleared. <a href="' . admin_url('admin.php?page=content-factory-senses') . '">Go back</a>');
      }
    });
    
    // Админка (только в админ-панели)
    if (is_admin()) {
      add_action('admin_menu', [Menu::class, 'register']);
      add_action('admin_enqueue_scripts', [Assets::class, 'enqueue']);
      
      // Assets для редактора Gutenberg
      add_action('enqueue_block_editor_assets', [EditorAssets::class, 'enqueue']);
    }
  }
}
