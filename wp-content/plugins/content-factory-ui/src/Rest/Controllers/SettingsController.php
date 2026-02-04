<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\Settings\SettingsRepository;
use ContentFactoryUI\Settings\SettingsValidator;
use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;

/**
 * REST контроллер для настроек
 */
class SettingsController {
  /**
   * Получить настройки
   */
  public static function get($request) {
    $settings = SettingsRepository::get_all();

    return rest_ensure_response([
      'success' => true,
      'data' => $settings
    ]);
  }

  /**
   * Сохранить настройки
   */
  public static function save($request) {
    $data = $request->get_json_params();

    // Валидация
    $validation = SettingsValidator::validate_all($data);
    if (is_wp_error($validation)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $validation->get_error_message(),
        'errors' => $validation->get_error_data()
      ]);
    }

    // Сохранение
    $saved = SettingsRepository::save($data);

    if (!$saved) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не удалось сохранить настройки', 'content-factory-ui')
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Настройки сохранены', 'content-factory-ui')
    ]);
  }

  /**
   * Тест подключения к n8n
   */
  public static function test_connection($request) {
    $client = new Client();
    $endpoint = Endpoints::get('test_connection');

    $response = $client->get($endpoint);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Соединение успешно', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('manage_options');
  }
}
