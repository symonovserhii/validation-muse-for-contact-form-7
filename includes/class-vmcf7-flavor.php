<?php
/**
 * Flavor translation bridge for the plugin.
 *
 * Wraps all Flavor plugin API calls behind class_exists() checks,
 * keeping the plugin independent and safe to use without Flavor.
 *
 * @package ValidationMuse
 * @since   1.4.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flavor bridge class.
 *
 * Provides static methods to interact with the Flavor translation plugin.
 * All methods gracefully return defaults when Flavor is not active.
 *
 * @since 1.4.0
 */
class VMCF7_Flavor {

	/**
	 * Check if Flavor plugin is active.
	 *
	 * @since 1.4.0
	 *
	 * @return bool True if Flavor is available.
	 */
	public static function is_active() {
		return class_exists( 'Flavor\Flv_Languages' );
	}

	/**
	 * Check if the current page needs translation.
	 *
	 * Returns true when Flavor is active and current language differs from default.
	 *
	 * @since 1.4.0
	 *
	 * @return bool True if translation is needed.
	 */
	public static function needs_translation() {
		if ( ! self::is_active() ) {
			return false;
		}

		return \Flavor\Flv_Languages::get_current_language() !== \Flavor\Flv_Languages::get_default_language();
	}

	/**
	 * Get the current language code.
	 *
	 * @since 1.4.0
	 *
	 * @return string Language code (e.g. 'de') or empty string.
	 */
	public static function get_current_language() {
		if ( ! self::is_active() ) {
			return '';
		}

		return \Flavor\Flv_Languages::get_current_language();
	}

	/**
	 * Get the default language code.
	 *
	 * @since 1.4.0
	 *
	 * @return string Language code (e.g. 'en') or empty string.
	 */
	public static function get_default_language() {
		if ( ! self::is_active() ) {
			return '';
		}

		return \Flavor\Flv_Languages::get_default_language();
	}

	/**
	 * Get target (non-default) languages.
	 *
	 * @since 1.4.0
	 *
	 * @return array Associative array of language data keyed by code.
	 */
	public static function get_target_languages() {
		if ( ! self::is_active() ) {
			return array();
		}

		return \Flavor\Flv_Languages::get_target_languages();
	}

	/**
	 * Build a Flavor field key from field name and message type.
	 *
	 * Converts meta key `_vmcf7_your_email_required` to Flavor field_name `vmcf7_your_email_required`.
	 *
	 * @since 1.4.0
	 *
	 * @param string $field The field name.
	 * @param string $type  The message type ('required' or 'invalid').
	 * @return string The Flavor field key.
	 */
	public static function field_key( $field, $type ) {
		return 'vmcf7_' . sanitize_key( $field ) . '_' . sanitize_key( $type );
	}

	/**
	 * Get a translated validation message.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $form_id The CF7 form post ID.
	 * @param string $field   The field name.
	 * @param string $type    The message type ('required' or 'invalid').
	 * @param string $lang    Optional. Language code. Defaults to current language.
	 * @return string|null Translated message or null if not found.
	 */
	public static function get_translation( $form_id, $field, $type, $lang = null ) {
		if ( ! self::is_active() ) {
			return null;
		}

		if ( null === $lang ) {
			$lang = \Flavor\Flv_Languages::get_current_language();
		}

		$key    = self::field_key( $field, $type );
		$record = \Flavor\Flv_DB::get( 'post_meta', $form_id, $key, $lang );

		if ( ! $record || empty( $record['translation'] ) ) {
			return null;
		}

		// Only return confirmed translations (translated or reviewed).
		$confirmed = array( \Flavor\Flv_Status::TRANSLATED, \Flavor\Flv_Status::REVIEWED );
		if ( ! in_array( $record['status'], $confirmed, true ) ) {
			return null;
		}

		return $record['translation'];
	}

