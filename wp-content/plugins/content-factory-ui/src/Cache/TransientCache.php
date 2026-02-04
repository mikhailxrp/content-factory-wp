<?php

namespace ContentFactoryUI\Cache;

/**
 * Кэширование через Transients API
 */
class TransientCache {
  private const PREFIX = 'cf_ui_';
  private const DEFAULT_TTL = 120; // 2 минуты

  /**
   * Получить из кэша
   */
  public static function get($key) {
    return get_transient(self::PREFIX . $key);
  }

  /**
   * Сохранить в кэш
   */
  public static function set($key, $value, $ttl = null) {
    $ttl = $ttl ?? self::DEFAULT_TTL;
    return set_transient(self::PREFIX . $key, $value, $ttl);
  }

  /**
   * Удалить из кэша
   */
  public static function delete($key) {
    return delete_transient(self::PREFIX . $key);
  }

  /**
   * Очистить весь кэш плагина
   */
  public static function flush() {
    global $wpdb;
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        '_transient_' . self::PREFIX . '%',
        '_transient_timeout_' . self::PREFIX . '%'
      )
    );
  }

  /**
   * Получить или вычислить значение
   */
  public static function remember($key, $callback, $ttl = null) {
    $value = self::get($key);
    
    if ($value === false) {
      $value = $callback();
      self::set($key, $value, $ttl);
    }
    
    return $value;
  }
}
