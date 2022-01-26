<?php

/*
 * Plugin Name: KGR Future Posts
 * Plugin URI: https://github.com/constracti/kgr-future-posts
 * Description: Order query of specific tag by a date post meta field.
 * Version: 2.0
 * Requires PHP: 8.0
 * Author: constracti
 * Author URI: https://github.com/constracti
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: kgr-future-posts
 */

if ( !defined( 'ABSPATH' ) )
	exit;

final class KGR_Future_Posts {

	public static function dir( string $dir ): string {
		return plugin_dir_path( __FILE__ ) . $dir;
	}

	public static function url( string $url ): string {
		return plugin_dir_url( __FILE__ ) . $url;
	}

	public static function version(): string {
		$plugin_data = get_plugin_data( __FILE__ );
		return $plugin_data['Version'];
	}

	public static function success( string $html ): void {
		header( 'content-type: application/json' );
		exit( json_encode( [
			'html' => $html,
		] ) );
	}

	public static function atts( array $atts ): string {
		$return = '';
		foreach ( $atts as $prop => $val ) {
			$return .= sprintf( ' %s="%s"', $prop, $val );
		}
		return $return;
	}

	private static function nonce_action( string $action, string ...$args ): string {
		foreach ( $args as $arg )
			$action .= '_' . $arg;
		return $action;
	}

	public static function nonce_create( string $action, string ...$args ): string {
		return wp_create_nonce( self::nonce_action( $action, ...$args ) );
	}

	public static function nonce_verify( string $action, string ...$args ): void {
		$nonce = KGR_Future_Posts_Request::get( 'str', 'nonce' );
		if ( !wp_verify_nonce( $nonce, self::nonce_action( $action, ...$args ) ) )
			exit( 'nonce' );
	}
}

// require php files

$files = glob( KGR_Future_Posts::dir( '*.php' ) );
foreach ( $files as $file ) {
	if ( $file !== __FILE__ )
		require_once( $file );
}

// load plugin translations

add_action( 'init', function(): void {
	load_plugin_textdomain( 'kgr-future-posts', FALSE, basename( __DIR__ ) . DIRECTORY_SEPARATOR . 'languages' );
} );

// settings page

add_action( 'admin_menu', function(): void {
	$page_title = esc_html__( 'KGR Future Posts', 'kgr-future-posts' );
	$menu_title = esc_html__( 'KGR Future Posts', 'kgr-future-posts' );
	$capability = 'manage_options';
	$menu_slug = 'kgr-future-posts';
	add_options_page( $page_title, $menu_title, $capability, $menu_slug, function() {
		$tab_curr = KGR_Future_Posts_Request::get( 'word', 'tab', TRUE ) ?? 'settings';
?>
<div class="wrap">
	<h1><?= esc_html__( 'KGR Future Posts', 'kgr-future-posts' ) ?></h1>
	<h2 class="nav-tab-wrapper">
<?php
		foreach ( apply_filters( 'kgr_future_posts_tab_list', [] ) as $tab_slug => $tab_name ) {
			$class = [];
			$class[] = 'nav-tab';
			if ( $tab_slug === $tab_curr )
				$class[] = 'nav-tab-active';
				$class = implode( ' ', $class );
				$href = menu_page_url( 'kgr-future-posts', FALSE );
				if ( $tab_slug !== 'settings' )
					$href = add_query_arg( 'tab', $tab_slug, $href );
?>
		<a class="<?= $class ?>" href="<?= $href ?>"><?= esc_html( $tab_name ) ?></a>
<?php
		}
?>
	</h2>
<?php
	do_action( 'kgr_future_posts_tab_html_' . $tab_curr );
?>
</div>
<?php
	} );
} );

add_filter( 'plugin_action_links', function( array $actions, string $plugin_file ): array {
	if ( $plugin_file !== basename( __DIR__ ) . '/' . basename( __FILE__ ) )
		return $actions;
	$actions['settings'] = sprintf( '<a href="%s">%s</a>', menu_page_url( 'kgr-future-posts', FALSE ), esc_html__( 'Settings', 'kgr-future-posts' ) );
	return $actions;
}, 10, 2 );

add_action( 'admin_enqueue_scripts', function( string $hook_suffix ): void {
	if ( $hook_suffix !== 'settings_page_kgr-future-posts' )
		return;
	wp_enqueue_style( 'kgr-future-posts-flex', KGR_Future_Posts::url( 'flex.css' ), [], KGR_Future_Posts::version() );
	wp_enqueue_style( 'kgr-future-posts-tree', KGR_Future_Posts::url( 'tree.css' ), [], KGR_Future_Posts::version() );
	wp_enqueue_script( 'kgr-future-posts-script', KGR_Future_Posts::url( 'script.js' ), [ 'jquery' ], KGR_Future_Posts::version() );
} );

add_action( 'pre_get_posts', function( WP_Query $query ): void {
	if ( is_admin() )
		return;
	if ( !$query->is_archive() )
		return;
	$tags = KGR_Future_Posts_Tags::select();
	if ( empty( $tags ) )
		return;
	if ( !$query->is_tag( $tags ) )
		return;
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'ASC' );
	$query->set( 'meta_key', 'kgr_future_posts_date' );
	$query->set( 'meta_compare', '>=' );
	$query->set( 'meta_value', current_time( 'Y-m-d' ) );
} );
