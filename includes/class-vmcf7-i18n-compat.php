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
		// Register strings when a contact form is saved. Priority 20: after
		// VMCF7_Admin::save_messages() has written the new messages, otherwise
		// the strings registered are the ones from the previous save.
		add_action( 'wpcf7_save_contact_form', array( $this, 'register_strings_on_save' ), 20 );

		// Polylang keeps registered strings in memory only, so its "Translations"
		// screen lists them only if they are registered on that very request.
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_polylang_strings' ) );
		}

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

			foreach ( VMCF7_Rules::MESSAGE_TYPES as $type ) {
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
	 * Register the messages of every enabled form with Polylang.
	 *
	 * Runs on Polylang's own admin screens only (string translations list and
	 * its save request), so other admin pages pay nothing.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function register_polylang_strings() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( ! str_starts_with( $page, 'mlang' ) ) {
			return;
		}

		$form_ids = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_vmcf7_enabled', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin screen, small post type.
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Admin screen, small post type.
			)
		);

		foreach ( (array) $form_ids as $form_id ) {
			$meta = get_post_meta( $form_id );
			if ( ! is_array( $meta ) ) {
				continue;
			}

			foreach ( $meta as $key => $values ) {
				$key   = (string) $key;
				$parts = str_starts_with( $key, '_' ) ? VMCF7_Rules::parse_key( $key, true ) : null;
				$value = is_array( $values ) && isset( $values[0] ) ? $values[0] : '';

				if ( $parts && is_string( $value ) && '' !== $value ) {
					pll_register_string(
						sprintf( 'vmcf7_%d_%s_%s', $form_id, $parts['field'], $parts['type'] ),
						$value,
						'validation-muse-for-contact-form-7'
					);
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
		// Polylang emulates icl_register_string() and stores those strings for
		// good, which listed every message twice next to the pll_register_string()
		// copy. Use only Polylang's own API there and drop the stored duplicate.
		if ( function_exists( 'pll_register_string' ) ) {
			if ( function_exists( 'icl_unregister_string' ) ) {
				icl_unregister_string( 'validation-muse-for-contact-form-7', $name );
			}
			pll_register_string( $name, $value, 'validation-muse-for-contact-form-7' );
			return;
		}

		// WPML
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'validation-muse-for-contact-form-7', $name, $value );
		} elseif ( has_action( 'wpml_register_string' ) ) {
			do_action( 'wpml_register_string', $value, $name, array( 'domain' => 'validation-muse-for-contact-form-7' ), $name, 'LITE' );
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