	/**
	 * Get all vmcf7 translations for a form in a given language.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $form_id The CF7 form post ID.
	 * @param string $lang    Language code.
	 * @return array Associative array keyed by field_name.
	 */
	public static function get_all_translations( $form_id, $lang ) {
		if ( ! self::is_active() ) {
			return array();
		}

		$all    = \Flavor\Flv_DB::get_all( 'post_meta', $form_id, $lang );
		$result = array();

		foreach ( $all as $record ) {
			if ( 0 === strpos( $record['field_name'], 'vmcf7_' ) ) {
				$result[ $record['field_name'] ] = $record['translation'];
			}
		}

		return $result;
	}

	/**
	 * Save a translation for a validation message.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $form_id The CF7 form post ID.
	 * @param string $field   The field name.
	 * @param string $type    The message type.
	 * @param string $lang    Language code.
	 * @param string $value   Translated message text.
	 * @param bool   $is_auto Whether this is an AI translation.
	 * @return void
	 */
	public static function save_translation( $form_id, $field, $type, $lang, $value, $is_auto = false ) {
		if ( ! self::is_active() ) {
			return;
		}

		\Flavor\Flv_DB::save(
			array(
				'object_type' => 'post_meta',
				'object_id'   => $form_id,
				'field_name'  => self::field_key( $field, $type ),
				'language'    => $lang,
				'translation' => $value,
				'is_auto'     => $is_auto ? 1 : 0,
				'status'      => $is_auto ? \Flavor\Flv_Status::TRANSLATED : \Flavor\Flv_Status::REVIEWED,
			)
		);
	}

	/**
	 * Delete a translation for a validation message.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $form_id The CF7 form post ID.
	 * @param string $field   The field name.
	 * @param string $type    The message type.
	 * @param string $lang    Language code.
	 * @return void
	 */
	public static function delete_translation( $form_id, $field, $type, $lang ) {
		if ( ! self::is_active() ) {
			return;
		}

		\Flavor\Flv_DB::delete_by_field( 'post_meta', $form_id, self::field_key( $field, $type ), $lang );
	}

	/**
	 * AI-translate all validation messages for a form into a target language.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $form_id The CF7 form post ID.
	 * @param string $lang    Target language code.
	 * @return array Associative array of translated messages keyed by Flavor field key.
	 */
	public static function ai_translate_form( $form_id, $lang ) {
		if ( ! self::is_active() || ! self::is_ai_available() ) {
			return array();
		}

		// Collect all EN messages from post meta.
		$meta  = get_post_meta( $form_id );
		$texts = array();

		foreach ( $meta as $key => $values ) {
			if ( 0 !== strpos( $key, '_vmcf7_' ) || '_vmcf7_enabled' === $key ) {
				continue;
			}

			$value = $values[0];
			if ( empty( $value ) ) {
				continue;
			}

			// Convert _vmcf7_field_type to vmcf7_field_type.
			$flavor_key           = substr( $key, 1 );
			$texts[ $flavor_key ] = $value;
		}

		if ( empty( $texts ) ) {
			return array();
		}

		$translator   = new \Flavor\Flv_AI_Translator();
		$translations = $translator->translate_batch( $texts, $lang );

		// Save each translation.
		foreach ( $translations as $flavor_key => $translated_text ) {
			if ( empty( $translated_text ) ) {
				continue;
			}

			\Flavor\Flv_DB::save(
				array(
					'object_type' => 'post_meta',
					'object_id'   => $form_id,
					'field_name'  => $flavor_key,
					'language'    => $lang,
					'translation' => $translated_text,
					'is_auto'     => 1,
					'status'      => \Flavor\Flv_Status::TRANSLATED,
				)
			);
		}

		return $translations;
	}

	/**
	 * Check if AI translation is available.
	 *
	 * @since 1.4.0
	 *
	 * @return bool True if AI translator is configured.
	 */
	public static function is_ai_available() {
		if ( ! self::is_active() || ! class_exists( 'Flavor\Flv_AI_Translator' ) ) {
			return false;
		}

		$translator = new \Flavor\Flv_AI_Translator();

		return $translator->is_configured();
	}
}
