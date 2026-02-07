<?php

namespace ContentFactoryUI\Support;

/**
 * Подключение CSS/JS для редактора постов (Gutenberg)
 */
class EditorAssets {
  /**
   * Подключить assets в редактор
   */
  public static function enqueue() {
    // Проверяем, что мы в редакторе постов
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'post') {
      return;
    }

    // JS - компонент генерации статьи
    wp_enqueue_script(
      'cf-editor-generate',
      CF_UI_URL . 'assets/editor/generate-button.js',
      [
        'wp-plugins',
        'wp-edit-post',
        'wp-element',
        'wp-components',
        'wp-data',
        'wp-api-fetch',
        'wp-i18n'
      ],
      CF_UI_VERSION,
      true
    );

    // CSS - стили для модалки и кнопки
    wp_enqueue_style(
      'cf-editor-generate',
      CF_UI_URL . 'assets/editor/editor.css',
      ['wp-edit-post'],
      CF_UI_VERSION
    );

    // Передаем данные в JS
    wp_localize_script('cf-editor-generate', 'cfEditorData', [
      'restUrl' => get_rest_url(null, 'content-factory/v1'),
      'nonce' => wp_create_nonce('wp_rest'),
      'postId' => get_the_ID(),
      'i18n' => [
        'loading' => __('Загрузка...', 'content-factory-ui'),
        'error' => __('Ошибка', 'content-factory-ui'),
        'success' => __('Успешно', 'content-factory-ui'),
        'generating' => __('Генерация...', 'content-factory-ui')
      ]
    ]);

    // Подключаем переводы для wp.i18n
    wp_set_script_translations('cf-editor-generate', 'content-factory-ui', CF_UI_DIR . 'languages');
  }
}
