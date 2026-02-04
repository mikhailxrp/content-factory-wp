<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница Telegram
 */
class TelegramPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('telegram', __('Telegram', 'content-factory-ui'));
  }
}
