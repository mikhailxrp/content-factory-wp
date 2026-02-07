<?php

namespace ContentFactoryUI\Admin\Pages;

/**
 * Страница промптов
 */
class PromptsPage {
  public static function render() {
    require_once CF_UI_DIR . 'src/Admin/Views/layout.php';
    cf_ui_layout('prompts', __('Промпты', 'content-factory-ui'));
  }
}
