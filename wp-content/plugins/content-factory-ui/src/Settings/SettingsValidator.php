<?php

namespace ContentFactoryUI\Settings;

/**
 * Валидация настроек перед сохранением
 */
class SettingsValidator {
  /**
   * Валидировать URL n8n
   */
  public static function validate_url($url) {
    if (empty($url)) {
      return new \WP_Error('empty_url', __('URL не может быть пустым', 'content-factory-ui'));
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      return new \WP_Error('invalid_url', __('Некорректный URL', 'content-factory-ui'));
    }

    // Проверяем протокол
    $parsed = parse_url($url);
    if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
      return new \WP_Error('invalid_protocol', __('URL должен начинаться с http:// или https://', 'content-factory-ui'));
    }

    return true;
  }

  /**
   * Валидировать все настройки
   */
  public static function validate_all($data) {
    $errors = [];

    if (isset($data['n8n_url'])) {
      $url_check = self::validate_url($data['n8n_url']);
      if (is_wp_error($url_check)) {
        $errors['n8n_url'] = $url_check->get_error_message();
      }
    }

    if (!empty($errors)) {
      return new \WP_Error('validation_failed', __('Ошибка валидации', 'content-factory-ui'), $errors);
    }

    return true;
  }
}
