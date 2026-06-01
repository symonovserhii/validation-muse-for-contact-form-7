/**
 * Frontend Validation for Validation Muse for Contact Form 7.
 *
 * @package ValidationMuse
 * @since   1.6.0
 */

( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		// Target all CF7 forms on the page
		$( '.wpcf7-form' ).each( function() {
			var $form = $( this );

			// Hook into input changes for instant validation
			$form.on( 'blur change', 'input, select, textarea', function() {
				validateField( $( this ), $form );
			} );

			// Hook into form submit to validate all fields before sending
			$form.on( 'submit', function( e ) {
				var hasErrors = false;
				var $firstError = null;

				$form.find( 'input, select, textarea' ).each( function() {
					var $input = $( this );
					if ( ! validateField( $input, $form ) ) {
						hasErrors = true;
						if ( ! $firstError ) {
							$firstError = $input;
						}
					}
				} );

				if ( hasErrors ) {
					e.preventDefault();
					e.stopImmediatePropagation();

					// Focus first error field
					if ( $firstError ) {
						$firstError.focus();
						$( 'html, body' ).animate( {
							scrollTop: $firstError.offset().top - 100
						}, 300 );
					}

					// Update CF7 response region
					var $response = $form.find( '.wpcf7-response-output' );
					if ( $response.length ) {
						$response.addClass( 'wpcf7-validation-errors' )
							.attr( 'role', 'alert' )
							.text( 'Validation errors occurred. Please confirm the fields and submit again.' )
							.show();
					}
				}
			} );
		} );
	} );

	/**
	 * Validate a single input field.
	 *
	 * @param {jQuery} $input The input element.
	 * @param {jQuery} $form  The parent form.
	 * @return {boolean} True if valid, false otherwise.
	 */
	function validateField( $input, $form ) {
		var name = $input.attr( 'name' );
		if ( ! name ) return true;

		// Clean name (remove brackets)
		var cleanName = name.replace( '[]', '' );
		var value = $input.val();
		var type = $input.attr( 'type' ) || $input.prop( 'tagName' ).toLowerCase();

		// Fetch rules from data attributes
		var msgRequired = $input.attr( 'data-vmcf7-msg-required' );
		var msgInvalid = $input.attr( 'data-vmcf7-msg-invalid' );
		var regexPattern = $input.attr( 'data-vmcf7-regex' );
		var msgRegex = $input.attr( 'data-vmcf7-msg-regex' );
		var minLength = $input.attr( 'data-vmcf7-min-length' );
		var maxLength = $input.attr( 'data-vmcf7-max-length' );
		var msgLength = $input.attr( 'data-vmcf7-msg-length' );
		var reqIfField = $input.attr( 'data-vmcf7-required-if-field' );
		var msgReqIf = $input.attr( 'data-vmcf7-msg-required-if' );

		var isRequired = $input.prop( 'required' ) || $input.attr( 'aria-required' ) === 'true' || !! msgRequired;
		var isEmpty = isValueEmpty( value, type, $input );

		// 1. Required-If Check
		if ( isEmpty && reqIfField ) {
			// Find companion field in the same form
			var $companion = $form.find( '[name="' + reqIfField + '"], [name="' + reqIfField + '[]"]' );
			if ( $companion.length ) {
				var compType = $companion.attr( 'type' ) || $companion.prop( 'tagName' ).toLowerCase();
				var compValue = $companion.val();
				if ( ! isValueEmpty( compValue, compType, $companion ) ) {
					var err = msgReqIf || msgRequired || 'This field is required.';
					showError( $input, err );
					return false;
				}
			}
		}

		// 2. Normal Required Check
		if ( isEmpty && isRequired ) {
			if ( msgRequired ) {
				showError( $input, msgRequired );
				return false;
			}
		}

		// If value is empty, and not required, it is valid
		if ( isEmpty ) {
			clearError( $input );
			return true;
		}

		// 3. Custom Regex Check
		if ( regexPattern ) {
			try {
				var regex = new RegExp( regexPattern );
				if ( ! regex.test( value ) ) {
					showError( $input, msgRegex || msgInvalid || 'Invalid format.' );
					return false;
				}
			} catch ( e ) {
				// Pattern error, log to console in debug mode
				console.warn( 'Validation Muse: Invalid regex pattern ' + regexPattern );
			}
		}

		// 4. Min/Max Length Check
		if ( minLength || maxLength ) {
			var len = value.length;
			if ( minLength && len < parseInt( minLength, 10 ) ) {
				showError( $input, msgLength || 'Too short.' );
				return false;
			}
			if ( maxLength && len > parseInt( maxLength, 10 ) ) {
				showError( $input, msgLength || 'Too long.' );
				return false;
			}
		}

		// 5. Default Format Validation (if data-vmcf7-msg-invalid is set)
		if ( msgInvalid ) {
			var isInvalid = false;
			var baseType = $input.hasClass( 'wpcf7-validates-as-email' ) || type === 'email' ? 'email' :
						   $input.hasClass( 'wpcf7-validates-as-url' ) || type === 'url' ? 'url' :
						   $input.hasClass( 'wpcf7-validates-as-tel' ) || type === 'tel' ? 'tel' :
						   $input.hasClass( 'wpcf7-validates-as-number' ) || type === 'number' ? 'number' :
						   $input.hasClass( 'wpcf7-validates-as-date' ) || type === 'date' ? 'date' : '';

			switch ( baseType ) {
				case 'email':
					isInvalid = ! /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test( value );
					break;
				case 'url':
					isInvalid = ! /^(?:https?|ftp):\/\/[^\s\/$.?#].[^\s]*$/i.test( value );
					break;
				case 'tel':
					isInvalid = ! /^[+]?[0-9\s.\-\/()]{6,20}$/.test( value );
					break;
				case 'number':
					isInvalid = isNaN( Number( value ) );
					break;
				case 'date':
					isInvalid = isNaN( Date.parse( value ) );
					break;
			}

			if ( isInvalid ) {
				showError( $input, msgInvalid );
				return false;
			}
		}

		clearError( $input );
		return true;
	}

	/**
	 * Check if a field value is empty.
	 */
	function isValueEmpty( value, type, $input ) {
		if ( type === 'checkbox' || type === 'radio' ) {
			// Find all siblings in the same group
			var name = $input.attr( 'name' );
			var $form = $input.closest( 'form' );
			return $form.find( 'input[name="' + name + '"]:checked' ).length === 0;
		}

		if ( Array.isArray( value ) ) {
			return value.length === 0 || ( value.length === 1 && value[0] === '' );
		}

		return ! value || $.trim( value ) === '';
	}

	/**
	 * Show error tip under the field and set accessibility tags.
	 */
	function showError( $input, message ) {
		var name = $input.attr( 'name' );
		var cleanName = name.replace( '[]', '' );
		var errId = 'vmcf7-err-' + cleanName;

		// Add classes to the field and wrapper
		$input.addClass( 'wpcf7-not-valid' );
		var $wrap = $input.closest( '.wpcf7-form-control-wrap' );
		if ( $wrap.length ) {
			$wrap.addClass( 'wpcf7-not-valid' );
		}

		// Find or create error tip
		var $tip = $wrap.find( '.wpcf7-not-valid-tip' );
		if ( ! $tip.length ) {
			$tip = $( '<span class="wpcf7-not-valid-tip"></span>' );
			$input.after( $tip );
		}

		$tip.attr( 'id', errId )
			.attr( 'role', 'alert' )
			.text( message );

		// Set aria-describedby for screen readers
		var existingDescribedBy = $input.attr( 'aria-describedby' ) || '';
		if ( existingDescribedBy.indexOf( errId ) === -1 ) {
			$input.attr( 'aria-describedby', $.trim( existingDescribedBy + ' ' + errId ) );
		}
	}

	/**
	 * Clear validation error for a field.
	 */
	function clearError( $input ) {
		var name = $input.attr( 'name' );
		var cleanName = name.replace( '[]', '' );
		var errId = 'vmcf7-err-' + cleanName;

		$input.removeClass( 'wpcf7-not-valid' );
		var $wrap = $input.closest( '.wpcf7-form-control-wrap' );
		if ( $wrap.length ) {
			$wrap.removeClass( 'wpcf7-not-valid' );
			$wrap.find( '.wpcf7-not-valid-tip' ).remove();
		}

		// Remove ID from aria-describedby
		var describedBy = $input.attr( 'aria-describedby' ) || '';
		if ( describedBy ) {
			var parts = describedBy.split( /\s+/ );
			parts = $.grep( parts, function( val ) {
				return val !== errId;
			} );
			if ( parts.length ) {
				$input.attr( 'aria-describedby', parts.join( ' ' ) );
			} else {
				$input.removeAttr( 'aria-describedby' );
			}
		}
	}

} )( jQuery );
