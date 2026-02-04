<?php

namespace ContentFactoryUI\N8n;

use ContentFactoryUI\Logger\Logger;

/**
 * HTTP клиент для запросов к n8n с retry и timeout
 */
class Client {
  private $base_url;
  private $timeout = 30;
  private $retry_count = 2;

  public function __construct($base_url = null) {
    $this->base_url = $base_url ?? \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
  }

  /**
   * Отправить POST запрос
   */
  public function post($endpoint, $data = []) {
    return $this->request('POST', $endpoint, $data);
  }

  /**
   * Отправить GET запрос
   */
  public function get($endpoint, $params = []) {
    if (!empty($params)) {
      $endpoint .= '?' . http_build_query($params);
    }
    return $this->request('GET', $endpoint);
  }

  /**
   * Основной метод для отправки запросов
   */
  private function request($method, $endpoint, $data = null, $attempt = 1) {
    $url = rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');
    
    $args = [
      'method' => $method,
      'timeout' => $this->timeout,
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ];

    if ($data !== null) {
      $args['body'] = wp_json_encode($data);
    }

    // Логируем запрос
    Logger::log_request($method, $url, $data);

    $response = wp_remote_request($url, $args);

    // Проверка на ошибку
    if (is_wp_error($response)) {
      Logger::log_error($method, $url, $response->get_error_message());
      
      // Retry при ошибке сети
      if ($attempt < $this->retry_count) {
        sleep(1);
        return $this->request($method, $endpoint, $data, $attempt + 1);
      }
      
      return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    error_log('[Client] Raw body: ' . $body);
    $decoded = json_decode($body, true);
    error_log('[Client] Decoded: ' . print_r($decoded, true));
    error_log('[Client] JSON error: ' . json_last_error_msg());

    // Логируем ответ
    Logger::log_response($method, $url, $status_code, $decoded);

    // Проверка статуса
    if ($status_code >= 400) {
      $error_message = $decoded['message'] ?? $decoded['error'] ?? 'Unknown error';
      return new \WP_Error('n8n_error', $error_message, ['status' => $status_code]);
    }

    return $decoded;
  }

  /**
   * Установить timeout
   */
  public function set_timeout($seconds) {
    $this->timeout = (int) $seconds;
    return $this;
  }

  /**
   * Установить количество retry
   */
  public function set_retry_count($count) {
    $this->retry_count = (int) $count;
    return $this;
  }
}
