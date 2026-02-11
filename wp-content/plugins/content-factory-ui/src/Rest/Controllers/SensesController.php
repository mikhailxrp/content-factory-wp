<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\Cache\TransientCache;
use ContentFactoryUI\Logger\Logger;

/**
 * REST контроллер для смыслов
 */
class SensesController {
  /**
   * Список run_id
   */
  public static function list_run_ids($request) {
    $endpoint = Endpoints::get('list_run_ids');
    Logger::debug('=== Запрос run_ids ===');
    Logger::debug('Endpoint: ' . $endpoint);
    
    $client = new Client();
    $run_ids = $client->get($endpoint);
    
    Logger::debug('Ответ от n8n', $run_ids);

    if (is_wp_error($run_ids)) {
      Logger::error('Ошибка запроса run_ids: ' . $run_ids->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $run_ids->get_error_message()
      ]);
    }

    // Преобразуем массив объектов [{run_id: "..."}, ...] в массив строк ["...", ...]
    $run_ids_list = [];
    
    if (is_array($run_ids)) {
      // Проверяем, если n8n вернул один объект вместо массива объектов
      if (isset($run_ids['run_id'])) {
        Logger::debug('n8n вернул один объект, оборачиваем в массив');
        $run_ids = [$run_ids];
      }
      
      foreach ($run_ids as $item) {
        if (is_array($item) && isset($item['run_id'])) {
          $run_ids_list[] = $item['run_id'];
        } elseif (is_object($item) && isset($item->run_id)) {
          $run_ids_list[] = $item->run_id;
        }
      }
    }

    Logger::debug('Преобразованный список run_ids', $run_ids_list);
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
    Logger::debug('=== Запрос списка смыслов ===');
    Logger::debug('run_id: ' . $run_id);
    
    if (empty($run_id)) {
      Logger::error('run_id не указан');
      return rest_ensure_response([
        'success' => false,
        'message' => 'run_id не указан'
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('list_senses_by_run_id');
    $full_url = $endpoint . '?run_id=' . urlencode($run_id);
    
    $senses = $client->get($full_url);
    Logger::debug('Ответ от n8n', $senses);

    if (is_wp_error($senses)) {
      Logger::error('Ошибка WP: ' . $senses->get_error_message());
      return rest_ensure_response([
        'success' => false,
        'message' => $senses->get_error_message()
      ]);
    }

    // Если n8n вернул один объект вместо массива, оборачиваем в массив
    if (is_array($senses) && !isset($senses[0]) && isset($senses['id'])) {
      Logger::debug('n8n вернул один объект смысла, оборачиваем в массив');
      $senses = [$senses];
    }

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
    $run_id = $request->get_param('run_id');
    Logger::debug('=== Запрос детального смысла ===');
    Logger::debug("ID: $meaning_id, Run ID: $run_id");
    
    $client = new Client();
    $endpoint = Endpoints::get('get_sense');
    $full_url = $endpoint . '?meaning_id=' . urlencode($meaning_id);
    
    if (!empty($run_id)) {
      $full_url .= '&run_id=' . urlencode($run_id);
    }
    
    $sense = $client->get($full_url);
    Logger::debug('Ответ от n8n', $sense);

    if (is_wp_error($sense)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $sense->get_error_message()
      ]);
    }

    // Если n8n вернул массив с одним объектом, берём первый элемент
    if (is_array($sense) && isset($sense[0])) {
      Logger::debug('n8n вернул массив, берём первый элемент');
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
   * Обновить один смысл по run_id и meaning_id
   */
  public static function update_one($request) {
    $run_id = $request->get_param('run_id');
    $meaning_id = $request->get_param('meaning_id');
    $data = $request->get_json_params();

    if (empty($run_id) || empty($meaning_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан run_id или meaning_id', 'content-factory-ui')
      ]);
    }

    $sense = isset($data['sense']) && is_array($data['sense']) ? $data['sense'] : [];

    if (empty($sense)) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не переданы данные смысла', 'content-factory-ui')
      ]);
    }

    // Гарантируем наличие meaning_id внутри данных смысла
    if (empty($sense['meaning_id'])) {
      $sense['meaning_id'] = $meaning_id;
    }

    $client = new Client();
    $endpoint = Endpoints::get('update_sense') ?? '/webhook/senses/update-one';

    $payload = [
      'run_id' => $run_id,
      'meaning_id' => $meaning_id,
      'sense' => $sense
    ];

    $response = $client->post($endpoint, $payload);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Смысл обновлён', 'content-factory-ui'),
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
