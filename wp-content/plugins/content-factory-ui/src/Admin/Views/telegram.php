<?php
/**
 * View: Telegram
 */
?>
<div class="cf-ui-telegram">
  <div class="cf-ui-form-section">
    <h2><?php _e('Генерация поста для Telegram', 'content-factory-ui'); ?></h2>
    
    <form id="cf-tg-generate-form">
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="article_id"><?php _e('Статья', 'content-factory-ui'); ?></label>
          </th>
          <td>
            <select id="article_id" name="article_id" class="regular-text" required>
              <option value=""><?php _e('Выберите статью...', 'content-factory-ui'); ?></option>
            </select>
          </td>
        </tr>
      </table>

      <p class="submit">
        <button type="submit" class="button button-primary"><?php _e('Сгенерировать пост', 'content-factory-ui'); ?></button>
      </p>
    </form>
  </div>

  <div id="cf-tg-preview" class="cf-ui-preview" style="display: none;">
    <h2><?php _e('Предпросмотр', 'content-factory-ui'); ?></h2>
    <div id="cf-tg-text" class="cf-ui-tg-text"></div>
    
    <p class="submit">
      <button type="button" id="cf-tg-publish" class="button button-primary"><?php _e('Опубликовать', 'content-factory-ui'); ?></button>
      <button type="button" id="cf-tg-edit" class="button"><?php _e('Редактировать', 'content-factory-ui'); ?></button>
    </p>
  </div>
</div>
