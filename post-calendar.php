<?php
/*
Plugin Name: Post Calendar
Plugin URI: https://github.com/constracti/post-calendar
Description: Assign a date to every post and display a relative calendar.
Author: constracti
Version: 1.0
Author URI: https://raktivan.tk/
Text Domain: postcal
*/

if ( !defined( 'ABSPATH' ) )
	exit;

require_once plugin_dir_path( __FILE__ ) . 'metabox.php';
require_once plugin_dir_path( __FILE__ ) . 'widget.php';

// TODO optionally: settings, custom post column, post save
