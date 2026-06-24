<?php
/**
 * Data migrations.
 *
 * @package ValidationMuse
 * @since   1.6.2
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles one-time data migrations between plugin versions.
 *
 * @since 1.6.2
 */
class VMCF7_Migrations {

	/**
	 * Legacy post meta key prefix used before the plugin was renamed.
	 *
	 * @since 1.6.2
	 * @var string
	 */
	const LEGACY_PREFIX = '_cf7cv_';

	/**
	 * Current post meta key prefix.
	 *
	 * @since 1.6.2
	 * @var string
	 */
	const CURRENT_PREFIX = '_vmcf7_';

	/**
	 * Option flag marking the legacy prefix migration as done.
	 *
	 * @since 1.6.2
	 * @var string
	 */
	const PREFIX_MIGRATION_FLAG = 'vmcf7_migrated_cf7cv_prefix';

	/**
	 * Run any pending migrations.
	 *
	 * Self-guarded and cheap on the hot path: once a migration has run its
	 * flag option is autoloaded, so subsequent page loads cost no extra query.
	 *
	 * @since 1.6.2
	 * @return void
	 */
	public static function maybe_run() {
		if ( ! get_option( self::PREFIX_MIGRATION_FLAG ) ) {
			self::migrate_legacy_prefix();
		}
	}

	/**
	 * Rename orphaned `_cf7cv_*` meta keys to the current `_vmcf7_*` prefix.
	 *
	 * The plugin was renamed in 1.2.0 (commit "fix: renamed plugin") which
	 * changed the meta key prefix without migrating existing rows, leaving
	 * messages saved by older versions invisible to the current code. This
	 * renames them in place. Collision-safe: if a current-prefix key already
	 * exists for the same post it is kept and the legacy row is discarded.
	 *
	 * @since 1.6.2
	 * @return int Number of post meta rows renamed.
	 */
	public static function migrate_legacy_prefix() {
		global $wpdb;

		$like         = $wpdb->esc_like( self::LEGACY_PREFIX ) . '%';
		$legacy_len   = strlen( self::LEGACY_PREFIX ) + 1;

		// Posts that hold legacy rows, captured before the rename so their
		// meta cache can be cleared afterwards.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time migration on raw meta keys.
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				$like
			)
		);

		if ( empty( $post_ids ) ) {
			update_option( self::PREFIX_MIGRATION_FLAG, VMCF7_VERSION, true );
			return 0;
		}

		// Rename only legacy keys that have no current-prefix counterpart on
		// the same post, so we never create duplicate (post_id, meta_key) rows.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- One-time migration; values are prepared below.
		$renamed = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} AS legacy
				 LEFT JOIN {$wpdb->postmeta} AS current_row
				        ON current_row.post_id = legacy.post_id
				       AND current_row.meta_key = CONCAT( %s, SUBSTRING( legacy.meta_key, %d ) )
				    SET legacy.meta_key = CONCAT( %s, SUBSTRING( legacy.meta_key, %d ) )
				  WHERE legacy.meta_key LIKE %s
				    AND current_row.meta_id IS NULL",
				self::CURRENT_PREFIX,
				$legacy_len,
				self::CURRENT_PREFIX,
				$legacy_len,
				$like
			)
		);

		// Drop any legacy rows that survived because a current-prefix key
		// already existed (the current value wins).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time migration cleanup.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				$like
			)
		);

		// Invalidate the meta cache for affected posts so the new keys are read.
		foreach ( $post_ids as $post_id ) {
			wp_cache_delete( (int) $post_id, 'post_meta' );
		}

		update_option( self::PREFIX_MIGRATION_FLAG, VMCF7_VERSION, true );

		do_action( 'vmcf7_debug', sprintf( 'Migrated %d legacy _cf7cv_ meta rows across %d forms.', (int) $renamed, count( $post_ids ) ) );

		return (int) $renamed;
	}
}
