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
			'nonce' => wp_create_nonce( 'vmcf7_ajax_nonce' ),
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

		include VMCF7_PATH . 'admin/views/panel.php';
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

				update_post_meta( $form_id, $meta_key, $clean_value );
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
					$type          = sanitize_key( $type );
					$clean_message = wp_kses_post( $message );

					if ( ! $type ) {
						continue;
					}

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
	 * @return mixed The sanitized value.
	 */
	public function sanitize_rule_value( $type, $value ) {
		if ( 'required_if_field' === $type ) {
			return sanitize_key( $value );
		} elseif ( 'min_length' === $type || 'max_length' === $type ) {
			return '' !== trim( $value ) ? intval( $value ) : '';
		} elseif ( 'regex' === $type ) {
			return trim( $value );
		} else {
			return wp_kses_post( $value );
		}
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
		);

		return isset( $messages[ $type ] ) ? $messages[ $type ] : '';
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
		$form_post = get_post( $form_id );
		if ( ! $form_post || 'wpcf7_contact_form' !== $form_post->post_type ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Validate language against enabled target languages.
		$valid_languages = array_keys( VMCF7_Flavor::get_target_languages() );
		if ( ! in_array( $language, $valid_languages, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid target language.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Check if we have source texts to translate.
		$meta  = get_post_meta( $form_id );
		$texts = array();
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( ! str_starts_with( $key, '_vmcf7_' ) || '_vmcf7_enabled' === $key ) {
					continue;
				}
				$val = isset( $values[0] ) ? $values[0] : '';
				if ( ! empty( $val ) ) {
					$texts[] = $val;
				}
			}
		}

		if ( empty( $texts ) ) {
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
	/**
	 * AJAX handler to export rules as JSON.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_export_rules() {
		check_ajax_referer( 'vmcf7_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}

		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		if ( ! $form_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$meta = get_post_meta( $form_id );
		$rules = array();
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( str_starts_with( $key, '_vmcf7_' ) ) {
					$rules[ $key ] = isset( $values[0] ) ? $values[0] : '';
				}
			}
		}

		$translations = array();
		if ( VMCF7_Flavor::is_active() ) {
			foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
				$translations[ $code ] = VMCF7_Flavor::export_language_set( $form_id, $code );
			}
		}

		$export_data = array(
			'rules'        => $rules,
			'translations' => $translations,
		);

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
		check_ajax_referer( 'vmcf7_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( ! $form_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( empty( $_FILES['import_file'] ) || empty( $_FILES['import_file']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$file_name = isset( $_FILES['import_file']['name'] ) ? $_FILES['import_file']['name'] : '';
		$ext = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( 'json' !== strtolower( $ext ) ) {
			wp_send_json_error( array( 'message' => __( 'Only JSON files are allowed.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$tmp_name  = $_FILES['import_file']['tmp_name'];
		$mime_type = '';
		if ( function_exists( 'finfo_open' ) ) {
			$finfo     = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $tmp_name );
			finfo_close( $finfo );
		} elseif ( function_exists( 'mime_content_type' ) ) {
			$mime_type = mime_content_type( $tmp_name );
		} else {
			$mime_type = isset( $_FILES['import_file']['type'] ) ? $_FILES['import_file']['type'] : '';
		}

		$allowed_mimes = array( 'application/json', 'text/plain', 'text/json', 'application/x-json' );
		if ( ! in_array( $mime_type, $allowed_mimes, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type. Only JSON is allowed.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		$json_content = file_get_contents( $tmp_name );
		$data         = json_decode( $json_content, true );

		if ( ! is_array( $data ) || ! isset( $data['rules'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON format.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Clear target rules
		$meta = get_post_meta( $form_id );
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( str_starts_with( $key, '_vmcf7_' ) ) {
					delete_post_meta( $form_id, $key );
				}
			}
		}

		// Clear translations
		if ( VMCF7_Flavor::is_active() ) {
			foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
				$old_trans = VMCF7_Flavor::get_all_translations( $form_id, $code );
				foreach ( $old_trans as $field_key => $trans_val ) {
					if ( preg_match( '/^vmcf7_(.+?)_(required|invalid|regex_message|length_message|required_if_message)$/', $field_key, $matches ) ) {
						VMCF7_Flavor::delete_translation( $form_id, $matches[1], $matches[2], $code );
					}
				}
			}
		}

		// Save new rules
		foreach ( $data['rules'] as $key => $value ) {
			if ( str_starts_with( $key, '_vmcf7_' ) ) {
				if ( '_vmcf7_enabled' === $key ) {
					update_post_meta( $form_id, '_vmcf7_enabled', '1' === $value || 1 === $value || '1' === (string) $value ? '1' : '0' );
				} else {
					if ( preg_match( '/^_vmcf7_(.+?)_(required|invalid|regex|regex_message|min_length|max_length|length_message|required_if_field|required_if_message)$/', $key, $matches ) ) {
						$type = $matches[2];
						$clean_val = $this->sanitize_rule_value( $type, $value );
						update_post_meta( $form_id, $key, $clean_val );
					}
				}
			}
		}

		// Save translations
		if ( VMCF7_Flavor::is_active() && isset( $data['translations'] ) && is_array( $data['translations'] ) ) {
			foreach ( $data['translations'] as $code => $translations ) {
				VMCF7_Flavor::import_language_set( $form_id, $code, $translations );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Rules imported successfully.', 'validation-muse-for-contact-form-7' ) ) );
	}

	/**
	 * AJAX handler to copy rules from another form.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_copy_rules() {
		check_ajax_referer( 'vmcf7_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}

		$from_id = isset( $_POST['from_form_id'] ) ? absint( $_POST['from_form_id'] ) : 0;
		$to_id   = isset( $_POST['to_form_id'] ) ? absint( $_POST['to_form_id'] ) : 0;

		if ( ! $from_id || ! $to_id ) {
			wp_send_json_error( array( 'message' => __( 'Missing source or target form ID.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		// Clear target rules
		$meta = get_post_meta( $to_id );
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( str_starts_with( $key, '_vmcf7_' ) ) {
					delete_post_meta( $to_id, $key );
				}
			}
		}

		// Copy rules from source to target
		$source_meta = get_post_meta( $from_id );
		if ( is_array( $source_meta ) ) {
			foreach ( $source_meta as $key => $values ) {
				if ( str_starts_with( $key, '_vmcf7_' ) ) {
					$val = isset( $values[0] ) ? $values[0] : '';
					if ( '_vmcf7_enabled' === $key ) {
						update_post_meta( $to_id, '_vmcf7_enabled', '1' === $val || 1 === $val || '1' === (string) $val ? '1' : '0' );
					} else {
						if ( preg_match( '/^_vmcf7_(.+?)_(required|invalid|regex|regex_message|min_length|max_length|length_message|required_if_field|required_if_message)$/', $key, $matches ) ) {
							$type = $matches[2];
							$clean_val = $this->sanitize_rule_value( $type, $val );
							update_post_meta( $to_id, $key, $clean_val );
						}
					}
				}
			}
		}

		// Copy Flavor translations
		if ( VMCF7_Flavor::is_active() ) {
			foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
				// Clear target translations
				$old_trans = VMCF7_Flavor::get_all_translations( $to_id, $code );
				foreach ( $old_trans as $field_key => $trans_val ) {
					if ( preg_match( '/^vmcf7_(.+?)_(required|invalid|regex_message|length_message|required_if_message)$/', $field_key, $matches ) ) {
						VMCF7_Flavor::delete_translation( $to_id, $matches[1], $matches[2], $code );
					}
				}

				// Save new translations
				$new_trans = VMCF7_Flavor::export_language_set( $from_id, $code );
				VMCF7_Flavor::import_language_set( $to_id, $code, $new_trans );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Rules copied successfully.', 'validation-muse-for-contact-form-7' ) ) );
	}

	/**
	 * AJAX handler for bulk actions (save default template, apply default, bulk apply to all).
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_bulk_apply() {
		check_ajax_referer( 'vmcf7_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'validation-muse-for-contact-form-7' ) ), 403 );
		}

		$form_id     = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$action_type = isset( $_POST['action_type'] ) ? sanitize_key( $_POST['action_type'] ) : '';

		if ( ! $form_id || ! $action_type ) {
			wp_send_json_error( array( 'message' => __( 'Missing form ID or action type.', 'validation-muse-for-contact-form-7' ) ), 400 );
		}

		if ( 'save_default' === $action_type ) {
			$meta  = get_post_meta( $form_id );
			$rules = array();
			if ( is_array( $meta ) ) {
				foreach ( $meta as $key => $values ) {
					if ( str_starts_with( $key, '_vmcf7_' ) ) {
						$rules[ $key ] = $values[0];
					}
				}
			}

			$translations = array();
			if ( VMCF7_Flavor::is_active() ) {
				foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
					$translations[ $code ] = VMCF7_Flavor::export_language_set( $form_id, $code );
				}
			}

			update_option( 'vmcf7_default_template', array(
				'rules'        => $rules,
				'translations' => $translations,
			) );

			wp_send_json_success( array( 'message' => __( 'Default template saved successfully.', 'validation-muse-for-contact-form-7' ) ) );
		}

		if ( 'apply_default' === $action_type ) {
			$template = get_option( 'vmcf7_default_template' );
			if ( ! is_array( $template ) || ! isset( $template['rules'] ) ) {
				wp_send_json_error( array( 'message' => __( 'No default template saved yet.', 'validation-muse-for-contact-form-7' ) ), 400 );
			}

			$meta = get_post_meta( $form_id );
			if ( is_array( $meta ) ) {
				foreach ( $meta as $key => $values ) {
					if ( str_starts_with( $key, '_vmcf7_' ) ) {
						delete_post_meta( $form_id, $key );
					}
				}
			}

			foreach ( $template['rules'] as $key => $value ) {
				if ( '_vmcf7_enabled' === $key ) {
					update_post_meta( $form_id, '_vmcf7_enabled', '1' === $value || 1 === $value || '1' === (string) $value ? '1' : '0' );
				} else {
					if ( preg_match( '/^_vmcf7_(.+?)_(required|invalid|regex|regex_message|min_length|max_length|length_message|required_if_field|required_if_message)$/', $key, $matches ) ) {
						$type = $matches[2];
						$clean_val = $this->sanitize_rule_value( $type, $value );
						update_post_meta( $form_id, $key, $clean_val );
					}
				}
			}

			if ( VMCF7_Flavor::is_active() && isset( $template['translations'] ) ) {
				foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
					$old_trans = VMCF7_Flavor::get_all_translations( $form_id, $code );
					foreach ( $old_trans as $field_key => $trans_val ) {
						if ( preg_match( '/^vmcf7_(.+?)_(required|invalid|regex_message|length_message|required_if_message)$/', $field_key, $matches ) ) {
							VMCF7_Flavor::delete_translation( $form_id, $matches[1], $matches[2], $code );
						}
					}
				}

				foreach ( $template['translations'] as $code => $translations ) {
					VMCF7_Flavor::import_language_set( $form_id, $code, $translations );
				}
			}

			wp_send_json_success( array( 'message' => __( 'Default template applied successfully.', 'validation-muse-for-contact-form-7' ) ) );
		}

		if ( 'bulk_apply_all' === $action_type ) {
			$forms = get_posts( array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'exclude'        => array( $form_id ),
			) );

			if ( empty( $forms ) ) {
				wp_send_json_success( array( 'message' => __( 'No other forms to apply templates to.', 'validation-muse-for-contact-form-7' ) ) );
			}

			$current_meta = get_post_meta( $form_id );
			$rules        = array();
			if ( is_array( $current_meta ) ) {
				foreach ( $current_meta as $key => $values ) {
					if ( str_starts_with( $key, '_vmcf7_' ) ) {
						$rules[ $key ] = $values[0];
					}
				}
			}

			$translations = array();
			if ( VMCF7_Flavor::is_active() ) {
				foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
					$translations[ $code ] = VMCF7_Flavor::export_language_set( $form_id, $code );
				}
			}

			foreach ( $forms as $f ) {
				$other_id   = $f->ID;
				$other_meta = get_post_meta( $other_id );
				if ( is_array( $other_meta ) ) {
					foreach ( $other_meta as $key => $values ) {
						if ( str_starts_with( $key, '_vmcf7_' ) ) {
							delete_post_meta( $other_id, $key );
						}
					}
				}

				foreach ( $rules as $key => $value ) {
					if ( '_vmcf7_enabled' === $key ) {
						update_post_meta( $other_id, '_vmcf7_enabled', '1' === $value || 1 === $value || '1' === (string) $value ? '1' : '0' );
					} else {
						if ( preg_match( '/^_vmcf7_(.+?)_(required|invalid|regex|regex_message|min_length|max_length|length_message|required_if_field|required_if_message)$/', $key, $matches ) ) {
							$type = $matches[2];
							$clean_val = $this->sanitize_rule_value( $type, $value );
							update_post_meta( $other_id, $key, $clean_val );
						}
					}
				}

				if ( VMCF7_Flavor::is_active() ) {
					foreach ( VMCF7_Flavor::get_target_languages() as $code => $lang_data ) {
						$old_trans = VMCF7_Flavor::get_all_translations( $other_id, $code );
						foreach ( $old_trans as $field_key => $trans_val ) {
							if ( preg_match( '/^vmcf7_(.+?)_(required|invalid|regex_message|length_message|required_if_message)$/', $field_key, $matches ) ) {
								VMCF7_Flavor::delete_translation( $other_id, $matches[1], $matches[2], $code );
							}
						}
					}

					foreach ( $translations as $code => $other_trans ) {
						VMCF7_Flavor::import_language_set( $other_id, $code, $other_trans );
					}
				}
			}

			wp_send_json_success( array( 'message' => sprintf( /* translators: %d: number of forms the rules were applied to. */ __( 'Rules applied to %d other forms successfully.', 'validation-muse-for-contact-form-7' ), count( $forms ) ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Invalid bulk action type.', 'validation-muse-for-contact-form-7' ) ), 400 );
	}
}