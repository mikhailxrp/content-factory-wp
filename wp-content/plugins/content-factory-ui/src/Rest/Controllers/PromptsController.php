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
   * Создать новый промпт
   */
  public static function create($request) {
    $data = $request->get_json_params();
    
    error_log('=== Создание нового промпта ===');
    error_log('Данные: ' . print_r($data, true));
    
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
    
    error_log('Отправка данных в n8n: ' . print_r($payload, true));
    
    $response = $client->post($endpoint, $payload);
    error_log('Ответ от n8n: ' . print_r($response, true));

    if (is_wp_error($response)) {
      error_log('Ошибка WP: ' . $response->get_error_message());
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
    
    error_log('=== Обновление промпта ===');
    error_log('ID: ' . $id);
    error_log('Данные: ' . print_r($data, true));
    
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
    
    error_log('Отправка данных в n8n: ' . print_r($payload, true));
    
    $response = $client->post($endpoint, $payload);
    error_log('Ответ от n8n: ' . print_r($response, true));

    if (is_wp_error($response)) {
      error_log('Ошибка WP: ' . $response->get_error_message());
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
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('edit_posts');
  }
}
