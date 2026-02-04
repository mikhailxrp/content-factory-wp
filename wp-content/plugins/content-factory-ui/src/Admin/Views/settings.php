<?php
/**
 * View: Настройки
 */
?>
<div class="cf-ui-settings">
  <form id="cf-settings-form" class="cf-ui-form">
    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="n8n_url"><?php _e('URL n8n', 'content-factory-ui'); ?></label>
        </th>
        <td>
          <input type="url" id="n8n_url" name="n8n_url" class="regular-text" placeholder="https://n8n.example.com" required>
          <p class="description"><?php _e('Базовый URL вашего сервера n8n', 'content-factory-ui'); ?></p>
        </td>
      </tr>
    </table>

    <p class="submit">
      <button type="submit" class="button button-primary"><?php _e('Сохранить настройки', 'content-factory-ui'); ?></button>
      <button type="button" id="cf-test-connection" class="button"><?php _e('Проверить подключение', 'content-factory-ui'); ?></button>
    </p>
  </form>

  <div id="cf-test-result" class="cf-ui-notice" style="display: none;"></div>
</div>
