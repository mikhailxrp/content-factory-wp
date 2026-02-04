<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница тем
 */
class TopicsPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('topics', __('Темы', 'content-factory-ui'));
  }
}
