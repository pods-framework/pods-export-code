/**
 * Things to do on document ready
 */
jQuery( function ( $ ) {

	/*global pods_export_pods, pods_export_templates, pods_export_pages */
	$( '#export-section-pods' ).export_to_code_section( pods_export_pods, null );
	$( '#export-section-templates' ).export_to_code_section( pods_export_templates, null );
	$( '#export-section-pages' ).export_to_code_section( pods_export_pages, null );

} );

/**
 *
 */
( function ( $ ) {

	/**
	 * @param items Array of exportable object item names
	 * @param options options to be merged with the defaults
	 * @returns {*}
	 */
	$.fn.export_to_code_section = function ( items, options ) {

		// Merge specified options with defaults
		options = $.extend( {
			id_prefix     : this.attr( 'id' ),
			ajax_action   : 'pods_export_code',
			ajax_item_type: this.data( 'item-type' ),
			form_class    : 'pods-submittable'
		}, options );

		var components = {
			$form      : $( '<form>', {
				action : '',
				method : 'post',
				'class': options.form_class
			} ),
			$toggle_all: $( '<a>', {
				href   : '#',
				'class': 'toggle-all button', // Todo
				click  : toggle_all_click,
				text   : 'Toggle all on / off'
			} ),
			$item_list : $( '<ul>' ),
			$submit    : $( '<a>', {
				'class': 'button button-primary pods-export-submit', // Todo
				id     : options.id_prefix + '-submit',
				href   : '#',
				click  : submit_click,
				text   : 'Export'
			} ),
			$output    : $( '<div>', { 'class': 'output-wrapper' } )
				.append( $( '<div>', { text: 'Output:' } ) )
				.append( $( '<textarea>', {
					id: options.id_prefix + '-result-output'
				} )
			)
		};

		// 'this' context will be a jQuery object to which we were applied
		return this.each( function () {

			// Build the checkbox list
			var $new_item;
			var list_class;

			// 'this' context will be an exportable item's name
			$.each( items, function () {

				list_class = ( 'pods-zebra-even' == list_class ) ? 'pods-zebra-odd' : 'pods-zebra-even';
				$new_item = $( '<li>', { 'class': list_class } );

				$new_item.append( $( '<input>', {
					name   : this,
					id     : options.id_prefix + this,
					type   : 'checkbox',
					checked: true
				} ) );

				$new_item.append( $( '<label>', {
					'for': options.id_prefix + this,
					text : this
				} ) );

				components.$item_list.append( $new_item );
			} );

			components.$form.append( components.$toggle_all );
			components.$form.append( components.$item_list );
			components.$form.append( $( '<div>', { css: { clear: 'both' } } ) );
			components.$form.append( components.$submit );
			components.$form.append( components.$output );
			components.$form.appendTo( this );
		} );

		/**
		 * @param e
		 */
		function submit_click( e ) {
			var $checkboxes = components.$item_list.find( ':checkbox' );

			e.preventDefault(); // Don't follow the href for the button link

			// Get an array of selected Pod names
			var checked_items = [];
			$checkboxes.filter( ':checked' ).each( function () {
				checked_items.push( $( this ).attr( 'name' ) );
			} );

			// AJAX call
			var data = { action: options.ajax_action };
			data[ options.ajax_item_type ] = checked_items; // e.g. pods-export-templates: array of template names

			/*global ajaxurl */
			$.post( ajaxurl, data, function ( response ) {
				components.$output.find( 'textarea' ).val( response );
			} );
		}

		/**
		 * @param e
		 */
		function toggle_all_click( e ) {
			var $checkboxes = components.$item_list.find( ':checkbox' );

			e.preventDefault();  // Don't follow the href for the button link

			// Any unchecked boxes?  Check them all.
			if ( $checkboxes.not( ':checked' ).length > 0 ) {
				$checkboxes.prop( 'checked', true );
			}
			// All were checked, uncheck them all.
			else {
				$checkboxes.prop( 'checked', false );
			}

		}

	};

}( jQuery ) );