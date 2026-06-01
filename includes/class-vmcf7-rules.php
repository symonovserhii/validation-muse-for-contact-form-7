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
	 * Check if a regex pattern compiles.
	 *
	 * @since 1.6.0
	 * @param string $pattern The regex pattern (without delimiters).
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_regex( $pattern ) {
		if ( empty( $pattern ) ) {
			return true;
		}
		$wrapped = '~' . str_replace( '~', '\~', $pattern ) . '~u';

		// ReDoS hardening: set low backtrack and recursion limits for compilation/check.
		$orig_backtrack = ini_get( 'pcre.backtrack_limit' );
		$orig_recursion = ini_get( 'pcre.recursion_limit' );

		@ini_set( 'pcre.backtrack_limit', '10000' );
		@ini_set( 'pcre.recursion_limit', '2000' );

		$res = @preg_match( $wrapped, '' );

		if ( false !== $orig_backtrack ) {
			@ini_set( 'pcre.backtrack_limit', $orig_backtrack );
		}
		if ( false !== $orig_recursion ) {
			@ini_set( 'pcre.recursion_limit', $orig_recursion );
		}

		return false !== $res;
	}

	/**
	 * Evaluate regex pattern.
	 *
	 * @since 1.6.0
	 *
	 * @param string $value   The field value.
	 * @param string $pattern The regex pattern (without delimiters).
	 * @return bool True if valid, false otherwise.
	 */
	public static function evaluate_regex( $value, $pattern ) {
		if ( empty( $pattern ) ) {
			return true;
		}

		if ( ! self::is_valid_regex( $pattern ) ) {
			do_action( 'vmcf7_debug', 'Invalid custom regex pattern: ' . $pattern );
			return false;
		}

		// Ensure delimiters are present or wrap it safely.
		$wrapped_pattern = '~' . str_replace( '~', '\~', $pattern ) . '~u';

		// ReDoS hardening: set low backtrack and recursion limits for evaluation.
		$orig_backtrack = ini_get( 'pcre.backtrack_limit' );
		$orig_recursion = ini_get( 'pcre.recursion_limit' );

		@ini_set( 'pcre.backtrack_limit', '10000' );
		@ini_set( 'pcre.recursion_limit', '2000' );

		// Run preg_match safely.
		$result = @preg_match( $wrapped_pattern, $value );

		// Restore original limits
		if ( false !== $orig_backtrack ) {
			@ini_set( 'pcre.backtrack_limit', $orig_backtrack );
		}
		if ( false !== $orig_recursion ) {
			@ini_set( 'pcre.recursion_limit', $orig_recursion );
		}

		if ( false === $result ) {
			do_action( 'vmcf7_debug', 'Regex match failure or limit reached for pattern: ' . $pattern );
			return false;
		}

		return 1 === $result;
	}

	/**
	 * Evaluate min and max length.
	 *
	 * @since 1.6.0
	 *
	 * @param string $value      The field value.
	 * @param string|int $min_length Minimum length.
	 * @param string|int $max_length Maximum length.
	 * @return array Array with validation result and failing type ('min' or 'max'), or true if valid.
	 */
	public static function evaluate_length( $value, $min_length, $max_length ) {
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );

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
}
