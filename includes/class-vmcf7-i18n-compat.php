<?php
/**
 * Multilingual compatibility for WPML and Polylang.
 *
 * @package ValidationMuse
 * @since   1.6.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML and Polylang compatibility class.
 *
 * Registers custom validation messages for translation in multilingual sites.
 *
 * @since 1.6.0
 */
class VMCF7_I18n_Compat {

	/**
	 * Register hooks for string registration and translation.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function init() {
		// Register strings when a contact form is saved.
		add_action( 'wpcf7_save_contact_form', array( $this, 'register_strings_on_save' ) );

		// Translate messages on load.
		add_filter( 'vmcf7_translate_message', array( $this, 'translate_message' ), 10, 4 );
	}

	/**
	 * Register all custom validation messages for a contact form in WPML/Polylang.
	 *
	 * @since 1.6.0
	 * @param WPCF7_ContactForm $contact_form The contact form object.
	 * @return void
	 */
	public function register_strings_on_save( $contact_form ) {
		$form_id = $contact_form->id();
		$enabled = get_post_meta( $form_id, '_vmcf7_enabled', true );
		if ( '1' !== $enabled ) {
			return;
		}

		$tags = $contact_form->scan_form_tags();
		foreach ( $tags as $tag ) {
			if ( empty( $tag->name ) ) {
				continue;
			}

			$field_name = sanitize_key( $tag->name );

			// Types of messages we want to translate.
			$types = array(
				'required',
				'invalid',
				'regex_message',
				'length_message',
				'required_if_message',
			);

			foreach ( $types as $type ) {
				$meta_key = sprintf( '_vmcf7_%s_%s', $field_name, $type );
				$message  = get_post_meta( $form_id, $meta_key, true );

				if ( ! empty( $message ) && is_string( $message ) ) {
					$string_name = sprintf( 'vmcf7_%d_%s_%s', $form_id, $field_name, $type );
					$this->register_string( $string_name, $message );
				}
			}
		}
	}

	/**
	 * Register a string with WPML or Polylang.
	 *
	 * @since 1.6.0
	 * @param string $name  Unique name.
	 * @param string $value The string content.
	 * @return void
	 */
	private function register_string( $name, $value ) {
		// WPML
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'validation-muse-for-contact-form-7', $name, $value );
		} elseif ( has_action( 'wpml_register_string' ) ) {
			do_action( 'wpml_register_string', $value, $name, array( 'domain' => 'validation-muse-for-contact-form-7' ), $name, 'LITE' );
		}

		// Polylang
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( $name, $value, 'validation-muse-for-contact-form-7' );
		}
	}

	/**
	 * Translate a custom message using WPML or Polylang.
	 *
	 * @since 1.6.0
	 * @param string $translated  The currently translated message.
	 * @param int    $form_id     Form ID.
	 * @param string $field_name  Field name.
	 * @param string $type        Message type.
	 * @return string Translated message.
	 */
	public function translate_message( $translated, $form_id, $field_name, $type ) {
		$string_name = sprintf( 'vmcf7_%d_%s_%s', $form_id, $field_name, $type );
		$original    = get_post_meta( $form_id, sprintf( '_vmcf7_%s_%s', $field_name, $type ), true );
		if ( empty( $original ) ) {
			return $translated;
		}

		// WPML translation check
		if ( function_exists( 'icl_t' ) ) {
			$wpml_trans = icl_t( 'validation-muse-for-contact-form-7', $string_name, $original );
			if ( ! empty( $wpml_trans ) ) {
				return $wpml_trans;
			}
		} elseif ( has_filter( 'wpml_translate_string' ) ) {
			$wpml_trans = apply_filters( 'wpml_translate_string', $original, $string_name, array( 'domain' => 'validation-muse-for-contact-form-7' ) );
			if ( ! empty( $wpml_trans ) ) {
				return $wpml_trans;
			}
		}

		// Polylang translation check
		if ( function_exists( 'pll__' ) ) {
			$pll_trans = pll__( $original );
			if ( ! empty( $pll_trans ) && $pll_trans !== $original ) {
				return $pll_trans;
			}
		}

		return $translated;
	}
}
