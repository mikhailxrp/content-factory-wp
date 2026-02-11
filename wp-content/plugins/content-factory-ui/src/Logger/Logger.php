<?php

namespace ContentFactoryUI\Logger;

use ContentFactoryUI\N8n\Endpoints;

/**
 * Логгер для получения логов генерации статей из n8n и отладки
 */
class Logger {
  /**
   * Debug лог (только при WP_DEBUG)
   */
  public static function debug($message, $context = []) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
      return;
    }
    
    $prefix = '[CF Debug] ';
    if (!empty($context)) {
      $message .= ' | Context: ' . print_r($context, true);
    }
    error_log($prefix . $message);
  }

  /**
   * Info лог (только при WP_DEBUG)
   */
  public static function info($message, $context = []) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
      return;
    }
    
    $prefix = '[CF Info] ';
    if (!empty($context)) {
      $message .= ' | Context: ' . print_r($context, true);
    }
    error_log($prefix . $message);
  }

  /**
   * Error лог (всегда пишется)
   */
  public static function error($message, $context = []) {
    $prefix = '[CF Error] ';
    if (!empty($context)) {
      $message .= ' | Context: ' . print_r($context, true);
    }
    error_log($prefix . $message);
  }

  /**
   * Получить логи из n8n
   */
  public static function get_logs() {
    self::debug('=== НАЧАЛО get_logs() ===');
    
    $endpoint = Endpoints::get('get_logs');
    self::debug('Endpoint: ' . ($endpoint ?? 'NULL'));
    
    if (!$endpoint) {
      self::debug('Endpoint не найден, возврат []');
      return [];
    }

    $base_url = \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
    self::debug('Base URL: ' . ($base_url ?? 'NULL'));
    
    if (!$base_url) {
      self::debug('Base URL не найден, возврат []');
      return [];
    }

    $url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');
    self::debug('Итоговый URL: ' . $url);
    
    $response = wp_remote_get($url, [
      'timeout' => 30,
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ]);

    if (is_wp_error($response)) {
      self::error('Ошибка получения логов: ' . $response->get_error_message());
      return [];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code >= 400) {
      self::error('HTTP ошибка ' . $status_code);
      return [];
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);

    self::debug('Raw response from n8n: ' . substr($body, 0, 1000));
    self::debug('Decoded data', $decoded);

    if (!$decoded) {
      self::error('Не удалось декодировать JSON');
      return [];
    }

    // Формат: [ {...}, {...} ] — массив логов без обёртки
    if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
      self::debug('Найден формат [ {...}, {...} ], количество: ' . count($decoded));
      return $decoded;
    }

    // n8n возвращает: [{"items": [...]}]
    if (isset($decoded[0]['items'])) {
      self::debug('Найден формат [{"items": [...]}], количество: ' . count($decoded[0]['items']));
      return $decoded[0]['items'];
    }

    // Альтернативный формат: {"data": [...]}
    if (isset($decoded['data'])) {
      self::debug('Найден формат {"data": [...]}, количество: ' . count($decoded['data']));
      return $decoded['data'];
    }

    self::error('Неизвестный формат данных от n8n');
    return [];
  }

  /**
   * Устаревшие методы для обратной совместимости
   * TODO: удалить после рефакторинга Client.php
   */
  public static function log_request($method, $url, $data = null) {
    // Больше не логируем
  }

  public static function log_response($method, $url, $status, $data = null) {
    // Больше не логируем
  }

  public static function log_error($method, $url, $error) {
    // Больше не логируем
  }

  public static function clear_logs() {
    // TODO: реализовать очистку логов на стороне n8n если понадобится
    return true;
  }
}
