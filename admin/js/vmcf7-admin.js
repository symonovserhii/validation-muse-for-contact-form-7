/**
 * Admin JavaScript for Validation Muse for Contact Form 7.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

/* global jQuery, vmcf7 */

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
				$( '.vmcf7-translations' ).removeClass( 'vmcf7-disabled' );
				$( '.vmcf7-translations' ).find( 'input' ).prop( 'disabled', false );
			} else {
				$table.addClass( 'vmcf7-disabled' );
				$table.find( 'input' ).prop( 'disabled', true );
				$( '.vmcf7-translations' ).addClass( 'vmcf7-disabled' );
				$( '.vmcf7-translations' ).find( 'input' ).prop( 'disabled', true );
			}
		} );

		// Initialize state on load.
		$toggle.trigger( 'change' );

		// Initialize Flavor tabs.
		initFlavorTabs();
		initAiTranslate();
	}

	/**
	 * Initialize language tab switching.
	 */
	function initFlavorTabs() {
		var $tabs = $( '.vmcf7-lang-tab' );
		if ( ! $tabs.length ) {
			return;
		}

		$tabs.on( 'click', function() {
			var lang = $( this ).data( 'lang' );

			// Update active tab.
			$tabs.removeClass( 'active' );
			$( this ).addClass( 'active' );

			// Show corresponding panel.
			$( '.vmcf7-lang-panel' ).removeClass( 'active' );
			$( '.vmcf7-lang-panel[data-lang="' + lang + '"]' ).addClass( 'active' );
		} );
	}

	/**
	 * Initialize AI Translate button.
	 */
	function initAiTranslate() {
		var $btn = $( '.vmcf7-ai-translate' );
		if ( ! $btn.length || ! vmcf7.flavor ) {
			return;
		}

		$btn.on( 'click', function() {
			var $button = $( this );
			var originalText = $button.text();
			var formId = $button.data( 'form-id' );
			var $activeTab = $( '.vmcf7-lang-tab.active' );
			var lang = $activeTab.data( 'lang' );

			if ( ! lang ) {
				return;
			}

			$button.prop( 'disabled', true ).text( 'Translating\u2026' );

			$.ajax( {
				url: vmcf7.flavor.ajax_url,
				type: 'POST',
				data: {
					action: 'vmcf7_ai_translate',
					nonce: vmcf7.flavor.nonce,
					form_id: formId,
					language: lang
				},
				success: function( response ) {
					if ( response.success && response.data.translations ) {
						populateTranslations( lang, response.data.translations );
					} else {
						showNotice( response.data && response.data.message ? response.data.message : 'Translation failed.' );
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					var message = 'Translation request failed.';
					if ( jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
						message = jqXHR.responseJSON.data.message;
					} else if ( jqXHR.status === 0 ) {
						message = 'Network error: Please check your internet connection.';
					} else if ( textStatus === 'timeout' ) {
						message = 'Request timeout: The server took too long to respond.';
					}
					showNotice( message );
				},
				complete: function() {
					$button.prop( 'disabled', false ).text( originalText );
				}
			} );
		} );
	}

	/**
	 * Populate translation inputs from AI response.
	 *
	 * @param {string} lang         Language code.
	 * @param {Object} translations Object keyed by Flavor field key (e.g. vmcf7_your_email_required).
	 */
	function populateTranslations( lang, translations ) {
		var $panel = $( '.vmcf7-lang-panel[data-lang="' + lang + '"]' );

		$.each( translations, function( flavorKey, value ) {
			$panel.find( 'input[data-flavor-key="' + flavorKey + '"]' ).val( value );
		} );
	}

	/**
	 * Show an inline notice message in the translations section.
	 *
	 * @param {string} message The message to display.
	 */
	function showNotice( message ) {
		var $existing = $( '.vmcf7-translations .vmcf7-notice' );
		$existing.remove();

		var $notice = $( '<div class="vmcf7-notice notice notice-error inline"><p></p></div>' );
		$notice.find( 'p' ).text( message );
		$( '.vmcf7-lang-tabs' ).after( $notice );

		setTimeout( function() {
			$notice.fadeOut( 300, function() {
				$notice.remove();
			} );
		}, 5000 );
	}

	// Initialize when DOM is ready.
	$( document ).ready( init );
}( jQuery ) );
