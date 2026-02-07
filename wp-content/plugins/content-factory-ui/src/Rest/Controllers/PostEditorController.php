<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\WP\PostPublisher;

/**
 * REST контроллер для генерации статей из редактора
 */
class PostEditorController {
  /**
   * Генерация статьи из редактора
   */
  public static function generate_article($request) {
    error_log('=== [PostEditorController] НАЧАЛО generate_article ===');
    
    $post_id = $request->get_param('id');
    $data = $request->get_json_params();
    
    error_log('[PostEditorController] Post ID: ' . $post_id);
    error_log('[PostEditorController] Входящие данные: ' . print_r($data, true));

    if (empty($post_id)) {
      error_log('[PostEditorController] ОШИБКА: Post ID пустой');
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID поста', 'content-factory-ui')
      ]);
    }

    // Проверяем, что пост существует
    $post = get_post($post_id);
    if (!$post) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Пост не найден', 'content-factory-ui')
      ]);
    }

    // Валидация обязательных полей
    if (empty($data['role']) || empty($data['prompt']) || empty($data['sections'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Заполните все обязательные поля: Роль, Промпт, Секции', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('generate_article_from_editor');
    
    error_log('[PostEditorController] Endpoint из Endpoints::get(): ' . ($endpoint ?? 'NULL'));
    error_log('[PostEditorController] N8N URL из настроек: ' . \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url'));
    
    if (!$endpoint) {
      error_log('[PostEditorController] ОШИБКА: Endpoint не настроен');
      return rest_ensure_response([
        'success' => false,
        'message' => __('Endpoint generate_article_from_editor не настроен', 'content-factory-ui')
      ]);
    }

    $payload = [
      'post_id' => $post_id,
      'role' => sanitize_text_field($data['role']),
      'prompt' => sanitize_textarea_field($data['prompt']),
      'sections' => sanitize_textarea_field($data['sections'])
    ];
    
    error_log('[PostEditorController] Генерация статьи для поста ID: ' . $post_id);
    error_log('[PostEditorController] Endpoint: ' . $endpoint);
    error_log('[PostEditorController] Payload для отправки: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));
    
    // Отправляем данные в N8N
    error_log('[PostEditorController] Отправка запроса в N8N...');
    $response = $client->post($endpoint, $payload);
    
    error_log('[PostEditorController] Тип ответа: ' . gettype($response));
    error_log('[PostEditorController] Ответ от N8N: ' . print_r($response, true));

    if (is_wp_error($response)) {
      error_log('[PostEditorController] ОШИБКА WP_Error: ' . $response->get_error_message());
      error_log('[PostEditorController] Код ошибки: ' . $response->get_error_code());
      error_log('[PostEditorController] Данные ошибки: ' . print_r($response->get_error_data(), true));
      
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    error_log('[PostEditorController] Генерация запущена успешно');
    error_log('[PostEditorController] Ответ (JSON): ' . json_encode($response, JSON_UNESCAPED_UNICODE));

    // Сохраняем информацию о запущенной генерации в post meta
    update_post_meta($post_id, '_cf_generation_status', 'started');
    update_post_meta($post_id, '_cf_generation_started_at', current_time('mysql'));
    
    if (isset($response['job_id'])) {
      update_post_meta($post_id, '_cf_generation_job_id', $response['job_id']);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Генерация статьи запущена', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Проверка статуса генерации статьи
   */
  public static function check_status($request) {
    $post_id = $request->get_param('id');

    if (empty($post_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID поста', 'content-factory-ui')
      ]);
    }

    // Проверяем, что пост существует
    $post = get_post($post_id);
    if (!$post) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Пост не найден', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('check_editor_article_status');
    
    if (!$endpoint) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Endpoint check_editor_article_status не настроен', 'content-factory-ui')
      ]);
    }

    error_log('[PostEditorController] Проверка статуса генерации для поста ID: ' . $post_id);
    
    // Получаем job_id из meta, если есть
    $job_id = get_post_meta($post_id, '_cf_generation_job_id', true);
    
    // Формируем параметры запроса
    $query_params = ['post_id' => $post_id];
    if (!empty($job_id)) {
      $query_params['job_id'] = $job_id;
    }
    
    $url = $endpoint . '?' . http_build_query($query_params);
    
    $response = $client->get($url);

    if (is_wp_error($response)) {
      error_log('[PostEditorController] Ошибка проверки статуса: ' . $response->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    error_log('[PostEditorController] Статус получен: ' . json_encode($response));

    // Если статья готова, обновляем пост
    if (isset($response['status']) && $response['status'] === 'completed') {
      if (!empty($response['content'])) {
        PostPublisher::update_post_content(
          $post_id,
          $response['content'],
          $response['title'] ?? null
        );
        
        // Обновляем meta
        update_post_meta($post_id, '_cf_generation_status', 'completed');
        update_post_meta($post_id, '_cf_generation_completed_at', current_time('mysql'));
        
        error_log('[PostEditorController] Контент поста обновлен');
      }
    } elseif (isset($response['status']) && $response['status'] === 'error') {
      // Обновляем статус на ошибку
      update_post_meta($post_id, '_cf_generation_status', 'error');
      update_post_meta($post_id, '_cf_generation_error', $response['error_message'] ?? 'Unknown error');
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $response
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission($request) {
    $post_id = $request->get_param('id');
    
    // Проверяем, что пользователь может редактировать этот пост
    if (!empty($post_id)) {
      return current_user_can('edit_post', $post_id);
    }
    
    return current_user_can('edit_posts');
  }
}
