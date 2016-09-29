<?php

if ( !defined( 'ABSPATH' ) )
	exit;

add_action( 'add_meta_boxes', function() {
	if ( !current_user_can( 'edit_posts' ) )
		return;
	add_meta_box( 'postcaldiv', __( 'Post Calendar', 'postcal' ), function( $post ) {
		$meta = get_post_meta( $post->ID, 'postcal', TRUE );
		$url = admin_url( 'admin-ajax.php?action=postcal_metabox&post=' . $post->ID );
?>
<p>
	<label>
		<input id="postcal-check" type="checkbox" value="on"<?php checked( $meta !== '' ); ?> autocomplete="off" />
		<span><?= esc_html__( 'Link this post to a date.', 'postcal' ) ?></span>
	</label>
</p>
<p id="postcal-container">
	<label>
		<span><?= esc_html__( 'Date', 'postcal' ) ?></span>
		<br />
		<input id="postcal-date" type="date" value="<?= $meta ?? '' ?>" autocomplete="off" />
	</label>
</p>
<p>
	<a id="postcal-save" class="button" href="<?= $url ?>"><?= esc_html__( 'Save', 'postcal' ) ?></a>
	<span id="postcal-spinner" class="spinner"></span>
</p>
<?php
	}, 'post', 'side' );
} );

add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
	if ( !current_user_can( 'edit_posts' ) )
		return;
	if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' )
		return;
	wp_enqueue_script( 'postcal_metabox_script', plugins_url( 'metabox.js', __FILE__ ), array( 'jquery' ) );
} );

add_action( 'wp_ajax_postcal_metabox', function() {
	if ( !array_key_exists( 'post', $_REQUEST ) )
		exit( 'post: argument not defined' ); // TODO i18n
	$post = filter_var( $_REQUEST['post'], FILTER_VALIDATE_INT );
	if ( $post === FALSE || !current_user_can( 'edit_post', $post ) )
		exit( 'post: argument not valid' ); // TODO i18n
	$check = array_key_exists( 'check', $_REQUEST );
	if ( !array_key_exists( 'date', $_REQUEST ) )
		exit( 'date: argument not defined' ); // TODO i18n
	if ( $check ) {
		$date = DateTime::createFromFormat( 'Y-m-d', $_REQUEST['date'] );
		if ( $date === FALSE )
			exit( __( 'date not specified', 'postcal' ) );
		$date = $date->format( 'Y-m-d' );
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
