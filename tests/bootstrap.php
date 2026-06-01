<?php
/**
 * Bootstrap for PHPUnit tests.
 *
 * @package ValidationMuse
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', sys_get_temp_dir() . '/' );
}

// Define plugin constants so they are available in tests.
if ( ! defined( 'VMCF7_VERSION' ) ) {
    define( 'VMCF7_VERSION', '1.4.3' );
}
if ( ! defined( 'VMCF7_FILE' ) ) {
    define( 'VMCF7_FILE', dirname( __DIR__ ) . '/validation-muse-for-contact-form-7.php' );
}
if ( ! defined( 'VMCF7_PATH' ) ) {
    define( 'VMCF7_PATH', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'VMCF7_URL' ) ) {
    define( 'VMCF7_URL', 'https://example.com/wp-content/plugins/validation-muse-for-contact-form-7/' );
}
if ( ! defined( 'VMCF7_BASENAME' ) ) {
    define( 'VMCF7_BASENAME', 'validation-muse-for-contact-form-7/validation-muse-for-contact-form-7.php' );
}

// Require Composer autoload.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';
