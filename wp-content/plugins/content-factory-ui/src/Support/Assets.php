<?php

namespace ContentFactoryUI\Support;

/**
 * Подключение CSS/JS для админки
 */
class Assets {
  /**
   * Подключить assets только на страницах плагина
   */
  public static function enqueue($hook) {
    // Проверяем, что мы на страницах плагина
    if (strpos($hook, 'content-factory') === false) {
      return;
    }

    // CSS
    wp_enqueue_style(
      'cf-ui-admin',
      CF_UI_URL . 'assets/admin/admin.css',
      [],
      CF_UI_VERSION
    );

    // JS
    wp_enqueue_script(
      'cf-ui-admin',
      CF_UI_URL . 'assets/admin/admin.js',
      ['jquery'],
      CF_UI_VERSION,
      true
    );

    // Передаем данные в JS
    wp_localize_script('cf-ui-admin', 'cfUIData', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'restUrl' => get_rest_url(null, 'content-factory/v1'),
      'nonce' => wp_create_nonce('wp_rest'),
      'i18n' => [
        'loading' => __('Загрузка...', 'content-factory-ui'),
        'error' => __('Ошибка', 'content-factory-ui'),
        'success' => __('Успешно', 'content-factory-ui')
      ]
    ]);
  }
}
