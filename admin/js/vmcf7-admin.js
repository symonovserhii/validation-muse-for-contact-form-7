/**
 * Admin JavaScript for Validation Muse for Contact Form 7.
 *
 * Drives the "Custom Validation" editor panel: field cards, filtering,
 * live preview, inline hints, rule-set tools and Flavor translations.
 *
 * Translated strings come from the panel's data-i18n attribute
 * (see admin/views/panel.php), so the view and its script stay in sync.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

/* global jQuery, vmcf7 */

( function( $ ) {
	'use strict';

	var settings = window.vmcf7 || {};
	var $panel = $();
	var L = {};

	/** Tags wp_kses_post() strips from a message on save. */
	var STRIPPED_TAGS = [ 'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'select', 'textarea', 'svg', 'link', 'meta', 'base' ];

	/* ------------------------------------------------------------------
	 * Helpers
	 * ---------------------------------------------------------------- */

	function t( key ) {
		return L[ key ] || '';
	}

	/** Minimal sprintf: supports %s, %d and %1$s style placeholders. */
	function format( template ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var index = 0;
		return String( template ).replace( /%(?:(\d+)\$)?[sd]/g, function( match, position ) {
			var value = position ? args[ position - 1 ] : args[ index++ ];
			return undefined === value ? '' : value;
		} );
	}

	function ajaxUrl() {
		return settings.ajax_url || ( settings.flavor && settings.flavor.ajax_url ) || window.ajaxurl || '/wp-admin/admin-ajax.php';
	}

	/** Best human-readable message from a failed or unsuccessful AJAX call. */
	function ajaxMessage( jqXHR, fallback ) {
		var json = jqXHR && jqXHR.responseJSON;
		if ( json && json.data && json.data.message ) {
			return json.data.message;
		}
		if ( jqXHR && 0 === jqXHR.status ) {
			return t( 'networkError' );
		}
		return fallback || t( 'requestFailed' );
	}

	/** Put an inline notice into a feedback region. type: success | error | info. */
	function showNotice( $region, message, type, busy ) {
		var $notice = $( '<div class="vmcf7-notice"></div>' )
			.addClass( 'vmcf7-notice--' + ( type || 'info' ) )
			.attr( 'role', 'error' === type ? 'alert' : 'status' );

		if ( busy ) {
			$notice.append( '<span class="spinner is-active" aria-hidden="true"></span>' );
		}
		$notice.append( $( '<span></span>' ).text( message ) );
		$region.empty().append( $notice );
	}

	function setBusy( $button, busy, label ) {
		if ( busy ) {
			$button.data( 'vmcf7Html', $button.html() ).prop( 'disabled', true ).attr( 'aria-busy', 'true' ).text( label || t( 'working' ) );
		} else {
			$button.prop( 'disabled', false ).removeAttr( 'aria-busy' );
			if ( undefined !== $button.data( 'vmcf7Html' ) ) {
				$button.html( $button.data( 'vmcf7Html' ) );
			}
		}
	}

	/** Reload the editor and come back to this tab. */
	function reloadToPanel() {
		var tabId = $panel.closest( '.contact-form-editor-panel' ).attr( 'id' );
		try {
			var url = new URL( window.location.href );
			if ( tabId ) {
				url.searchParams.set( 'active-tab', tabId );
			}
			window.location.assign( url.toString() );
		} catch ( e ) {
			window.location.reload();
		}
	}

	function setExpanded( $button, $target, expanded ) {
		$button.attr( 'aria-expanded', expanded ? 'true' : 'false' );
		$target.prop( 'hidden', ! expanded );
	}

	/* ------------------------------------------------------------------
	 * Master switch
	 * ---------------------------------------------------------------- */

	function initMasterSwitch() {
		$panel.on( 'change', '#vmcf7-enabled', function() {
			$panel.toggleClass( 'is-off', ! this.checked );
		} );
	}

	/* ------------------------------------------------------------------
	 * Field labels (resolved in PHP, so they match {field_label} on the site)
	 * ---------------------------------------------------------------- */

	function prettyName( fieldName ) {
		var pretty = String( fieldName ).replace( /[-_]+/g, ' ' );
		return pretty.charAt( 0 ).toUpperCase() + pretty.slice( 1 );
	}

	/* ------------------------------------------------------------------
	 * Field cards: open/close, state, filtering
	 * ---------------------------------------------------------------- */

	function fieldValue( $card, type ) {
		var $input = $card.find( '[data-rule-type="' + type + '"]' ).first();
		return $input.length ? String( $input.val() || '' ).trim() : '';
	}

	function isCustomized( $card ) {
		var custom = false;
		$card.find( '[name^="vmcf7["]' ).each( function() {
			if ( '' !== String( $( this ).val() || '' ).trim() ) {
				custom = true;
				return false;
			}
		} );
		return custom;
	}

	function refreshCardState( $card ) {
		var custom = isCustomized( $card );
		$card.toggleClass( 'is-custom', custom );
		$card.find( '.vmcf7-field__state' ).text( custom ? t( 'stateCustom' ) : t( 'stateDefault' ) );
	}

	function refreshCount() {
		var $cards = $panel.find( '.vmcf7-field' );
		$( '#vmcf7-count' ).text( format( t( 'countSummary' ), $cards.filter( '.is-custom' ).length, $cards.length ) );
	}

	function toggleCard( $card, open ) {
		$card.toggleClass( 'is-open', open );
		setExpanded( $card.find( '.vmcf7-field__toggle' ), $card.children( '.vmcf7-field__body' ), open );
		if ( open ) {
			refreshPreview( $card );
		}
	}

	function applyFilter() {
		var query = String( $( '#vmcf7-search' ).val() || '' ).trim().toLowerCase();
		var onlyCustom = $( '#vmcf7-only-custom' ).is( ':checked' );
		var visible = 0;

		$panel.find( '.vmcf7-field' ).each( function() {
			var $card = $( this );
			var haystack = String( $card.data( 'search' ) || '' );
			var show = ( ! query || haystack.indexOf( query ) !== -1 ) && ( ! onlyCustom || $card.hasClass( 'is-custom' ) );
			$card.prop( 'hidden', ! show );
			if ( show ) {
				visible++;
			}
		} );

		$panel.find( '.vmcf7-fields__none' ).toggleClass( 'vmcf7-hidden', visible > 0 );
	}

	function initCards() {
		$panel.on( 'click', '.vmcf7-field__toggle', function() {
			var $card = $( this ).closest( '.vmcf7-field' );
			toggleCard( $card, ! $card.hasClass( 'is-open' ) );
		} );

		$panel.on( 'click', '.vmcf7-extra__toggle', function() {
			var $button = $( this );
			var open = 'true' !== $button.attr( 'aria-expanded' );
			$button.closest( '.vmcf7-extra' ).toggleClass( 'is-open', open );
			setExpanded( $button, $button.next( '.vmcf7-extra__body' ), open );
		} );

		$panel.on( 'click', '.vmcf7-expand-all, .vmcf7-collapse-all', function() {
			var open = $( this ).hasClass( 'vmcf7-expand-all' );
			$panel.find( '.vmcf7-field' ).not( '[hidden]' ).each( function() {
				toggleCard( $( this ), open );
			} );
		} );

		$panel.on( 'input search', '#vmcf7-search', applyFilter );
		$panel.on( 'change', '#vmcf7-only-custom', applyFilter );

		// Enter in a helper input must not submit the whole CF7 form.
		$panel.on( 'keydown', '#vmcf7-search, .vmcf7-regex-test', function( e ) {
			if ( 'Enter' === e.key ) {
				e.preventDefault();
			}
		} );

		$panel.on( 'click', '.vmcf7-clear-field', function() {
			var $card = $( this ).closest( '.vmcf7-field' );
			$card.find( '[name^="vmcf7["]' ).val( '' ).trigger( 'change' );
			$card.find( '.vmcf7-regex-preset, .vmcf7-regex-test' ).val( '' ).trigger( 'input' );
			$card.find( '.vmcf7-field__toggle' ).trigger( 'focus' );
		} );

		// Any change inside a card: refresh its badge, preview and the counter.
		$panel.on( 'input change', '.vmcf7-field [name^="vmcf7["]', function() {
			var $card = $( this ).closest( '.vmcf7-field' );
			refreshCardState( $card );
			refreshPreview( $card );
			refreshCount();
		} );
	}

	/* ------------------------------------------------------------------
	 * Live preview (mirrors the fallbacks in VMCF7_Loader::validate_field)
	 * ---------------------------------------------------------------- */

	function refreshPreview( $card ) {
		var label = String( $card.attr( 'data-label' ) || '' ) || prettyName( $card.data( 'field' ) );
		var min = fieldValue( $card, 'min_length' );
		var max = fieldValue( $card, 'max_length' );
		var required = fieldValue( $card, 'required' );
		var invalid = fieldValue( $card, 'invalid' );

		function expand( message ) {
			return message
				.replace( /\{field_label\}/g, label )
				.replace( /\{min\}/g, min )
				.replace( /\{max\}/g, max );
		}

		/** rows[type] = { show, text, isDefault } */
		var rows = {
			required: {
				show: true,
				text: required ? expand( required ) : t( 'cf7Default' ),
				isDefault: ! required
			},
			invalid: {
				show: true,
				text: invalid ? expand( invalid ) : t( 'cf7Default' ),
				isDefault: ! invalid
			},
			length_message: {
				show: '' !== min || '' !== max,
				text: expand( fieldValue( $card, 'length_message' ) || t( 'defaultLength' ) )
			},
			regex_message: {
				show: '' !== fieldValue( $card, 'regex' ),
				text: expand( fieldValue( $card, 'regex_message' ) || invalid || t( 'defaultFormat' ) )
			},
			required_if_message: {
				show: '' !== fieldValue( $card, 'required_if_field' ),
				text: expand( fieldValue( $card, 'required_if_message' ) || required || t( 'defaultRequired' ) )
			}
		};

		$card.find( '.vmcf7-preview__label' ).text( label );
		$card.find( '.vmcf7-preview__row' ).each( function() {
			var $row = $( this );
			var row = rows[ $row.data( 'msg-type' ) ];
			if ( ! row ) {
				return;
			}
			$row.prop( 'hidden', ! row.show ).toggleClass( 'is-default', !! row.isDefault );
			// Messages may contain simple HTML on the site; the preview shows the text only.
			$row.find( '.vmcf7-preview__tip' ).text( row.text.replace( /<[^>]+>/g, '' ) );
		} );
	}

	/* ------------------------------------------------------------------
	 * Smart words (placeholder tokens)
	 * ---------------------------------------------------------------- */

	function initTokens() {
		$panel.on( 'focusin', '.vmcf7-field .vmcf7-msg-input', function() {
			$( this ).closest( '.vmcf7-field' ).data( 'vmcf7LastInput', this );
		} );

		$panel.on( 'click', '.vmcf7-token', function() {
			var $card = $( this ).closest( '.vmcf7-field' );
			var token = String( $( this ).data( 'token' ) );
			var input = $card.data( 'vmcf7LastInput' );

			if ( ! input || ! $( input ).is( ':visible' ) ) {
				// {min}/{max} belong to the length message; everything else to the first message.
				input = ( '{field_label}' === token ? $card.find( '.vmcf7-msg-input:visible' ) : $card.find( '[data-rule-type="length_message"]:visible' ) ).get( 0 );
			}
			if ( ! input ) {
				var $extra = $card.find( '.vmcf7-extra__toggle' );
				if ( 'true' !== $extra.attr( 'aria-expanded' ) ) {
					$extra.trigger( 'click' );
				}
				input = $card.find( '[data-rule-type="length_message"]' ).get( 0 );
			}
			if ( ! input ) {
				return;
			}

			var start = 'number' === typeof input.selectionStart ? input.selectionStart : input.value.length;
			var end = 'number' === typeof input.selectionEnd ? input.selectionEnd : input.value.length;
			input.value = input.value.slice( 0, start ) + token + input.value.slice( end );
			input.focus();
			try {
				input.setSelectionRange( start + token.length, start + token.length );
			} catch ( e ) {} // eslint-disable-line no-empty
			$( input ).trigger( 'input' );
		} );
	}

	/* ------------------------------------------------------------------
	 * Pattern (regex) presets and tester
	 * ---------------------------------------------------------------- */

	/**
	 * Same semantics as VMCF7_Rules::evaluate_regex(): the pattern is used as
	 * written with the Unicode flag (~pattern~u) and is NOT anchored, so it only
	 * has to be found somewhere in the value. PCRE accepts a few escapes that
	 * JavaScript's Unicode mode rejects, hence the non-Unicode fallback.
	 */
	function compilePattern( pattern ) {
		try {
			return new RegExp( pattern, 'u' );
		} catch ( e ) {
			try {
				return new RegExp( pattern );
			} catch ( e2 ) {
				return null;
			}
		}
	}

	function refreshTester( $card ) {
		var $result = $card.find( '.vmcf7-tester__result' );
		var pattern = fieldValue( $card, 'regex' );
		var sample = String( $card.find( '.vmcf7-regex-test' ).val() || '' );

		$result.removeClass( 'is-match is-nomatch is-unknown' ).text( '' );
		if ( '' === pattern || '' === sample ) {
			return;
		}

		var re = compilePattern( pattern );
		if ( ! re ) {
			$result.addClass( 'is-unknown' ).text( t( 'testUnavailable' ) );
		} else if ( re.test( sample ) ) {
			$result.addClass( 'is-match' ).text( t( 'testMatch' ) );
		} else {
			$result.addClass( 'is-nomatch' ).text( t( 'testNoMatch' ) );
		}
	}

	function initPatterns() {
		$panel.on( 'change', '.vmcf7-regex-preset', function() {
			var value = $( this ).val();
			if ( ! value ) {
				return;
			}
			$( this ).closest( '.vmcf7-field' ).find( '[data-rule-type="regex"]' ).val( value ).trigger( 'input' ).trigger( 'focus' );
			this.selectedIndex = 0;
		} );

		$panel.on( 'input', '[data-rule-type="regex"]', function() {
			// The server-side "saved pattern is invalid" note no longer applies once it is edited.
			$( this ).closest( '.vmcf7-field' ).find( '.vmcf7-regex-saved-invalid' ).remove();
		} );

		$panel.on( 'input', '.vmcf7-regex-test, [data-rule-type="regex"]', function() {
			refreshTester( $( this ).closest( '.vmcf7-field' ) );
		} );
	}

	/* ------------------------------------------------------------------
	 * Inline hints while typing
	 * ---------------------------------------------------------------- */

	function setWarning( $warning, messages ) {
		if ( ! $warning.length ) {
			return;
		}
		if ( messages.length ) {
			$warning.empty().removeClass( 'vmcf7-hidden' );
			$.each( messages, function( i, message ) {
				$warning.append( $( '<div></div>' ).text( message ) );
			} );
		} else {
			$warning.addClass( 'vmcf7-hidden' ).empty();
		}
	}

	function lintMessage( $input ) {
		var value = String( $input.val() || '' );
		var warnings = [];
		var found = [];
		var tagRe = /<\/?([a-z][a-z0-9]*)/gi;
		var match;

		if ( value.length > 255 ) {
			warnings.push( format( t( 'lintLong' ), value.length ) );
		}

		while ( ( match = tagRe.exec( value ) ) ) {
			var tag = match[1].toLowerCase();
			if ( $.inArray( tag, STRIPPED_TAGS ) !== -1 && $.inArray( '<' + tag + '>', found ) === -1 ) {
				found.push( '<' + tag + '>' );
			}
		}
		if ( found.length ) {
			warnings.push( format( t( 'lintHtml' ), found.join( ', ' ) ) );
		}

		var $card = $input.closest( '.vmcf7-field' );
		if ( /\{(min|max)\}/.test( value ) &&
			'' === fieldValue( $card, 'min_length' ) && '' === fieldValue( $card, 'max_length' ) ) {
			warnings.push( t( 'lintMinMax' ) );
		}

		setWarning( $input.nextAll( '.vmcf7-lint-warning' ).first(), warnings );
	}

	function lintRules( $card ) {
		var min = fieldValue( $card, 'min_length' );
		var max = fieldValue( $card, 'max_length' );
		var pattern = fieldValue( $card, 'regex' );
		var regexWarnings = [];

		setWarning(
			$card.find( '.vmcf7-lint-range' ),
			'' !== min && '' !== max && parseInt( min, 10 ) > parseInt( max, 10 ) ? [ t( 'lintRange' ) ] : []
		);

		if ( '' !== pattern && ! compilePattern( pattern ) ) {
			regexWarnings.push( t( 'lintRegex' ) );
		}
		setWarning( $card.find( '.vmcf7-lint-regex' ), regexWarnings );
	}

	function initLint() {
		$panel.on( 'input change', '.vmcf7-msg-input', function() {
			lintMessage( $( this ) );
		} );
		$panel.on( 'input change', '.vmcf7-rule-input', function() {
			var $card = $( this ).closest( '.vmcf7-field' );
			lintRules( $card );
			$card.find( '.vmcf7-msg-input' ).each( function() {
				lintMessage( $( this ) );
			} );
		} );

		$panel.find( '.vmcf7-msg-input' ).each( function() {
			if ( this.value ) {
				lintMessage( $( this ) );
			}
		} );
		$panel.find( '.vmcf7-field' ).each( function() {
			lintRules( $( this ) );
		} );
	}

	/* ------------------------------------------------------------------
	 * Tools: copy / file / template / all forms
	 * ---------------------------------------------------------------- */

	/**
	 * Ask for confirmation inside the tool row instead of a browser dialog.
	 */
	function askConfirm( $tool, $trigger, message, yesLabel, onYes ) {
		$panel.find( '.vmcf7-confirm' ).remove();

		var $box = $( '<div class="vmcf7-confirm" role="alertdialog"></div>' ).attr( 'aria-label', message );
		var $yes = $( '<button type="button" class="button button-primary"></button>' ).text( yesLabel );
		var $no = $( '<button type="button" class="button"></button>' ).text( t( 'cancel' ) );

		function close( refocus ) {
			$box.remove();
			if ( refocus ) {
				$trigger.trigger( 'focus' );
			}
		}

		$box.append( $( '<p class="vmcf7-confirm__text"></p>' ).text( message ) )
			.append( $( '<div class="vmcf7-confirm__buttons"></div>' ).append( $yes, $no ) );

		$yes.on( 'click', function() {
			close( true );
			onYes();
		} );
		$no.on( 'click', function() {
			close( true );
		} );
		$box.on( 'keydown', function( e ) {
			if ( 'Escape' === e.key ) {
				e.stopPropagation();
				close( true );
			}
		} );

		$tool.append( $box );
		$no.trigger( 'focus' );
	}

	function initTools() {
		var $tools = $panel.find( '.vmcf7-tools' );
		if ( ! $tools.length ) {
			return;
		}

		var $feedback = $tools.find( '.vmcf7-tools__feedback' );
		var $importFile = $( '#vmcf7-import-file' );
		var $importBtn = $( '#vmcf7-import-btn' );
		var formId = $panel.data( 'form-id' );

		$tools.on( 'click', '.vmcf7-tools__toggle', function() {
			setExpanded( $( this ), $( '#vmcf7-tools-body' ), 'true' !== $( this ).attr( 'aria-expanded' ) );
		} );

		/**
		 * Run one tool request. Options: data (object or FormData), $button, reload.
		 */
		function run( options ) {
			var isForm = window.FormData && options.data instanceof window.FormData;

			setBusy( options.$button, true );
			showNotice( $feedback, t( 'working' ), 'info', true );

			$.ajax( {
				url: ajaxUrl(),
				type: 'POST',
				data: options.data,
				dataType: 'json',
				processData: ! isForm,
				contentType: isForm ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
			} ).done( function( response ) {
				var message = response && response.data && response.data.message ? response.data.message : '';
				if ( response && response.success ) {
					if ( options.reload ) {
						showNotice( $feedback, String( message + ' ' + t( 'reloading' ) ).trim(), 'success', true );
						window.setTimeout( reloadToPanel, 900 );
						return;
					}
					showNotice( $feedback, message, 'success' );
				} else {
					showNotice( $feedback, message || t( 'requestFailed' ), 'error' );
				}
				setBusy( options.$button, false );
			} ).fail( function( jqXHR ) {
				showNotice( $feedback, ajaxMessage( jqXHR ), 'error' );
				setBusy( options.$button, false );
			} );
		}

		// Download.
		$( '#vmcf7-export-btn' ).on( 'click', function() {
			window.location.href = ajaxUrl() + '?action=vmcf7_export_rules&form_id=' + encodeURIComponent( formId ) + '&nonce=' + encodeURIComponent( settings.nonce || '' );
		} );

		// Load from file.
		$importBtn.on( 'click', function() {
			$importFile.val( '' ).trigger( 'click' );
		} );

		$importFile.on( 'change', function() {
			var file = this.files && this.files[0];
			if ( ! file ) {
				return;
			}
			if ( ! /\.json$/i.test( file.name ) ) {
				showNotice( $feedback, t( 'importNotJson' ), 'error' );
				return;
			}

			askConfirm( $importBtn.closest( '.vmcf7-tool' ), $importBtn, format( t( 'confirmImport' ), file.name ), t( 'confirmImportYes' ), function() {
				var data = new window.FormData();
				data.append( 'action', 'vmcf7_import_rules' );
				data.append( 'nonce', settings.nonce || '' );
				data.append( 'form_id', formId );
				data.append( 'import_file', file );
				run( { data: data, $button: $importBtn, reload: true } );
			} );
		} );

		// Copy from another form.
		$( '#vmcf7-copy-confirm-btn' ).on( 'click', function() {
			var $button = $( this );
			var $source = $( '#vmcf7-copy-source-form' );
			var sourceId = $source.val();

			if ( ! sourceId ) {
				showNotice( $feedback, t( 'selectSource' ), 'error' );
				$source.trigger( 'focus' );
				return;
			}

			askConfirm( $button.closest( '.vmcf7-tool' ), $button, format( t( 'confirmCopy' ), $source.find( 'option:selected' ).text().trim() ), t( 'confirmCopyYes' ), function() {
				run( {
					data: {
						action: 'vmcf7_copy_rules',
						nonce: settings.nonce || '',
						from_form_id: sourceId,
						to_form_id: $button.data( 'target-id' )
					},
					$button: $button,
					reload: true
				} );
			} );
		} );

		// Template + all forms.
		var bulk = {
			save_default: { confirm: 'confirmSave', yes: 'confirmSaveYes', reload: false },
			apply_default: { confirm: 'confirmApply', yes: 'confirmApplyYes', reload: true },
			bulk_apply_all: { confirm: 'confirmAll', yes: 'confirmAllYes', reload: false }
		};

		$tools.on( 'click', '.vmcf7-bulk-btn', function() {
			var $button = $( this );
			var action = $button.data( 'action' );
			var config = bulk[ action ];
			if ( ! config ) {
				return;
			}

			askConfirm( $button.closest( '.vmcf7-tool' ), $button, t( config.confirm ), t( config.yes ), function() {
				run( {
					data: {
						action: 'vmcf7_bulk_apply',
						nonce: settings.nonce || '',
						form_id: $button.data( 'form-id' ),
						action_type: action
					},
					$button: $button,
					reload: config.reload
				} );
			} );
		} );

		// The tools read saved data: warn once something on this tab was edited.
		$panel.one( 'input change', '[name^="vmcf7"]', function() {
			$tools.find( '.vmcf7-tools__unsaved' ).removeClass( 'vmcf7-hidden' );
		} );
	}

	/* ------------------------------------------------------------------
	 * Translations (Flavor)
	 * ---------------------------------------------------------------- */

	function selectLanguage( $tab, focus ) {
		var $tabs = $panel.find( '.vmcf7-lang-tab' );
		var lang = $tab.data( 'lang' );

		$tabs.removeClass( 'active' ).attr( { 'aria-selected': 'false', tabindex: '-1' } );
		$tab.addClass( 'active' ).attr( { 'aria-selected': 'true', tabindex: '0' } );

		$panel.find( '.vmcf7-lang-panel' ).each( function() {
			var active = $( this ).data( 'lang' ) === lang;
			$( this ).toggleClass( 'active', active ).prop( 'hidden', ! active );
		} );

		if ( focus ) {
			$tab.trigger( 'focus' );
		}
	}

	function initTranslations() {
		var $tabs = $panel.find( '.vmcf7-lang-tab' );
		if ( ! $tabs.length ) {
			return;
		}

		$tabs.on( 'click', function() {
			selectLanguage( $( this ), false );
		} );

		$tabs.on( 'keydown', function( e ) {
			var index = $tabs.index( this );
			var rtl = $( 'body' ).hasClass( 'rtl' );
			var next = -1;

			if ( 'ArrowRight' === e.key ) {
				next = index + ( rtl ? -1 : 1 );
			} else if ( 'ArrowLeft' === e.key ) {
				next = index + ( rtl ? 1 : -1 );
			} else if ( 'Home' === e.key ) {
				next = 0;
			} else if ( 'End' === e.key ) {
				next = $tabs.length - 1;
			} else {
				return;
			}

			e.preventDefault();
			selectLanguage( $tabs.eq( ( next + $tabs.length ) % $tabs.length ), true );
		} );

		$panel.on( 'change', '.vmcf7-show-all-slots', function() {
			$( this ).closest( '.vmcf7-lang-panel' ).toggleClass( 'is-show-all', this.checked );
		} );

		$panel.on( 'click', '.vmcf7-ai-translate', function() {
			var $button = $( this );
			var $langPanel = $button.closest( '.vmcf7-lang-panel' );
			var $feedback = $langPanel.find( '.vmcf7-lang-panel__feedback' );
			var lang = $button.data( 'lang' ) || $langPanel.data( 'lang' );

			if ( ! lang || ! settings.flavor ) {
				return;
			}

			setBusy( $button, true, t( 'translating' ) );
			showNotice( $feedback, t( 'translating' ), 'info', true );

			$.ajax( {
				url: ajaxUrl(),
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'vmcf7_ai_translate',
					nonce: settings.flavor.nonce,
					form_id: $button.data( 'form-id' ),
					language: lang
				}
			} ).done( function( response ) {
				if ( response && response.success && response.data && response.data.translations ) {
					var filled = populateTranslations( $langPanel, response.data.translations );
					showNotice( $feedback, format( t( 'translateDone' ), filled ), 'success' );
				} else {
					showNotice( $feedback, response && response.data && response.data.message ? response.data.message : t( 'translateFailed' ), 'error' );
				}
			} ).fail( function( jqXHR ) {
				showNotice( $feedback, ajaxMessage( jqXHR, t( 'translateFailed' ) ), 'error' );
			} ).always( function() {
				setBusy( $button, false );
			} );
		} );
	}

	/** Fill a language panel from an AI response. Returns how many inputs were filled. */
	function populateTranslations( $langPanel, translations ) {
		var filled = 0;

		$.each( translations, function( flavorKey, value ) {
			if ( 'string' !== typeof value || '' === value ) {
				return;
			}
			$langPanel.find( 'input[data-flavor-key]' ).filter( function() {
				return $( this ).data( 'flavor-key' ) === flavorKey;
			} ).each( function() {
				$( this ).val( value ).trigger( 'change' );
				$( this ).closest( '.vmcf7-trans-item' ).removeClass( 'is-idle' );
				$( this ).closest( '.vmcf7-trans-row' ).removeClass( 'is-idle' );
				filled++;
			} );
		} );

		if ( filled ) {
			$langPanel.find( '.vmcf7-lang-panel__empty' ).addClass( 'vmcf7-hidden' );
		}
		return filled;
	}

	/* ------------------------------------------------------------------
	 * Boot
	 * ---------------------------------------------------------------- */

	/**
	 * Make an input count as unchanged for CF7's "leave page?" check.
	 *
	 * CF7 compares every input in the editor form with its default value. A
	 * <select> without a `selected` option, or a helper control that is never
	 * saved (filter, pattern tester, form picker), would otherwise make CF7
	 * ask "Leave site?" on every navigation and after the tools reload.
	 */
	function markPristine( el ) {
		if ( 'checkbox' === el.type || 'radio' === el.type ) {
			el.defaultChecked = el.checked;
		} else if ( 'SELECT' === el.tagName ) {
			$.each( el.options, function( i, option ) {
				option.defaultSelected = option.selected;
			} );
		} else if ( 'file' !== el.type ) {
			el.defaultValue = el.value;
		}
	}

	var VIEW_CONTROLS = '#vmcf7-search, #vmcf7-only-custom, .vmcf7-show-all-slots, .vmcf7-regex-test, .vmcf7-regex-preset, #vmcf7-copy-source-form';

	function initPristine() {
		$panel.find( 'select' ).each( function() {
			markPristine( this );
		} );
		$panel.on( 'input change', VIEW_CONTROLS, function() {
			markPristine( this );
		} );
		// A chosen import file is read and uploaded at once; it is never saved with the form.
		$panel.on( 'change', '#vmcf7-import-file', function() {
			var input = this;
			setTimeout( function() {
				try {
					input.value = '';
				} catch ( e ) {}
			}, 0 );
		} );
	}

	function init() {
		$panel = $( '.vmcf7-panel' ).first();
		if ( ! $panel.length ) {
			return;
		}

		L = $panel.data( 'i18n' ) || {};
		initPristine();

		initMasterSwitch();
		initCards();
		initTokens();
		initPatterns();
		initLint();
		initTools();
		initTranslations();

		$panel.find( '.vmcf7-field.is-open' ).each( function() {
			refreshPreview( $( this ) );
		} );
	}

	$( init );
}( jQuery ) );
