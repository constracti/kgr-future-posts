jQuery( function() {

jQuery( '.postcal-metabox-delete' ).click( function() {
	jQuery( this ).parent( '.postcal-metabox-field' ).remove();
	return false;
} );

jQuery( '#postcal-metabox-add' ).click( function() {
	jQuery( '#postcal-metabox-sample' ).children( '.postcal-metabox-field' ).clone( true ).appendTo( '#postcal-metabox-container' );
	return false;
} );

jQuery( '#postcal-metabox-save' ).click( function() {
	var spinner = jQuery( '#postcal-metabox-spinner' ).addClass( 'is-active' );
	var url = jQuery( this ).prop( 'href' );
	var dates = [];
	jQuery( '#postcal-metabox-container' ).children( '.postcal-metabox-field' ).each( function() {
		var value = jQuery( this ).children( '.postcal-metabox-input' ).val();
		if ( value !== '' )
			dates.push( value );
		else
			jQuery( this ).remove();
	} );
	var data = {
		dates: dates,
	};
	jQuery.post( url, data, function( data ) {
		spinner.removeClass( 'is-active' );
	} );
	return false;
} );

} );
