<?php

if ( !defined( 'ABSPATH' ) )
	exit;

class postcal_widget extends WP_Widget {

	const INSTANCE = array(
		'title' => NULL,
	);

	public function __construct() {
		$widget_ops = array(
			'classname' => 'postcal_widget',
			'description' => __( 'Displays a calendar widget. When a day is selected, linked posts appear below.', 'postcal' ),
		);
		parent::__construct( 'postcal_widget', __( 'Post Calendar', 'postcal' ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		if ( is_null( $instance ) || !is_array( $instance ) || empty( $instance ) )
			$instance = self::INSTANCE;
		echo $args['before_widget'] . "\n";
		if ( !is_null( $instance['title'] ) )
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'] . "\n";
		echo sprintf( '<div class="postcal-widget" data-action="%s">', admin_url( 'admin-ajax.php?action=postcal-widget' ) ) . "\n";
		postcal_widget_content();
		echo '</div>' . "\n";
		echo $args['after_widget'] . "\n";
	}

	public function form( $instance ) {
		if ( is_null( $instance ) || !is_array( $instance ) || empty( $instance ) )
			$instance = self::INSTANCE;
?>
<p>
	<label>
		<span><?= esc_html__( 'Title', 'postcal' ) ?></span>
		<input class="widefat" id="<?= esc_attr( $this->get_field_id( 'title' ) ) ?>" name="<?= esc_attr( $this->get_field_name( 'title' ) ) ?>" type="text" value="<?= esc_attr( $instance['title'] ?? '' ) ?>" autocomplete="off" />
	</label>
</p>
<?php
	}

	public function update( $new_instance, $old_instance ) {
		if ( array_key_exists( 'title', $new_instance ) ) {
			$title = $new_instance['title'];
			if ( !is_null( $title ) && is_string( $title ) ) {
				$title = trim( preg_replace( '/\s+/', ' ', $title ) );
				if ( $title === '' ) {
					$title = NULL;
				}
			} else {
				$title = NULL;
			}
		} else {
			$title = NULL;
		}
		return array(
			'title' => $title,
		);
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'postcal_widget' );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'postcal_widget_style', plugins_url( 'widget.css', __FILE__ ) );
	wp_enqueue_script( 'postcal_widget_script', plugins_url( 'widget.js', __FILE__ ), array( 'jquery' ) );
}, 12 );

function postcal_month( int $month ): string {
	switch ( $month ) {
		case  1: return __( 'January'  , 'postcal' );
		case  2: return __( 'February' , 'postcal' );
		case  3: return __( 'March'    , 'postcal' );
		case  4: return __( 'April'    , 'postcal' );
		case  5: return __( 'May'      , 'postcal' );
		case  6: return __( 'June'     , 'postcal' );
		case  7: return __( 'July'     , 'postcal' );
		case  8: return __( 'August'   , 'postcal' );
		case  9: return __( 'September', 'postcal' );
		case 10: return __( 'October'  , 'postcal' );
		case 11: return __( 'November' , 'postcal' );
		case 12: return __( 'December' , 'postcal' );
		default: exit( 'postcal_month: internal error' ); // TODO i18n
	}
}

function postcal_weekdays(): array {
	return array(
		__( 'Sun', 'postcal' ),
		__( 'Mon', 'postcal' ),
		__( 'Tue', 'postcal' ),
		__( 'Wed', 'postcal' ),
		__( 'Thu', 'postcal' ),
		__( 'Fri', 'postcal' ),
		__( 'Sat', 'postcal' ),
	);
}

