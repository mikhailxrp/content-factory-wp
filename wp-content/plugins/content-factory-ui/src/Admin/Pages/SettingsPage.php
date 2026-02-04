<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница настроек
 */
class SettingsPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('settings', __('Настройки подключения', 'content-factory-ui'));
  }
}
