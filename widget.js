jQuery( function() {

jQuery( '.postcal-widget' ).each( function() {
	var div = jQuery( this );
	init( div );
} );


function init( obj ) {
	obj.find( '.postcal-head' ).find( 'a' ).click( function() {
		var link = jQuery( this );
		var div = link.parents( '.postcal-widget' );
		jQuery.get( link.prop( 'href' ), function( data ) {
			div.html( data );
			init( div );
		} );
		return false;
	} );
	obj.find( '.postcal-nonempty' ).click( function() {
		var td = jQuery( this );
		var check = !td.hasClass( 'postcal-active' );
		var div = td.parents( '.postcal-widget' );
		var posts = div.find( '.postcal-post' ).hide();
		div.find( 'td' ).removeClass( 'postcal-active' );
		if ( check ) {
			td.addClass( 'postcal-active' );
			posts.filter( '[data-date="' + td.data( 'date' ) + '"]' ).show();
		}
	} );
}

} );
