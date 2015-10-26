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
    add_action('update_option_' . $this->prefix . 'rewrite_options', [
      $this, $this->prefix . 'rewrite_options_updated_callback'
    ], 10, 2);
    add_action('template_redirect', [$this, $this->prefix . 'redirect_to_index']);
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
      $this->prefix . 'jobvite_settings',
      'API Settings',
      [$this, $this->prefix . 'jobvite_settings_callback'],
      $this->slug . '-api-settings'
    );

    register_setting(
      $this->prefix . 'jobvite_settings',
      $this->prefix . 'api_keys'
    );

    register_setting(
      $this->prefix . 'jobvite_settings',
      $this->prefix . 'template_options'
    );

    register_setting(
      $this->prefix . 'jobvite_settings',
      $this->prefix . 'rewrite_options',
      [$this, $this->prefix . 'rewrite_sanitize_callback']
    );

    add_settings_section(
      $this->prefix . 'jobvite_feed_settings',
      'Jobvite Feed',
      [$this, $this->prefix . 'jobvite_feed_callback'],
      $this->slug . '-feed-settings'
    );

    register_setting(
      $this->prefix . 'jobvite_feed_settings',
      $this->prefix . 'job_feed'
    );

    register_setting(
      $this->prefix . 'jobvite_feed_settings',
      $this->prefix . 'api_timeout'
    );
  }

  public function jfw_add_custom_rewrites() {
    $rewrite_options = get_option($this->prefix . 'rewrite_options');
    $current_rewrite_url = trailingslashit(
      !empty($rewrite_options['url']) ?
        $rewrite_options['url'] :
        'jobs'
    );

    add_rewrite_rule(
      $current_rewrite_url . '([a-zA-Z\d]+)/?',
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

  public function jfw_jobvite_settings_callback() {
    settings_fields($this->prefix . 'jobvite_settings');
    $this->jfw_api_settings_callback();
    $this->jfw_template_settings_callback();
    $this->jfw_rewrite_settings_callback();
  }

  public function jfw_jobvite_feed_callback() {
    settings_fields($this->prefix . 'jobvite_feed_settings');
    $this->jfw_admin_notice_error();
    $this->jfw_admin_notice_success();

    unset($GLOBALS['jfw_notification_error']);
    unset($GLOBALS['jfw_notification_success']);

    settings_fields($this->prefix . 'jobvite_feed_settings');

    if(!empty($_REQUEST['cache-jobvite-feed'])) {
      $this->jfw_cache_jobvite_feed();
    }
  }

  public function jfw_api_settings_callback() {
    $api_options = get_option($this->prefix . 'api_keys');
    ?>

    <label for="<?php echo $this->prefix; ?>api_keys[api]">API Key</label>
    <input name="<?php echo $this->prefix; ?>api_keys[api]" type="text" value="<?php echo $api_options['api']; ?>" />
    <br>
    <label for="<?php echo $this->prefix; ?>api_keys[secret]">Secret Key</label>
    <input name="<?php echo $this->prefix; ?>api_keys[secret]" type="text" value="<?php echo $api_options['secret']; ?>" />
    <br>
    <label for="<?php echo $this->prefix; ?>api_keys[company_id]">Company ID</label>
    <input name="<?php echo $this->prefix; ?>api_keys[company_id]" type="text" value="<?php echo $api_options['company_id']; ?>" />

    <?php
  }

  public function jfw_template_settings_callback() {
    $template_options = get_option($this->prefix . 'template_options');
    $theme_templates = get_page_templates();
    $current_template = !empty($template_options['custom_template']) ?
      $template_options['custom_template'] :
      false;

    ?>

    <h3>Template Settings</h3>

    <?php
    $options_html = '';

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

  public function jfw_rewrite_settings_callback() {
    $rewrite_options = get_option($this->prefix . 'rewrite_options');
    $current_rewrite_url = !empty($rewrite_options['url']) ?
      $rewrite_options['url'] :
      'jobs';

    $example_url = trailingslashit(get_bloginfo('url')) .
                   trailingslashit($current_rewrite_url) . '123'

    ?>

    <h3>Rewrite Settings</h3>
    <p>Currently jobvite posts can be viewed by their ID by going to:
      <code><?php echo $example_url; ?></code>
      <em>(Note: 123 is an example ID)</em>
      - You can amend this url below.
    </p>

    <label for="<?php echo $this->prefix; ?>rewrite_options[url]">Amend the url to: </label>
    <input name="<?php echo $this->prefix; ?>rewrite_options[url]" type="text" value="<?php echo $current_rewrite_url; ?>" />
    <em>(Note: you do not need to add a trailing slash)</em>

    <?php
  }

  public function jfw_rewrite_sanitize_callback($input) {
    array_walk($input, function(&$val, $key) {
      $val = $key == 'url' ? rtrim($val, '/') : $val;
    });

    return $input;
  }

  public function jfw_rewrite_options_updated_callback($old_val, $new_val) {
    if($new_val !== $old_val) {
      flush_rewrite_rules();
    }
  }

  public function jfw_redirect_to_index() {
    $job_id = get_query_var('jobvite_id');
    $job_feed = get_option($this->prefix . 'job_feed');

    if(!empty($job_id) && empty($job_feed[$job_id])) {
      $rewrite_options = get_option($this->prefix . 'rewrite_options');
      $rewrite_url = $rewrite_options['url'];
      $redirect_url = trailingslashit(get_bloginfo('url')) .
        trailingslashit($rewrite_url);

      wp_safe_redirect($redirect_url, 301);
      exit;
    }
  }

  public function jfw_render_settings_page() {
    ?>

    <div class="wrap">
      <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
      <form method="post" action="options.php">

        <?php
        do_settings_sections($this->slug . '-api-settings');
        submit_button('Save Jobvite Settings', 'primary', 'submit');
        ?>

      </form>
      <form method="post" action="options-general.php?page=<?php echo $this->slug; ?>">
        <p>Please note that the Jobvite cache can only be queried once every hour in order to prevent excessive API calls. Time remaining until next refresh can be made:
        <strong><?php echo $this->jfw_get_timeout_remaining(); ?></strong>

        <?php
        do_settings_sections($this->slug . '-feed-settings');
        submit_button(
          'Update Jobvite Cache',
          'secondary',
          'cache-jobvite-feed',
          false
        );
        ?>
      </form>
    </div>

    <?php
  }

  private function jfw_get_timeout_remaining() {
    $now = new DateTime;
    $api_timeout = $this->jfw_get_api_timeout($now);
    $api_timeout->modify('+1 hour');
    $time_diff = $api_timeout->diff($now);

    return $time_diff->i . ' mins';
  }

  private function jfw_get_api_timeout($now) {
    $api_timeout = get_option($this->prefix . 'api_timeout');

    return !empty($api_timeout) ? new DateTime($api_timeout) : $now;
  }

  private function jfw_cache_jobvite_feed() {
    $jobvite_feed = get_option($this->prefix . 'job_feed');
    $now = new DateTime;
    $timeout_datetime = $this->jfw_get_api_timeout($now);

    if(empty($jobvite_feed) || ($now > $timeout_datetime->modify('+1 hour'))) {
      $api_options = get_option($this->prefix . 'api_keys');
      $jobvite_feed = new JobviteAPI(
        $api_options['api'],
        $api_options['secret'],
        $api_options['company_id']
      );

      $job_feed = $jobvite_feed->get_results();

      if(!empty($job_feed)) {
        update_option(
          $this->prefix . 'api_timeout',
          $now->format('Y-m-d H:i:s')
        );

        update_option(
          $this->prefix . 'job_feed',
          $jobvite_feed->get_results()
        );

        $GLOBALS['jfw_notification_success'] = true;
      } else {
        $GLOBALS['jfw_notification_error'] = true;
      }
    }
  }

  private function jfw_admin_notice_error() {
    if(!empty($GLOBALS['jfw_notification_error'])) {
    ?>

      <div class="error">Error: An hour has not yet elapsed since the Jobvite feed was last updated. Please waited until the hour has passed in order to manually refresh the feed.</div>

    <?php
    }
  }

  private function jfw_admin_notice_success() {
    if(!empty($GLOBALS['jfw_notification_success'])) {
    ?>

      <div class="updated">Success: Jobvite feed has been updated successfully.</div>

    <?php
    }
  }
}
