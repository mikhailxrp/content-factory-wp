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
    error_log("[PostStatusSync] ===== КЛАСС ЗАГРУЖЕН И REGISTER() ВЫЗВАН =====");
    error_log("[PostStatusSync] Регистрируем hook transition_post_status");
    add_action('transition_post_status', [self::class, 'on_post_status_change'], 10, 3);
    add_action('init', [self::class, 'register_post_meta']);
    error_log("[PostStatusSync] Хуки зарегистрированы успешно");
  }

  /**
   * Регистрация post meta для REST API
   */
  public static function register_post_meta() {
    error_log("[PostStatusSync] Регистрируем post_meta topic_candidate_id");
    register_post_meta('post', 'topic_candidate_id', [
      'type' => 'string',
      'description' => 'ID темы из Content Factory',
      'single' => true,
      'show_in_rest' => true,
      'auth_callback' => function() {
        return current_user_can('edit_posts');
      }
    ]);
    error_log("[PostStatusSync] Post meta зарегистрирован");
  }

  /**
   * Обработчик изменения статуса поста
   */
  public static function on_post_status_change($new_status, $old_status, $post) {
    // Логируем в опцию WP для отладки
    $debug_log = get_option('cf_post_status_debug', []);
    $debug_log[] = [
      'time' => current_time('mysql'),
      'post_id' => $post->ID,
      'old_status' => $old_status,
      'new_status' => $new_status,
      'post_type' => $post->post_type
    ];
    // Храним только последние 20 записей
    $debug_log = array_slice($debug_log, -20);
    update_option('cf_post_status_debug', $debug_log);
    
    error_log("[PostStatusSync] Hook сработал! post_id={$post->ID}, old_status={$old_status}, new_status={$new_status}, post_type={$post->post_type}");
    
    // Только для постов типа 'post'
    if ($post->post_type !== 'post') {
      error_log("[PostStatusSync] Пропускаем - не post type");
      return;
    }

    // Проверяем, что это статья из нашей системы (есть topic_candidate_id)
    $topicId = get_post_meta($post->ID, 'topic_candidate_id', true);
    
    // Логируем в опцию для отладки
    $debug_log = get_option('cf_post_status_debug', []);
    $debug_log[count($debug_log) - 1]['topic_id'] = $topicId ?: 'НЕТ';
    $debug_log[count($debug_log) - 1]['all_meta'] = array_keys(get_post_meta($post->ID));
    update_option('cf_post_status_debug', $debug_log);
    
    error_log("[PostStatusSync] Получен topic_candidate_id из meta: " . var_export($topicId, true));
    
    if (empty($topicId)) {
      error_log("[PostStatusSync] Пропускаем - нет topic_candidate_id в post_meta");
      // Выведем все meta для отладки
      $all_meta = get_post_meta($post->ID);
      error_log("[PostStatusSync] Все post_meta: " . print_r($all_meta, true));
      
      // ВРЕМЕННО: отправляем запрос даже без topic_id для теста
      if ($new_status === 'publish' && $old_status !== 'publish') {
        error_log("[PostStatusSync] ТЕСТ: Отправляем без topic_id");
        self::notify_published($post->ID, 'TEST_' . $post->ID);
      }
      return;
    }

    // Отслеживаем только переход в статус 'publish'
    if ($new_status === 'publish' && $old_status !== 'publish') {
      error_log("[PostStatusSync] Переход в publish - отправляем уведомление");
      self::notify_published($post->ID, $topicId);
    }

    // Опционально: отслеживаем переход обратно в draft
    if ($new_status === 'draft' && $old_status === 'publish') {
      error_log("[PostStatusSync] Переход в draft - отправляем уведомление");
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
