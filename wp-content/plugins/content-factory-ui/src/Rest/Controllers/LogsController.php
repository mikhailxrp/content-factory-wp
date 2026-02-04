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
    $type = $request->get_param('type');
    $limit = $request->get_param('limit') ?? 50;

    $logs = Logger::get_logs($type, $limit);

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
