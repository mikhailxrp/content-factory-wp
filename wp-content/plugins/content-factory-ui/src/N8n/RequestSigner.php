<?php

namespace ContentFactoryUI\N8n;

/**
 * Подпись запросов к n8n (token/HMAC при необходимости)
 */
class RequestSigner {
  /**
   * Подписать данные через HMAC (если требуется)
   */
  public static function sign($data, $secret) {
    $json = is_string($data) ? $data : wp_json_encode($data);
    return hash_hmac('sha256', $json, $secret);
  }

  /**
   * Проверить подпись
   */
  public static function verify($data, $signature, $secret) {
    $expected = self::sign($data, $secret);
    return hash_equals($expected, $signature);
  }

  /**
   * Сгенерировать заголовки с подписью
   */
  public static function generate_headers($data, $secret) {
    return [
      'X-Signature' => self::sign($data, $secret),
      'X-Timestamp' => time()
    ];
  }
}
