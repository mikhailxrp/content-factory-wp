<?php

namespace ContentFactoryUI\Settings;

/**
 * Работа с настройками плагина через Options API
 */
class SettingsRepository {
  private const OPTION_NAME = 'cf_ui_settings';

  /**
   * Получить все настройки
   */
  public static function get_all() {
    return get_option(self::OPTION_NAME, [
      'n8n_url' => '',
      'endpoints' => []
    ]);
  }

  /**
   * Получить конкретное значение
   */
  public static function get($key, $default = null) {
    $settings = self::get_all();
    return $settings[$key] ?? $default;
  }

  /**
   * Сохранить настройки
   */
  public static function save($data) {
    $current = self::get_all();
    $updated = array_merge($current, $data);
    return update_option(self::OPTION_NAME, $updated);
  }

  /**
   * Сохранить одно значение
   */
  public static function set($key, $value) {
    $settings = self::get_all();
    $settings[$key] = $value;
    return update_option(self::OPTION_NAME, $settings);
  }

  /**
   * Удалить настройки
   */
  public static function delete() {
    return delete_option(self::OPTION_NAME);
  }
}
