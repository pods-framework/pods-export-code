(function ( $ ) {
	"use strict";

	$( function () {

		var ajax_action = 'pods_export_code';

		var $export_button = $( '#export' );
		var $toggle_all_button = $( '#toggle-all' );

		var $checkboxes = $( '.pods-field.pods-boolean input' );
		var $output = $( '#feedback' );

		// Event to run the export
		$export_button.click( function ( e ) {
			e.preventDefault();

			// Get an array of selected Pod names
			var pod_names = [];
			$checkboxes.filter( ':checked' ).each( function () {
				pod_names.push( $( this ).attr( 'name' ) );
			} );

			// AJAX call
			var data = {
				action : ajax_action,
				pod_names : pod_names
			};
			$.post( ajaxurl, data, function ( response ) {
				$output.html( response );
			} );

		} );

		// Handle toggle click
		$toggle_all_button.click( function ( e ) {
			e.preventDefault();

			// Any unchecked boxes?  Check them all.
			if ( $checkboxes.not( ':checked' ).length > 0 ) {
				$checkboxes.prop( 'checked', true );
			}
			// All were checked, uncheck them all.
			else {
				$checkboxes.prop( 'checked', false );
			}
		} );

	} );

}( jQuery ));