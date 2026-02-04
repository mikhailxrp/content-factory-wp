<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\Cache\TransientCache;

/**
 * REST контроллер для тем
 */
class TopicsController {
  /**
   * Получить список тем по run_id
   */
  public static function list($request) {
    $run_id = $request->get_param('run_id');

    if (empty($run_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан run_id', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('list_topics');
    
    $topics = $client->get($endpoint . '?run_id=' . urlencode($run_id));

    if (is_wp_error($topics)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $topics->get_error_message()
      ]);
    }

    // Сбрасываем кэш при получении новых данных
    TransientCache::delete('topics_list');

    return rest_ensure_response([
      'success' => true,
      'data' => $topics
    ]);
  }

  /**
   * Генерация тем по run_id
   */
  public static function generate($request) {
    $run_id = $request->get_param('run_id');

    if (empty($run_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан run_id', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $client->set_timeout(120);
    
    $endpoint = Endpoints::get('generate_topics');
    
    $response = $client->post($endpoint . '?run_id=' . urlencode($run_id));

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    // Сбрасываем кэш после генерации
    TransientCache::delete('topics_list');

    return rest_ensure_response([
      'success' => true,
      'message' => __('Темы сгенерированы', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Обновление тем по run_id
   */
  public static function update($request) {
    $run_id = $request->get_param('run_id');

    if (empty($run_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан run_id', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $client->set_timeout(120);
    
    $endpoint = Endpoints::get('update_topics');
    
    $response = $client->post($endpoint . '?run_id=' . urlencode($run_id));

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    // Сбрасываем кэш после обновления
    TransientCache::delete('topics_list');

    return rest_ensure_response([
      'success' => true,
      'message' => __('Темы обновлены', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Получить конкретную тему
   */
  public static function get($request) {
    $id = $request->get_param('id');

    if (empty($id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID темы', 'content-factory-ui')
      ]);
    }
    
    $client = new Client();
    $endpoint = Endpoints::get('get_topic');
    
    $topic = $client->get($endpoint . '?id=' . urlencode($id));

    if (is_wp_error($topic)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $topic->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $topic
    ]);
  }

  /**
   * Обновить структуру темы
   */
  public static function update_outline($request) {
    $id = $request->get_param('id');
    $data = $request->get_json_params();

    $client = new Client();
    $endpoint = Endpoints::get('update_outline') ?? '/webhook/topics/' . $id . '/outline';

    $response = $client->post($endpoint, [
      'outline' => $data['outline']
    ]);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Структура обновлена', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Генерация статьи из темы
   */
  public static function generate_article($request) {
    $id = $request->get_param('id');

    if (empty($id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID темы', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $client->set_timeout(300); // 5 минут для генерации статьи
    
    $endpoint = Endpoints::get('generate_article');
    
    error_log('[TopicsController] Генерация статьи для темы ID: ' . $id);
    error_log('[TopicsController] Endpoint: ' . $endpoint);
    
    // Отправляем id как query-параметр
    $response = $client->post($endpoint . '?id=' . urlencode($id));

    if (is_wp_error($response)) {
      error_log('[TopicsController] Ошибка генерации: ' . $response->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    error_log('[TopicsController] Статья успешно сгенерирована');
    
    // Сбрасываем кэш статей
    TransientCache::delete('articles_list');

    return rest_ensure_response([
      'success' => true,
      'message' => __('Генерация статьи запущена', 'content-factory-ui'),
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
