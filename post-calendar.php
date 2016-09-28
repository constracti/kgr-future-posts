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

add_action( 'admin_notices', function() {
	echo '<p>ready for a plugin?</p>';
} );
