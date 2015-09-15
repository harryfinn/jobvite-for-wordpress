<?php

class JobviteAdmin extends JobviteSetup {
  public function __construct() {
    parent::__construct();

    add_action('admin_menu', [$this, $this->prefix . 'add_admin_page']);
    add_action('admin_init', [$this, $this->prefix . 'add_custom_settings']);
  }

  public function jfw_add_admin_page() {
    add_options_page(
      $this->title,
      $this->title,
      'manage_options',
      $this->slug,
      [$this, $this->prefix . 'render_settings_page']
    );
  }

  public function jfw_add_custom_settings() {
    add_settings_section(
      $this->prefix . 'api_settings',
      'API Settings',
      '',
      $this->slug
    );

    register_setting(
      $this->prefix . 'api_settings',
      $this->prefix . 'api_keys'
    );
  }

  public function jfw_render_settings_page() {
    $updated_settings = $_REQUEST['settings-updated'];
    $updated_settings = !empty(updated_settings) ? $updated_settings : false;
    ?>

    <div class="wrap">
      <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

      <form method="post" action="options.php">
        <?php
        settings_fields($this->prefix . 'api_settings');
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

    <?php
  }
}

