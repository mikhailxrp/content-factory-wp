<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\Logger\Logger;

/**
 * REST контроллер для логов
 */
class LogsController {
  /**
   * Получить логи из n8n
   */
  public static function list($request) {
    Logger::debug('=== Запрос на получение логов ===');
    
    $logs = Logger::get_logs();
    
    Logger::debug('Получено логов: ' . count($logs));

    return rest_ensure_response([
      'success' => true,
      'data' => $logs
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('edit_posts');
  }
}
