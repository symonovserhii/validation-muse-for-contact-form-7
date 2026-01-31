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
		if ( false === strpos( $hook, 'wpcf7' ) ) {
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

		wp_localize_script(
			'vmcf7-admin',
			'vmcf7',
			array(
				'nonce' => wp_create_nonce( 'vmcf7_ajax_nonce' ),
			)
		);
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
		$fields = array();
		$tags   = $post->scan_form_tags();

		foreach ( $tags as $tag ) {
			if ( $tag->is_required() ) {
				$fields[] = array(
					'name'             => sanitize_key( $tag->name ),
					'type'             => sanitize_key( $tag->basetype ),
					'required_message' => $this->get_message( $post->id(), $tag->name, 'required' ),
					'invalid_message'  => $this->get_message( $post->id(), $tag->name, 'invalid' ),
				);
			}
		}

		return $fields;
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

				$meta_key      = sprintf( '_vmcf7_%s_%s', $field_name, $type );
				$clean_message = wp_kses_post( $message );

				if ( '' === $clean_message ) {
					delete_post_meta( $form_id, $meta_key );
					continue;
				}

				update_post_meta( $form_id, $meta_key, $clean_message );
			}
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
		);

		return isset( $messages[ $type ] ) ? $messages[ $type ] : '';
	}
}