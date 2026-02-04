<?php

namespace ContentFactoryUI\Admin;

use ContentFactoryUI\Admin\Pages\SettingsPage;
use ContentFactoryUI\Admin\Pages\ContextPage;
use ContentFactoryUI\Admin\Pages\SensesPage;
use ContentFactoryUI\Admin\Pages\TopicsPage;
use ContentFactoryUI\Admin\Pages\ArticlesPage;
use ContentFactoryUI\Admin\Pages\TelegramPage;
use ContentFactoryUI\Admin\Pages\LogsPage;

/**
 * Регистрация меню и роутинг страниц админки
 */
class Menu {
  /**
   * Регистрация пунктов меню
   */
  public static function register() {
    // Главное меню
    add_menu_page(
      __('Content Factory', 'content-factory-ui'),
      __('Content Factory', 'content-factory-ui'),
      'edit_posts',
      'content-factory',
      [ContextPage::class, 'render'],
      'dashicons-welcome-write-blog',
      30
    );

    // Подменю
    add_submenu_page(
      'content-factory',
      __('Ниша и ЦА', 'content-factory-ui'),
      __('Ниша и ЦА', 'content-factory-ui'),
      'edit_posts',
      'content-factory',
      [ContextPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Смыслы', 'content-factory-ui'),
      __('Смыслы', 'content-factory-ui'),
      'edit_posts',
      'content-factory-senses',
      [SensesPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Темы', 'content-factory-ui'),
      __('Темы', 'content-factory-ui'),
      'edit_posts',
      'content-factory-topics',
      [TopicsPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Статьи', 'content-factory-ui'),
      __('Статьи', 'content-factory-ui'),
      'edit_posts',
      'content-factory-articles',
      [ArticlesPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Telegram', 'content-factory-ui'),
      __('Telegram', 'content-factory-ui'),
      'edit_posts',
      'content-factory-telegram',
      [TelegramPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Логи', 'content-factory-ui'),
      __('Логи', 'content-factory-ui'),
      'manage_options',
      'content-factory-logs',
      [LogsPage::class, 'render']
    );

    add_submenu_page(
      'content-factory',
      __('Настройки', 'content-factory-ui'),
      __('Настройки', 'content-factory-ui'),
      'manage_options',
      'content-factory-settings',
      [SettingsPage::class, 'render']
    );
  }
}
