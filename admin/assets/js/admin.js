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
			ajax      : {
				action   : 'pods_export_code',
				item_type: this.data( 'item-type' )
			},
			id_prefix : this.attr( 'id' ) + '-',
			form_class: 'pods-submittable'
		}, options );

		var $form = $( '<form>', {
			action : '',
			method : 'post',
			'class': options.form_class
		} );

		var components = {
			$toggle_all: $( '<a>', {
				href   : '#',
				'class': 'toggle-all button', // Todo
				click  : toggle_all_click,
				text   : 'Toggle all on / off'
			} ),
			$item_list : $( '<ul>' ),
			$submit    : $( '<a>', {
				'class': 'button button-primary pods-export-submit', // Todo
				id     : options.id_prefix + 'submit',
				href   : '#',
				click  : submit_click,
				text   : 'Export'
			} ),
			$output    : $( '<div>', { 'class': 'output-wrapper' } )
				.append( $( '<div>', { text: 'Output:' } ) )
				.append( $( '<textarea>', {
					id: options.id_prefix + 'result-output'

				} )
			).hide()
		};

		return this.each( function () {

			// Build the checkbox list
			var $new_item;
			var list_class;

			// Items are in { name: label } format
			for ( var item in items ) {
				if ( items.hasOwnProperty( item ) ) {
					list_class = ( 'pods-zebra-odd' == list_class ) ? 'pods-zebra-even' : 'pods-zebra-odd';
					$new_item = $( '<li>', { 'class': list_class } );

					$new_item.append( $( '<input>', {
						name   : item,
						id     : options.id_prefix + item,
						type   : 'checkbox',
						checked: true
					} ) );

					$new_item.append( $( '<label>', {
						'for': options.id_prefix + item,
						text : items[ item ] // Get the label
					} ) );

					components.$item_list.append( $new_item );
				}
			}

			// Add all the components to the form
			for ( var component in components ) {
				if ( components.hasOwnProperty( component ) ) {
					$form.append( components[ component ] );
				}
			}

			// Add the form to the targeted container
			$form.appendTo( this );

		} );

		/**
		 * @param e
		 */
		function submit_click( e ) {
			var $checkboxes = components.$item_list.find( ':checkbox' );

			e.preventDefault(); // Don't follow the href for the button link

			// Build an array of selected item names
			var checked_items = [];
			$checkboxes.filter( ':checked' ).each( function () {
				checked_items.push( $( this ).attr( 'name' ) );
			} );

			// AJAX call
			var data = { action: options.ajax.action };
			data[ options.ajax.item_type ] = checked_items; // e.g. pods-export-templates: array of template names

			/*global ajaxurl */
			$.post( ajaxurl, data, function ( response ) {
				components.$output.find( 'textarea' ).val( response );
				components.$output.show();
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