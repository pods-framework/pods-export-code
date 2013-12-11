(function ( $ ) {
	"use strict";

	$( function () {

		// Event to run the export
		$( '.submit #export' ).click( function ( e ) {
			e.preventDefault();

			// Get an array of selected Pod names
			var pod_names = [];
			$( '.pods-field.pods-boolean input:checked' ).each( function () {
				pod_names.push( $( this ).attr( 'name' ) );
			} );

			// AJAX call
			var data = {
				action : 'pods_export_code',
				pod_names : pod_names
			};
			$.post( ajaxurl, data, function ( response ) {
				$('#feedback').html( response );
			} );

		} );

	} );

}( jQuery ));