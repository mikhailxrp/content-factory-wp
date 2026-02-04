<?php
/**
 * View: Смыслы
 */
?>
<div class="cf-ui-senses">
  <div class="cf-ui-toolbar">
    <div class="cf-ui-toolbar-group">
      <label for="cf-run-id-select"><?php _e('Запуски генерации:', 'content-factory-ui'); ?></label>
      <select id="cf-run-id-select" class="cf-ui-select">
        <option value=""><?php _e('Загрузка...', 'content-factory-ui'); ?></option>
      </select>
      <button type="button" id="cf-load-senses" class="button button-primary"><?php _e('Получить смыслы', 'content-factory-ui'); ?></button>
    </div>
    <button type="button" id="cf-refresh-run-ids" class="button"><?php _e('Обновить run_id', 'content-factory-ui'); ?></button>
  </div>

  <div id="cf-senses-list" class="cf-ui-list">
    <p><?php _e('Выберите запуск генерации и нажмите "Получить смыслы"', 'content-factory-ui'); ?></p>
  </div>

  <div id="cf-sense-detail" class="cf-ui-detail" style="display: none;">
    <!-- Детали смысла -->
  </div>
</div>
