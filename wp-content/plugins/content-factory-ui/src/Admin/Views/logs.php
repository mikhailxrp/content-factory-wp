<?php
/**
 * View: Логи
 */
?>
<div class="cf-ui-logs">
  <div class="cf-ui-toolbar">
    <label>
      <?php _e('Фильтр:', 'content-factory-ui'); ?>
      <select id="cf-logs-filter">
        <option value=""><?php _e('Все', 'content-factory-ui'); ?></option>
        <option value="request"><?php _e('Запросы', 'content-factory-ui'); ?></option>
        <option value="response"><?php _e('Ответы', 'content-factory-ui'); ?></option>
        <option value="error"><?php _e('Ошибки', 'content-factory-ui'); ?></option>
      </select>
    </label>
    <button type="button" id="cf-refresh-logs" class="button"><?php _e('Обновить', 'content-factory-ui'); ?></button>
    <button type="button" id="cf-clear-logs" class="button button-link-delete"><?php _e('Очистить логи', 'content-factory-ui'); ?></button>
  </div>

  <div id="cf-logs-list" class="cf-ui-logs-list">
    <!-- Логи заполняются через JS -->
  </div>
</div>
