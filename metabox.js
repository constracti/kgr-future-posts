jQuery( function() {

jQuery( '#postcal-check' ).change( function() {
	var check = jQuery( this );
	if ( check.prop( 'checked' ) )
		jQuery( '#postcal-container' ).show();
	else
		jQuery( '#postcal-container' ).hide();
} ).change();

jQuery( '#postcal-save' ).click( function() {
	var link = jQuery( this );
	var check = jQuery( '#postcal-check' );
	var input = jQuery( '#postcal-date' );
	var spinner = jQuery( '#postcal-spinner' ).addClass( 'is-active' );
	var data = {};
	if ( check.prop( 'checked' ) )
		data.check = check.val();
	data.date = input.val();
	jQuery.post( link.prop( 'href' ), data, function( data ) {
		if ( typeof( data ) === 'object' ) {
			check.prop( 'checked', data.check ).change();
			input.val( data.date );
		} else {
			alert( data );
		}
		spinner.removeClass( 'is-active' );
	} );
	return false;
} );

} );
