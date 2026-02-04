<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница смыслов
 */
class SensesPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('senses', __('Смыслы', 'content-factory-ui'));
  }
}
