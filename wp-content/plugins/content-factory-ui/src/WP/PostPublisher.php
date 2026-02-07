<?php

namespace ContentFactoryUI\WP;

/**
 * Создание и обновление WP постов
 */
class PostPublisher {
  /**
   * Создать черновик поста
   */
  public static function create_draft($title, $content, $meta = []) {
    $post_data = [
      'post_title' => sanitize_text_field($title),
      'post_content' => wp_kses_post($content),
      'post_status' => 'draft',
      'post_type' => 'post',
      'post_author' => get_current_user_id()
    ];

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
      return $post_id;
    }

    // Сохраняем мета-данные
    foreach ($meta as $key => $value) {
      update_post_meta($post_id, 'cf_ui_' . $key, $value);
    }

    return $post_id;
  }

  /**
   * Обновить существующий пост
   */
  public static function update($post_id, $data) {
    $update_data = ['ID' => $post_id];

    if (isset($data['title'])) {
      $update_data['post_title'] = sanitize_text_field($data['title']);
    }

    if (isset($data['content'])) {
      $update_data['post_content'] = wp_kses_post($data['content']);
    }

    if (isset($data['status'])) {
      $update_data['post_status'] = sanitize_key($data['status']);
    }

    $result = wp_update_post($update_data);

    if (is_wp_error($result)) {
      return $result;
    }

    // Обновляем мета
    if (isset($data['meta']) && is_array($data['meta'])) {
      foreach ($data['meta'] as $key => $value) {
        update_post_meta($post_id, 'cf_ui_' . $key, $value);
      }
    }

    return $result;
  }

  /**
   * Получить ID поста по meta-данным
   */
  public static function find_by_meta($key, $value) {
    $posts = get_posts([
      'post_type' => 'post',
      'posts_per_page' => 1,
      'meta_query' => [
        [
          'key' => 'cf_ui_' . $key,
          'value' => $value
        ]
      ],
      'fields' => 'ids'
    ]);

    return !empty($posts) ? $posts[0] : null;
  }

  /**
   * Связать WP пост с n8n article
   */
  public static function link_to_n8n($post_id, $n8n_article_id) {
    return update_post_meta($post_id, 'cf_ui_article_id', $n8n_article_id);
  }

  /**
   * Обновить контент поста (для генерации из редактора)
   */
  public static function update_post_content($post_id, $content, $title = null) {
    $update_data = [
      'ID' => $post_id,
      'post_content' => wp_kses_post($content)
    ];

    // Опционально обновляем заголовок
    if (!empty($title)) {
      $update_data['post_title'] = sanitize_text_field($title);
    }

    $result = wp_update_post($update_data, true);

    if (is_wp_error($result)) {
      error_log('[PostPublisher] Ошибка обновления поста: ' . $result->get_error_message());
      return $result;
    }

    error_log('[PostPublisher] Пост ID ' . $post_id . ' успешно обновлен');
    return $result;
  }
}
