<?php
/**
 * View: Статьи
 */
?>
<div class="cf-ui-articles">
  <div class="cf-ui-toolbar">
    <div class="cf-ui-toolbar-row">
      <label for="cf-articles-run-id-select">
        <?php _e('Выберите запуск генерации:', 'content-factory-ui'); ?>
      </label>
      <select id="cf-articles-run-id-select" class="cf-ui-select">
        <option value=""><?php _e('Все', 'content-factory-ui'); ?></option>
      </select>
      
      <label for="cf-articles-status-select">
        <?php _e('Статус:', 'content-factory-ui'); ?>
      </label>
      <select id="cf-articles-status-select" class="cf-ui-select">
        <option value=""><?php _e('Все', 'content-factory-ui'); ?></option>
        <option value="draft"><?php _e('Черновик', 'content-factory-ui'); ?></option>
        <option value="published"><?php _e('Опубликовано', 'content-factory-ui'); ?></option>
      </select>
      
      <button type="button" id="cf-load-articles" class="button button-primary">
        <?php _e('Загрузить статьи', 'content-factory-ui'); ?>
      </button>
      <button type="button" id="cf-refresh-articles" class="button">
        <?php _e('Обновить', 'content-factory-ui'); ?>
      </button>
    </div>
  </div>

  <div id="cf-articles-list" class="cf-ui-list">
    <div class="cf-ui-notice info">
      <p><strong>Инструкция:</strong></p>
      <ol style="margin: 10px 0 0 20px; padding: 0;">
        <li>Выберите запуск генерации</li>
        <li>Выберите статус статьи</li>
        <li>Нажмите кнопку "Загрузить статьи"</li>
      </ol>
    </div>
  </div>

  <div id="cf-article-detail" class="cf-ui-detail" style="display: none;">
    <!-- Детали статьи + кнопка создания черновика -->
  </div>
</div>
