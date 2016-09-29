jQuery( function() {

jQuery( '.postcal-container' ).each( function() {
	var div = jQuery( this );
	jQuery.get( div.data( 'action' ), function( data ) {
		div.html( data );
		init( div );
	} );
} );


function init( obj ) {
	obj.find( '.postcal-navigate' ).click( function() {
		var link = jQuery( this );
		var div = link.parents( '.postcal-container' );
		jQuery.get( link.prop( 'href' ), function( data ) {
			div.html( data );
			init( div );
		} );
		return false;
	} );
	obj.find( '.postcal-nonempty' ).click( function() {
		var td = jQuery( this );
		td.parents( '.postcal-container' ).
		find( '.postcal-post' ).hide().
		filter( '[data-date="' + td.data( 'date' ) + '"]' ).show();
	} );
}

} );
