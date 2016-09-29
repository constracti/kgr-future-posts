<?php
/*
 * Plugin Name: Post Calendar
 * Plugin URI: https://github.com/constracti/postcal
 * Description: Links selected posts to a date and displays them in a relevant calendar widget.
 * Author: constracti
 * Version: 1.0
 * Text Domain: postcal
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) )
	exit;

require_once plugin_dir_path( __FILE__ ) . 'metabox.php';
require_once plugin_dir_path( __FILE__ ) . 'widget.php';

// TODO optionally: settings, custom post column, post save
