<?php

if ( !defined( 'ABSPATH' ) )
	exit;

final class KGR_Future_Posts_Dates {

	private $post;
	private $dates;

	public function __construct( WP_Post $post ) {
		$this->post = $post;
		$this->dates = NULL;
	}

	private function db_select(): array {
		return get_post_meta( $this->post->ID, 'kgr_future_posts_date', FALSE );
	}

	private function db_insert( string $date ): void {
		add_post_meta( $this->post->ID, 'kgr_future_posts_date', $date );
	}

	private function db_delete( string $date ): void {
		delete_post_meta( $this->post->ID, 'kgr_future_posts_date', $date );
	}

	// functions

	public function select(): array {
		if ( is_null( $this->dates ) )
			$this->dates = $this->db_select();
		return $this->dates;
	}

	public function insert(): void {
		$this->select();
		$date = KGR_Future_Posts_Request::post( 'date' );
		$key = array_search( $date, $this->dates, TRUE );
		if ( $key !== FALSE )
			exit( 'date' );
		$this->dates[] = $date;
		$this->db_insert( $date );
	}

	public function delete(): void {
		$this->select();
		$date = KGR_Future_Posts_Request::get( 'date' );
		$key = array_search( $date, $this->dates, TRUE );
		if ( $key === FALSE )
			exit( 'date' );
		unset( $this->dates[$key] );
		$this->db_delete( $date );
	}

	// metabox

	public function metabox_echo(): void {
		echo $this->metabox();
	}

	public function metabox(): string {
		$html = '<div class="kgr-future-posts-home kgr-future-posts-root kgr-future-posts-flex-col" style="margin: -6px -12px -12px -12px;">' . "\n";
		$html .= $this->metabox_table();
		$html .= $this->metabox_insert();
		$html .= $this->metabox_form();
		$html .= '<hr class="kgr-future-posts-leaf" />' . "\n";
		$html .= $this->metabox_refresh();
		$html .= '</div>' . "\n";
		return $html;
	}

	private function metabox_refresh(): string {
		$html = '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a%s>%s</a>', KGR_Future_Posts::atts( [
			'href' => add_query_arg( [
				'action' => 'kgr_future_posts_dates_metabox_refresh',
				'post' => $this->post->ID,
				'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_dates_metabox_refresh', $this->post->ID ),
			], admin_url( 'admin-ajax.php' ) ),
			'class' => 'kgr-future-posts-link button kgr-future-posts-leaf',
		] ), esc_html__( 'Refresh', 'kgr-future-posts' ) ) . "\n";
		$html .= '<span class="kgr-future-posts-spinner spinner kgr-future-posts-leaf" data-kgr-future-posts-spinner-toggle="is-active"></span>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private function metabox_insert(): string {
		$html = '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a%s>%s</a>', KGR_Future_Posts::atts( [
			'href' => add_query_arg( [
				'action' => 'kgr_future_posts_dates_metabox_insert',
				'post' => $this->post->ID,
				'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_dates_metabox_insert', $this->post->ID ),
			], admin_url( 'admin-ajax.php' ) ),
			'class' => 'kgr-future-posts-insert button kgr-future-posts-leaf',
			'data-kgr-future-posts-form' => '.kgr-future-posts-form-date',
		] ), esc_html__( 'Insert', 'kgr-future-posts' ) ) . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private function metabox_table(): string {
		$dates = $this->select();
		sort( $dates, SORT_STRING );
		$html = '<div class="kgr-future-posts-leaf">' . "\n";
		$html .= '<table class="fixed widefat striped">' . "\n";
		$html .= '<thead>' . "\n";
		$html .= '<tr>' . "\n";
		$html .= sprintf( '<th class="column-primary">%s</th>', esc_html__( 'Dates', 'kgr-future-posts' ) ) . "\n";
		$html .= '</tr>' . "\n";
		$html .= '</thead>' . "\n";
		$html .= '<tbody>' . "\n";
		foreach ( $dates as $date ) {
			$dt = DateTime::createFromFormat( 'Y-m-d', $date, wp_timezone() );
			$actions = [];
			$actions[] = sprintf( '<span class="delete"><a%s>%s</a></span>', KGR_Future_Posts::atts( [
				'href' => add_query_arg( [
					'action' => 'kgr_future_posts_dates_metabox_delete',
					'post' => $this->post->ID,
					'date' => $date,
					'nonce' => KGR_Future_Posts::nonce_create( 'kgr_future_posts_dates_metabox_delete', $this->post->ID ),
				], admin_url( 'admin-ajax.php' ) ),
				'class' => 'kgr-future-posts-link',
				'data-kgr-future-posts-confirm' => esc_attr( sprintf( __( 'Delete date %s?', 'kgr-future-posts' ), $date ) ),
			] ), esc_html__( 'Delete', 'kgr-future-posts' ) ) . "\n";
			$html .= '<tr>' . "\n";
			$html .= '<td class="column-primary has-row-actions">' . "\n";
			$html .= sprintf( '<strong>%s</strong>', esc_html( wp_date( 'l, j F Y', $dt->getTimestamp() ) ) ) . "\n";
			$html .= '<div class="row-actions">' . implode( ' | ', $actions ) . '</div>' . "\n";
			$html .= '</td>' . "\n";
			$html .= '</tr>' . "\n";
		}
		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}

	private function metabox_form(): string {
		$html = '<div class="kgr-future-posts-form kgr-future-posts-form-date kgr-future-posts-flex-col" style="display: none;">' . "\n";
		$html .= '<label class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<span class="kgr-future-posts-flex-noshrink kgr-future-posts-leaf">%s</span>', esc_html__( 'Date', 'kgr-future-posts' ) ) . "\n";
		$html .= '<input type="date" class="kgr-future-posts-field kgr-future-posts-leaf" data-kgr-future-posts-name="date" />' . "\n";
		$html .= '</label>' . "\n";
		$html .= '<div class="kgr-future-posts-flex-row kgr-future-posts-flex-justify-between kgr-future-posts-flex-align-center">' . "\n";
		$html .= sprintf( '<a href="" class="kgr-future-posts-link kgr-future-posts-submit button button-primary kgr-future-posts-leaf">%s</a>', esc_html__( 'Submit', 'kgr-future-posts' ) ) . "\n";
		$html .= sprintf( '<a href="" class="kgr-future-posts-cancel button kgr-future-posts-leaf">%s</a>', esc_html__( 'Cancel', 'kgr-future-posts' ) ) . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		return $html;
	}
}

