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
		require_once VMCF7_PATH . 'includes/class-vmcf7-review-notice.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-rules.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-i18n-compat.php';
		require_once VMCF7_PATH . 'includes/class-vmcf7-migrations.php';

		// Run one-time data migrations (self-guarded, cheap once applied).
		VMCF7_Migrations::maybe_run();

		$i18n = new VMCF7_I18n_Compat();
		$i18n->init();

		// Register validation filters for each supported tag type.
		// Priority 20 = after CF7 core (10) and SWV, so we can replace error messages.
		foreach ( $this->get_validation_tag_types() as $tag_type ) {
			add_filter( "wpcf7_validate_{$tag_type}", array( $this, 'validate_field' ), 20, 2 );
			add_filter( "wpcf7_validate_{$tag_type}*", array( $this, 'validate_field' ), 20, 2 );
		}

		// Let other code read the messages written here.
		add_filter( 'vmcf7_message', array( $this, 'filter_message' ), 10, 4 );

		// Inject custom rules into SWV schema.
		add_filter( 'wpcf7_swv_create_schema', array( $this, 'add_swv_rules' ), 20, 2 );

		// Initialize admin functionality.
		if ( is_admin() ) {
			$admin = new VMCF7_Admin();
			( new VMCF7_Review_Notice() )->register();
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
			// Forms rendered outside the post content (widgets, templates, builders).
			add_action( 'wpcf7_shortcode_callback', array( $this, 'enqueue_assets_on_render' ) );
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
		wp_enqueue_style(
			'vmcf7-frontend',
			VMCF7_URL . 'assets/css/vmcf7-frontend.css',
			array(),
			VMCF7_VERSION
		);
	}

	/**
	 * Enqueue the frontend stylesheet when a form is actually rendered.
	 *
	 * Covers forms placed where the post content is not the source of the
	 * shortcode: widgets, theme templates, page builders, popups.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function enqueue_assets_on_render() {
		if ( ! wp_style_is( 'vmcf7-frontend', 'enqueued' ) ) {
			$this->enqueue_assets();
		}
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

		$form_id         = $form->id();
		$value           = $this->get_posted_value( $tag );
		$invalid_fields  = $result->get_invalid_fields();
		$already_invalid = isset( $invalid_fields[ $tag->name ] );
		$min_length      = $this->get_length_setting( $form_id, $field_name, 'min_length' );
		$max_length      = $this->get_length_setting( $form_id, $field_name, 'max_length' );

		// Empty checks.
		if ( $this->value_is_empty( $value ) ) {
			$required_message  = $this->get_custom_message( $form_id, $field_name, 'required' );
			$required_if_field = get_post_meta( $form_id, "_vmcf7_{$field_name}_required_if_field", true );

			// Required-if check.
			if ( is_string( $required_if_field ) && '' !== $required_if_field ) {
				$companion_value = $this->get_field_value_by_name( $this->resolve_field_name( $form, $required_if_field ) );
				if ( ! $this->value_is_empty( $companion_value ) ) {
					$required_if_message = $this->get_custom_message( $form_id, $field_name, 'required_if_message' );
					$msg                 = $required_if_message ? $required_if_message : ( $required_message ? $required_message : __( 'This field is required', 'validation-muse-for-contact-form-7' ) );

					return $this->set_error( $result, $tag, $this->expand_placeholders( $msg, $form, $tag, $min_length, $max_length ), $already_invalid );
				}
			}

			// Normal required check. An empty field that CF7 already rejected (a
			// required field, an unticked acceptance box, an unanswered quiz)
			// gets the custom required message too.
			if ( '' !== $required_message && ( $already_invalid || $tag->is_required() ) ) {
				return $this->set_error( $result, $tag, $this->expand_placeholders( $required_message, $form, $tag, $min_length, $max_length ), $already_invalid );
			}

			return $result;
		}

		// Field value is NOT empty.
		$invalid_message = $this->get_custom_message( $form_id, $field_name, 'invalid' );

		if ( $this->supports_text_rules( $tag ) ) {
			// 1. Regex rule check.
			$regex = get_post_meta( $form_id, "_vmcf7_{$field_name}_regex", true );
			if ( is_string( $regex ) && '' !== $regex && ! VMCF7_Rules::evaluate_regex( $value, $regex ) ) {
				$regex_message = $this->get_custom_message( $form_id, $field_name, 'regex_message' );
				$msg           = $regex_message ? $regex_message : ( $invalid_message ? $invalid_message : __( 'Invalid format.', 'validation-muse-for-contact-form-7' ) );

				return $this->set_error( $result, $tag, $this->expand_placeholders( $msg, $form, $tag, $min_length, $max_length ), $already_invalid );
			}

			// 2. Length check.
			if ( '' !== $min_length || '' !== $max_length ) {
				$length_res = VMCF7_Rules::evaluate_length( $value, $min_length, $max_length );
				if ( ! $length_res['valid'] ) {
					$length_message = $this->get_custom_message( $form_id, $field_name, 'length_message' );
					$msg            = $length_message ? $length_message : __( 'Invalid length.', 'validation-muse-for-contact-form-7' );

					return $this->set_error( $result, $tag, $this->expand_placeholders( $msg, $form, $tag, $min_length, $max_length ), $already_invalid );
				}
			}
		}

		if ( '' === $invalid_message ) {
			return $result;
		}

		// 3. Custom invalid message replacement if already invalid (format validation check from CF7/SWV).
		if ( $already_invalid ) {
			return $this->set_error( $result, $tag, $this->expand_placeholders( $invalid_message, $form, $tag, $min_length, $max_length ), true );
		}

		// If not already invalid, evaluate built-in formats for fields not checked by SWV.
		$checks = array(
			'email'  => 'wpcf7_is_email',
			'url'    => 'wpcf7_is_url',
			'tel'    => 'wpcf7_is_tel',
			'number' => 'wpcf7_is_number',
			'range'  => 'wpcf7_is_number',
			'date'   => 'wpcf7_is_date',
			'time'   => 'wpcf7_is_time',
		);

		$check = isset( $checks[ $tag->basetype ] ) ? $checks[ $tag->basetype ] : '';

		if ( $check && is_string( $value ) && function_exists( $check ) && ! $check( $value ) ) {
			$result->invalidate( $tag, $this->expand_placeholders( $invalid_message, $form, $tag, $min_length, $max_length ) );
		}

		return $result;
	}

	/**
	 * Put an error message on a field: replace the one CF7 set, or add a new one.
	 *
	 * @since 1.8.0
	 *
	 * @param WPCF7_Validation $result          The validation result.
	 * @param WPCF7_FormTag    $tag             The form tag.
	 * @param string           $message         The error message.
	 * @param bool             $already_invalid Whether the field already has an error.
	 * @return WPCF7_Validation The validation result.
	 */
	private function set_error( $result, $tag, $message, $already_invalid ) {
		if ( $already_invalid ) {
			$this->replace_error( $result, $tag->name, $message );
		} else {
			$result->invalidate( $tag, $message );
		}

		return $result;
	}

	/**
	 * Whether the regex and length rules apply to a tag.
	 *
	 * File fields hold an upload, not text, so matching a pattern against the
	 * temporary file path or counting its characters makes no sense.
	 *
	 * @since 1.8.0
	 *
	 * @param WPCF7_FormTag $tag The form tag.
	 * @return bool True if regex/length rules apply.
	 */
	private function supports_text_rules( $tag ) {
		/**
		 * Filters whether the regex and min/max length rules apply to a form tag.
		 *
		 * @since 1.8.0
		 *
		 * @param bool          $supports True for every type but `file`.
		 * @param WPCF7_FormTag $tag      The form tag.
		 */
		return (bool) apply_filters( 'vmcf7_supports_text_rules', 'file' !== $tag->basetype, $tag );
	}

	/**
	 * Read a min/max length setting as a positive integer string, or '' when unset.
	 *
	 * @since 1.8.0
	 *
	 * @param int    $form_id    The form ID.
	 * @param string $field_name The normalized field name.
	 * @param string $type       'min_length' or 'max_length'.
	 * @return string The length as a numeric string, or an empty string.
	 */
	private function get_length_setting( $form_id, $field_name, $type ) {
		$raw = get_post_meta( $form_id, "_vmcf7_{$field_name}_{$type}", true );

		if ( ! is_scalar( $raw ) || ! is_numeric( $raw ) || (int) $raw <= 0 ) {
			return '';
		}

		return (string) (int) $raw;
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
			if ( PHP_VERSION_ID < 80100 ) {
				$ref->setAccessible( true );
			}

			$invalid_fields = $ref->getValue( $result );

			if ( isset( $invalid_fields[ $field_name ] ) ) {
				$invalid_fields[ $field_name ]['reason'] = $message;
				$ref->setValue( $result, $invalid_fields );
			}
		} catch ( \ReflectionException $e ) {
			$this->report_reflection_failure( $e );
		}
	}

	/**
	 * Report a Reflection failure without breaking the request.
	 *
	 * @since 1.8.0
	 *
	 * @param \Throwable $e The failure.
	 * @return void
	 */
	private function report_reflection_failure( $e ) {
		do_action( 'vmcf7_debug', 'Reflection failure: ' . $e->getMessage() );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only under WP_DEBUG.
			error_log( 'Validation Muse ReflectionException: ' . $e->getMessage() );
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

		// CF7 has already unslashed the posted data; unslashing again would eat
		// the backslashes a visitor actually typed.
		$value = $submission->get_posted_data( $name );
		if ( null === $value ) {
			return null;
		}

		if ( is_array( $value ) ) {
			return array_map( 'trim', VMCF7_Rules::flatten( $value ) );
		}

		return trim( strtr( (string) $value, "\n", ' ' ) );
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
			foreach ( VMCF7_Rules::flatten( $value ) as $item ) {
				if ( '' !== trim( $item ) ) {
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
	 * Answer the `vmcf7_message` filter: the message written for a field, or the default passed in.
	 *
	 * Lets other code (a theme, a form pipeline) show the same text as the validation does,
	 * without reading the post meta itself. A form that is not enabled has no custom messages.
	 *
	 * @since 1.7.0
	 *
	 * @param string $message    The default, returned when no message was written.
	 * @param int    $form_id    The form ID.
	 * @param string $field_name The field name.
	 * @param string $type       The message type, e.g. 'required' or 'invalid'.
	 * @return string The custom message, or the default.
	 */
	public function filter_message( $message, $form_id, $field_name, $type ) {
		if ( ! $this->is_enabled( $form_id ) ) {
			return $message;
		}

		$custom = $this->get_custom_message( $form_id, (string) $field_name, (string) $type );

		return '' === $custom ? $message : $custom;
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

		if ( '' === $message ) {
			/**
			 * Filters the fallback used when no message was written for a field.
			 *
			 * Return a non-empty string to give every enabled form a site-wide
			 * default for a message type, e.g. one "required" text for all fields.
			 * Placeholder tokens are expanded in it like in any other message.
			 *
			 * @since 1.8.0
			 *
			 * @param string $message    The fallback message. Default empty: use CF7's own text.
			 * @param int    $form_id    The form ID.
			 * @param string $field_name The normalized field name.
			 * @param string $type       The message type: required, invalid, regex_message,
			 *                           length_message or required_if_message.
			 */
			$default = apply_filters( 'vmcf7_default_message', '', $form_id, $field_name, $type );

			return is_string( $default ) ? $default : '';
		}

		// Allow multilingual plugins like WPML/Polylang to filter this message.
		$translated = apply_filters( 'vmcf7_translate_message', $message, $form_id, $field_name, $type );
		if ( $translated !== $message ) {
			return is_string( $translated ) ? $translated : $message;
		}

		// Return translated message if available and needed (Flavor).
		if ( VMCF7_Flavor::needs_translation() ) {
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
	 * Find the real name of a form field from its normalized (meta key) form.
	 *
	 * Rules store the companion field lowercased; the posted data is keyed by
	 * the name as written in the form template, which may contain capitals.
	 *
	 * @since 1.8.0
	 *
	 * @param object $form       The contact form object.
	 * @param string $normalized The normalized field name.
	 * @return string The field name as used in the form, or the input when not found.
	 */
	private function resolve_field_name( $form, $normalized ) {
		if ( $form && method_exists( $form, 'scan_form_tags' ) ) {
			foreach ( (array) $form->scan_form_tags() as $tag ) {
				if ( ! empty( $tag->name ) && $this->normalize_field_name( $tag->name ) === $normalized ) {
					return $tag->name;
				}
			}
		}

		return $normalized;
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
			} elseif ( isset( $_FILES[ $name ]['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only presence check inside CF7's own submission flow.
				// Uploads are moved after the text fields are validated, so a file
				// companion is only visible in the raw request at this point.
				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only tested for emptiness.
				$val = $_FILES[ $name ]['name'];
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
		$message = (string) $message;

		if ( ! str_contains( $message, '{' ) ) {
			return $message;
		}

		$replacements = array(
			'{min}' => (string) $min,
			'{max}' => (string) $max,
		);

		// The label is parsed out of the form template, so only do it on demand.
		if ( str_contains( $message, '{field_label}' ) ) {
			$replacements['{field_label}'] = $this->get_field_label( $form, $tag );
		}

		/**
		 * Filters the placeholder tokens expanded in a validation message.
		 *
		 * Add your own `'{token}' => 'value'` pairs here. `{field_label}` is
		 * only present when the message uses it.
		 *
		 * @since 1.8.0
		 *
		 * @param array         $replacements Token => replacement text.
		 * @param string        $message      The message before expansion.
		 * @param object        $form         The contact form object.
		 * @param WPCF7_FormTag $tag          The form tag object.
		 */
		$replacements = apply_filters( 'vmcf7_placeholders', $replacements, $message, $form, $tag );

		if ( ! is_array( $replacements ) ) {
			return $message;
		}

		return strtr( $message, array_map( 'strval', array_filter( $replacements, 'is_scalar' ) ) );
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
	public function get_field_label( $form, $tag ) {
		$label        = '';
		$form_content = $form ? $form->prop( 'form' ) : '';

		if ( is_string( $form_content ) && '' !== $form_content ) {
			$tag_name = preg_quote( $tag->name, '/' );

			// The tag itself, as written in the template: [type name ...]. Matching
			// the bracketed tag (not the bare word) keeps a field called "name"
			// from picking up another label that merely mentions "name".
			$tag_pattern = '\[[^\]\s]+\s+' . $tag_name . '(?=[\s\]])';

			// 1. Check if the tag is wrapped in a <label> tag.
			if ( preg_match( '/<label\b[^>]*>((?:(?!<\/label>).)*?' . $tag_pattern . '(?:(?!<\/label>).)*)<\/label>/is', $form_content, $matches ) ) {
				$label = $this->clean_label( preg_replace( '/\[[^\]]+\]/', '', $matches[1] ) );
			}

			// 2. A <label> that directly precedes the tag: <label>Email</label>[email* your-email].
			// The label must not hold a form tag itself, or the previous field's
			// <label>Message [textarea your-message]</label> would be taken.
			if ( '' === $label && preg_match( '/<label\b[^>]*>((?:(?!<\/label>)[^\[])*)<\/label>\s*(?:<br\s*\/?>\s*)?' . $tag_pattern . '/is', $form_content, $matches ) ) {
				$label = $this->clean_label( preg_replace( '/\[[^\]]+\]/', '', $matches[1] ) );
			}

			// 3. Check for <label for="id_or_name"> in the template.
			if ( '' === $label ) {
				$id       = method_exists( $tag, 'get_id_option' ) ? $tag->get_id_option() : '';
				$for_attr = $id ? '(?:' . preg_quote( $id, '/' ) . '|' . $tag_name . ')' : $tag_name;
				if ( preg_match( '/<label\b[^>]*\bfor=["\']' . $for_attr . '["\'][^>]*>(.*?)<\/label>/is', $form_content, $matches ) ) {
					$label = $this->clean_label( $matches[1] );
				}
			}
		}

		// Fallback to name capitalized.
		if ( '' === $label ) {
			$label = ucwords( str_replace( array( '-', '_' ), ' ', $tag->name ) );
		}

		/**
		 * Filters the label that replaces `{field_label}` in a message.
		 *
		 * @since 1.8.0
		 *
		 * @param string        $label The label found in the form template, or the prettified field name.
		 * @param WPCF7_FormTag $tag   The form tag object.
		 * @param object        $form  The contact form object.
		 */
		return (string) apply_filters( 'vmcf7_field_label', $label, $tag, $form );
	}

	/**
	 * Turn label markup into plain label text.
	 *
	 * @since 1.8.0
	 *
	 * @param string $html The label's inner HTML.
	 * @return string The label text, without tags, extra whitespace or a trailing required mark / colon.
	 */
	private function clean_label( $html ) {
		$text = wp_strip_all_tags( (string) $html );
		$text = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );

		return rtrim( $text, " *:\t" );
	}

	/**
	 * SWV rules whose error is the field's "invalid" message.
	 *
	 * Mirrors the server-side behaviour, where any error CF7 raised on a filled
	 * field is replaced by the custom invalid message.
	 *
	 * @since 1.8.0
	 *
	 * @return string[] SWV rule names.
	 */
	private function get_swv_invalid_rules() {
		$rules = array(
			'email',
			'url',
			'tel',
			'number',
			'minnumber',
			'maxnumber',
			'stepnumber',
			'date',
			'mindate',
			'maxdate',
			'dayofweek',
			'time',
			'file',
			'minfilesize',
			'maxfilesize',
		);

		/**
		 * Filters the SWV rule names that take the field's custom "invalid" message.
		 *
		 * @since 1.8.0
		 *
		 * @param string[] $rules SWV rule names.
		 */
		return (array) apply_filters( 'vmcf7_swv_invalid_rules', $rules );
	}

	/**
	 * Inject custom rules and error messages into the SWV schema.
	 *
	 * Supports both SWV generations: the `Contactable\SWV` rules of CF7 up to
	 * 6.1 (mutable `properties` array) and the `RockLobsterInc\Swv` rules of
	 * CF7 6.2+ (readonly properties — a rule is rebuilt and swapped in place).
	 *
	 * @since 1.6.1
	 *
	 * @param object $schema       The SWV schema.
	 * @param object $contact_form The contact form.
	 * @return object The modified schema.
	 */
	public function add_swv_rules( $schema, $contact_form ) {
		if ( ! is_object( $schema ) || ! method_exists( $schema, 'rules' ) ) {
			return $schema;
		}

		if ( ! $contact_form || ! $this->is_enabled( $contact_form->id() ) ) {
			return $schema;
		}

		$form_id  = $contact_form->id();
		$tags_map = array();
		foreach ( (array) $contact_form->scan_form_tags() as $tag ) {
			if ( ! empty( $tag->name ) && ! isset( $tags_map[ $tag->name ] ) ) {
				$tags_map[ $tag->name ] = $tag;
			}
		}

		// CF7 up to 6.1 returns a generator here, 6.2+ an array.
		$rules = $schema->rules();
		$rules = is_array( $rules ) ? array_values( $rules ) : iterator_to_array( $rules, false );

		$existing_rules = array();
		$swapped        = false;

		// Iterate over existing rules to override their messages/properties.
		foreach ( $rules as $index => $rule ) {
			$rule_data  = $this->get_swv_rule_data( $rule );
			$rule_name  = isset( $rule_data['rule'] ) && is_string( $rule_data['rule'] ) ? $rule_data['rule'] : '';
			$field_name = isset( $rule_data['field'] ) && is_string( $rule_data['field'] ) ? $rule_data['field'] : '';

			if ( '' === $field_name || '' === $rule_name ) {
				continue;
			}

			$existing_rules[ $field_name ][ $rule_name ] = true;

			if ( ! isset( $tags_map[ $field_name ] ) ) {
				continue;
			}

			$overrides = $this->get_swv_overrides( $form_id, $contact_form, $tags_map[ $field_name ], $rule_name );
			if ( ! $overrides ) {
				continue;
			}

			$new_rule = $this->override_swv_rule( $rule, $rule_data, $overrides );
			if ( $new_rule && $new_rule !== $rule ) {
				$rules[ $index ] = $new_rule;
				$swapped         = true;
			}
		}

		if ( $swapped ) {
			$this->set_swv_rules( $schema, $rules );
		}

		// Add custom rules (minlength, maxlength) if they don't exist.
		if ( ! function_exists( 'wpcf7_swv_create_rule' ) ) {
			return $schema;
		}

		foreach ( $tags_map as $field_name => $tag ) {
			if ( ! $this->supports_text_rules( $tag ) ) {
				continue;
			}

			$normalized_field = $this->normalize_field_name( $field_name );
			$thresholds       = array(
				'minlength' => $this->get_length_setting( $form_id, $normalized_field, 'min_length' ),
				'maxlength' => $this->get_length_setting( $form_id, $normalized_field, 'max_length' ),
			);

			foreach ( $thresholds as $rule_name => $threshold ) {
				if ( '' === $threshold || isset( $existing_rules[ $field_name ][ $rule_name ] ) ) {
					continue;
				}

				$length_message = $this->get_custom_message( $form_id, $normalized_field, 'length_message' );
				$msg            = '' !== $length_message ? $length_message : __( 'Invalid length.', 'validation-muse-for-contact-form-7' );

				$new_rule = $this->create_swv_rule(
					$rule_name,
					array(
						'field'     => $field_name,
						'threshold' => $threshold,
						'error'     => $this->expand_placeholders( $msg, $contact_form, $tag, $thresholds['minlength'], $thresholds['maxlength'] ),
					)
				);

				if ( $new_rule ) {
					if ( method_exists( $schema, 'addRule' ) ) {
						$schema->addRule( $new_rule );
					} elseif ( method_exists( $schema, 'add_rule' ) ) {
						$schema->add_rule( $new_rule );
					}
				}
			}
		}

		return $schema;
	}

	/**
	 * Work out which properties of an existing SWV rule the form's settings override.
	 *
	 * @since 1.8.0
	 *
	 * @param int           $form_id   The form ID.
	 * @param object        $form      The contact form.
	 * @param WPCF7_FormTag $tag       The form tag the rule belongs to.
	 * @param string        $rule_name The SWV rule name.
	 * @return array Property => value pairs; empty when nothing changes.
	 */
	private function get_swv_overrides( $form_id, $form, $tag, $rule_name ) {
		$field     = $this->normalize_field_name( $tag->name );
		$overrides = array();
		$type      = '';

		if ( 'required' === $rule_name || 'requiredfile' === $rule_name ) {
			$type = 'required';
		} elseif ( 'minlength' === $rule_name || 'maxlength' === $rule_name ) {
			$type = 'length_message';
		} elseif ( in_array( $rule_name, $this->get_swv_invalid_rules(), true ) ) {
			$type = 'invalid';
		}

		if ( '' === $type ) {
			return $overrides;
		}

		$min = $this->get_length_setting( $form_id, $field, 'min_length' );
		$max = $this->get_length_setting( $form_id, $field, 'max_length' );

		if ( 'length_message' === $type ) {
			if ( ! $this->supports_text_rules( $tag ) ) {
				return $overrides;
			}

			$threshold = 'minlength' === $rule_name ? $min : $max;
			if ( '' !== $threshold ) {
				$overrides['threshold'] = $threshold;
			}
		}

		$message = $this->get_custom_message( $form_id, $field, $type );
		if ( '' !== $message ) {
			$overrides['error'] = $this->expand_placeholders( $message, $form, $tag, $min, $max );
		}

		return $overrides;
	}

	/**
	 * Read an SWV rule's properties as an array, whichever SWV generation it is.
	 *
	 * @since 1.8.0
	 *
	 * @param object $rule The SWV rule.
	 * @return array The rule properties ('rule', 'field', 'error', ...).
	 */
	private function get_swv_rule_data( $rule ) {
		$data = array();

		if ( is_object( $rule ) ) {
			if ( method_exists( $rule, 'toArray' ) ) {
				$data = $rule->toArray();
			} elseif ( method_exists( $rule, 'to_array' ) ) {
				$data = $rule->to_array();
			}
		}

		$data = is_array( $data ) ? $data : array();

		if ( empty( $data['field'] ) && is_object( $rule ) && method_exists( $rule, 'get_property' ) ) {
			$data['field'] = $rule->get_property( 'field' );
		}

		return $data;
	}

	/**
	 * Apply property overrides to an SWV rule.
	 *
	 * Legacy rules are changed in place. CF7 6.2+ rules are immutable, so a new
	 * rule of the same kind is built from the old one's properties.
	 *
	 * @since 1.8.0
	 *
	 * @param object $rule      The SWV rule.
	 * @param array  $rule_data The rule's current properties.
	 * @param array  $overrides Property => value pairs to change.
	 * @return object|null The rule to keep in the schema (the same object when
	 *                     changed in place), or null when it could not be changed.
	 */
	private function override_swv_rule( $rule, $rule_data, $overrides ) {
		if ( property_exists( $rule, 'properties' ) ) {
			foreach ( $overrides as $key => $value ) {
				$this->set_swv_property( $rule, $key, $value );
			}
			return $rule;
		}

		$properties = array_merge( $rule_data, $overrides );
		unset( $properties['rule'] );

		return $this->create_swv_rule( $rule_data['rule'], $properties );
	}

	/**
	 * Create an SWV rule through CF7, never letting a failure reach the visitor.
	 *
	 * @since 1.8.0
	 *
	 * @param string $rule_name  The SWV rule name.
	 * @param array  $properties The rule properties.
	 * @return object|null The rule, or null on failure.
	 */
	private function create_swv_rule( $rule_name, $properties ) {
		if ( ! function_exists( 'wpcf7_swv_create_rule' ) ) {
			return null;
		}

		// CF7 6.2+ declares `threshold` and `error` as strings.
		foreach ( array( 'threshold', 'error', 'field' ) as $key ) {
			if ( isset( $properties[ $key ] ) && is_scalar( $properties[ $key ] ) ) {
				$properties[ $key ] = (string) $properties[ $key ];
			}
		}

		try {
			$rule = wpcf7_swv_create_rule( $rule_name, $properties );
		} catch ( \Throwable $e ) {
			$this->report_reflection_failure( $e );
			return null;
		}

		return is_object( $rule ) ? $rule : null;
	}

	/**
	 * Replace the rule list of an SWV schema.
	 *
	 * The schema only offers "add", so swapping a rebuilt rule into its original
	 * position goes through Reflection on the protected `rules` property.
	 *
	 * @since 1.8.0
	 *
	 * @param object $schema The SWV schema.
	 * @param array  $rules  The full rule list.
	 * @return void
	 */
	private function set_swv_rules( $schema, $rules ) {
		try {
			$ref = new \ReflectionProperty( $schema, 'rules' );
			if ( PHP_VERSION_ID < 80100 ) {
				$ref->setAccessible( true );
			}
			$ref->setValue( $schema, array_values( $rules ) );
		} catch ( \Throwable $e ) {
			$this->report_reflection_failure( $e );
		}
	}

	/**
	 * Set a protected property on a legacy (CF7 ≤ 6.1) SWV rule via Reflection.
	 *
	 * @since 1.6.1
	 *
	 * @param object $rule  The rule object.
	 * @param string $key   The property key.
	 * @param mixed  $value The property value.
	 * @return void
	 */
	private function set_swv_property( $rule, $key, $value ) {
		try {
			$ref = new \ReflectionProperty( $rule, 'properties' );
			if ( PHP_VERSION_ID < 80100 ) {
				$ref->setAccessible( true );
			}

			$properties = $ref->getValue( $rule );
			if ( is_array( $properties ) ) {
				$properties[ $key ] = $value;
				$ref->setValue( $rule, $properties );
			}
		} catch ( \Throwable $e ) {
			$this->report_reflection_failure( $e );
		}
	}
}