function postcal_widget_content( string $curr = '' ) {
	$dt = new DateTime();
	$dt->setTimestamp( current_time( 'timestamp' ) );
	$today = $dt->format( 'Y-m-d' );
	$curr = DateTime::createFromFormat( 'Y-m-d', $curr );
	if ( $curr !== FALSE )
		$dt = $curr;
	$y = intval( $dt->format( 'Y' ) );
	$m = intval( $dt->format( 'n' ) );
	$dt->setDate( $y, $m, 1 );
	$one_day = new DateInterval( 'P1D' );
	$one_month = new DateInterval( 'P1M' );
	$prev = new DateTime();
	$prev->setTimestamp( $dt->getTimestamp() )->sub( $one_month );
	$prev = sprintf( 'admin-ajax.php?action=postcal-widget&year=%s&month=%s', $prev->format( 'Y' ), $prev->format( 'm' ) );
	$curr = new DateTime();
	$curr->setTimestamp( $dt->getTimestamp() );
	$curr = sprintf( 'admin-ajax.php?action=postcal-widget&year=%s&month=%s', $curr->format( 'Y' ), $curr->format( 'm' ) );
	$next = new DateTime();
	$next->setTimestamp( $dt->getTimestamp() )->add( $one_month );
	$next = sprintf( 'admin-ajax.php?action=postcal-widget&year=%s&month=%s', $next->format( 'Y' ), $next->format( 'm' ) );
?>
<p class="postcal-head">
	<a class="postcal-prev" href="<?= admin_url( $prev ) ?>">&laquo;</a>
	<a class="postcal-curr" href="<?= admin_url( $curr ) ?>">
		<span><?= esc_html( postcal_month( $m ) ) ?></span>
		<span><?= esc_html( $y ) ?></span>
	</a>
	<a class="postcal-next" href="<?= admin_url( $next ) ?>">&raquo;</a>
</p>
<table>
	<thead>
		<tr>
<?php
	foreach ( postcal_weekdays() as $weekday )
		echo sprintf( '<th>%s</th>', esc_html( $weekday ) ) . "\n";
?>
		</tr>
	</thead>
	<tbody>
<?php
	$dates = array();
	$w = intval( $dt->format( 'w' ) );
	if ( $w !== 0 ) {
		echo '<tr>' . "\n";
		for ( $cnt = 0; $cnt < $w; $cnt++ )
			echo '<td></td>' . "\n";
	}
	while ( intval( $dt->format( 'n' ) ) === $m ) {
		$format = $dt->format( 'Y-m-d' );
		$class = array();
		if ( $format === $today )
			$class[] = 'postcal-today';
		if ( $w === 0 )
			echo '<tr>' . "\n";
		$posts = get_posts( array(
			'meta_compare' => '=',
			'meta_key' => 'postcal',
			'meta_value' => $dt->format( 'Y-m-d' ),
			'order' => 'ASC',
			'orderby' => 'title',
			'post_type' => 'post',
		) );
		if ( !empty( $posts ) ) {
			$dates[ $format ] = $posts;
			$class[] = 'postcal-nonempty';
		}
		echo sprintf( '<td class="%s" data-date="%s">%d</td>', implode( ' ', $class ), $format, intval( $dt->format( 'j' ) ) ) . "\n";
		$dt->add( $one_day );
		$w++;
		if ( $w === 7 ) {
			$w = 0;
			echo '</tr>' . "\n";
		}
	}
	if ( $w !== 0 ) {
		for ( $cnt = $w; $cnt < 7; $cnt++ )
			echo '<td></td>' . "\n";
		echo '</tr>' . "\n";
	}
?>
	</tbody>
</table>
<div class="postcal-foot">
<?php
	foreach ( $dates as $date => $posts )
		foreach ( $posts as $post )
			echo sprintf( '<p class="postcal-post" data-date="%s"><a href="%s">%s</a></p>', $date, $post->guid, esc_html( $post->post_title ) ) . "\n";
?>
</div>
<?php
}

function postcal_widget_callback() {
	$curr = sprintf( '%s-%s-01', $_GET['year'] ?? '', $_GET['month'] ?? '' );
	postcal_widget_content( $curr );
	exit;
}
add_action( 'wp_ajax_postcal-widget', 'postcal_widget_callback' );
add_action( 'wp_ajax_nopriv_postcal-widget', 'postcal_widget_callback' );
