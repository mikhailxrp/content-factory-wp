<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\Logger\Logger;

/**
 * REST контроллер для логов
 */
class LogsController {
  /**
   * Список логов
   */
  public static function list($request) {
    error_log('[LogsController] Запрос на получение логов');
    $logs = Logger::get_logs();
    error_log('[LogsController] Получено логов: ' . count($logs));
    error_log('[LogsController] Данные логов: ' . print_r($logs, true));

    return rest_ensure_response([
      'success' => true,
      'data' => $logs
    ]);
  }

  /**
   * Очистить логи
   */
  public static function clear($request) {
    Logger::clear_logs();

    return rest_ensure_response([
      'success' => true,
      'message' => __('Логи очищены', 'content-factory-ui')
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('manage_options');
  }
}
