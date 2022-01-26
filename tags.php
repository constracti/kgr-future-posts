<?php

if ( !defined( 'ABSPATH' ) )
	exit;

final class KGR_Future_Posts_Tags {

	private static $tags = NULL;

	private static function load(): void {
		self::$tags = get_option( 'kgr_future_posts_tags', [] );
	}

	private static function save(): void {
		if ( !empty( self::$tags ) )
			update_option( 'kgr_future_posts_tags', self::$tags );
		else
			delete_option( 'kgr_future_posts_tags' );
	}

	// functions

	public static function select(): array {
		if ( is_null( self::$tags ) )
			self::load();
		return self::$tags;
	}

	public static function insert(): void {
		self::select();
		$tag = KGR_Future_Posts_Request::post( 'tag' );
		$key = array_search( $tag->term_id, self::$tags, TRUE );
		if ( $key !== FALSE )
			exit( 'tag' );
		self::$tags[] = $tag->term_id;
		self::save();
	}

	public static function delete(): void {
		self::select();
		$tag = KGR_Future_Posts_Request::get( 'tag' );
		$key = array_search( $tag->term_id, self::$tags, TRUE );
		if ( $key === FALSE )
			exit( 'tag' );
		unset( self::$tags[$key] );
		self::save();
	}

	// settings

	public static function settings_echo(): void {
		echo self::settings();
	}

	public static function settings(): string {
		$html = '<div class="kgr-future-posts-home kgr-future-posts-root kgr-future-posts-flex-col">' . "\n";
		$html .= self::settings_refresh();
		$html .= '<hr class="kgr-future-posts-leaf" />' . "\n";
		$html .= self::settings_insert();
		$html .= self::settings_table();
		$html .= self::settings_form();
		$html .= '</div>' . "\n";
		return $html;
	}

