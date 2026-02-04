<?php
/**
 * Общий layout для страниц админки
 */

function cf_ui_layout($page, $title) {
  ?>
  <div class="wrap cf-ui-wrap">
    <h1><?php echo esc_html($title); ?></h1>
    
    <div class="cf-ui-container" id="cf-ui-app" data-page="<?php echo esc_attr($page); ?>">
      <div class="cf-ui-loading">
        <span class="spinner is-active"></span>
        <p><?php _e('Загрузка...', 'content-factory-ui'); ?></p>
      </div>
      
      <div class="cf-ui-content" style="display: none;">
        <?php
        $view_file = CF_UI_DIR . 'src/Admin/Views/' . $page . '.php';
        if (file_exists($view_file)) {
          require_once $view_file;
        }
        ?>
      </div>
    </div>
  </div>
  <?php
}
