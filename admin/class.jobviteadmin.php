<?php

class JobviteAdmin extends JobviteSetup {
  public function __construct() {
    parent::__construct();

    add_action('admin_menu', [$this, $this->prefix . 'add_admin_page']);
    add_action('admin_init', [$this, $this->prefix . 'add_custom_settings']);
    add_action('admin_init', [$this, $this->prefix . 'add_custom_rewrites']);
    add_filter('query_vars', [$this, $this->prefix . 'add_custom_query_vars']);
    add_filter(
      'template_include', [$this, $this->prefix . 'add_job_template'], 1, 1
    );
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
      [$this, $this->prefix . 'api_settings_callback'],
      $this->slug
    );

    add_settings_section(
      $this->prefix . 'template_settings',
      'Template Settings',
      [$this, $this->prefix . 'template_settings_callback'],
      $this->slug
    );

    register_setting(
      $this->prefix . 'api_settings',
      $this->prefix . 'api_keys'
    );

    register_setting(
      $this->prefix . 'template_settings',
      $this->prefix . 'template_options'
    );
  }

  public function jfw_add_custom_rewrites() {
    add_rewrite_rule(
      'jobs/([0-9]+)/?',
      'index.php?jobvite_id=$matches[1]',
      'top'
    );

    flush_rewrite_rules();
  }

  public function jfw_add_custom_query_vars($query_vars) {
    $query_vars[] = 'jobvite_id';
    return $query_vars;
  }

  public function jfw_add_job_template($template) {
    global $wp_query;

    if(isset($wp_query->query_vars['jobvite_id'])) {
      $plugin_default_template = plugin_dir_path(__FILE__) . '../templates/single-jobvite.php';
      $template_options = get_option($this->prefix . 'template_options');

      if(!empty($template_options['custom_template'])) {
        $template_location = locate_template([
          $template_options['custom_template']
        ]);

        return !empty($template_location) ?
          $template_location :
          $plugin_default_template;
      } else {
        return $plugin_default_template;
      }
    }

    return $template;
  }

  public function jfw_api_settings_callback() {
    settings_fields($this->prefix . 'api_settings');
    $api_options = get_option($this->prefix . 'api_keys');
    ?>

    <label for="<?php echo $this->prefix; ?>api_keys[api]">API Key</label>
    <input name="<?php echo $this->prefix; ?>api_keys[api]" type="text" value="<?php echo $api_options['api']; ?>" />
    <br>
    <label for="<?php echo $this->prefix; ?>api_keys[secret]">Secret Key</label>
    <input name="<?php echo $this->prefix; ?>api_keys[secret]" type="text" value="<?php echo $api_options['secret']; ?>" />

    <?php
  }

  public function jfw_template_settings_callback() {
    settings_fields($this->prefix . 'template_settings');
    $template_options = get_option($this->prefix . 'template_options');
    $theme_templates = get_page_templates();
    $current_template = !empty($template_options['custom_template']) ?
      $template_options['custom_template'] :
      false;

    if(!empty($theme_templates)) {
      foreach($theme_templates as $name => $file) {
        $options_html .= '<option value="' . $file . '"' .
                      selected($file, $current_template, false) .
                      '>' . $name . ' (' . $file . ')</option>';
      }
    ?>
    <p>Use the select field below to choose a theme template you'd like to use to display each (individual) job post on. This allows for your own template markup and styling to be used.</p>

    <p>Otherwise, you can use a simple template included with this plugin to display your job posts which will just include the header and footer templates.</p>

    <label for="<?php echo $this->prefix; ?>template_options[custom_template]">Use a theme template?</label>
    <select name="<?php echo $this->prefix; ?>template_options[custom_template]">
      <option value="false">Use plugin template</option>
      <?php echo $options_html; ?>
    </select>

    <?php
    }
  }

  public function jfw_render_settings_page() {
    $updated_settings = $_REQUEST['settings-updated'];
    $updated_settings = !empty(updated_settings) ? $updated_settings : false;
    ?>

    <div class="wrap">
      <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
      <form method="post" action="options.php">
        <?php
        do_settings_sections($this->slug);
        submit_button();
        ?>
      </form>
    </div>

    <?php
  }
}

