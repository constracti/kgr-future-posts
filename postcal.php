<?php
/*
 * Plugin Name: Post Calendar
 * Plugin URI: https://github.com/constracti/postcal
 * Description: Registers a calendar widget with links to associated posts. Links are filtered by a selected day. Each post may appear in one or more dates.
 * Author: constracti
 * Version: 1.2.1
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: postcal
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) )
	exit;

require_once plugin_dir_path( __FILE__ ) . 'metabox.php';
require_once plugin_dir_path( __FILE__ ) . 'widget.php';

// TODO optionally: settings page, custom post column, post save

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'postcal', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );
