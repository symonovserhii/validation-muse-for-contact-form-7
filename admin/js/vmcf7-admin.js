/**
 * Admin JavaScript for Validation Muse for Contact Form 7.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

/* global jQuery */

( function( $ ) {
	'use strict';

	/**
	 * Initialize the admin panel functionality.
	 */
	function init() {
		var $toggle = $( '.vmcf7-enable-toggle input' );
		var $table = $( '.vmcf7-fields-table' );

		if ( ! $toggle.length || ! $table.length ) {
			return;
		}

		// Toggle input availability when the feature is enabled/disabled.
		$toggle.on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$table.removeClass( 'vmcf7-disabled' );
				$table.find( 'input' ).prop( 'disabled', false );
			} else {
				$table.addClass( 'vmcf7-disabled' );
				$table.find( 'input' ).prop( 'disabled', true );
			}
		} );

		// Initialize state on load.
		$toggle.trigger( 'change' );
	}

	// Initialize when DOM is ready.
	$( document ).ready( init );
}( jQuery ) );