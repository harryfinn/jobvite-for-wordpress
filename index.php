<?php
/*
Plugin Name: Jobvite for Wordpress
Plugin URI:  http://1minus1.com
Description: A WP plugin to pull in a JSON feed of job listings from Jobvite
Version:     0.1.0
Author:      harryfinn
Author URI:  http://1minus1.com
License:     GPL2

Jobvite for Wordpress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Jobvite for Wordpress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Jobvite for Wordpress. If not, see https://www.gnu.org/licenses/gpl.html.
*/

class JobviteSetup {
  public $name,
         $prefix,
         $title,
         $slug;

  public function __construct() {
    $this->name = 'jobvite_for_wp';
    $this->prefix = 'jfw_';
    $this->title = ucwords(str_replace('_', ' ', $this->name));
    $this->slug = str_replace('_', '-', $this->name);

    spl_autoload_register([$this, $this->prefix . 'autoload_classes']);

    if(function_exists('__autoload')) {
      spl_autoload_register('__autoload');
    }
  }

  public function jfw_autoload_classes($name) {
    $class_path = plugin_dir_path(__FILE__) . 'admin/class.' . strtolower($name) . '.php';

    if(file_exists($class_path)) {
      require_once $class_path;
    }
  }

  public function init() {
    register_activation_hook(
      __FILE__,
      [$this, $this->prefix . 'activate_plugin']
    );

    new JobviteAdmin();
  }

  public function jfw_activate_plugin() {
    update_option($this->prefix . 'version', '0.1.0');
  }
}

$jobvite_plugin = new JobviteSetup();
$jobvite_plugin->init();
