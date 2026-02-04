<?php

namespace ContentFactoryUI\Rest\Controllers;

use ContentFactoryUI\N8n\Client;
use ContentFactoryUI\N8n\Endpoints;
use ContentFactoryUI\WP\PostPublisher;
use ContentFactoryUI\Cache\TransientCache;

/**
 * REST контроллер для статей
 */
class ArticlesController {
  /**
   * Список статей
   */
  public static function list($request) {
    $articles = TransientCache::remember('articles_list', function() {
      $client = new Client();
      $endpoint = Endpoints::get('list_articles') ?? '/webhook/articles';
      return $client->get($endpoint);
    }, 120);

    if (is_wp_error($articles)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $articles->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $articles
    ]);
  }

  /**
   * Получить конкретную статью
   */
  public static function get($request) {
    $id = $request->get_param('id');
    
    $client = new Client();
    $endpoint = Endpoints::get('get_article') ?? '/webhook/articles/' . $id;
    
    $article = $client->get($endpoint);

    if (is_wp_error($article)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $article->get_error_message()
      ]);
    }

    return rest_ensure_response([
      'success' => true,
      'data' => $article
    ]);
  }

  /**
   * Создать черновик WP из статьи
   */
  public static function create_draft($request) {
    $id = $request->get_param('id');
    
    // Получаем статью из n8n
    $client = new Client();
    $endpoint = Endpoints::get('get_article') ?? '/webhook/articles/' . $id;
    $article = $client->get($endpoint);

    if (is_wp_error($article)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $article->get_error_message()
      ]);
    }

    // Создаём черновик
    $post_id = PostPublisher::create_draft(
      $article['title'],
      $article['content'],
      [
        'article_id' => $id,
        'topic_id' => $article['topic_id'] ?? null
      ]
    );

    if (is_wp_error($post_id)) {
      return rest_ensure_response([
        'success' => false,
        'message' => $post_id->get_error_message()
      ]);
    }

    // Отправляем обратно в n8n ID поста
    $link_endpoint = Endpoints::get('link_article') ?? '/webhook/articles/' . $id . '/link';
    $client->post($link_endpoint, ['wp_post_id' => $post_id]);

    return rest_ensure_response([
      'success' => true,
      'message' => __('Черновик создан', 'content-factory-ui'),
      'data' => [
        'post_id' => $post_id,
        'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit')
      ]
    ]);
  }

  /**
   * Проверка прав доступа
   */
  public static function check_permission() {
    return current_user_can('edit_posts');
  }
}
