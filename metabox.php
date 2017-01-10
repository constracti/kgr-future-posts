<?php

if ( !defined( 'ABSPATH' ) )
	exit;

function postcal_field( string $meta = '' ) {
?>
	<p class="postcal-metabox-field">
		<input class="postcal-metabox-input" type="date" value="<?= $meta ?? '' ?>" autocomplete="off" />
		<a class="postcal-metabox-delete" href="#postcal-metabox-delete"><?= esc_html__( 'Delete', 'postcal' ) ?></a>
	</p>
<?php
}

add_action( 'add_meta_boxes', function() {
	if ( !current_user_can( 'edit_posts' ) )
		return;
	add_meta_box( 'postcaldiv', __( 'Post Calendar', 'postcal' ), function( $post ) {
		$meta = get_post_meta( $post->ID, 'postcal', FALSE );
		$url = admin_url( 'admin-ajax.php?action=postcal-metabox&post=' . $post->ID );
?>
<p class="dashicons-before dashicons-info"><?= esc_html__( 'When one of the following dates is selected in the Post Calendar widget, a link to this post will be displayed.', 'postcal' ) ?></p>
<div id="postcal-metabox-container">
<?php
		sort( $meta, SORT_STRING );
		foreach ( $meta as $date )
			postcal_field( $date );
?>
</div>
<div id="postcal-metabox-sample">
<?php
		postcal_field();
?>
</div>
<p><a id="postcal-metabox-add" href="#postcal-metabox-add"><?= esc_html__( 'Add', 'postcal' ) ?></a></p>
<p>
	<a id="postcal-metabox-save" class="button" href="<?= $url ?>"><?= esc_html__( 'Save', 'postcal' ) ?></a>
	<span id="postcal-metabox-spinner" class="spinner"></span>
</p>
<hr />
<p>
	<small class="dashicons-before dashicons-warning">
		<span><?= esc_html__( 'Currently, Internet Explorer and Mozilla Firefox do not support the input date element.', 'postcal' ) ?></span>
		<span><?= esc_html__( 'Date format is YYYY-MM-DD.', 'postcal' ) ?></span>
	</small>
</p>
<?php
	}, 'post', 'side' );
} );

add_action( 'admin_enqueue_scripts', function( $hook_suffix ) {
	if ( !current_user_can( 'edit_posts' ) )
		return;
	if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' )
		return;
	wp_enqueue_style( 'postcal_metabox_style', plugins_url( 'metabox.css', __FILE__ ) );
	wp_enqueue_script( 'postcal_metabox_script', plugins_url( 'metabox.js', __FILE__ ), array( 'jquery' ) );
} );

add_action( 'wp_ajax_postcal-metabox', function() {
	if ( !array_key_exists( 'post', $_GET ) )
		exit;
	$post = filter_var( $_GET['post'], FILTER_VALIDATE_INT );
	if ( $post === FALSE )
		exit;
	if ( !current_user_can( 'edit_post', $post ) )
		exit;
	if ( !array_key_exists( 'dates', $_POST ) ) {
		delete_post_meta( $post, 'postcal' );
		exit;
	}
	$dates = $_POST['dates'];
	if ( !is_array( $dates ) )
		exit;
	$meta = get_post_meta( $post, 'postcal', FALSE );
	foreach ( $meta as $date )
		if ( !in_array( $date, $dates ) )
			delete_post_meta( $post, 'postcal', $date );
	foreach ( $dates as $date ) {
		$date = DateTime::createFromFormat( 'Y-m-d', $date );
		if ( $date === FALSE )
			continue;
		$date = $date->format( 'Y-m-d' );
		if ( !in_array( $date, $meta ) )
			add_post_meta( $post, 'postcal', $date, FALSE );
	}
} );
