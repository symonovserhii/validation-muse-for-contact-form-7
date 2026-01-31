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
		require_once VMCF7_PATH . 'includes/class-vmcf7-admin.php';

		// Register validation filters for each supported tag type.
		foreach ( $this->get_validation_tag_types() as $tag_type ) {
			add_filter( "wpcf7_validate_{$tag_type}", array( $this, 'validate_field' ), 9, 2 );
			add_filter( "wpcf7_validate_{$tag_type}*", array( $this, 'validate_field' ), 9, 2 );
		}

		// Initialize admin functionality.
		if ( is_admin() ) {
			$admin = new VMCF7_Admin();
			add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
			add_filter( 'wpcf7_editor_panels', array( $admin, 'add_panel' ) );
			add_action( 'wpcf7_save_contact_form', array( $admin, 'save_messages' ) );
		}

		/**
		 * Fires after the plugin has been initialized.
		 *
		 * @since 1.2.0
		 */
		do_action( 'vmcf7_loaded' );
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

		$value = $this->get_posted_value( $tag );

		// Check for custom required message.
		$required_message = $this->get_custom_message( $form->id(), $field_name, 'required' );
		if ( $required_message && $tag->is_required() && $this->value_is_empty( $value ) ) {
			$result->invalidate( $tag, $required_message );
			return $result;
		}

		// Check for custom invalid message.
		$invalid_message = $this->get_custom_message( $form->id(), $field_name, 'invalid' );
		if ( ! $invalid_message || $this->value_is_empty( $value ) || is_array( $value ) ) {
			return $result;
		}

		// Validate based on field type.
		switch ( $tag->basetype ) {
			case 'email':
				if ( ! wpcf7_is_email( $value ) ) {
					$result->invalidate( $tag, $invalid_message );
				}
				break;
			case 'url':
				if ( ! wpcf7_is_url( $value ) ) {
					$result->invalidate( $tag, $invalid_message );
				}
				break;
			case 'tel':
				if ( ! wpcf7_is_tel( $value ) ) {
					$result->invalidate( $tag, $invalid_message );
				}
				break;
			case 'number':
			case 'range':
				if ( ! wpcf7_is_number( $value ) ) {
					$result->invalidate( $tag, $invalid_message );
				}
				break;
			case 'date':
				if ( ! wpcf7_is_date( $value ) ) {
					$result->invalidate( $tag, $invalid_message );
				}
				break;
		}

		return $result;
	}

	/**
	 * Get the list of tag types that support validation.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of supported tag types.
	 */
	private function get_validation_tag_types() {
		$types = array(
			'text',
			'email',
			'url',
			'tel',
			'number',
			'range',
			'date',
			'textarea',
			'select',
			'checkbox',
			'radio',
			'file',
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

		return is_string( $message ) ? $message : '';
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
}