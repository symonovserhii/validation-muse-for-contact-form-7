<?php
/**
 * Main plugin loader class.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin loader class.
 *
 * Handles initialization and validation logic for Contact Form 7 fields.
 *
 * @since 1.0.0
 */
class VMCF7_Loader {

	/**
	 * Initialize the plugin.
	 *
	 * Sets up validation filters and admin functionality.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		require_once VMCF7_PATH . 'includes/class-vmcf7-flavor.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-admin.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-rules.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-i18n-compat.php';

		$i18n = new VMCF7_I18n_Compat();
		$i18n->init();

		// Register validation filters for each supported tag type.
		// Priority 20 = after CF7 core (10) and SWV, so we can replace error messages.
		foreach ( $this->get_validation_tag_types() as $tag_type ) {
			add_filter( "wpcf7_validate_{$tag_type}", array( $this, 'validate_field' ), 20, 2 );
			add_filter( "wpcf7_validate_{$tag_type}*", array( $this, 'validate_field' ), 20, 2 );
		}

		// Inject frontend rules.
		add_filter( 'wpcf7_form_elements', array( $this, 'inject_frontend_rules' ) );

		// Initialize admin functionality.
		if ( is_admin() ) {
			$admin = new VMCF7_Admin();
			add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
			add_filter( 'wpcf7_editor_panels', array( $admin, 'add_panel' ) );
			add_action( 'wpcf7_save_contact_form', array( $admin, 'save_messages' ) );

			if ( VMCF7_Flavor::is_active() ) {
				add_action( 'wp_ajax_vmcf7_ai_translate', array( $admin, 'ajax_ai_translate' ) );
			}

			add_action( 'wp_ajax_vmcf7_export_rules', array( $admin, 'ajax_export_rules' ) );
			add_action( 'wp_ajax_vmcf7_import_rules', array( $admin, 'ajax_import_rules' ) );
			add_action( 'wp_ajax_vmcf7_copy_rules', array( $admin, 'ajax_copy_rules' ) );
			add_action( 'wp_ajax_vmcf7_bulk_apply', array( $admin, 'ajax_bulk_apply' ) );
		} else {
			add_action( 'wpcf7_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		}

		/**
		 * Fires after the plugin has been initialized.
		 *
		 * @since 1.2.0
		 */
		do_action( 'vmcf7_loaded' );
	}

	/**
	 * Enqueue frontend script conditionally.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		global $post;
		$has_form = false;

		if ( is_a( $post, 'WP_Post' ) ) {
			if ( has_shortcode( $post->post_content, 'contact-form-7' ) ) {
				$has_form = true;
			} elseif ( has_block( 'contact-form-7/contact-form-selector', $post->post_content ) ) {
				$has_form = true;
			}
		}

		if ( $has_form ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Helper to enqueue frontend assets.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_script(
			'vmcf7-frontend',
			VMCF7_URL . 'assets/js/vmcf7-frontend.js',
			array( 'jquery' ),
			VMCF7_VERSION,
			true
		);
		wp_enqueue_style(
			'vmcf7-frontend',
			VMCF7_URL . 'assets/css/vmcf7-frontend.css',
			array(),
			VMCF7_VERSION
		);
	}

	/**
	 * Validate a form field.
	 *
	 * Applies custom validation messages if configured for the field.
	 *
	 * @since 1.0.0
	 *
	 * @param WPCF7_Validation $result The validation result object.
	 * @param WPCF7_FormTag    $tag    The form tag being validated.
	 * @return WPCF7_Validation The modified validation result.
	 */
	public function validate_field( $result, $tag ) {
		$form = wpcf7_get_current_contact_form();
		if ( ! $form || ! $this->is_enabled( $form->id() ) ) {
			return $result;
		}

		$field_name = $this->normalize_field_name( $tag->name );
		if ( ! $field_name ) {
			return $result;
		}

		$value          = $this->get_posted_value( $tag );
		$invalid_fields = $result->get_invalid_fields();
		$already_invalid = isset( $invalid_fields[ $tag->name ] );

		$form_id             = $form->id();
		$required_message    = $this->get_custom_message( $form_id, $field_name, 'required' );
		$invalid_message     = $this->get_custom_message( $form_id, $field_name, 'invalid' );
		$regex               = get_post_meta( $form_id, "_vmcf7_{$field_name}_regex", true );
		$regex_message       = $this->get_custom_message( $form_id, $field_name, 'regex_message' );
		$min_length          = get_post_meta( $form_id, "_vmcf7_{$field_name}_min_length", true );
		$max_length          = get_post_meta( $form_id, "_vmcf7_{$field_name}_max_length", true );
		$length_message      = $this->get_custom_message( $form_id, $field_name, 'length_message' );
		$required_if_field   = get_post_meta( $form_id, "_vmcf7_{$field_name}_required_if_field", true );
		$required_if_message = $this->get_custom_message( $form_id, $field_name, 'required_if_message' );

		// Empty checks
		if ( $this->value_is_empty( $value ) ) {
			// Required-if check
			if ( ! empty( $required_if_field ) ) {
				$companion_value = $this->get_field_value_by_name( $required_if_field );
				if ( ! $this->value_is_empty( $companion_value ) ) {
					$msg = $required_if_message ? $required_if_message : ( $required_message ? $required_message : __( 'This field is required', 'validation-muse-for-contact-form-7' ) );
					$msg = $this->expand_placeholders( $msg, $form, $tag );
					if ( $already_invalid ) {
						$this->replace_error( $result, $tag->name, $msg );
					} else {
						$result->invalidate( $tag, $msg );
					}
					return $result;
				}
			}

			// Normal required check
			if ( $tag->is_required() ) {
				if ( ! empty( $required_message ) ) {
					$msg = $this->expand_placeholders( $required_message, $form, $tag );
					if ( $already_invalid ) {
						$this->replace_error( $result, $tag->name, $msg );
					} else {
						$result->invalidate( $tag, $msg );
					}
					return $result;
				}
			}

			return $result;
		}

		// Field value is NOT empty

		// 1. Regex rule check
		if ( ! empty( $regex ) && ! VMCF7_Rules::evaluate_regex( $value, $regex ) ) {
			$msg = $regex_message ? $regex_message : ( $invalid_message ? $invalid_message : __( 'Invalid format.', 'validation-muse-for-contact-form-7' ) );
			$msg = $this->expand_placeholders( $msg, $form, $tag );
			if ( $already_invalid ) {
				$this->replace_error( $result, $tag->name, $msg );
			} else {
				$result->invalidate( $tag, $msg );
			}
			return $result;
		}

		// 2. Length check
		if ( '' !== $min_length || '' !== $max_length ) {
			$length_res = VMCF7_Rules::evaluate_length( $value, $min_length, $max_length );
			if ( ! $length_res['valid'] ) {
				$msg = $length_message ? $length_message : __( 'Invalid length.', 'validation-muse-for-contact-form-7' );
				$msg = $this->expand_placeholders( $msg, $form, $tag, $min_length, $max_length );
				if ( $already_invalid ) {
					$this->replace_error( $result, $tag->name, $msg );
				} else {
					$result->invalidate( $tag, $msg );
				}
				return $result;
			}
		}

		// 3. Custom invalid message replacement if already invalid (format validation check from CF7/SWV)
		if ( $already_invalid ) {
			if ( ! empty( $invalid_message ) ) {
				$msg = $this->expand_placeholders( $invalid_message, $form, $tag );
				$this->replace_error( $result, $tag->name, $msg );
				return $result;
			}
		} else {
			// If not already invalid, evaluate built-in formats for fields not checked by SWV
			if ( ! empty( $invalid_message ) ) {
				$is_invalid = false;

				switch ( $tag->basetype ) {
					case 'email':
						$is_invalid = ! wpcf7_is_email( $value );
						break;
					case 'url':
						$is_invalid = ! wpcf7_is_url( $value );
						break;
					case 'tel':
						$is_invalid = ! wpcf7_is_tel( $value );
						break;
					case 'number':
					case 'range':
						$is_invalid = ! wpcf7_is_number( $value );
						break;
					case 'date':
						$is_invalid = ! wpcf7_is_date( $value );
						break;
					case 'time':
						$is_invalid = ! wpcf7_is_time( $value );
						break;
				}

				if ( $is_invalid ) {
					$msg = $this->expand_placeholders( $invalid_message, $form, $tag );
					$result->invalidate( $tag, $msg );
				}
			}
		}

		return $result;
	}

	/**
	 * Replace an existing validation error message.
	 *
	 * CF7's WPCF7_Validation::invalidate() skips already-invalid fields
	 * and invalid_fields is private, so we use Reflection to replace the message.
	 *
	 * @since 1.3.0
	 *
	 * @param WPCF7_Validation $result     The validation result.
	 * @param string           $field_name The field name.
	 * @param string           $message    The replacement message.
	 * @return void
	 */
	private function replace_error( $result, $field_name, $message ) {
		try {
			$ref = new \ReflectionProperty( $result, 'invalid_fields' );
			$ref->setAccessible( true );

			$invalid_fields = $ref->getValue( $result );

			if ( isset( $invalid_fields[ $field_name ] ) ) {
				$invalid_fields[ $field_name ]['reason'] = $message;
				$ref->setValue( $result, $invalid_fields );
			}
		} catch ( \ReflectionException $e ) {
			do_action( 'vmcf7_debug', 'Reflection failure: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Validation Muse ReflectionException: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Get the list of tag types that support validation.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of supported tag types.
	 */
	public function get_validation_tag_types() {
		$types = array(
			'text',
			'email',
			'url',
			'tel',
			'number',
			'range',
			'date',
			'time',
			'textarea',
			'select',
			'checkbox',
			'radio',
			'file',
			'acceptance',
			'quiz',
		);

		/**
		 * Filters the list of tag types that support custom validation messages.
		 *
		 * @since 1.2.0
		 *
		 * @param array $types List of supported tag types.
		 */
		return apply_filters( 'vmcf7_validation_tag_types', $types );
	}

	/**
	 * Get the posted value for a tag.
	 *
	 * @since 1.0.0
	 *
	 * @param WPCF7_FormTag $tag The form tag.
	 * @return mixed The posted value or null.
	 */
	private function get_posted_value( $tag ) {
		$submission = WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return null;
		}

		$name = $tag->name;

		// Handle file uploads separately.
		if ( 'file' === $tag->basetype ) {
			$files = $submission->uploaded_files();
			if ( ! isset( $files[ $name ] ) ) {
				return null;
			}

			$file = $files[ $name ];

			if ( is_array( $file ) ) {
				return array_values( array_filter( array_map( 'strval', $file ), 'strlen' ) );
			}

			return (string) $file;
		}

		$value = $submission->get_posted_data( $name );
		if ( null === $value ) {
			return null;
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'wp_unslash', $value );
			return array_map( 'trim', $value );
		}

		return trim( strtr( (string) wp_unslash( $value ), "\n", ' ' ) );
	}

	/**
	 * Check if a value is empty.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is empty.
	 */
	private function value_is_empty( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( '' !== $item ) {
					return false;
				}
			}
			return true;
		}

		return null === $value || '' === $value;
	}

	/**
	 * Check if custom validation is enabled for a form.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id The form ID.
	 * @return bool True if custom validation is enabled.
	 */
	private function is_enabled( $form_id ) {
		return '1' === get_post_meta( $form_id, '_vmcf7_enabled', true );
	}

	/**
	 * Get a custom validation message for a field.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $form_id    The form ID.
	 * @param string $field_name The field name.
	 * @param string $type       The message type ('required' or 'invalid').
	 * @return string The custom message or empty string.
	 */
	private function get_custom_message( $form_id, $field_name, $type ) {
		$field_name = $this->normalize_field_name( $field_name );
		$type       = $this->normalize_field_name( $type );

		if ( ! $field_name || ! $type ) {
			return '';
		}

		$meta_key = sprintf( '_vmcf7_%s_%s', $field_name, $type );
		$message  = get_post_meta( $form_id, $meta_key, true );
		$message  = is_string( $message ) ? $message : '';

		// Allow multilingual plugins like WPML/Polylang to filter this message.
		$translated = apply_filters( 'vmcf7_translate_message', $message, $form_id, $field_name, $type );
		if ( $translated !== $message ) {
			return $translated;
		}

		// Return translated message if available and needed (Flavor).
		if ( '' !== $message && VMCF7_Flavor::needs_translation() ) {
			$flavor_translated = VMCF7_Flavor::get_translation( $form_id, $field_name, $type );
			if ( null !== $flavor_translated ) {
				return $flavor_translated;
			}
		}

		return $message;
	}

	/**
	 * Normalize a field name for use in meta keys.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_name The field name to normalize.
	 * @return string The normalized field name.
	 */
	private function normalize_field_name( $field_name ) {
		return sanitize_key( $field_name );
	}

	/**
	 * Get a field's posted value by field name.
	 *
	 * @since 1.6.0
	 *
	 * @param string $name The field name.
	 * @return mixed The field value or null.
	 */
	private function get_field_value_by_name( $name ) {
		$submission = WPCF7_Submission::get_instance();
		if ( ! $submission ) {
			return null;
		}
		$val = $submission->get_posted_data( $name );
		if ( null === $val ) {
			$uploaded = $submission->uploaded_files();
			if ( isset( $uploaded[ $name ] ) ) {
				$val = $uploaded[ $name ];
			}
		}
		return $val;
	}

	/**
	 * Expand placeholder tokens in a validation message.
	 *
	 * @since 1.6.0
	 *
	 * @param string        $message The validation message.
	 * @param object        $form    The contact form object.
	 * @param WPCF7_FormTag $tag     The form tag object.
	 * @param string        $min     The min length token.
	 * @param string        $max     The max length token.
	 * @return string The expanded message.
	 */
	private function expand_placeholders( $message, $form, $tag, $min = '', $max = '' ) {
		$label = $this->get_field_label( $form, $tag );
		$replacements = array(
			'{field_label}' => $label,
			'{min}'         => $min,
			'{max}'         => $max,
		);
		return strtr( $message, $replacements );
	}

	/**
	 * Find the user-visible label for a form tag within the form layout template.
	 *
	 * @since 1.6.0
	 *
	 * @param object        $form The contact form object.
	 * @param WPCF7_FormTag $tag  The form tag object.
	 * @return string The label text, or prettified field name if not found.
	 */
	private function get_field_label( $form, $tag ) {
		if ( ! $form ) {
			return $tag->name;
		}

		$form_content = $form->prop( 'form' );
		if ( ! $form_content ) {
			return $tag->name;
		}

		$tag_name = preg_quote( $tag->name, '/' );

		// 1. Check if the tag is wrapped in a <label> tag.
		if ( preg_match( '/<label\b[^>]*>(?:(?!<\/label>).)*\b' . $tag_name . '\b(?:(?!<\/label>).)*<\/label>/is', $form_content, $matches ) ) {
			$label_content = $matches[0];
			$label_text = preg_replace( '/\[[^\]]+\]/', '', $label_content );
			$label_text = wp_strip_all_tags( $label_text );
			$label_text = trim( $label_text );
			if ( ! empty( $label_text ) ) {
				return $label_text;
			}
		}

		// 2. Check for <label for="id_or_name"> in the template.
		$id = $tag->get_id_option();
		$for_attr = $id ? '(?:' . preg_quote( $id, '/' ) . '|' . $tag_name . ')' : $tag_name;
		if ( preg_match( '/<label\b[^>]*\bfor=["\']' . $for_attr . '["\'][^>]*>(.*?)<\/label>/is', $form_content, $matches ) ) {
			$label_text = wp_strip_all_tags( trim( $matches[1] ) );
			if ( ! empty( $label_text ) ) {
				return $label_text;
			}
		}

		// Fallback to name capitalized.
		$label_text = str_replace( array( '-', '_' ), ' ', $tag->name );
		return ucwords( $label_text );
	}

	/**
	 * Inject custom rules into field elements as data attributes.
	 *
	 * @since 1.6.0
	 *
	 * @param string $content The form HTML content.
	 * @return string The modified form HTML content.
	 */
	public function inject_frontend_rules( $content ) {
		$form = wpcf7_get_current_contact_form();
		if ( ! $form || ! $this->is_enabled( $form->id() ) ) {
			return $content;
		}

		$form_id = $form->id();
		$tags = $form->scan_form_tags();

		foreach ( $tags as $tag ) {
			if ( empty( $tag->name ) ) {
				continue;
			}

			$field_name = $this->normalize_field_name( $tag->name );
			$attrs = array();

			// 1. Required message
			$req_msg = $this->get_custom_message( $form_id, $field_name, 'required' );
			if ( ! empty( $req_msg ) ) {
				$attrs['data-vmcf7-msg-required'] = $this->expand_placeholders( $req_msg, $form, $tag );
			}

			// 2. Invalid format message
			$inv_msg = $this->get_custom_message( $form_id, $field_name, 'invalid' );
			if ( ! empty( $inv_msg ) ) {
				$attrs['data-vmcf7-msg-invalid'] = $this->expand_placeholders( $inv_msg, $form, $tag );
			}

			// 3. Regex pattern and message
			$regex = get_post_meta( $form_id, "_vmcf7_{$field_name}_regex", true );
			if ( ! empty( $regex ) ) {
				$attrs['data-vmcf7-regex'] = $regex;
				$regex_msg = $this->get_custom_message( $form_id, $field_name, 'regex_message' );
				$attrs['data-vmcf7-msg-regex'] = $regex_msg ? $this->expand_placeholders( $regex_msg, $form, $tag ) : __( 'Invalid format', 'validation-muse-for-contact-form-7' );
			}

			// 4. Min/Max length
			$min_len = get_post_meta( $form_id, "_vmcf7_{$field_name}_min_length", true );
			$max_len = get_post_meta( $form_id, "_vmcf7_{$field_name}_max_length", true );
			if ( '' !== $min_len || '' !== $max_len ) {
				if ( '' !== $min_len ) {
					$attrs['data-vmcf7-min-length'] = $min_len;
				}
				if ( '' !== $max_len ) {
					$attrs['data-vmcf7-max-length'] = $max_len;
				}
				$len_msg = $this->get_custom_message( $form_id, $field_name, 'length_message' );
				$attrs['data-vmcf7-msg-length'] = $len_msg ? $this->expand_placeholders( $len_msg, $form, $tag, $min_len, $max_len ) : __( 'Invalid length', 'validation-muse-for-contact-form-7' );
			}

			// 5. Required-if
			$req_if_field = get_post_meta( $form_id, "_vmcf7_{$field_name}_required_if_field", true );
			if ( ! empty( $req_if_field ) ) {
				$attrs['data-vmcf7-required-if-field'] = sanitize_key( $req_if_field );
				$req_if_msg = $this->get_custom_message( $form_id, $field_name, 'required_if_message' );
				$attrs['data-vmcf7-msg-required-if'] = $req_if_msg ? $this->expand_placeholders( $req_if_msg, $form, $tag ) : ( $req_msg ? $this->expand_placeholders( $req_msg, $form, $tag ) : __( 'This field is required', 'validation-muse-for-contact-form-7' ) );
			}

			if ( empty( $attrs ) ) {
				continue;
			}

			$attr_str = '';
			foreach ( $attrs as $k => $v ) {
				$attr_str .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
			}

			if ( ! is_admin() ) {
				if ( ! wp_script_is( 'vmcf7-frontend', 'enqueued' ) ) {
					$this->enqueue_assets();
				}
			}

			// Inject attributes into matched elements.
			$pattern = '/(<(?:input|select|textarea)\b[^>]*\bname=["\']' . preg_quote( $tag->name, '/' ) . '(?:\[\])?["\'][^>]*)/is';
			$content = preg_replace_callback( $pattern, function( $matches ) use ( $attr_str ) {
				return $matches[1] . $attr_str;
			}, $content );
		}

		return $content;
	}
}