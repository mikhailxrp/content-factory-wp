<?php

namespace ContentFactoryUI\Support;

/**
 * Вспомогательные функции
 */
class Helpers {
  /**
   * Безопасный JSON decode
   */
  public static function json_decode($json, $default = []) {
    if (empty($json)) {
      return $default;
    }

    $decoded = json_decode($json, true);
    return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
  }

  /**
   * Форматировать дату для отображения
   */
  public static function format_date($date) {
    return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
  }

  /**
   * Получить статус с переводом
   */
  public static function get_status_label($status) {
    $labels = [
      'draft' => __('Черновик', 'content-factory-ui'),
      'pending' => __('Ожидает', 'content-factory-ui'),
      'published' => __('Опубликовано', 'content-factory-ui'),
      'error' => __('Ошибка', 'content-factory-ui')
    ];

    return $labels[$status] ?? $status;
  }

  /**
   * Обрезать текст
   */
  public static function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
      return $text;
    }

    return mb_substr($text, 0, $length) . $suffix;
  }

  /**
   * Проверить, является ли значение валидным JSON
   */
  public static function is_json($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }
}
