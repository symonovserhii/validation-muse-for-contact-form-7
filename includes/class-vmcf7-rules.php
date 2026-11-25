<?php
/**
 * Custom validation rules evaluator.
 *
 * @package ValidationMuse
 * @since   1.6.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rules evaluation class.
 *
 * @since 1.6.0
 */
class VMCF7_Rules {

	/**
	 * Message types: translatable text shown to the visitor.
	 *
	 * @since 1.8.0
	 * @var string[]
	 */
	const MESSAGE_TYPES = array( 'required', 'invalid', 'regex_message', 'length_message', 'required_if_message' );

	/**
	 * Rule setting types: configuration, never translated.
	 *
	 * @since 1.8.0
	 * @var string[]
	 */
	const SETTING_TYPES = array( 'regex', 'min_length', 'max_length', 'required_if_field' );

	/**
	 * Split a stored key into its field name and type.
	 *
	 * Accepts a post meta key (`_vmcf7_{field}_{type}`) or a translation key
	 * (`vmcf7_{field}_{type}`). The longest known type wins, so
	 * `_vmcf7_phone_required_if_message` is field `phone`, not `phone_required_if`.
	 *
	 * @since 1.8.0
	 *
	 * @param string $key           The key to parse.
	 * @param bool   $messages_only Only accept message types.
	 * @return array{field:string,type:string}|null The parts, or null when the key is not one of ours.
	 */
	public static function parse_key( $key, $messages_only = false ) {
		if ( ! is_string( $key ) ) {
			return null;
		}

		$types = $messages_only ? self::MESSAGE_TYPES : array_merge( self::MESSAGE_TYPES, self::SETTING_TYPES );

		if ( ! preg_match( '/^_?vmcf7_([a-z0-9_\-]+?)_(' . implode( '|', $types ) . ')$/', $key, $matches ) ) {
			return null;
		}

		return array(
			'field' => $matches[1],
			'type'  => $matches[2],
		);
	}

	/**
	 * Per-request cache of pattern validity, keyed by the raw pattern.
	 *
	 * @since 1.8.0
	 * @var array<string,bool>
	 */
	private static $valid_cache = array();

	/**
	 * Wrap an admin-supplied pattern in delimiters.
	 *
	 * Only tildes that are not already escaped get a backslash, so a pattern
	 * that contains `\~` is not turned into an escaped backslash followed by
	 * a bare delimiter.
	 *
	 * @since 1.8.0
	 * @param string $pattern The regex pattern (without delimiters).
	 * @return string The delimited pattern.
	 */
	public static function wrap_pattern( $pattern ) {
		$escaped = preg_replace( '/(?<!\\\\)((?:\\\\\\\\)*)~/', '$1\\~', (string) $pattern );

		return '~' . ( null === $escaped ? (string) $pattern : $escaped ) . '~u';
	}

	/**
	 * Run preg_match under temporarily lowered PCRE limits (ReDoS hardening).
	 *
	 * @since 1.8.0
	 * @param string $wrapped The delimited pattern.
	 * @param string $subject The subject string.
	 * @return int|false preg_match() result.
	 */
	private static function guarded_match( $wrapped, $subject ) {
		$orig_backtrack = ini_get( 'pcre.backtrack_limit' );
		$orig_recursion = ini_get( 'pcre.recursion_limit' );

		// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky -- Limits are restored right below.
		@ini_set( 'pcre.backtrack_limit', '10000' );
		@ini_set( 'pcre.recursion_limit', '2000' );

		$result = @preg_match( $wrapped, $subject );

		if ( false !== $orig_backtrack ) {
			@ini_set( 'pcre.backtrack_limit', $orig_backtrack );
		}
		if ( false !== $orig_recursion ) {
			@ini_set( 'pcre.recursion_limit', $orig_recursion );
		}
		// phpcs:enable

		return $result;
	}

	/**
	 * Check if a regex pattern compiles.
	 *
	 * @since 1.6.0
	 * @param string $pattern The regex pattern (without delimiters).
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_regex( $pattern ) {
		if ( ! is_string( $pattern ) && ! is_numeric( $pattern ) ) {
			return false;
		}

		$pattern = (string) $pattern;
		if ( '' === $pattern ) {
			return true;
		}

		if ( ! isset( self::$valid_cache[ $pattern ] ) ) {
			self::$valid_cache[ $pattern ] = false !== self::guarded_match( self::wrap_pattern( $pattern ), '' );
		}

		return self::$valid_cache[ $pattern ];
	}

	/**
	 * Evaluate regex pattern.
	 *
	 * A pattern that does not compile is ignored (the value passes) and reported
	 * through `vmcf7_debug`: a typo in the editor must not make the form reject
	 * every submission. A list of values (checkbox, multi-select) passes when
	 * every non-empty item matches.
	 *
	 * @since 1.6.0
	 *
	 * @param string|array $value   The field value.
	 * @param string       $pattern The regex pattern (without delimiters).
	 * @return bool True if valid, false otherwise.
	 */
	public static function evaluate_regex( $value, $pattern ) {
		if ( empty( $pattern ) && '0' !== $pattern ) {
			return true;
		}

		if ( ! self::is_valid_regex( $pattern ) ) {
			do_action( 'vmcf7_debug', 'Invalid custom regex pattern ignored: ' . ( is_scalar( $pattern ) ? $pattern : gettype( $pattern ) ) );
			return true;
		}

		if ( is_array( $value ) ) {
			foreach ( self::flatten( $value ) as $item ) {
				if ( '' !== $item && ! self::evaluate_regex( $item, $pattern ) ) {
					return false;
				}
			}
			return true;
		}

		$result = self::guarded_match( self::wrap_pattern( $pattern ), (string) $value );

		if ( false === $result ) {
			do_action( 'vmcf7_debug', 'Regex match failure or limit reached for pattern: ' . $pattern );
			return false;
		}

		return 1 === $result;
	}

	/**
	 * Evaluate min and max length.
	 *
	 * A list of values is measured as the total length of its items, the same
	 * way Contact Form 7's own minlength/maxlength rules count.
	 *
	 * @since 1.6.0
	 *
	 * @param string|array $value      The field value.
	 * @param string|int   $min_length Minimum length.
	 * @param string|int   $max_length Maximum length.
	 * @return array Array with validation result and failing type ('min' or 'max').
	 */
	public static function evaluate_length( $value, $min_length, $max_length ) {
		$length = 0;
		foreach ( is_array( $value ) ? self::flatten( $value ) : array( (string) $value ) as $item ) {
			$length += function_exists( 'mb_strlen' ) ? mb_strlen( $item, 'UTF-8' ) : strlen( $item );
		}

		if ( '' !== $min_length && null !== $min_length ) {
			$min = intval( $min_length );
			if ( $length < $min ) {
				return array(
					'valid' => false,
					'type'  => 'min',
				);
			}
		}

		if ( '' !== $max_length && null !== $max_length ) {
			$max = intval( $max_length );
			if ( $length > $max ) {
				return array(
					'valid' => false,
					'type'  => 'max',
				);
			}
		}

		return array(
			'valid' => true,
			'type'  => '',
		);
	}

	/**
	 * Flatten a (possibly nested) list of posted values into strings.
	 *
	 * @since 1.8.0
	 * @param array $values The values.
	 * @return string[] Flat list of strings.
	 */
	public static function flatten( array $values ) {
		$flat = array();
		array_walk_recursive(
			$values,
			static function ( $item ) use ( &$flat ) {
				if ( is_scalar( $item ) ) {
					$flat[] = (string) $item;
				}
			}
		);

		return $flat;
	}
}
