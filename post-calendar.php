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

require_once plugin_dir_path( __FILE__ ) . 'request.php';

// TODO privileges

add_action( 'add_meta_boxes', function() {
	add_meta_box( 'postcaldiv', 'Post Calendar', function( $post ) {
		$meta = get_post_meta( $post->ID, 'postcal', TRUE );
?>
<p>
	<input id="postcal-check" type="checkbox" name="postcal_check" value="on"<?php checked( $meta !== '' ); ?> autocomplete="off" />
	<label for="postcal-check">Assign a date to this post.</label>
</p>
<p id="postcal-container">
	<label for="postcal-date">Date</label>
	<br />
	<input id="postcal-date" type="date" name="postcal_date" value="<?= $meta ?>" autocomplete="off" />
</p>
<p>
	<a id="postcal-save" class="button" href="<?= admin_url( 'admin-ajax.php?action=postcal&post=' . $post->ID ) ?>">Save</a>
	<span id="postcal-spinner" class="spinner"></span>
</p>
<?php
	}, 'post', 'side' );
} );

add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
	wp_enqueue_script( 'postcal_metabox_script', plugins_url( 'metabox.js', __FILE__ ) );
} );

add_action( 'wp_ajax_postcal', function() {
	$post = postcal_request_int( 'post' );
	$check = postcal_request_bool( 'postcal_check' );
	$date = postcal_request_date( 'postcal_date', TRUE );
	if ( $check && !is_null( $date ) ) {
		update_post_meta( $post, 'postcal', $date );
	} else {
		$check = FALSE;
		$date = NULL;
		delete_post_meta( $post, 'postcal' );
	}
	header( 'content-type: application/json' );
	exit( json_encode( array(
		'check' => $check,
		'date' => $date,
	) ) );
} );
