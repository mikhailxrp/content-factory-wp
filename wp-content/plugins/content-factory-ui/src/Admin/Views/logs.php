<?php
/**
 * View: Логи
 */
?>
<div class="cf-ui-logs">
  <div class="cf-ui-toolbar">
    <label>
      <?php _e('Дата:', 'content-factory-ui'); ?>
      <input type="date" id="cf-logs-date" style="width: 200px;" />
    </label>
    <button type="button" id="cf-refresh-logs" class="button"><?php _e('Обновить', 'content-factory-ui'); ?></button>
    <button type="button" id="cf-clear-logs" class="button button-link-delete" style="display:none;"><?php _e('Очистить логи', 'content-factory-ui'); ?></button>
  </div>

  <div id="cf-logs-list" class="cf-ui-logs-list">
    <!-- Логи заполняются через JS -->
  </div>
</div>
