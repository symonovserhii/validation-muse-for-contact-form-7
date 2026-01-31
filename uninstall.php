<?php
/**
 * Uninstall script for Validation Muse for Contact Form 7.
 *
 * Removes all plugin data when the plugin is deleted.
 *
 * @package ValidationMuse
 * @since   1.0.0
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Get all Contact Form 7 forms.
$vmcf7_forms = get_posts(
	array(
		'post_type'      => 'wpcf7_contact_form',
		'post_status'    => 'any',
		'fields'         => 'ids',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	)
);

// Delete all plugin meta from forms.
if ( $vmcf7_forms ) {
	foreach ( $vmcf7_forms as $vmcf7_form_id ) {
		$vmcf7_meta = get_post_meta( $vmcf7_form_id );

		foreach ( $vmcf7_meta as $vmcf7_key => $vmcf7_values ) {
			if ( 0 !== strpos( (string) $vmcf7_key, '_vmcf7_' ) ) {
				continue;
			}

			delete_post_meta( $vmcf7_form_id, $vmcf7_key );
		}
	}
}

// Delete plugin options.
delete_option( 'vmcf7_version' );