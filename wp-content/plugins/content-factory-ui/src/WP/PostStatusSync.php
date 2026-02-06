<?php

namespace ContentFactoryUI\WP;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;

/**
 * Синхронизация статуса постов с внешней БД через n8n
 */
class PostStatusSync {
  /**
   * Регистрация хуков
   */
  public static function register() {
    add_action('transition_post_status', [self::class, 'on_post_status_change'], 10, 3);
    add_action('init', [self::class, 'register_post_meta']);
  }

  /**
   * Регистрация post meta для REST API
   */
  public static function register_post_meta() {
    register_post_meta('post', 'topic_candidate_id', [
      'type' => 'string',
      'description' => 'ID темы из Content Factory',
      'single' => true,
      'show_in_rest' => true,
      'auth_callback' => function() {
        return current_user_can('edit_posts');
      }
    ]);
  }

  /**
   * Обработчик изменения статуса поста
   */
  public static function on_post_status_change($new_status, $old_status, $post) {
    // Только для постов типа 'post'
    if ($post->post_type !== 'post') {
      return;
    }

    // Проверяем, что это статья из нашей системы (есть topic_candidate_id)
    $topicId = get_post_meta($post->ID, 'topic_candidate_id', true);
    if (empty($topicId)) {
      return;
    }

    // Отслеживаем только переход в статус 'publish'
    if ($new_status === 'publish' && $old_status !== 'publish') {
      self::notify_published($post->ID, $topicId);
    }

    // Опционально: отслеживаем переход обратно в draft
    if ($new_status === 'draft' && $old_status === 'publish') {
      self::notify_unpublished($post->ID, $topicId);
    }
  }

  /**
   * Уведомить n8n о публикации статьи
   */
  private static function notify_published($post_id, $topic_id) {
    error_log("[PostStatusSync] Статья опубликована: post_id={$post_id}, topic_id={$topic_id}");

    $client = new Client();
    $endpoint = Endpoints::get('update_article_status');

    if (!$endpoint) {
      error_log("[PostStatusSync] Endpoint 'update_article_status' не настроен");
      return;
    }

    // Формируем query-параметры
    $params = [
      'wordpress_post_id' => $post_id,
      'topic_candidate_id' => $topic_id,
      'status' => 'published',
      'published_at' => current_time('mysql'),
      'post_url' => get_permalink($post_id)
    ];

    // Добавляем параметры в URL
    $url = $endpoint . '?' . http_build_query($params);
    
    error_log("[PostStatusSync] Отправка запроса: {$url}");

    $response = $client->post($url);

    if (is_wp_error($response)) {
      error_log("[PostStatusSync] Ошибка отправки уведомления: " . $response->get_error_message());
    } else {
      error_log("[PostStatusSync] Уведомление отправлено успешно");
    }
  }

  /**
   * Уведомить n8n о снятии статьи с публикации
   */
  private static function notify_unpublished($post_id, $topic_id) {
    error_log("[PostStatusSync] Статья снята с публикации: post_id={$post_id}, topic_id={$topic_id}");

    $client = new Client();
    $endpoint = Endpoints::get('update_article_status');

    if (!$endpoint) {
      return;
    }

    // Формируем query-параметры
    $params = [
      'wordpress_post_id' => $post_id,
      'topic_candidate_id' => $topic_id,
      'status' => 'draft',
      'unpublished_at' => current_time('mysql')
    ];

    // Добавляем параметры в URL
    $url = $endpoint . '?' . http_build_query($params);

    $response = $client->post($url);

    if (is_wp_error($response)) {
      error_log("[PostStatusSync] Ошибка отправки уведомления: " . $response->get_error_message());
    }
  }
}
