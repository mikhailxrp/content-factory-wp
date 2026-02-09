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
    $required_fields = ['request', 'audience', 'keywords', 'volume_from', 'volume_to', 'requirements', 'tone', 'angle', 'context', 'additional_elements', 'avoid'];
    foreach ($required_fields as $field) {
      if (empty($data[$field]) && $data[$field] !== 0) {
        return rest_ensure_response([
          'success' => false,
          'message' => sprintf(__('Заполните обязательное поле: %s', 'content-factory-ui'), $field)
        ]);
      }
    }
    
    // Валидация диапазона объёма
    $volume_from = intval($data['volume_from']);
    $volume_to = intval($data['volume_to']);
    
    if ($volume_from < 500 || $volume_from > 3000) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Объём "от" должен быть от 500 до 3000', 'content-factory-ui')
      ]);
    }
    
    if ($volume_to < 500 || $volume_to > 3000) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Объём "до" должен быть от 500 до 3000', 'content-factory-ui')
      ]);
    }
    
    if ($volume_from > $volume_to) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Объём "от" не может быть больше чем "до"', 'content-factory-ui')
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

    // Обработка ключевых слов
    $keywords = [];
    if (!empty($data['keywords']) && is_array($data['keywords'])) {
      $keywords = array_map('sanitize_text_field', $data['keywords']);
    }
    
    $payload = [
      'post_id' => $post_id,
      'request' => sanitize_textarea_field($data['request']),
      'audience' => sanitize_textarea_field($data['audience']),
      'keywords' => $keywords,
      'volume_from' => intval($data['volume_from']),
      'volume_to' => intval($data['volume_to']),
      'requirements' => sanitize_textarea_field($data['requirements']),
      'tone' => sanitize_text_field($data['tone']),
      'angle' => sanitize_text_field($data['angle']),
      'context' => sanitize_textarea_field($data['context']),
      'format' => 'WordPress',
      'additional_elements' => sanitize_textarea_field($data['additional_elements']),
      'avoid' => sanitize_textarea_field($data['avoid'])
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

    error_log('[PostEditorController] Статус получен: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
    error_log('[PostEditorController] Тип response: ' . gettype($response));
    error_log('[PostEditorController] Is array: ' . (is_array($response) ? 'YES' : 'NO'));
    
    // Если N8N вернул массив, берём первый элемент
    if (is_array($response) && isset($response[0]) && is_array($response[0])) {
      error_log('[PostEditorController] Response is array, taking first element');
      $response = $response[0];
      error_log('[PostEditorController] Response after extraction: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    // Если статья готова, обновляем пост
    if (isset($response['status']) && $response['status'] === 'completed') {
      error_log('[PostEditorController] Status is COMPLETED');
      
      if (!empty($response['content'])) {
        error_log('[PostEditorController] Content exists, length: ' . strlen($response['content']));
        error_log('[PostEditorController] Title: ' . ($response['title'] ?? 'NO TITLE'));
        
        PostPublisher::update_post_content(
          $post_id,
          $response['content'],
          $response['title'] ?? null
        );
        
        // Обновляем meta
        update_post_meta($post_id, '_cf_generation_status', 'completed');
        update_post_meta($post_id, '_cf_generation_completed_at', current_time('mysql'));
        
        error_log('[PostEditorController] Контент поста обновлен в БД');
      } else {
        error_log('[PostEditorController] WARNING: Content is empty!');
      }
    } elseif (isset($response['status']) && $response['status'] === 'error') {
      error_log('[PostEditorController] Status is ERROR');
      // Обновляем статус на ошибку
      update_post_meta($post_id, '_cf_generation_status', 'error');
      update_post_meta($post_id, '_cf_generation_error', $response['error_message'] ?? 'Unknown error');
    } else {
      error_log('[PostEditorController] Status: ' . ($response['status'] ?? 'UNKNOWN'));
    }

    error_log('[PostEditorController] Возвращаем ответ в JavaScript');
    error_log('[PostEditorController] Response to JS: ' . json_encode($response, JSON_UNESCAPED_UNICODE));

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
