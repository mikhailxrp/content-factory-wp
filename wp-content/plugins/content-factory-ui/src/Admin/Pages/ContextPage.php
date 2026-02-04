<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница ниши и ЦА
 */
class ContextPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('context', __('Ниша и целевая аудитория', 'content-factory-ui'));
  }
}
