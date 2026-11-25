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
	 * Per-request cache for translations.
	 *
	 * @var array
	 */
	private static $cache = array();

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
		$form_id = absint( $form_id );
		if ( ! self::is_active() || ! $form_id ) {
			return null;
		}

		if ( null === $lang ) {
			$lang = \Flavor\Flv_Languages::get_current_language();
		}

		$key       = self::field_key( $field, $type );
		$cache_key = "{$form_id}_{$key}_{$lang}";

		if ( array_key_exists( $cache_key, self::$cache ) ) {
			return self::$cache[ $cache_key ];
		}

		$record = \Flavor\Flv_DB::get( 'post_meta', $form_id, $key, $lang );

		if ( ! $record || empty( $record['translation'] ) ) {
			self::$cache[ $cache_key ] = null;
			return null;
		}

		// Only return confirmed translations (translated or reviewed).
		$confirmed = array( \Flavor\Flv_Status::TRANSLATED, \Flavor\Flv_Status::REVIEWED );
		if ( ! in_array( $record['status'], $confirmed, true ) ) {
			$pending_status = defined( 'Flavor\Flv_Status::PENDING' ) ? \Flavor\Flv_Status::PENDING : 'pending';
			if ( $record['status'] === $pending_status ) {
				do_action( 'vmcf7_debug', "Pending translation accessed for key: {$key} (Language: {$lang})" );
				self::$cache[ $cache_key ] = $record['translation'];
				return $record['translation'];
			}

			self::$cache[ $cache_key ] = null;
			return null;
		}

		self::$cache[ $cache_key ] = $record['translation'];
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
		// A form that has not been saved yet has no ID and nothing translated.
		$form_id = absint( $form_id );
		if ( ! self::is_active() || ! $form_id ) {
			return array();
		}

		$all    = \Flavor\Flv_DB::get_all( 'post_meta', $form_id, $lang );
		$result = array();

		foreach ( $all as $record ) {
			if ( str_starts_with( $record['field_name'], 'vmcf7_' ) ) {
				$result[ $record['field_name'] ] = $record['translation'];
			}
		}

		return $result;
	}

	/**
	 * Export translation sets for Flavor.
	 *
	 * @since 1.6.0
	 * @param int    $form_id Form ID.
	 * @param string $lang    Language code.
	 * @return array Translation set.
	 */
	public static function export_language_set( $form_id, $lang ) {
		return self::get_all_translations( $form_id, $lang );
	}

	/**
	 * Import translation sets for Flavor.
	 *
	 * @since 1.6.0
	 * @param int    $form_id      Form ID.
	 * @param string $lang         Language code.
	 * @param array  $translations Translations to import.
	 * @return void
	 */
	public static function import_language_set( $form_id, $lang, $translations ) {
		if ( ! self::is_active() || ! is_array( $translations ) ) {
			return;
		}

		$lang = sanitize_key( $lang );
		if ( ! $lang ) {
			return;
		}

		foreach ( $translations as $field_key => $value ) {
			$parts = VMCF7_Rules::parse_key( $field_key, true );
			if ( ! $parts || ! is_string( $value ) ) {
				continue;
			}

			$value = trim( wp_kses_post( $value ) );
			if ( '' !== $value ) {
				self::save_translation( $form_id, $parts['field'], $parts['type'], $lang, $value );
			}
		}
	}

	/**
	 * Collect a form's source-language messages, keyed by Flavor field key.
	 *
	 * Only message types are returned: regex patterns, length limits and
	 * companion field names are configuration, not text to translate.
	 *
	 * @since 1.8.0
	 *
	 * @param int $form_id The CF7 form post ID.
	 * @return array Flavor field key (`vmcf7_{field}_{type}`) => message.
	 */
	public static function get_source_messages( $form_id ) {
		$texts = array();
		$meta  = get_post_meta( absint( $form_id ) );

		if ( ! is_array( $meta ) ) {
			return $texts;
		}

		foreach ( $meta as $key => $values ) {
			$key = (string) $key;
			if ( ! str_starts_with( $key, '_' ) || ! VMCF7_Rules::parse_key( $key, true ) ) {
				continue;
			}

			$value = is_array( $values ) && isset( $values[0] ) ? $values[0] : '';
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				// Convert _vmcf7_field_type to vmcf7_field_type.
				$texts[ substr( $key, 1 ) ] = $value;
			}
		}

		return $texts;
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
		$form_id = absint( $form_id );
		if ( ! self::is_active() || ! $form_id ) {
			return;
		}

		$key       = self::field_key( $field, $type );
		$cache_key = "{$form_id}_{$key}_{$lang}";
		self::$cache[ $cache_key ] = $value;

		\Flavor\Flv_DB::save(
			array(
				'object_type' => 'post_meta',
				'object_id'   => $form_id,
				'field_name'  => $key,
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
		$form_id = absint( $form_id );
		if ( ! self::is_active() || ! $form_id ) {
			return;
		}

		$key       = self::field_key( $field, $type );
		$cache_key = "{$form_id}_{$key}_{$lang}";
		unset( self::$cache[ $cache_key ] );

		\Flavor\Flv_DB::delete_by_field( 'post_meta', $form_id, $key, $lang );
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
		$form_id = absint( $form_id );
		if ( ! $form_id || ! self::is_active() || ! self::is_ai_available() ) {
			return array();
		}

		$texts = self::get_source_messages( $form_id );

		if ( empty( $texts ) ) {
			return array();
		}

		$translator   = new \Flavor\Flv_AI_Translator();
		$translations = $translator->translate_batch( $texts, $lang );

		if ( ! is_array( $translations ) ) {
			return array();
		}

		// Keep only answers to what was asked, as text.
		$translations = array_filter( array_intersect_key( $translations, $texts ), 'is_string' );
		$translations = array_filter( array_map( 'trim', array_map( 'wp_kses_post', $translations ) ), 'strlen' );

		// Save each translation.
		foreach ( $translations as $flavor_key => $translated_text ) {

			$cache_key = "{$form_id}_{$flavor_key}_{$lang}";
			self::$cache[ $cache_key ] = $translated_text;

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
