<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\Cache\TransientCache;

/**
 * REST контроллер для смыслов
 */
class SensesController {
  /**
   * Список run_id
   */
  public static function list_run_ids($request) {
    $endpoint = Endpoints::get('list_run_ids');
    error_log('=== Запрос run_ids ===');
    error_log('Endpoint: ' . $endpoint);
    
    // Временно отключаем кэш для отладки
    $client = new Client();
    $n8n_url = \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
    error_log('n8n_url из настроек: ' . $n8n_url);
    error_log('Полный URL: ' . rtrim($n8n_url, '/') . '/' . ltrim($endpoint, '/'));
    error_log('Отправка GET запроса...');
    
    $run_ids = $client->get($endpoint);
    
    error_log('Тип ответа: ' . gettype($run_ids));
    error_log('Ответ от n8n (raw): ' . print_r($run_ids, true));

    if (is_wp_error($run_ids)) {
      error_log('Ошибка запроса run_ids: ' . $run_ids->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $run_ids->get_error_message()
      ]);
    }

    // Преобразуем массив объектов [{run_id: "..."}, ...] в массив строк ["...", ...]
    $run_ids_list = [];
    
    if (is_array($run_ids)) {
      error_log('Количество элементов в массиве: ' . count($run_ids));
      
      // Проверяем, если n8n вернул один объект вместо массива объектов
      if (isset($run_ids['run_id'])) {
        error_log('n8n вернул один объект, оборачиваем в массив');
        $run_ids = [$run_ids];
      }
      
      foreach ($run_ids as $key => $item) {
        error_log('Элемент #' . $key . ': ' . print_r($item, true));
        if (is_array($item) && isset($item['run_id'])) {
          $run_ids_list[] = $item['run_id'];
          error_log('Добавлен run_id (array): ' . $item['run_id']);
        } elseif (is_object($item) && isset($item->run_id)) {
          $run_ids_list[] = $item->run_id;
          error_log('Добавлен run_id (object): ' . $item->run_id);
        }
      }
    } else {
      error_log('ВНИМАНИЕ: run_ids не является массивом!');
    }

    error_log('Преобразованный список run_ids: ' . print_r($run_ids_list, true));
    return rest_ensure_response([
      'success' => true,
      'data' => $run_ids_list
    ]);
  }

  /**
   * Список смыслов по run_id
   */
  public static function list($request) {
    $run_id = $request->get_param('run_id');
    error_log('=== Запрос списка смыслов ===');
    error_log('run_id: ' . $run_id);
    
    if (empty($run_id)) {
      error_log('Ошибка: run_id не указан');
      return rest_ensure_response([
        'success' => false,
        'message' => 'run_id не указан'
      ]);
    }

    // Временно отключаем кеш для отладки
    $client = new Client();
    $endpoint = Endpoints::get('list_senses_by_run_id');
    $full_url = $endpoint . '?run_id=' . urlencode($run_id);
    error_log('Endpoint: ' . $endpoint);
    error_log('Полный URL запроса: ' . $full_url);
    
    $senses = $client->get($full_url);
    error_log('Ответ от n8n: ' . print_r($senses, true));

    if (is_wp_error($senses)) {
      error_log('Ошибка WP: ' . $senses->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $senses->get_error_message()
      ]);
    }

    // Если n8n вернул один объект вместо массива, оборачиваем в массив
    if (is_array($senses) && !isset($senses[0]) && isset($senses['id'])) {
      error_log('n8n вернул один объект смысла, оборачиваем в массив');
      $senses = [$senses];
    }

    error_log('Финальный результат для отправки: ' . print_r($senses, true));

    return rest_ensure_response([
      'success' => true,
      'data' => $senses
    ]);
  }

  /**
   * Получить конкретный смысл
   */
  public static function get($request) {
    $meaning_id = $request->get_param('id');
    error_log('=== Запрос детального смысла ===');
    error_log('ID (meaning_id): ' . $meaning_id);
    
    $client = new Client();
    $endpoint = Endpoints::get('get_sense');
    $full_url = $endpoint . '?meaning_id=' . urlencode($meaning_id);
    
    error_log('Endpoint: ' . $endpoint);
    error_log('Полный URL с параметром: ' . $full_url);
    $n8n_url = \ContentFactoryUI\Settings\SettingsRepository::get('n8n_url');
    error_log('Запрос к n8n: ' . rtrim($n8n_url, '/') . $full_url);
    
    $sense = $client->get($full_url);
    error_log('Ответ от n8n: ' . print_r($sense, true));

    if (is_wp_error($sense)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $sense->get_error_message()
      ]);
    }

    // Если n8n вернул массив с одним объектом, берём первый элемент
    if (is_array($sense) && isset($sense[0])) {
      error_log('n8n вернул массив, берём первый элемент');
      $sense = $sense[0];
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $sense
    ]);
  }

  /**
   * Генерация тем из смысла
   */
  public static function generate_topics($request) {
    $id = $request->get_param('id');
    $data = $request->get_json_params();

    $client = new Client();
    $endpoint = Endpoints::get('generate_topics');

    $response = $client->post($endpoint, [
      'sense_id' => $id,
      'count' => $data['count'] ?? 10
    ]);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    // Сбрасываем кэш тем
    TransientCache::delete('topics_list');

    return rest_ensure_response([
      'success' => true,
      'message' => __('Темы сгенерированы', 'content-factory-ui'),
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
