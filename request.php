<?php

if ( !defined( 'ABSPATH' ) )
	exit;

function postcal_request_bool( string $key ): bool {
	if ( !array_key_exists( $key, $_REQUEST ) )
		return FALSE;
	$var = $_REQUEST[ $key ];
	if ( is_null( $var ) || $var === '' )
		return FALSE;
	return TRUE;
}

function postcal_request_var( string $key, bool $nullable = FALSE ) {
	if ( postcal_request_bool( $key ) )
		return $_REQUEST[ $key ];
	if ( $nullable )
		return NULL;
	exit( $key . ' not defined' );
}

function postcal_request_int( string $key, bool $nullable = FALSE ) {
	$var = postcal_request_var( $key, $nullable );
	if ( is_null( $var ) )
		return NULL;
	$var = filter_var( $var, FILTER_VALIDATE_INT );
	if ( $var !== FALSE )
		return $var;
	exit( $key . ' not valid' );
}

function postcal_request_date( string $key, bool $nullable = FALSE ) {
	$var = postcal_request_var( $key, $nullable );
	if ( is_null( $var ) )
		return NULL;
	$dt = DateTime::createFromFormat( 'Y-m-d', $var );
	if ( $dt !== FALSE )
		return $var;
	exit( $key . ' not valid' );
}
