<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\Logger\Logger;

/**
 * REST контроллер для промптов
 */
class PromptsController {
  /**
   * Список всех промптов
   */
  public static function list($request) {
    Logger::debug('=== Запрос списка промптов ===');
    
    $client = new Client();
    $endpoint = Endpoints::get('list_prompts');
    
    $prompts = $client->get($endpoint);
    Logger::debug('Ответ от n8n', $prompts);

    if (is_wp_error($prompts)) {
      Logger::error('Ошибка WP: ' . $prompts->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $prompts->get_error_message()
      ]);
    }

    // Если n8n вернул один объект вместо массива, оборачиваем в массив
    if (is_array($prompts) && !isset($prompts[0]) && isset($prompts['id'])) {
      Logger::debug('n8n вернул один объект промпта, оборачиваем в массив');
      $prompts = [$prompts];
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $prompts
    ]);
  }

  /**
   * Создать новый промпт
   */
  public static function create($request) {
    $data = $request->get_json_params();
    
    Logger::debug('=== Создание нового промпта ===', $data);
    
    // Валидация обязательных полей
    if (empty($data['angle']) || empty($data['template_name']) || empty($data['system_prompt'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => 'Заполните все обязательные поля'
      ]);
    }
    
    // Подготовка данных для отправки в n8n
    $payload = [
      'angle' => sanitize_text_field($data['angle']),
      'template_name' => sanitize_text_field($data['template_name']),
      'system_prompt' => sanitize_textarea_field($data['system_prompt']),
      'structure_rules' => $data['structure_rules'] ?? [], // JSON объект
      'tone' => sanitize_text_field($data['tone'] ?? 'professional'),
      'min_words' => intval($data['min_words'] ?? 2000),
      'max_words' => intval($data['max_words'] ?? 2500),
      'is_active' => intval($data['is_active'] ?? 1)
    ];
    
    $client = new Client();
    $endpoint = Endpoints::get('create_prompt');
    
    $response = $client->post($endpoint, $payload);

    if (is_wp_error($response)) {
      Logger::error('Ошибка WP: ' . $response->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => 'Промпт успешно создан',
      'data' => $response
    ]);
  }

  /**
   * Обновить промпт
   */
  public static function update($request) {
    $id = $request->get_param('id');
    $data = $request->get_json_params();
    
    Logger::debug("=== Обновление промпта ID: $id ===", $data);
    
    // Валидация обязательных полей
    if (empty($data['angle']) || empty($data['template_name']) || empty($data['system_prompt'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => 'Заполните все обязательные поля'
      ]);
    }
    
    // Подготовка данных для отправки в n8n
    $payload = [
      'id' => $id,
      'angle' => sanitize_text_field($data['angle']),
      'template_name' => sanitize_text_field($data['template_name']),
      'system_prompt' => sanitize_textarea_field($data['system_prompt']),
      'structure_rules' => $data['structure_rules'], // JSON объект
      'tone' => sanitize_text_field($data['tone'] ?? 'professional'),
      'min_words' => intval($data['min_words'] ?? 2000),
      'max_words' => intval($data['max_words'] ?? 2500),
      'is_active' => intval($data['is_active'] ?? 1)
    ];
    
    $client = new Client();
    $endpoint = Endpoints::get('update_prompt');
    
    $response = $client->post($endpoint, $payload);

    if (is_wp_error($response)) {
      Logger::error('Ошибка WP: ' . $response->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => 'Промпт успешно обновлён',
      'data' => $response
    ]);
  }

  /**
   * Удалить промпт
   */
  public static function delete($request) {
    $id = $request->get_param('id');
    
    Logger::debug("=== Удаление промпта ID: $id ===");
    
    if (empty($id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => 'ID промпта не указан'
      ]);
    }
    
    // Защита дефолтных промптов (ID 1-21)
    if ((int)$id <= 21) {
      Logger::error("Попытка удалить системный промпт: $id");
      return rest_ensure_response([
        'success' => false,
        'message' => 'Нельзя удалить системный промпт. Системные промпты имеют ID от 1 до 21.'
      ]);
    }
    
    $client = new Client();
    $endpoint = Endpoints::get('delete_prompt');
    
    $response = $client->post($endpoint, ['id' => $id]);

    if (is_wp_error($response)) {
      Logger::error('Ошибка WP: ' . $response->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => 'Промпт успешно удалён',
      'data' => $response
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('edit_posts');
  }
}
