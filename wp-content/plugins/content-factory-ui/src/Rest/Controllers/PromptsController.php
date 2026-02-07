<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;

/**
 * REST контроллер для промптов
 */
class PromptsController {
  /**
   * Список всех промптов
   */
  public static function list($request) {
    error_log('=== Запрос списка промптов ===');
    
    $client = new Client();
    $endpoint = Endpoints::get('list_prompts');
    error_log('Endpoint: ' . $endpoint);
    
    $prompts = $client->get($endpoint);
    error_log('Ответ от n8n: ' . print_r($prompts, true));

    if (is_wp_error($prompts)) {
      error_log('Ошибка WP: ' . $prompts->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $prompts->get_error_message()
      ]);
    }

    // Если n8n вернул один объект вместо массива, оборачиваем в массив
    if (is_array($prompts) && !isset($prompts[0]) && isset($prompts['id'])) {
      error_log('n8n вернул один объект промпта, оборачиваем в массив');
      $prompts = [$prompts];
    }

    error_log('Финальный результат для отправки: ' . print_r($prompts, true));

    return rest_ensure_response([
      'success' => true,
      'data' => $prompts
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('edit_posts');
  }
}
