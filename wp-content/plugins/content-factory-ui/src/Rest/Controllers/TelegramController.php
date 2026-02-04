<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;

/**
 * REST контроллер для Telegram
 */
class TelegramController {
  /**
   * Генерация TG поста
   */
  public static function generate($request) {
    $data = $request->get_json_params();
    $article_id = $data['article_id'] ?? null;

    if (!$article_id) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID статьи', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('generate_telegram');

    $response = $client->post($endpoint, [
      'article_id' => $article_id
    ]);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Пост для TG сгенерирован', 'content-factory-ui'),
      'data' => $response
    ]);
  }

  /**
   * Публикация TG поста
   */
  public static function publish($request) {
    $data = $request->get_json_params();
    $post_id = $data['post_id'] ?? null;
    $text = $data['text'] ?? null;

    if (!$post_id || !$text) {
      return rest_ensure_response([
        'success' => false,
        'message' => __('Не указан ID поста или текст', 'content-factory-ui')
      ]);
    }

    $client = new Client();
    $endpoint = Endpoints::get('publish_telegram');

    $response = $client->post($endpoint, [
      'post_id' => $post_id,
      'text' => $text
    ]);

    if (is_wp_error($response)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $response->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'message' => __('Пост опубликован в Telegram', 'content-factory-ui'),
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
