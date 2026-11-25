<?php
/**
 * Admin functionality for the plugin.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 *
 * Handles admin panel, scripts, and saving custom validation messages.
 *
 * @since 1.0.0
 */
class VMCF7_Admin {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Constructor is intentionally empty.
		// Capability checks are performed in individual methods.
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! str_contains( $hook, 'wpcf7' ) ) {
			return;
		}

		wp_enqueue_style(
			'vmcf7-admin',
			VMCF7_URL . 'admin/css/vmcf7-admin.css',
			array(),
			VMCF7_VERSION
		);

		wp_enqueue_script(
			'vmcf7-admin',
			VMCF7_URL . 'admin/js/vmcf7-admin.js',
			array( 'jquery' ),
			VMCF7_VERSION,
			true
		);

		$script_data = array(
			'nonce'    => wp_create_nonce( 'vmcf7_ajax_nonce' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);

		if ( VMCF7_Flavor::is_active() ) {
			$script_data['flavor'] = array(
				'active'       => true,
				'ai_available' => VMCF7_Flavor::is_ai_available(),
				'languages'    => VMCF7_Flavor::get_target_languages(),
				'nonce'        => wp_create_nonce( 'vmcf7_flavor_nonce' ),
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
			);
		}

		wp_localize_script( 'vmcf7-admin', 'vmcf7', $script_data );
	}

	/**
	 * Add custom validation panel to Contact Form 7 editor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $panels Existing editor panels.
	 * @return array Modified panels array.
	 */
	public function add_panel( $panels ) {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			return $panels;
		}

		$panels['custom-validation'] = array(
			'title'    => __( 'Custom Validation', 'validation-muse-for-contact-form-7' ),
			'callback' => array( $this, 'display_panel' ),
		);

		return $panels;
	}

	/**
	 * Display the custom validation panel.
	 *
	 * @since 1.0.0
	 *
	 * @param WPCF7_ContactForm $post The contact form object.
	 * @return void
	 */
	public function display_panel( $post ) {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			return;
		}

		$form_id      = absint( $post->id() );
		$vmcf7_fields = $this->get_form_fields( $post );
		$enabled      = get_post_meta( $form_id, '_vmcf7_enabled', true );

		$vmcf7_string_translation = $this->get_string_translation_screens();

		include VMCF7_PATH . 'admin/views/panel.php';
	}

	/**
	 * Where the messages can be translated when WPML or Polylang handles them.
	 *
	 * Empty when Flavor is active (the panel then has its own language tabs)
	 * or when neither plugin is present.
	 *
	 * @since 1.8.0
	 *
	 * @return array[] List of array{name:string,path:string,url:string}.
	 */
	private function get_string_translation_screens() {
		if ( VMCF7_Flavor::is_active() ) {
			return array();
		}

		$domain  = 'validation-muse-for-contact-form-7';
		$screens = array();

		// Polylang emulates the icl_* API, so detect WPML String Translation itself.
		if ( defined( 'WPML_ST_VERSION' ) ) {
			$screens[] = array(
				'name' => 'WPML',
				'path' => __( 'WPML → String Translation', 'validation-muse-for-contact-form-7' ),
				'url'  => admin_url( 'admin.php?page=wpml-string-translation/menu/string-translation.php&context=' . $domain ),
			);
		}

		if ( function_exists( 'pll_register_string' ) ) {
			$screens[] = array(
				'name' => 'Polylang',
				'path' => __( 'Languages → Translations', 'validation-muse-for-contact-form-7' ),
				'url'  => admin_url( 'admin.php?page=mlang_strings&group=' . $domain ),
			);
		}

		return $screens;
	}

	/**
	 * Get form fields that support custom validation.
	 *
	 * @since 1.0.0
	 *
	 * @param WPCF7_ContactForm $post The contact form object.
	 * @return array Array of field data.
	 */
	private function get_form_fields( $post ) {
		$fields  = array();
		$tags    = $post->scan_form_tags();
		$form_id = $post->id();

		// Preload Flavor translations for all languages.
		$flavor_translations = array();
		if ( VMCF7_Flavor::is_active() ) {
			foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
				$flavor_translations[ $code ] = VMCF7_Flavor::get_all_translations( $form_id, $code );
			}
		}

		$loader        = new VMCF7_Loader();
		$allowed_types = $loader->get_validation_tag_types();

		foreach ( $tags as $tag ) {
			if ( empty( $tag->name ) ) {
				continue;
			}
			$basetype = sanitize_key( $tag->basetype );
			if ( ! in_array( $basetype, $allowed_types, true ) ) {
				continue;
			}

			$name = sanitize_key( $tag->name );
			if ( isset( $fields[ $name ] ) ) {
				continue;
			}

			$field_data = array(
				'name'                => $name,
				'type'                => $basetype,
				'label'               => $loader->get_field_label( $post, $tag ),
				'required'            => $tag->is_required(),
				'required_message'    => $this->get_message( $form_id, $tag->name, 'required' ),
				'invalid_message'     => $this->get_message( $form_id, $tag->name, 'invalid' ),
				'regex'               => get_post_meta( $form_id, "_vmcf7_{$name}_regex", true ),
				'regex_message'       => $this->get_message( $form_id, $tag->name, 'regex_message' ),
				'min_length'          => get_post_meta( $form_id, "_vmcf7_{$name}_min_length", true ),
				'max_length'          => get_post_meta( $form_id, "_vmcf7_{$name}_max_length", true ),
				'length_message'      => $this->get_message( $form_id, $tag->name, 'length_message' ),
				'required_if_field'   => get_post_meta( $form_id, "_vmcf7_{$name}_required_if_field", true ),
				'required_if_message' => $this->get_message( $form_id, $tag->name, 'required_if_message' ),
			);

			// Hints for the panel: a stored pattern that does not compile is
			// ignored at validation time, and file fields take no text rules.
			$field_data['regex_invalid'] = is_string( $field_data['regex'] ) && '' !== $field_data['regex'] && ! VMCF7_Rules::is_valid_regex( $field_data['regex'] );
			$field_data['text_rules']    = 'file' !== $basetype;

			// Add per-language translations.
			if ( ! empty( $flavor_translations ) ) {
				$field_data['translations'] = array();

				foreach ( $flavor_translations as $code => $translations ) {
					$field_data['translations'][ $code ] = array(
						'required'            => isset( $translations[ VMCF7_Flavor::field_key( $name, 'required' ) ] ) ? $translations[ VMCF7_Flavor::field_key( $name, 'required' ) ] : '',
						'invalid'             => isset( $translations[ VMCF7_Flavor::field_key( $name, 'invalid' ) ] ) ? $translations[ VMCF7_Flavor::field_key( $name, 'invalid' ) ] : '',
						'regex_message'       => isset( $translations[ VMCF7_Flavor::field_key( $name, 'regex_message' ) ] ) ? $translations[ VMCF7_Flavor::field_key( $name, 'regex_message' ) ] : '',
						'length_message'      => isset( $translations[ VMCF7_Flavor::field_key( $name, 'length_message' ) ] ) ? $translations[ VMCF7_Flavor::field_key( $name, 'length_message' ) ] : '',
						'required_if_message' => isset( $translations[ VMCF7_Flavor::field_key( $name, 'required_if_message' ) ] ) ? $translations[ VMCF7_Flavor::field_key( $name, 'required_if_message' ) ] : '',
					);
				}
			}

			$fields[ $name ] = $field_data;
		}

		return array_values( $fields );
	}

	/**
	 * Get a saved validation message.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $form_id    The form ID.
	 * @param string $field_name The field name.
	 * @param string $type       The message type.
	 * @return string The saved message or empty string.
	 */
	private function get_message( $form_id, $field_name, $type ) {
		$field_name = sanitize_key( $field_name );
		$type       = sanitize_key( $type );

		if ( ! $field_name || ! $type ) {
			return '';
		}

		$meta_key = sprintf( '_vmcf7_%s_%s', $field_name, $type );
		$message  = get_post_meta( $form_id, $meta_key, true );

		return is_string( $message ) ? wp_kses_post( $message ) : '';
	}

	/**
	 * Save custom validation messages.
	 *
	 * @since 1.0.0
	 *
	 * @param WPCF7_ContactForm $contact_form The contact form being saved.
	 * @return void
	 */
	public function save_messages( $contact_form ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified below.
		if ( ! isset( $_POST['vmcf7_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['vmcf7_nonce'] ) );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) || ! wp_verify_nonce( $nonce, 'vmcf7_save_messages' ) ) {
			return;
		}

		$form_id = absint( $contact_form->id() );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$enabled = isset( $_POST['vmcf7_enabled'] ) ? '1' : '0';
		update_post_meta( $form_id, '_vmcf7_enabled', $enabled );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		if ( ! isset( $_POST['vmcf7'] ) || ! is_array( $_POST['vmcf7'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above. Data is sanitized in the loop below.
		$fields = wp_unslash( $_POST['vmcf7'] );

		foreach ( $fields as $field_name => $messages ) {
			if ( ! is_array( $messages ) ) {
				continue;
			}

			$field_name = sanitize_key( $field_name );
			if ( ! $field_name ) {
				continue;
			}

			foreach ( $messages as $type => $message ) {
				$type = sanitize_key( $type );
				if ( ! $type ) {
					continue;
				}

				$meta_key = sprintf( '_vmcf7_%s_%s', $field_name, $type );

				$clean_value = $this->sanitize_rule_value( $type, $message );

				if ( '' === $clean_value ) {
					delete_post_meta( $form_id, $meta_key );
					continue;
				}

				// update_post_meta() unslashes its input: slash first so backslashes
				// (every other regex has one) survive.
				update_post_meta( $form_id, $meta_key, wp_slash( $clean_value ) );
				VMCF7_Review_Notice::record_success();
			}
		}

		// Save Flavor translations.
		$this->save_flavor_translations( $form_id );
	}

	/**
	 * Save Flavor translations from POST data.
	 *
	 * @since 1.4.0
	 *
	 * @param int $form_id The form ID.
	 * @return void
	 */
	private function save_flavor_translations( $form_id ) {
		if ( ! VMCF7_Flavor::is_active() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in save_messages().
		if ( ! isset( $_POST['vmcf7_translations'] ) || ! is_array( $_POST['vmcf7_translations'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in save_messages(). Data is sanitized in the loop below.
		$translations = wp_unslash( $_POST['vmcf7_translations'] );

		foreach ( $translations as $lang => $fields ) {
			$lang = sanitize_key( $lang );
			if ( ! $lang || ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field_name => $messages ) {
				$field_name = sanitize_key( $field_name );
				if ( ! $field_name || ! is_array( $messages ) ) {
					continue;
				}

				foreach ( $messages as $type => $message ) {
					$type = sanitize_key( $type );

					// Only messages are translated; rule settings are shared by all languages.
					if ( ! in_array( $type, VMCF7_Rules::MESSAGE_TYPES, true ) ) {
						continue;
					}

					$clean_message = $this->sanitize_rule_value( $type, $message );

					if ( '' === $clean_message ) {
						VMCF7_Flavor::delete_translation( $form_id, $field_name, $type, $lang );
					} else {
						VMCF7_Flavor::save_translation( $form_id, $field_name, $type, $lang, $clean_message );
					}
				}
			}
		}
	}

	/**
	 * Sanitize a rule value based on its type.
	 *
	 * @since 1.6.0
	 *
	 * @param string $type  The rule type.
	 * @param mixed  $value The value to sanitize.
	 * @return mixed The sanitized value; an empty string means "not set".
	 */
	public function sanitize_rule_value( $type, $value ) {
		// A nested array (crafted request, hand-edited import file) is not a value.
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );

		if ( 'required_if_field' === $type ) {
			return sanitize_key( $value );
		}

		if ( 'min_length' === $type || 'max_length' === $type ) {
			// Zero and negative lengths mean "no limit": a maximum of 0 would
			// reject every answer.
			return ( is_numeric( $value ) && (int) $value > 0 ) ? (int) $value : '';
		}

		if ( 'regex' === $type ) {
			return $value;
		}

		return trim( wp_kses_post( $value ) );
	}

	/**
	 * Get default invalid message for a field type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The field type.
	 * @return string The default message.
	 */
	public function get_default_invalid_message( $type ) {
		$messages = array(
			'email'  => __( 'Please enter a valid email address', 'validation-muse-for-contact-form-7' ),
			'url'    => __( 'Please enter a valid URL', 'validation-muse-for-contact-form-7' ),
			'tel'    => __( 'Please enter a valid phone number', 'validation-muse-for-contact-form-7' ),
			'number' => __( 'Please enter a valid number', 'validation-muse-for-contact-form-7' ),
			'range'  => __( 'Please enter a valid number', 'validation-muse-for-contact-form-7' ),
			'date'   => __( 'Please enter a valid date', 'validation-muse-for-contact-form-7' ),
			'time'   => __( 'Please enter a valid time', 'validation-muse-for-contact-form-7' ),
			'quiz'   => __( 'The answer is not correct', 'validation-muse-for-contact-form-7' ),
			'file'   => __( 'This file cannot be uploaded', 'validation-muse-for-contact-form-7' ),
		);

		return isset( $messages[ $type ] ) ? $messages[ $type ] : '';
	}

	/**
	 * Verify the nonce and the general capability of a rule-set AJAX request.
	 *
	 * @since 1.8.0
	 * @return void Sends a JSON error and exits on failure.
	 */
	private function verify_rules_request() {
		check_ajax_referer( 'vmcf7_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}
	}

	/**
	 * Whether an ID is a Contact Form 7 form the current user may edit.
	 *
	 * @since 1.8.0
	 *
	 * @param int $form_id The post ID.
	 * @return bool True for an editable CF7 form.
	 */
	private function is_editable_form( $form_id ) {
		$form_id = absint( $form_id );

		return $form_id
			&& 'wpcf7_contact_form' === get_post_type( $form_id )
			&& current_user_can( 'wpcf7_edit_contact_form', $form_id );
	}

	/**
	 * Read a form's rule set: every `_vmcf7_*` meta row, first value each.
	 *
	 * @since 1.8.0
	 *
	 * @param int $form_id The form ID.
	 * @return array Meta key => value.
	 */
	private function get_rule_set( $form_id ) {
		$rules = array();
		$meta  = get_post_meta( $form_id );

		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( str_starts_with( (string) $key, '_vmcf7_' ) ) {
					$rules[ $key ] = is_array( $values ) && isset( $values[0] ) ? $values[0] : '';
				}
			}
		}

		return $rules;
	}

	/**
	 * Replace a form's rule set with the given one.
	 *
	 * Unknown keys are dropped and every value is sanitized as on a normal save.
	 *
	 * @since 1.8.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $rules   Meta key => value.
	 * @return void
	 */
	private function replace_rule_set( $form_id, $rules ) {
		foreach ( array_keys( $this->get_rule_set( $form_id ) ) as $key ) {
			delete_post_meta( $form_id, $key );
		}

		foreach ( (array) $rules as $key => $value ) {
			if ( '_vmcf7_enabled' === $key ) {
				update_post_meta( $form_id, '_vmcf7_enabled', ( is_scalar( $value ) && '1' === (string) $value ) ? '1' : '0' );
				continue;
			}

			$parts = VMCF7_Rules::parse_key( $key );
			if ( ! $parts || ! str_starts_with( $key, '_' ) ) {
				continue;
			}

			$clean_val = $this->sanitize_rule_value( $parts['type'], $value );
			if ( '' === $clean_val ) {
				continue;
			}

			// update_post_meta() unslashes its input: slash first so backslashes
			// (every other regex has one) survive.
			update_post_meta( $form_id, $key, wp_slash( $clean_val ) );
		}
	}

	/**
	 * Read a form's Flavor translations for every target language.
	 *
	 * @since 1.8.0
	 *
	 * @param int $form_id The form ID.
	 * @return array Language code => ( translation key => text ). Empty without Flavor.
	 */
	private function get_translation_sets( $form_id ) {
		$sets = array();

		if ( VMCF7_Flavor::is_active() ) {
			foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
				$sets[ $code ] = VMCF7_Flavor::export_language_set( $form_id, $code );
			}
		}

		return $sets;
	}

	/**
	 * Replace a form's Flavor translations with the given sets.
	 *
	 * Languages that are not enabled on this site are skipped.
	 *
	 * @since 1.8.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $sets    Language code => ( translation key => text ).
	 * @return void
	 */
	private function replace_translation_sets( $form_id, $sets ) {
		if ( ! VMCF7_Flavor::is_active() ) {
			return;
		}

		$languages = VMCF7_Flavor::get_target_languages();

		foreach ( $languages as $code => $lang_data ) {
			foreach ( VMCF7_Flavor::get_all_translations( $form_id, $code ) as $field_key => $unused ) {
				$parts = VMCF7_Rules::parse_key( $field_key );
				if ( $parts ) {
					VMCF7_Flavor::delete_translation( $form_id, $parts['field'], $parts['type'], $code );
				}
			}
		}

		if ( ! is_array( $sets ) ) {
			return;
		}

		foreach ( $sets as $code => $translations ) {
			if ( isset( $languages[ $code ] ) && is_array( $translations ) ) {
				VMCF7_Flavor::import_language_set( $form_id, $code, $translations );
			}
		}
	}

	/**
	 * AJAX handler for AI translation of form messages.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function ajax_ai_translate() {
		check_ajax_referer( 'vmcf7_flavor_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}

		if ( ! VMCF7_Flavor::is_active() ) {
			wp_send_json_error( array( 'message' => __( 'Flavor plugin is not active.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( ! VMCF7_Flavor::is_ai_available() ) {
			wp_send_json_error( array( 'message' => __( 'AI translation provider is not configured. Please check Flavor settings.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$form_id  = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$language = isset( $_POST['language'] ) ? sanitize_key( wp_unslash( $_POST['language'] ) ) : '';

		if ( ! $form_id || ! $language ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID or language.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Validate form ID belongs to a CF7 form.
		if ( ! $this->is_editable_form( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Validate language against enabled target languages.
		$valid_languages = array_map( 'strval', array_keys( VMCF7_Flavor::get_target_languages() ) );
		if ( ! in_array( $language, $valid_languages, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid target language.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Check if we have source texts to translate.
		if ( empty( VMCF7_Flavor::get_source_messages( $form_id ) ) ) {
			wp_send_json_error( array( 'message' => __( 'No custom validation messages written yet. Please write and save them in the main language first.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$translations = VMCF7_Flavor::ai_translate_form( $form_id, $language );

		if ( empty( $translations ) ) {
			wp_send_json_error( array( 'message' => __( 'AI translation service failed. Please check your network connection or provider limits.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		wp_send_json_success( array( 'translations' => $translations ) );
	}

	/**
	 * AJAX handler to export rules as JSON.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_export_rules() {
		$this->verify_rules_request();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified in verify_rules_request().
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		if ( ! $form_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( ! $this->is_editable_form( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$export_data = array(
			'rules'        => $this->get_rule_set( $form_id ),
			'translations' => $this->get_translation_sets( $form_id ),
		);

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="vmcf7-rules-form-' . $form_id . '.json"' );
		echo wp_json_encode( $export_data );
		exit;
	}

	/**
	 * AJAX handler to import rules from JSON file.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_import_rules() {
		$this->verify_rules_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_rules_request().
		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( ! $form_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( ! $this->is_editable_form( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( empty( $_FILES['import_file'] ) || empty( $_FILES['import_file']['tmp_name'] ) || ! is_string( $_FILES['import_file']['tmp_name'] ) || ! empty( $_FILES['import_file']['error'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$file_name = isset( $_FILES['import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['import_file']['name'] ) ) : '';
		$ext       = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( 'json' !== strtolower( $ext ) ) {
			wp_send_json_error( array( 'message' => __( 'Only JSON files are allowed.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Server-generated temporary path.
		$tmp_name = $_FILES['import_file']['tmp_name'];
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// A rule set is a few kilobytes; refuse anything that is clearly not one.
		$size = @filesize( $tmp_name ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( false === $size || $size > MB_IN_BYTES ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type. Only JSON is allowed.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$mime_type = '';
		if ( function_exists( 'finfo_open' ) ) {
			$finfo     = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = $finfo ? finfo_file( $finfo, $tmp_name ) : '';
		} elseif ( function_exists( 'mime_content_type' ) ) {
			$mime_type = mime_content_type( $tmp_name );
		}

		// An unknown type (no fileinfo) is settled by the JSON decode below.
		$allowed_mimes = array( 'application/json', 'text/plain', 'text/json', 'application/x-json', 'application/x-ndjson' );
		if ( $mime_type && ! in_array( $mime_type, $allowed_mimes, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type. Only JSON is allowed.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local uploaded temp file.
		$json_content = file_get_contents( $tmp_name );
		$data         = is_string( $json_content ) ? json_decode( $json_content, true ) : null;

		if ( ! is_array( $data ) || ! isset( $data['rules'] ) || ! is_array( $data['rules'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON format.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$this->replace_rule_set( $form_id, $data['rules'] );
		$this->replace_translation_sets( $form_id, isset( $data['translations'] ) ? $data['translations'] : array() );

		wp_send_json_success( array( 'message' => __( 'Rules imported successfully.', 'validation-muse-for-contact-form-7' ) ) );
	}

	/**
	 * AJAX handler to copy rules from another form.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_copy_rules() {
		$this->verify_rules_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_rules_request().
		$from_id = isset( $_POST['from_form_id'] ) ? absint( $_POST['from_form_id'] ) : 0;
		$to_id   = isset( $_POST['to_form_id'] ) ? absint( $_POST['to_form_id'] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $from_id || ! $to_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing source or target form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Copying a form onto itself would clear it first and then copy nothing.
		if ( $from_id === $to_id || ! $this->is_editable_form( $from_id ) || ! $this->is_editable_form( $to_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Read the source in full before the target is touched.
		$rules        = $this->get_rule_set( $from_id );
		$translations = $this->get_translation_sets( $from_id );

		$this->replace_rule_set( $to_id, $rules );
		$this->replace_translation_sets( $to_id, $translations );

		wp_send_json_success( array( 'message' => __( 'Rules copied successfully.', 'validation-muse-for-contact-form-7' ) ) );
	}

	/**
	 * AJAX handler for bulk actions (save default template, apply default, bulk apply to all).
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_bulk_apply() {
		$this->verify_rules_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_rules_request().
		$form_id     = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$action_type = isset( $_POST['action_type'] ) ? sanitize_key( wp_unslash( $_POST['action_type'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $form_id || ! $action_type ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID or action type.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( ! $this->is_editable_form( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( 'save_default' === $action_type ) {
			update_option(
				'vmcf7_default_template',
				array(
					'rules'        => $this->get_rule_set( $form_id ),
					'translations' => $this->get_translation_sets( $form_id ),
				),
				false
			);

			wp_send_json_success( array( 'message' => __( 'Default template saved successfully.', 'validation-muse-for-contact-form-7' ) ) );
		}

		if ( 'apply_default' === $action_type ) {
			$template = get_option( 'vmcf7_default_template' );
			if ( ! is_array( $template ) || ! isset( $template['rules'] ) || ! is_array( $template['rules'] ) ) {
				wp_send_json_error( array( 'message' => __( 'No default template saved yet.', 'validation-muse-for-contact-form-7' ) ), 400 );
			}

			$this->replace_rule_set( $form_id, $template['rules'] );

			if ( isset( $template['translations'] ) ) {
				$this->replace_translation_sets( $form_id, $template['translations'] );
			}

			wp_send_json_success( array( 'message' => __( 'Default template applied successfully.', 'validation-muse-for-contact-form-7' ) ) );
		}

		if ( 'bulk_apply_all' === $action_type ) {
			$other_ids = get_posts(
				array(
					'post_type'      => 'wpcf7_contact_form',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'exclude'        => array( $form_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- One admin action, one ID.
				)
			);

			if ( empty( $other_ids ) ) {
				wp_send_json_success( array( 'message' => __( 'No other forms to apply templates to.', 'validation-muse-for-contact-form-7' ) ) );
			}

			$rules        = $this->get_rule_set( $form_id );
			$translations = $this->get_translation_sets( $form_id );
			$applied      = 0;

			foreach ( $other_ids as $other_id ) {
				$other_id = absint( $other_id );
				if ( ! $other_id || ! current_user_can( 'wpcf7_edit_contact_form', $other_id ) ) {
					continue;
				}

				$this->replace_rule_set( $other_id, $rules );
				$this->replace_translation_sets( $other_id, $translations );
				++$applied;
			}

			wp_send_json_success( array( 'message' => sprintf( /* translators: %d: number of forms the rules were applied to. */ __( 'Rules applied to %d other forms successfully.', 'validation-muse-for-contact-form-7' ), $applied ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Invalid bulk action type.', 'validation-muse-for-contact-form-7' ) ), 400 );
	}
}
