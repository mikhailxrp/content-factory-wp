<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница статей
 */
class ArticlesPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('articles', __('Статьи', 'content-factory-ui'));
  }
}
