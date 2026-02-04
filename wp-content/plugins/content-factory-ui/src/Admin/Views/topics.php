<?php
/**
 * View: Темы
 */
?>
<div class="cf-ui-topics">
  <div class="cf-ui-toolbar">
    <div class="cf-ui-toolbar-row">
      <label for="cf-topics-run-id-select">
        <?php _e('Выберите запуск генерации:', 'content-factory-ui'); ?>
      </label>
      <select id="cf-topics-run-id-select" class="cf-ui-select">
        <option value=""><?php _e('Загрузка...', 'content-factory-ui'); ?></option>
      </select>
      <button type="button" id="cf-refresh-run-ids-topics" class="button">
        <?php _e('Обновить список', 'content-factory-ui'); ?>
      </button>
    </div>
    
    <div class="cf-ui-toolbar-row" style="margin-top: 10px;">
      <button type="button" id="cf-list-topics" class="button button-primary">
        <?php _e('Получить темы', 'content-factory-ui'); ?>
      </button>
      <button type="button" id="cf-generate-topics" class="button button-primary">
        <?php _e('Сгенерировать темы', 'content-factory-ui'); ?>
      </button>
      <button type="button" id="cf-update-topics" class="button button-primary">
        <?php _e('Обновить темы', 'content-factory-ui'); ?>
      </button>
    </div>
  </div>

  <div id="cf-topics-list" class="cf-ui-list">
    <!-- Список тем заполняется через JS -->
  </div>

  <div id="cf-topic-detail" class="cf-ui-detail" style="display: none;">
    <!-- Детали темы + редактор структуры -->
  </div>
</div>
