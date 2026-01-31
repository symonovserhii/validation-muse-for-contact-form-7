<?php
/**
 * Plugin Name:       Validation Muse for Contact Form 7
 * Plugin URI:        https://github.com/symonovserhii/validation-muse-for-contact-form-7
 * Description:       Customize validation messages for each Contact Form 7 field.
 * Version:           1.2.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            simmotorlp
 * Author URI:        https://github.com/symonovserhii
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       validation-muse-for-contact-form-7
 * Domain Path:       /languages
 *
 * @package ValidationMuse
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @var string
 */
define( 'VMCF7_VERSION', '1.2.1' );

/**
 * Plugin main file path.
 *
 * @var string
 */
define( 'VMCF7_FILE', __FILE__ );

/**
 * Plugin directory path.
 *
 * @var string
 */
define( 'VMCF7_PATH', plugin_dir_path( VMCF7_FILE ) );

/**
 * Plugin directory URL.
 *
 * @var string
 */
define( 'VMCF7_URL', plugin_dir_url( VMCF7_FILE ) );

/**
 * Plugin basename.
 *
 * @var string
 */
define( 'VMCF7_BASENAME', plugin_basename( VMCF7_FILE ) );

/**
 * Activation hook.
 *
 * Checks for Contact Form 7 dependency and stores plugin version.
 *
 * @since 1.0.0
 * @return void
 */
function vmcf7_activate() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		deactivate_plugins( VMCF7_BASENAME );
		wp_die(
			esc_html__( 'This plugin requires Contact Form 7 to be installed and activated.', 'validation-muse-for-contact-form-7' ),
			esc_html__( 'Plugin dependency check', 'validation-muse-for-contact-form-7' ),
			array( 'back_link' => true )
		);
	}

	update_option( 'vmcf7_version', VMCF7_VERSION );
}
register_activation_hook( VMCF7_FILE, 'vmcf7_activate' );

/**
 * Initialize the plugin.
 *
 * Checks for Contact Form 7 dependency and loads the main plugin class.
 *
 * @since 1.0.0
 * @return void
 */
function vmcf7_init() {
	// Check if Contact Form 7 is active.
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		add_action( 'admin_notices', 'vmcf7_missing_cf7_notice' );
		return;
	}

	require_once VMCF7_PATH . 'includes/class-vmcf7-loader.php';
	$loader = new VMCF7_Loader();
	$loader->init();
}
add_action( 'plugins_loaded', 'vmcf7_init' );

/**
 * Display admin notice when Contact Form 7 is missing.
 *
 * @since 1.0.0
 * @return void
 */
function vmcf7_missing_cf7_notice() {
	$install_url = admin_url( 'plugin-install.php?tab=search&s=contact+form+7' );

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: 1: opening link tag, 2: closing link tag. */
				__( 'Validation Muse requires Contact Form 7 to be installed and activated. %1$sInstall Contact Form 7%2$s', 'validation-muse-for-contact-form-7' ),
				'<a href="' . esc_url( $install_url ) . '">',
				'</a>'
			),
			array(
				'a' => array(
					'href' => array(),
				),
			)
		)
	);
}