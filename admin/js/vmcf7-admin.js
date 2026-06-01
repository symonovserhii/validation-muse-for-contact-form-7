/**
 * Admin JavaScript for Validation Muse for Contact Form 7.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

/* global jQuery, vmcf7 */

( function( $ ) {
	'use strict';

	var currentPreviewField = '';

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
			var isDisabled = ! $( this ).is( ':checked' );
			if ( isDisabled ) {
				$table.addClass( 'vmcf7-disabled' );
				$table.find( 'input, select, button' ).prop( 'disabled', true );
				$( '.vmcf7-translations' ).addClass( 'vmcf7-disabled' );
				$( '.vmcf7-translations' ).find( 'input, select, button' ).prop( 'disabled', true );
				$( '.vmcf7-toolbar' ).addClass( 'vmcf7-disabled' ).find( 'button, select' ).prop( 'disabled', true );
			} else {
				$table.removeClass( 'vmcf7-disabled' );
				$table.find( 'input, select, button' ).prop( 'disabled', false );
				$( '.vmcf7-translations' ).removeClass( 'vmcf7-disabled' );
				$( '.vmcf7-translations' ).find( 'input, select, button' ).prop( 'disabled', false );
				$( '.vmcf7-toolbar' ).removeClass( 'vmcf7-disabled' ).find( 'button, select' ).prop( 'disabled', false );
			}
		} );

		// Initialize state on load.
		$toggle.trigger( 'change' );

		// Initialize tabs and buttons.
		initFlavorTabs();
		initAiTranslate();
		initAdvancedToggles();
		initSharingToolbar();
		initLivePreview();
		initEditorLint();
	}

	/**
	 * Initialize advanced rules collapsible accordions.
	 */
	function initAdvancedToggles() {
		// Toggle rules for main language fields
		$( document ).on( 'click', '.vmcf7-toggle-advanced', function( e ) {
			e.preventDefault();
			var $btn = $( this );
			var $row = $btn.closest( '.vmcf7-field-row' );
			var fieldName = $row.data( 'field' );
			var $advancedRow = $( '#vmcf7-advanced-' + fieldName );
			var isExpanded = $btn.attr( 'aria-expanded' ) === 'true';

			$btn.attr( 'aria-expanded', ! isExpanded );
			if ( isExpanded ) {
				$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-up-alt2' ).addClass( 'dashicons-arrow-down-alt2' );
				$advancedRow.hide();
			} else {
				$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-down-alt2' ).addClass( 'dashicons-arrow-up-alt2' );
				$advancedRow.show();
			}
		} );

		// Toggle rules for translated fields
		$( document ).on( 'click', '.vmcf7-toggle-advanced-trans', function( e ) {
			e.preventDefault();
			var $btn = $( this );
			var $row = $btn.closest( '.vmcf7-trans-row' );
			var fieldName = $row.data( 'field' );
			var lang = $row.closest( '.vmcf7-lang-panel' ).data( 'lang' );
			var $advancedRow = $( '#vmcf7-advanced-trans-' + fieldName + '-' + lang );
			var isExpanded = $btn.attr( 'aria-expanded' ) === 'true';

			$btn.attr( 'aria-expanded', ! isExpanded );
			if ( isExpanded ) {
				$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-up-alt2' ).addClass( 'dashicons-arrow-down-alt2' );
				$advancedRow.hide();
			} else {
				$btn.find( '.dashicons' ).removeClass( 'dashicons-arrow-down-alt2' ).addClass( 'dashicons-arrow-up-alt2' );
				$advancedRow.show();
			}
		} );
	}

	/**
	 * Initialize sharing, copy, and bulk template features.
	 */
	function initSharingToolbar() {
		var $exportBtn = $( '#vmcf7-export-btn' );
		var $importBtn = $( '#vmcf7-import-btn' );
		var $importFile = $( '#vmcf7-import-file' );
		var $copyBtn = $( '#vmcf7-copy-btn' );
		var $copyConfirmBtn = $( '#vmcf7-copy-confirm-btn' );
		var $copySelectWrap = $( '.vmcf7-copy-select-wrap' );
		var ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';

		// Export Rules
		$exportBtn.on( 'click', function() {
			var formId = $( this ).data( 'form-id' );
			if ( ! formId ) return;
			var url = ajaxUrl + '?action=vmcf7_export_rules&form_id=' + formId + '&nonce=' + vmcf7.nonce;
			window.location.href = url;
		} );

		// Import Rules
		$importBtn.on( 'click', function() {
			$importFile.trigger( 'click' );
		} );

		$importFile.on( 'change', function() {
			var file = this.files[0];
			if ( ! file ) return;

			var formId = $exportBtn.data( 'form-id' );
			var formData = new FormData();
			formData.append( 'action', 'vmcf7_import_rules' );
			formData.append( 'nonce', vmcf7.nonce );
			formData.append( 'form_id', formId );
			formData.append( 'import_file', file );

			$importBtn.prop( 'disabled', true ).text( 'Importing\u2026' );

			$.ajax( {
				url: ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function( response ) {
					if ( response.success ) {
						showGlobalNotice( response.data.message, 'success' );
						setTimeout( function() {
							window.location.reload();
						}, 1000 );
					} else {
						showGlobalNotice( response.data.message || 'Import failed.', 'error' );
						$importBtn.prop( 'disabled', false ).text( 'Import JSON' );
					}
				},
				error: function() {
					showGlobalNotice( 'Import request failed.', 'error' );
					$importBtn.prop( 'disabled', false ).text( 'Import JSON' );
				}
			} );
		} );

		// Copy From...
		$copyBtn.on( 'click', function() {
			$copySelectWrap.toggle();
		} );

		$copyConfirmBtn.on( 'click', function() {
			var sourceFormId = $( '#vmcf7-copy-source-form' ).val();
			var targetFormId = $( this ).data( 'target-id' );

			if ( ! sourceFormId ) {
				alert( 'Please select a source form.' );
				return;
			}

			if ( ! confirm( 'This will overwrite current validation settings. Continue?' ) ) {
				return;
			}

			$copyConfirmBtn.prop( 'disabled', true );

			$.ajax( {
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'vmcf7_copy_rules',
					nonce: vmcf7.nonce,
					from_form_id: sourceFormId,
					to_form_id: targetFormId
				},
				success: function( response ) {
					if ( response.success ) {
						showGlobalNotice( response.data.message, 'success' );
						setTimeout( function() {
							window.location.reload();
						}, 1000 );
					} else {
						showGlobalNotice( response.data.message || 'Copy failed.', 'error' );
						$copyConfirmBtn.prop( 'disabled', false );
					}
				},
				error: function() {
					showGlobalNotice( 'Copy request failed.', 'error' );
					$copyConfirmBtn.prop( 'disabled', false );
				}
			} );
		} );

		// Bulk apply actions
		$( '.vmcf7-bulk-btn' ).on( 'click', function( e ) {
			e.preventDefault();
			var $btn = $( this );
			var action = $btn.data( 'action' );
			var formId = $btn.data( 'form-id' );

			if ( 'bulk_apply_all' === action && ! confirm( 'This will overwrite rules on ALL other contact forms in the database. Are you sure?' ) ) {
				return;
			}

			var originalText = $btn.text();
			$btn.prop( 'disabled', true ).text( 'Processing\u2026' );

			$.ajax( {
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'vmcf7_bulk_apply',
					nonce: vmcf7.nonce,
					form_id: formId,
					action_type: action
				},
				success: function( response ) {
					if ( response.success ) {
						showGlobalNotice( response.data.message, 'success' );
						if ( action === 'apply_default' ) {
							setTimeout( function() {
								window.location.reload();
							}, 1000 );
						}
					} else {
						showGlobalNotice( response.data.message || 'Bulk action failed.', 'error' );
					}
				},
				error: function() {
					showGlobalNotice( 'Bulk action request failed.', 'error' );
				},
				complete: function() {
					$btn.prop( 'disabled', false ).text( originalText );
				}
			} );
		} );
	}

	/**
	 * Display custom inline warnings for validation messages.
	 */
	function initEditorLint() {
		$( document ).on( 'input change', '.vmcf7-msg-input', function() {
			var $input = $( this );
			var val = $input.val();
			var $warning = $input.siblings( '.vmcf7-lint-warning' );
			var warnings = [];

			if ( ! $warning.length ) {
				$warning = $( '<div class="vmcf7-lint-warning"></div>' );
				$input.after( $warning );
			}

			// 1. Check overly long
			if ( val.length > 255 ) {
				warnings.push( 'Notice: Message is very long (' + val.length + ' chars). Recommended to stay under 255 characters.' );
			}

			// 2. Check disallowed HTML
			var tags = val.match( /<\/?[a-z0-9]+/gi );
			if ( tags ) {
				var allowed = [ 'a', 'b', 'i', 'strong', 'em', 'span', 'br' ];
				var disallowed = [];
				$.each( tags, function( idx, tag ) {
					var tagName = tag.replace( /<\/?[#]?/g, '' ).toLowerCase();
					if ( $.inArray( tagName, allowed ) === -1 && $.inArray( tagName, disallowed ) === -1 ) {
						disallowed.push( tagName );
					}
				} );
				if ( disallowed.length > 0 ) {
					warnings.push( 'Notice: Disallowed HTML tags found (<' + disallowed.join( '>, <' ) + '>). Allowed tags: a, b, i, strong, em, span, br.' );
				}
			}

			// 3. Warning for empty message on required field (if it is the required message)
			var isRequiredInput = $input.data( 'rule-type' ) === 'required';
			var isFieldRequiredInCF7 = $input.closest( 'tr' ).find( '.vmcf7-field-type' ).prev().find( '.vmcf7-toggle-advanced' ).length > 0; // Wait, tag check is easier.
			if ( isRequiredInput && ! val && isFieldRequiredInCF7 ) {
				// optional warning: "Default CF7 validation message will be used."
			}

			if ( warnings.length > 0 ) {
				$warning.html( warnings.join( '<br>' ) ).show();
			} else {
				$warning.hide().empty();
			}
		} );

		// Run initially on all inputs
		$( '.vmcf7-msg-input' ).trigger( 'change' );
	}

	/**
	 * Initialize Live Message Preview.
	 */
	function initLivePreview() {
		var $selector = $( '#vmcf7-preview-field-selector' );
		var $card = $( '.vmcf7-preview-card' );

		if ( ! $selector.length || ! $card.length ) {
			return;
		}

		$selector.on( 'change', function() {
			var fieldName = $( this ).val();
			currentPreviewField = fieldName;

			if ( ! fieldName ) {
				$card.hide();
				return;
			}

			// Get field label from layout template
			var label = getFieldLabelJS( fieldName );
			$( '#vmcf7-preview-label-text' ).text( label );

			$card.show();
			updatePreviewCard();
		} );

		// Dynamically update preview card on input changes
		$( document ).on( 'input change', 'input', function() {
			if ( currentPreviewField ) {
				updatePreviewCard();
			}
		} );
	}

	/**
	 * Read layout template and find the user-visible label.
	 */
	function getFieldLabelJS( fieldName ) {
		var formContent = $( '#wpcf7-form' ).val() || '';
		var tagEscaped = fieldName.replace( /[-\/\\^$*+?.()|[\]{}]/g, '\\$&' );
		
		// Match wrapped <label> tag containing the shortcode
		var wrapReg = new RegExp( '<label\\b[^>]*>(?:(?!<\\/label>).)*\\[' + tagEscaped + '\\b(?:(?!<\\/label>).)*<\\/label>', 'is' );
		var match = formContent.match( wrapReg );
		if ( match ) {
			var text = match[0].replace( /\[[^\]]+\]/g, '' ).replace( /<[^>]+>/g, '' ).trim();
			if ( text ) return text;
		}

		// Fallback pretty capitalized
		var pretty = fieldName.replace( /[-_]/g, ' ' );
		return pretty.charAt( 0 ).toUpperCase() + pretty.slice( 1 );
	}

	/**
	 * Update the live preview card with current input values.
	 */
	function updatePreviewCard() {
		if ( ! currentPreviewField ) return;

		var fieldName = currentPreviewField;
		var label = getFieldLabelJS( fieldName );
		
		// Find current values
		var reqVal = $( '#vmcf7-' + fieldName + '-required' ).val() || 'This field is required';
		var invVal = $( '#vmcf7-' + fieldName + '-invalid' ).val() || 'Please enter valid format';
		var regexVal = $( '#vmcf7-' + fieldName + '-regex' ).val();
		var regexMsg = $( '#vmcf7-' + fieldName + '-regex-message' ).val() || 'Invalid format';
		var minVal = $( '#vmcf7-' + fieldName + '-min-length' ).val() || '';
		var maxVal = $( '#vmcf7-' + fieldName + '-max-length' ).val() || '';
		var lenMsg = $( '#vmcf7-' + fieldName + '-length-message' ).val() || 'Must be between {min} and {max} characters';
		var reqIfField = $( '#vmcf7-' + fieldName + '-required-if-field' ).val();
		var reqIfMsg = $( '#vmcf7-' + fieldName + '-required-if-message' ).val() || 'This field is required';

		// Expand helpers
		function expand( msg ) {
			return msg.replace( /{field_label}/g, label )
				.replace( /{min}/g, minVal )
				.replace( /{max}/g, maxVal );
		}

		// Populate card fields
		var $previewErrors = $( '.vmcf7-preview-errors' );
		
		$previewErrors.find( '[data-msg-type="required"] .wpcf7-not-valid-tip' ).text( expand( reqVal ) );
		$previewErrors.find( '[data-msg-type="invalid"] .wpcf7-not-valid-tip' ).text( expand( invVal ) );

		if ( regexVal ) {
			$previewErrors.find( '[data-msg-type="regex_message"]' ).show().find( '.wpcf7-not-valid-tip' ).text( expand( regexMsg ) );
		} else {
			$previewErrors.find( '[data-msg-type="regex_message"]' ).hide();
		}

		if ( minVal || maxVal ) {
			$previewErrors.find( '[data-msg-type="length_message"]' ).show().find( '.wpcf7-not-valid-tip' ).text( expand( lenMsg ) );
		} else {
			$previewErrors.find( '[data-msg-type="length_message"]' ).hide();
		}

		if ( reqIfField ) {
			$previewErrors.find( '[data-msg-type="required_if_message"]' ).show().find( '.wpcf7-not-valid-tip' ).text( expand( reqIfMsg ) );
		} else {
			$previewErrors.find( '[data-msg-type="required_if_message"]' ).hide();
		}
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
	 */
	function populateTranslations( lang, translations ) {
		var $panel = $( '.vmcf7-lang-panel[data-lang="' + lang + '"]' );

		$.each( translations, function( flavorKey, value ) {
			$panel.find( 'input[data-flavor-key="' + flavorKey + '"]' ).val( value );
		} );
	}

	/**
	 * Show an inline notice message in the translations section.
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

	/**
	 * Show a global status notice under the toolbar.
	 */
	function showGlobalNotice( message, type ) {
		var $notice = $( '.vmcf7-sharing-notice' );
		$notice.removeClass( 'success error' ).addClass( type ).text( message ).show();
		setTimeout( function() {
			$notice.fadeOut( 300 );
		}, 4000 );
	}

	// Initialize when DOM is ready.
	$( document ).ready( init );
}( jQuery ) );
