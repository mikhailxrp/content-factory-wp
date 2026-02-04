<?php
/**
 * View: Ниша и ЦА
 */
?>
<div class="cf-ui-context">
  <form id="cf-context-form" class="cf-ui-form">
    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="service_name"><?php _e('Ниша (продукт/услуга)', 'content-factory-ui'); ?></label>
        </th>
        <td>
          <input type="text" id="service_name" name="service_name" class="regular-text" placeholder="Например: IT-консалтинг, фитнес-тренировки" required>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="service_description"><?php _e('Описание ниши', 'content-factory-ui'); ?></label>
        </th>
        <td>
          <textarea id="service_description" name="service_description" rows="4" class="large-text" placeholder="Подробно опишите вашу нишу, продукт или услугу..." required></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="target_audience"><?php _e('Целевая аудитория', 'content-factory-ui'); ?></label>
        </th>
        <td>
          <textarea id="target_audience" name="target_audience" rows="5" class="large-text" placeholder="Опишите вашу ЦА..." required></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="keywords"><?php _e('Ключевые слова', 'content-factory-ui'); ?></label>
        </th>
        <td>
          <textarea id="keywords" name="keywords" rows="3" class="large-text" placeholder="Через запятую"></textarea>
          <p class="description"><?php _e('Основные ключевые слова через запятую', 'content-factory-ui'); ?></p>
        </td>
      </tr>
    </table>

    <p class="submit">
      <button type="submit" class="button button-primary"><?php _e('Сохранить контекст', 'content-factory-ui'); ?></button>
      <button type="button" id="cf-generate-senses" class="button button-secondary"><?php _e('Сгенерировать смыслы', 'content-factory-ui'); ?></button>
    </p>
  </form>
</div>
