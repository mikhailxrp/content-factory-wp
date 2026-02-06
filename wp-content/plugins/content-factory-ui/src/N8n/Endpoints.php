<?php

namespace ContentFactoryUI\N8n;

/**
 * Маппинг действий на endpoints n8n
 */
class Endpoints {
  private static $endpoints = [
    'generate_senses' => '/webhook/generate-senses',
    'generate_topics' => '/webhook/generate-topics',
    'update_topics' => '/webhook-test/update-topics',
    'list_topics' => '/webhook/topics/list',
    'get_topic' => '/webhook/topics/get',
    'generate_article' => '/webhook-test/generate-article',
    'check_article_status' => '/webhook/check-article-status',
    'update_article_status' => '/webhook/update-article-status',
    'generate_telegram' => '/webhook-test/generate-telegram',
    'publish_telegram' => '/webhook-test/publish-telegram',
    'test_connection' => '/webhook/test',
    'list_run_ids' => '/webhook/senses/run-ids',
    'list_senses_by_run_id' => '/webhook/senses/list',
    'get_sense' => '/webhook/senses'
  ];

  /**
   * Получить endpoint по действию
   */
  public static function get($action) {
    // Сначала проверяем кастомные endpoints из настроек
    $custom_endpoints = \ContentFactoryUI\Settings\SettingsRepository::get('endpoints', []);
    
    if (isset($custom_endpoints[$action])) {
      return $custom_endpoints[$action];
    }

    // Иначе используем дефолтные
    return self::$endpoints[$action] ?? null;
  }

  /**
   * Получить все endpoints
   */
  public static function get_all() {
    $custom = \ContentFactoryUI\Settings\SettingsRepository::get('endpoints', []);
    return array_merge(self::$endpoints, $custom);
  }

  /**
   * Обновить endpoint
   */
  public static function set($action, $endpoint) {
    $endpoints = \ContentFactoryUI\Settings\SettingsRepository::get('endpoints', []);
    $endpoints[$action] = $endpoint;
    return \ContentFactoryUI\Settings\SettingsRepository::set('endpoints', $endpoints);
  }
}
