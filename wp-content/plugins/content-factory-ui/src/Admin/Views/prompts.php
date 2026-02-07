<?php
/**
 * View: Промпты
 */
?>
<div class="cf-ui-prompts">
  <div class="cf-ui-toolbar">
    <div class="cf-ui-toolbar-group">
      <button type="button" id="cf-add-prompt" class="button button-primary"><?php _e('Добавить промпт', 'content-factory-ui'); ?></button>
    </div>
    <button type="button" id="cf-refresh-prompts" class="button"><?php _e('Обновить', 'content-factory-ui'); ?></button>
  </div>

  <div id="cf-prompts-list" class="cf-ui-list">
    <p><?php _e('Загрузка промптов...', 'content-factory-ui'); ?></p>
  </div>

  <div id="cf-prompt-detail" class="cf-ui-detail" style="display: none;">
    <!-- Детали промпта -->
  </div>
</div>
