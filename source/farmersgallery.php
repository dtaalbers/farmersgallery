<?php

/**
* Plugin Name: Farmers Gallery
* Plugin URI: https://github.com/dtaalbers/farmersgallery
* Description: A simple easy-to-use gallery plugin. Easily add gallerys to your website
* Version: 1.0
* Author: Tom Aalbers (dtaalbers)
* Author URI: http://www.dtaalbers.com 
**/

if (!function_exists('add_action')) {
    echo 'I can do nothing when called directly.';
    exit;
}

define('FARMERSGALLERY_VERSION', '1.0');
define('FARMERSGALLERY_MINIMUM_WP_VERSION', '3.2');
define('FARMERSGALLERY_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('FARMERSGALLERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once(FARMERSGALLERY_PLUGIN_DIR.'classes/class.pagetemplater.php');
require_once(FARMERSGALLERY_PLUGIN_DIR.'classes/class.farmer.php');
add_action( 'plugins_loaded', 'load_language_files');
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ));
add_action('init', array('Farmer', 'init'));

function load_language_files() {
    load_plugin_textdomain('farmersgallery', false, dirname(plugin_basename(__FILE__) ).'/lang/' );
}