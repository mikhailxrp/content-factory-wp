<?php

namespace ContentFactoryUI\WP;

/**
 * Проверка прав доступа
 */
class Capability {
  /**
   * Может ли пользователь редактировать контент
   */
  public static function can_edit() {
    return current_user_can('edit_posts');
  }

  /**
   * Может ли пользователь управлять настройками
   */
  public static function can_manage_settings() {
    return current_user_can('manage_options');
  }

  /**
   * Может ли пользователь публиковать посты
   */
  public static function can_publish() {
    return current_user_can('publish_posts');
  }

  /**
   * Проверить права или вернуть WP_Error
   */
  public static function check($capability) {
    if (!current_user_can($capability)) {
      return new \WP_Error(
        'forbidden',
        __('Недостаточно прав для выполнения действия', 'content-factory-ui'),
        ['status' => 403]
      );
    }
    return true;
  }
}
