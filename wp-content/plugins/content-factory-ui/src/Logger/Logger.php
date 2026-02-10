<?php

namespace ContentFactoryUI\Logger;

use ContentFactoryUI\N8n\Endpoints;

/**
 * Логгер для получения логов генерации статей из n8n
 */
class Logger {
  /**
   * Получить логи из n8n
   */
  public static function get_logs() {
    error_log('[Logger] === НАЧАЛО get_logs() ===');
    
    $endpoint = Endpoints::get('get_logs');
    error_log('[Logger] Endpoint: ' . ($endpoint ?? 'NULL'));
    
    if (!$endpoint) {
      error_log('[Logger] Endpoint не найден, возврат []');
      return [];
    }

    $base_url = \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
    error_log('[Logger] Base URL: ' . ($base_url ?? 'NULL'));
    
    if (!$base_url) {
      error_log('[Logger] Base URL не найден, возврат []');
      return [];
    }

    $url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');
    error_log('[Logger] Итоговый URL: ' . $url);
    
    $response = wp_remote_get($url, [
      'timeout' => 30,
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ]);

    if (is_wp_error($response)) {
      error_log('[Logger] Ошибка получения логов: ' . $response->get_error_message());
      return [];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code >= 400) {
      error_log('[Logger] HTTP ошибка ' . $status_code);
      return [];
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);

    error_log('[Logger] Raw response from n8n: ' . substr($body, 0, 1000));
    error_log('[Logger] Decoded data: ' . print_r($decoded, true));

    if (!$decoded) {
      error_log('[Logger] Не удалось декодировать JSON');
      return [];
    }

    // n8n возвращает: [{"items": [...]}]
    if (isset($decoded[0]['items'])) {
      error_log('[Logger] Найден формат [{"items": [...]}], количество: ' . count($decoded[0]['items']));
      return $decoded[0]['items'];
    }

    // Альтернативный формат: {"data": [...]}
    if (isset($decoded['data'])) {
      error_log('[Logger] Найден формат {"data": [...]}, количество: ' . count($decoded['data']));
      return $decoded['data'];
    }

    error_log('[Logger] Неизвестный формат данных от n8n');
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
