<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\Cache\TransientCache;

/**
 * REST контроллер для контекста (ниша/ЦА)
 */
class ContextController {
  private const CACHE_KEY = 'context';

  /**
   * Получить контекст
   */
  public static function get($request) {
    $context = get_option('cf_ui_context', []);

    return rest_ensure_response([
      'success' => true,
      'data' => $context
    ]);
  }

  /**
   * Сохранить контекст
   */
  public static function save($request) {
    $data = $request->get_json_params();

    // Валидация обязательных полей
    if (empty($data['service_name'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Укажите название продукта/услуги', 'content-factory-ui')
      ], 400);
    }

    if (empty($data['service_description'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Укажите описание ниши', 'content-factory-ui')
      ], 400);
    }

    if (empty($data['target_audience'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Укажите целевую аудиторию', 'content-factory-ui')
      ], 400);
    }

    $context = [
      'service_name' => sanitize_text_field($data['service_name']),
      'service_description' => sanitize_textarea_field($data['service_description']),
      'target_audience' => sanitize_textarea_field($data['target_audience']),
      'keywords' => array_map('sanitize_text_field', $data['keywords'] ?? []),
      'updated_at' => current_time('mysql')
    ];

    update_option('cf_ui_context', $context);
    TransientCache::delete(self::CACHE_KEY);

    return rest_ensure_response([
      'success' => true,
      'message' => __('Контекст сохранён', 'content-factory-ui'),
      'data' => $context
    ]);
  }

  /**
   * Генерация смыслов из контекста
   */
  public static function generate_senses($request) {
    $context = get_option('cf_ui_context', []);

    if (empty($context['service_name'])) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Сначала заполните нишу и ЦА', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('generate_senses');

    $response = $client->post($endpoint, ['context' => $context]);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    // Кэшируем сгенерированные смыслы
    TransientCache::set('senses_list', $response, 120);

    return rest_ensure_response([
      'success' => true,
      'message' => __('Смыслы сгенерированы', 'content-factory-ui'),
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
