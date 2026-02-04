<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница логов
 */
class LogsPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('logs', __('Логи и ошибки', 'content-factory-ui'));
  }
}
