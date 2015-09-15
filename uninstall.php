<?php

require_once(plugin_dir_path( __FILE__ ) . '/index.php');

class JobviteUninstall extends JobviteSetup {
  public function __construct() {
    if(!defined('WP_UNINSTALL_PLUGIN'))
      exit();

    parent::__construct();

    delete_option($this->prefix . 'version');
    delete_option($this->prefix . 'api_keys');
    delete_option($this->prefix . 'template_options');
  }
}

new JobviteUninstall;
