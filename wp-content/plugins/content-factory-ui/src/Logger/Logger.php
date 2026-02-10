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
    $endpoint = Endpoints::get('get_logs');
    
    if (!$endpoint) {
      return [];
    }

    $base_url = \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
    if (!$base_url) {
      return [];
    }

    $url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');
    
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

    if (!$decoded || !isset($decoded['data'])) {
      return [];
    }

    return $decoded['data'];
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
