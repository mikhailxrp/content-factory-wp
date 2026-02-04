<?php

namespace ContentFactoryUI\Logger;

/**
 * Минимальный логгер запросов/ответов/ошибок
 */
class Logger {
  private const OPTION_NAME = 'cf_ui_logs';
  private const MAX_LOGS = 100;

  /**
   * Логировать запрос
   */
  public static function log_request($method, $url, $data = null) {
    self::add_log('request', [
      'method' => $method,
      'url' => $url,
      'data' => $data,
      'timestamp' => current_time('mysql')
    ]);
  }

  /**
   * Логировать ответ
   */
  public static function log_response($method, $url, $status, $data = null) {
    self::add_log('response', [
      'method' => $method,
      'url' => $url,
      'status' => $status,
      'data' => $data,
      'timestamp' => current_time('mysql')
    ]);
  }

  /**
   * Логировать ошибку
   */
  public static function log_error($method, $url, $error) {
    self::add_log('error', [
      'method' => $method,
      'url' => $url,
      'error' => $error,
      'timestamp' => current_time('mysql')
    ]);
  }

  /**
   * Получить все логи
   */
  public static function get_logs($type = null, $limit = 50) {
    $logs = get_option(self::OPTION_NAME, []);
    
    if ($type) {
      $logs = array_filter($logs, function($log) use ($type) {
        return $log['type'] === $type;
      });
    }
    
    // Сортируем по времени (новые сверху)
    usort($logs, function($a, $b) {
      return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($logs, 0, $limit);
  }

  /**
   * Очистить логи
   */
  public static function clear_logs() {
    return delete_option(self::OPTION_NAME);
  }

  /**
   * Добавить лог
   */
  private static function add_log($type, $data) {
    $logs = get_option(self::OPTION_NAME, []);
    
    $logs[] = array_merge(['type' => $type], $data);
    
    // Ограничиваем количество логов
    if (count($logs) > self::MAX_LOGS) {
      $logs = array_slice($logs, -self::MAX_LOGS);
    }
    
    update_option(self::OPTION_NAME, $logs, false);
  }
}