add_action( 'add_meta_boxes_post', function( WP_Post $post ): void {
	if ( !current_user_can( 'edit_post', $post->ID ) )
		return;
	$dates = new KGR_Future_Posts_Dates( $post );
	add_meta_box( 'kgr-future-posts', __( 'KGR Future Posts', 'kgr-future-posts' ), [ $dates, 'metabox_echo' ], NULL, 'side' );
} );

add_action( 'admin_enqueue_scripts', function( string $hook_suffix ): void {
	if ( !in_array( $hook_suffix, [ 'post.php', 'post-new.php', ], TRUE ) )
		return;
	wp_enqueue_style( 'kgr-future-posts-flex', KGR_Future_Posts::url( 'flex.css' ), [], KGR_Future_Posts::version() );
	wp_enqueue_style( 'kgr-future-posts-tree', KGR_Future_Posts::url( 'tree.css' ), [], KGR_Future_Posts::version() );
	wp_enqueue_script( 'kgr-future-posts-script', KGR_Future_Posts::url( 'script.js' ), [ 'jquery' ], KGR_Future_Posts::version() );
} );

// ajax

add_action( 'wp_ajax_' . 'kgr_future_posts_dates_metabox_refresh', function(): void {
	$post = KGR_Future_Posts_Request::get( 'post' );
	if ( !current_user_can( 'edit_post', $post->ID ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_dates_metabox_refresh', $post->ID );
	$dates = new KGR_Future_Posts_Dates( $post );
	KGR_Future_Posts::success( $dates->metabox() );
} );

add_action( 'wp_ajax_' . 'kgr_future_posts_dates_metabox_insert', function(): void {
	$post = KGR_Future_Posts_Request::get( 'post' );
	if ( !current_user_can( 'edit_post', $post->ID ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_dates_metabox_insert', $post->ID );
	$dates = new KGR_Future_Posts_Dates( $post );
	$dates->insert();
	KGR_Future_Posts::success( $dates->metabox() );
} );

add_action( 'wp_ajax_' . 'kgr_future_posts_dates_metabox_delete', function(): void {
	$post = KGR_Future_Posts_Request::get( 'post' );
	if ( !current_user_can( 'edit_post', $post->ID ) )
		exit( 'role' );
	KGR_Future_Posts::nonce_verify( 'kgr_future_posts_dates_metabox_delete', $post->ID );
	$dates = new KGR_Future_Posts_Dates( $post );
	$dates->delete();
	KGR_Future_Posts::success( $dates->metabox() );
} );