	private static function settings_refresh(): string {
		$html = '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a%s>%s</a>', KGR_Future_Posts::atts( [
			'href' => add_query_arg( [
				'action' => 'kgr_future_posts_tags_settings_refresh',
				'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_tags_settings_refresh' ),
			], admin_url( 'admin-ajax.php' ) ),
			'class' => 'kgr-future-posts-link button kgr-future-posts-leaf',
		] ), esc_html__( 'Refresh', 'kgr-future-posts' ) ) . "\n";
		$html .= '<span class="kgr-future-posts-spinner spinner kgr-future-posts-leaf" data-kgr-future-posts-spinner-toggle="is-active"></span>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private static function settings_insert(): string {
		$html = '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-end kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a%s>%s</a>', KGR_Future_Posts::atts( [
			'href' => add_query_arg( [
				'action' => 'kgr_future_posts_tags_settings_insert',
				'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_tags_settings_insert' ),
			], admin_url( 'admin-ajax.php' ) ),
			'class' => 'kgr-future-posts-insert button kgr-future-posts-leaf',
			'data-kgr-future-posts-form' => '.kgr-future-posts-form-tag',
		] ), esc_html__( 'Insert', 'kgr-future-posts' ) ) . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private static function settings_table(): string {
		$html = '<div class="kgr-future-posts-leaf">' . "\n";
		$html .= '<table class="fixed widefat striped">' . "\n";
		$html .= '<thead>' . "\n";
		$html .= '<tr>' . "\n";
		$html .= sprintf( '<th class="column-primary has-row-actions">%s</th>', esc_html__( 'Tag', 'kgr-future-posts' ) ) . "\n";
		$html .= sprintf( '<th>%s</th>', esc_html__( 'Posts', 'kgr-future-posts' ) ) . "\n";
		$html .= '</tr>' . "\n";
		$html .= '</thead>' . "\n";
		$html .= '<tbody>' . "\n";
		foreach ( self::select() as $tag )
			$html .= self::settings_table_body_row( $tag );
		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private static function settings_table_body_row( int $tag ): string {
		$tag = get_tag( $tag );
		if ( is_null( $tag ) )
			return '';
		$actions = [];
		$actions[] = sprintf( '<span><a%s>%s</a></span>', KGR_Future_Posts::atts( [
			'href' => get_term_link( $tag ),
		] ), esc_html__( 'View', 'kgr-future-posts' ) ) . "\n";
		$actions[] = sprintf( '<span class="delete"><a%s>%s</a></span>', KGR_Future_Posts::atts( [
			'href' => add_query_arg( [
				'action' => 'kgr_future_posts_tags_settings_delete',
				'tag' => $tag->term_id,
				'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_tags_settings_delete' ),
			], admin_url( 'admin-ajax.php' ) ),
			'class' => 'kgr-future-posts-link',
			'data-kgr-future-posts-confirm' => esc_attr( sprintf( __( 'Delete tag %s?', 'kgr-future-posts' ), $tag->name ) ),
		] ), esc_html__( 'Delete', 'kgr-future-posts' ) ) . "\n";
		$html = '<tr>' . "\n";
		$html .= '<td class="column-primary has-row-actions">' . "\n";
		$html .= sprintf( '<strong>%s</strong>', esc_html( $tag->name ) ) . "\n";
		$html .= '<div class="row-actions">' . implode( ' | ', $actions ) . '</div>' . "\n";
		$html .= '</td>' . "\n";
		$html .= sprintf( '<td>%s</td>', esc_html( $tag->count ) ) . "\n";
		$html .= '</tr>' . "\n";
		return $html;
	}

	private static function settings_form(): string {
		$tags = get_terms( [
			'taxonomy' => 'post_tag',
			'hide_empty' => FALSE,
			'exclude' => self::select(),
		] );
		$html = '<div class="kgr-future-posts-form kgr-future-posts-form-tag kgr-future-posts-leaf kgr-future-posts-root kgr-future-posts-root-border kgr-future-posts-flex-col" style="display: none;">' . "\n";
		$html .= '<div class="kgr-future-posts-leaf">' . "\n";
		$html .= '<table class="form-table" role="presentation">' . "\n";
		$html .= '<tbody>' . "\n";
		$html .= '<tr>' . "\n";
		$html .= sprintf( '<th scope="row"><label for="kgr-future-posts-form-tag-id">%s</label></th>', esc_html__( 'Tag', 'kgr-future-posts' ) ) . "\n";
		$html .= '<td>' . "\n";
		$html .= '<select class="kgr-future-posts-field" data-kgr-future-posts-name="tag" id="kgr-future-posts-form-tag-id">' . "\n";
		$html .= '<option value=""></option>' . "\n";
		foreach ( $tags as $tag )
			$html .= sprintf( '<option value="%d">%s</option>', esc_attr( $tag->term_id ), esc_html( $tag->name ) ) . "\n";
		$html .= '</select>' . "\n";
		$html .= '</td>' . "\n";
		$html .= '</tr>' . "\n";
		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a href="" class="kgr-future-posts-link kgr-future-posts-submit button button-primary kgr-future-posts-leaf">%s</a>', esc_html__( 'Submit', 'kgr-future-posts' ) ) . "\n";
		$html .= sprintf( '<a href="" class="kgr-future-posts-cancel button kgr-future-posts-leaf">%s</a>', esc_html__( 'Cancel', 'kgr-future-posts' ) ) . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}
}

add_filter( 'kgr_future_posts_tab_list', function( array $tabs ): array {
	$tabs['settings'] = esc_html__( 'Settings', 'kgr-future-posts' );
	return $tabs;
} );

add_action( 'kgr_future_posts_tab_html_settings', [ 'KGR_Future_Posts_Tags', 'settings_echo' ] );

// ajax

add_action( 'wp_ajax_' . 'kgr_future_posts_tags_settings_refresh', function(): void {
	if ( !current_user_can( 'manage_options' ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_tags_settings_refresh' );
	KGR_Future_Posts::success( KGR_Future_Posts_Tags::settings() );
} );

add_action( 'wp_ajax_' . 'kgr_future_posts_tags_settings_insert', function(): void {
	if ( !current_user_can( 'manage_options' ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_tags_settings_insert' );
	KGR_Future_Posts_Tags::insert();
	KGR_Future_Posts::success( KGR_Future_Posts_Tags::settings() );
} );

add_action( 'wp_ajax_' . 'kgr_future_posts_tags_settings_delete', function(): void {
	if ( !current_user_can( 'manage_options' ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_tags_settings_delete' );
	KGR_Future_Posts_Tags::delete();
	KGR_Future_Posts::success( KGR_Future_Posts_Tags::settings() );
} );
