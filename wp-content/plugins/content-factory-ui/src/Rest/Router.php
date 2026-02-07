<?php

namespace ContentFactoryUI\Rest;

use ContentFactoryUI\Rest\Controllers\SettingsController;
use ContentFactoryUI\Rest\Controllers\ContextController;
use ContentFactoryUI\Rest\Controllers\SensesController;
use ContentFactoryUI\Rest\Controllers\TopicsController;
use ContentFactoryUI\Rest\Controllers\ArticlesController;
use ContentFactoryUI\Rest\Controllers\TelegramController;
use ContentFactoryUI\Rest\Controllers\LogsController;
use ContentFactoryUI\Rest\Controllers\PromptsController;

/**
 * Регистрация REST API маршрутов
 */
class Router {
  private const NAMESPACE = 'content-factory/v1';

  /**
   * Регистрация всех маршрутов
   */
  public static function register() {
    error_log('=== Регистрация REST API роутов ===');
    // Settings
    register_rest_route(self::NAMESPACE, '/settings', [
      [
        'methods' => 'GET',
        'callback' => [SettingsController::class, 'get'],
        'permission_callback' => [SettingsController::class, 'check_permission']
      ],
      [
        'methods' => 'POST',
        'callback' => [SettingsController::class, 'save'],
        'permission_callback' => [SettingsController::class, 'check_permission']
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/settings/test', [
      'methods' => 'POST',
      'callback' => [SettingsController::class, 'test_connection'],
      'permission_callback' => [SettingsController::class, 'check_permission']
    ]);

    // Context
    register_rest_route(self::NAMESPACE, '/context', [
      [
        'methods' => 'GET',
        'callback' => [ContextController::class, 'get'],
        'permission_callback' => [ContextController::class, 'check_permission']
      ],
      [
        'methods' => 'POST',
        'callback' => [ContextController::class, 'save'],
        'permission_callback' => [ContextController::class, 'check_permission']
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/context/generate-senses', [
      'methods' => 'POST',
      'callback' => [ContextController::class, 'generate_senses'],
      'permission_callback' => [ContextController::class, 'check_permission']
    ]);

    // Senses
    register_rest_route(self::NAMESPACE, '/senses/run-ids', [
      'methods' => 'GET',
      'callback' => [SensesController::class, 'list_run_ids'],
      'permission_callback' => [SensesController::class, 'check_permission']
    ]);

    $result = register_rest_route(self::NAMESPACE, '/senses/list', [
      'methods' => 'GET',
      'callback' => [SensesController::class, 'list'],
      'permission_callback' => [SensesController::class, 'check_permission'],
      'args' => [
        'run_id' => [
          'required' => true,
          'type' => 'string',
          'description' => 'ID запуска генерации',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);
    error_log('Регистрация роута /senses/list: ' . ($result ? 'SUCCESS' : 'FAILED'));

    register_rest_route(self::NAMESPACE, '/senses/(?P<id>[a-zA-Z0-9_-]+)', [
      'methods' => 'GET',
      'callback' => [SensesController::class, 'get'],
      'permission_callback' => [SensesController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/senses/(?P<id>[\d]+)/generate-topics', [
      'methods' => 'POST',
      'callback' => [SensesController::class, 'generate_topics'],
      'permission_callback' => [SensesController::class, 'check_permission']
    ]);

    // Topics
    register_rest_route(self::NAMESPACE, '/topics/list', [
      'methods' => 'GET',
      'callback' => [TopicsController::class, 'list'],
      'permission_callback' => [TopicsController::class, 'check_permission'],
      'args' => [
        'run_id' => [
          'required' => true,
          'type' => 'string',
          'description' => 'ID запуска генерации',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/topics/generate', [
      'methods' => 'POST',
      'callback' => [TopicsController::class, 'generate'],
      'permission_callback' => [TopicsController::class, 'check_permission'],
      'args' => [
        'run_id' => [
          'required' => true,
          'type' => 'string',
          'description' => 'ID запуска генерации',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/topics/update', [
      'methods' => 'POST',
      'callback' => [TopicsController::class, 'update'],
      'permission_callback' => [TopicsController::class, 'check_permission'],
      'args' => [
        'run_id' => [
          'required' => true,
          'type' => 'string',
          'description' => 'ID запуска генерации',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/topics/get', [
      'methods' => 'GET',
      'callback' => [TopicsController::class, 'get'],
      'permission_callback' => [TopicsController::class, 'check_permission'],
      'args' => [
        'id' => [
          'required' => true,
          'type' => 'string',
          'description' => 'ID темы',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/topics/(?P<id>[\d]+)/outline', [
      'methods' => 'PUT',
      'callback' => [TopicsController::class, 'update_outline'],
      'permission_callback' => [TopicsController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/topics/(?P<id>[\d]+)/generate-article', [
      'methods' => 'POST',
      'callback' => [TopicsController::class, 'generate_article'],
      'permission_callback' => [TopicsController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/topics/(?P<id>[\d]+)/check-article-status', [
      'methods' => 'GET',
      'callback' => [TopicsController::class, 'check_article_status'],
      'permission_callback' => [TopicsController::class, 'check_permission']
    ]);

    // Articles
    register_rest_route(self::NAMESPACE, '/articles', [
      'methods' => 'GET',
      'callback' => [ArticlesController::class, 'list'],
      'permission_callback' => [ArticlesController::class, 'check_permission'],
      'args' => [
        'run_id' => [
          'required' => false,
          'type' => 'string',
          'description' => 'ID запуска генерации',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'status' => [
          'required' => false,
          'type' => 'string',
          'description' => 'Статус статьи (draft, published)',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ]
    ]);

    register_rest_route(self::NAMESPACE, '/articles/(?P<id>[\d]+)', [
      'methods' => 'GET',
      'callback' => [ArticlesController::class, 'get'],
      'permission_callback' => [ArticlesController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/articles/(?P<id>[\d]+)/create-draft', [
      'methods' => 'POST',
      'callback' => [ArticlesController::class, 'create_draft'],
      'permission_callback' => [ArticlesController::class, 'check_permission']
    ]);

    // Telegram
    register_rest_route(self::NAMESPACE, '/telegram/generate', [
      'methods' => 'POST',
      'callback' => [TelegramController::class, 'generate'],
      'permission_callback' => [TelegramController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/telegram/publish', [
      'methods' => 'POST',
      'callback' => [TelegramController::class, 'publish'],
      'permission_callback' => [TelegramController::class, 'check_permission']
    ]);

    // Logs
    register_rest_route(self::NAMESPACE, '/logs', [
      'methods' => 'GET',
      'callback' => [LogsController::class, 'list'],
      'permission_callback' => [LogsController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/logs/clear', [
      'methods' => 'POST',
      'callback' => [LogsController::class, 'clear'],
      'permission_callback' => [LogsController::class, 'check_permission']
    ]);

    // Prompts
    register_rest_route(self::NAMESPACE, '/prompts', [
      'methods' => 'GET',
      'callback' => [PromptsController::class, 'list'],
      'permission_callback' => [PromptsController::class, 'check_permission']
    ]);

    register_rest_route(self::NAMESPACE, '/prompts/(?P<id>[\d]+)', [
      'methods' => 'PUT',
      'callback' => [PromptsController::class, 'update'],
      'permission_callback' => [PromptsController::class, 'check_permission']
    ]);
  }
}
