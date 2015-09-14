<?php

class JobviteAdmin {
  public function jfw_render_settings_page() {
    $updated_settings = $_REQUEST['settings-updated'];
    $updated_settings = !empty(updated_settings) ? $updated_settings : false;
    ?>

    <div class="wrap">
      <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

      <div id="poststuff">
        <div id="post-body">
          <div id="post-body-content">
            <form method="post" action="options.php">
              <?php
              settings_fields($this->slug);
              do_settings_sections($this->slug);
              $options = get_option($this->prefix . 'api_keys');
              $field_name = $this->prefix . '_option_';
              ?>

              <label for="<?php echo $this->prefix; ?>api_keys[api]">API Key</label>
              <input name="<?php echo $this->prefix; ?>api_keys[api]" type="text" value="<?php echo $options['api']; ?>" />
              <br>
              <label for="<?php echo $this->prefix; ?>api_keys[secret]">Secret Key</label>
              <input name="<?php echo $this->prefix; ?>api_keys[secret]" type="text" value="<?php echo $options['secret']; ?>" />

              <?php submit_button(); ?>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php
  }
}

