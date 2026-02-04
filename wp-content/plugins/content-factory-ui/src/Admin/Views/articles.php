<?php
/**
 * View: Статьи
 */
?>
<div class="cf-ui-articles">
  <div class="cf-ui-toolbar">
    <button type="button" id="cf-refresh-articles" class="button"><?php _e('Обновить', 'content-factory-ui'); ?></button>
  </div>

  <div id="cf-articles-list" class="cf-ui-list">
    <!-- Список статей заполняется через JS -->
  </div>

  <div id="cf-article-detail" class="cf-ui-detail" style="display: none;">
    <!-- Детали статьи + кнопка создания черновика -->
  </div>
</div>
