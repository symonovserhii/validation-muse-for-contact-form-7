<?php
/**
 * Class VMCF7_Test
 *
 * @package ValidationMuse
 */

namespace {
    // Define stubs for CF7 classes.
    class WPCF7_ContactForm {
        private $id;
        public function __construct( $id ) {
            $this->id = $id;
        }
        public function id() {
            return $this->id;
        }
    }

    class WPCF7_FormTag {
        public $name;
        public $basetype;
        private $required;
        public function __construct( $name, $basetype, $required = false ) {
            $this->name     = $name;
            $this->basetype = $basetype;
            $this->required = $required;
        }
        public function is_required() {
            return $this->required;
        }
    }

    class WPCF7_Validation {
        private $invalid_fields = array();
        public function get_invalid_fields() {
            return $this->invalid_fields;
        }
        public function set_invalid_fields( $fields ) {
            $this->invalid_fields = $fields;
        }
        public function invalidate( $tag, $message ) {
            $this->invalid_fields[ $tag->name ] = array(
                'reason' => $message,
                'idref'  => '',
            );
        }
    }

    class WPCF7_Submission {
        private static $instance = null;
        private $posted_data = array();
        private $uploaded_files = array();
        public static function set_instance( $instance ) {
            self::$instance = $instance;
        }
        public static function get_instance() {
            return self::$instance;
        }
        public function set_posted_data( $data ) {
            $this->posted_data = $data;
        }
        public function get_posted_data( $name ) {
            return isset( $this->posted_data[ $name ] ) ? $this->posted_data[ $name ] : null;
        }
        public function set_uploaded_files( $files ) {
            $this->uploaded_files = $files;
        }
        public function uploaded_files() {
            return $this->uploaded_files;
        }
    }
}

// Define stubs for Flavor classes in their namespace.
namespace Flavor {
    class Flv_Languages {
        public static $current = 'en';
        public static $default = 'en';
        public static $targets = array();

        public static function get_current_language() {
            return self::$current;
        }
        public static function get_default_language() {
            return self::$default;
        }
        public static function get_target_languages() {
            return self::$targets;
        }
    }

    class Flv_DB {
        public static $records = array();

        public static function get( $type, $id, $key, $lang ) {
            $db_key = "{$type}_{$id}_{$key}_{$lang}";
            return isset( self::$records[ $db_key ] ) ? self::$records[ $db_key ] : null;
        }

        public static function get_all( $type, $id, $lang ) {
            $result = array();
            foreach ( self::$records as $db_key => $record ) {
                $prefix = "{$type}_{$id}_";
                if ( str_starts_with( $db_key, $prefix ) && str_ends_with( $db_key, "_{$lang}" ) ) {
                    $field_name = substr( $db_key, strlen( $prefix ) );
                    $field_name = substr( $field_name, 0, -strlen( "_{$lang}" ) );
                    $result[] = array(
                        'field_name'  => $field_name,
                        'translation' => $record['translation'],
                    );
                }
            }
            return $result;
        }

        public static function save( $data ) {
            $type   = $data['object_type'];
            $id     = $data['object_id'];
            $key    = $data['field_name'];
            $lang   = $data['language'];
            $db_key = "{$type}_{$id}_{$key}_{$lang}";

            self::$records[ $db_key ] = array(
                'translation' => $data['translation'],
                'status'      => $data['status'],
            );
        }

        public static function delete_by_field( $type, $id, $key, $lang ) {
            $db_key = "{$type}_{$id}_{$key}_{$lang}";
            unset( self::$records[ $db_key ] );
        }
    }

    class Flv_Status {
        const PENDING    = 'pending';
        const TRANSLATED = 'translated';
        const REVIEWED   = 'reviewed';
    }
}

// Main test class.
namespace {
    class VMCF7_Test extends \PHPUnit\Framework\TestCase {

        protected function setUp(): void {
            parent::setUp();
            \Brain\Monkey\setUp();

            // Set up common stubs for WordPress functions.
            \Brain\Monkey\Functions\stubs( array(
                'sanitize_key' => function( $key ) {
                    return strtolower( preg_replace( '/[^a-z0-9_]/i', '', $key ) );
                },
                'wp_unslash' => function( $val ) {
                    return $val;
                },
                'wp_kses_post' => function( $val ) {
                    return preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $val );
                },
                'sanitize_text_field' => function( $val ) {
                    return trim( (string) $val );
                },
                '__' => function( $text, $domain = 'default' ) {
                    return $text;
                },
                'esc_html__' => function( $text, $domain = 'default' ) {
                    return $text;
                },
            ) );

            // Clear Flavor DB mock.
            \Flavor\Flv_DB::$records = array();
            \Flavor\Flv_Languages::$current = 'en';
            \Flavor\Flv_Languages::$default = 'en';
            \Flavor\Flv_Languages::$targets = array();

            // Reset cache in Flavor via Reflection.
            $ref = new \ReflectionProperty( 'VMCF7_Flavor', 'cache' );
            $ref->setAccessible( true );
            $ref->setValue( null, array() );
        }

        protected function tearDown(): void {
            \Brain\Monkey\tearDown();
            parent::tearDown();
        }

        /**
         * Test basic custom required messages validation.
         */
        public function test_validate_field_required() {
            $loader = new VMCF7_Loader();
            $form   = new WPCF7_ContactForm( 123 );
            $tag    = new WPCF7_FormTag( 'your-name', 'text', true );

            // Set up current contact form.
            \Brain\Monkey\Functions\expect( 'wpcf7_get_current_contact_form' )
                ->andReturn( $form );

            // Mock get_post_meta to return custom messages.
            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturnUsing( function( $post_id, $key, $single ) {
                    if ( '_vmcf7_enabled' === $key ) {
                        return '1';
                    }
                    if ( '_vmcf7_yourname_required' === $key ) {
                        return 'Please enter your name!';
                    }
                    return '';
                } );

            // Mock submission.
            $submission = new WPCF7_Submission();
            $submission->set_posted_data( array( 'your-name' => '' ) );
            WPCF7_Submission::set_instance( $submission );

            // 1. Test not yet invalid (SWV does not invalidate, loader invalidates).
            $result = new WPCF7_Validation();
            $result = $loader->validate_field( $result, $tag );

            $invalid_fields = $result->get_invalid_fields();
            $this->assertArrayHasKey( 'your-name', $invalid_fields );
            $this->assertEquals( 'Please enter your name!', $invalid_fields['your-name']['reason'] );

            // 2. Test already invalid (SWV already invalidated, loader replaces reason).
            $result = new WPCF7_Validation();
            $result->invalidate( $tag, 'CF7 Default Required' );
            $result = $loader->validate_field( $result, $tag );

            $invalid_fields = $result->get_invalid_fields();
            $this->assertEquals( 'Please enter your name!', $invalid_fields['your-name']['reason'] );
        }

        /**
         * Test format validation for various types including the new time type.
         */
        public function test_validate_field_invalid_formats() {
            $loader = new VMCF7_Loader();
            $form   = new WPCF7_ContactForm( 123 );

            \Brain\Monkey\Functions\expect( 'wpcf7_get_current_contact_form' )
                ->andReturn( $form );

            // Mock get_post_meta.
            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturnUsing( function( $post_id, $key, $single ) {
                    if ( '_vmcf7_enabled' === $key ) {
                        return '1';
                    }
                    if ( str_ends_with( $key, '_invalid' ) ) {
                        return "Custom invalid message for test-field";
                    }
                    return '';
                } );

            // Define validation helper mocks once using Functions\when to avoid expectation conflicts.
            \Brain\Monkey\Functions\when( 'wpcf7_is_email' )->alias( function( $val ) {
                return $val === 'test@example.com';
            } );
            \Brain\Monkey\Functions\when( 'wpcf7_is_url' )->alias( function( $val ) {
                return $val === 'http://example.com';
            } );
            \Brain\Monkey\Functions\when( 'wpcf7_is_tel' )->alias( function( $val ) {
                return $val === '1234567890';
            } );
            \Brain\Monkey\Functions\when( 'wpcf7_is_number' )->alias( function( $val ) {
                return $val === '42' || $val === '15';
            } );
            \Brain\Monkey\Functions\when( 'wpcf7_is_date' )->alias( function( $val ) {
                return $val === '2026-05-31';
            } );
            \Brain\Monkey\Functions\when( 'wpcf7_is_time' )->alias( function( $val ) {
                return $val === '14:30';
            } );

            $types = array(
                'email'  => array( 'invalid' => 'not-an-email', 'valid' => 'test@example.com' ),
                'url'    => array( 'invalid' => 'not-a-url', 'valid' => 'http://example.com' ),
                'tel'    => array( 'invalid' => 'not-a-tel', 'valid' => '1234567890' ),
                'number' => array( 'invalid' => 'not-a-number', 'valid' => '42' ),
                'range'  => array( 'invalid' => 'not-a-number', 'valid' => '15' ),
                'date'   => array( 'invalid' => 'not-a-date', 'valid' => '2026-05-31' ),
                'time'   => array( 'invalid' => 'not-a-time', 'valid' => '14:30' ),
            );

            foreach ( $types as $basetype => $config ) {
                $tag = new WPCF7_FormTag( 'test-field', $basetype );

                // 1. Test validation on invalid input.
                $submission = new WPCF7_Submission();
                $submission->set_posted_data( array( 'test-field' => $config['invalid'] ) );
                WPCF7_Submission::set_instance( $submission );

                $result = new WPCF7_Validation();
                $result = $loader->validate_field( $result, $tag );

                $invalid_fields = $result->get_invalid_fields();
                $this->assertArrayHasKey( 'test-field', $invalid_fields );
                $this->assertEquals( "Custom invalid message for test-field", $invalid_fields['test-field']['reason'] );

                // 2. Test validation on valid input.
                $submission = new WPCF7_Submission();
                $submission->set_posted_data( array( 'test-field' => $config['valid'] ) );
                WPCF7_Submission::set_instance( $submission );

                $result = new WPCF7_Validation();
                $result = $loader->validate_field( $result, $tag );

                $invalid_fields = $result->get_invalid_fields();
                $this->assertArrayNotHasKey( 'test-field', $invalid_fields );
            }
        }

        /**
         * Test ReflectionProperty failure debug logging.
         */
        public function test_replace_error_reflection_failure() {
            $loader = new VMCF7_Loader();

            // Create a stub of WPCF7_Validation that does NOT have 'invalid_fields' property, causing ReflectionException.
            $bad_result = new \stdClass();

            // Expect action 'vmcf7_debug' to be fired.
            \Brain\Monkey\Actions\expectDone( 'vmcf7_debug' )
                ->once()
                ->with( \Mockery::on( function( $msg ) {
                    return str_contains( $msg, 'Reflection failure' );
                } ) );

            // Call private method replace_error using Reflection.
            $ref = new \ReflectionMethod( $loader, 'replace_error' );
            $ref->setAccessible( true );
            $ref->invoke( $loader, $bad_result, 'test-field', 'some message' );

            $this->assertTrue( true );
        }

        /**
         * Test VMCF7_Admin::save_messages sanitization.
         */
        public function test_save_messages_sanitization() {
            $admin = new VMCF7_Admin();
            $form  = new WPCF7_ContactForm( 123 );

            // Mock check_ajax_referer or nonce/capability.
            \Brain\Monkey\Functions\expect( 'current_user_can' )
                ->with( 'wpcf7_edit_contact_forms' )
                ->andReturn( true );

            \Brain\Monkey\Functions\expect( 'wp_verify_nonce' )
                ->andReturn( true );

            $saved_meta = array();
            \Brain\Monkey\Functions\expect( 'update_post_meta' )
                ->andReturnUsing( function( $id, $key, $value ) use ( &$saved_meta ) {
                    $saved_meta[ $key ] = $value;
                    return true;
                } );

            $_POST['vmcf7_nonce']   = 'test_nonce';
            $_POST['vmcf7_enabled'] = '1';
            $_POST['vmcf7']         = array(
                'your-email' => array(
                    'required' => 'Required <script>alert(1)</script>field',
                    'invalid'  => 'Invalid format message',
                ),
            );

            $admin->save_messages( $form );

            $this->assertEquals( '1', $saved_meta['_vmcf7_enabled'] );
            $this->assertEquals( 'Required field', $saved_meta['_vmcf7_youremail_required'] );
            $this->assertEquals( 'Invalid format message', $saved_meta['_vmcf7_youremail_invalid'] );

            // Clean up $_POST.
            unset( $_POST['vmcf7_nonce'] );
            unset( $_POST['vmcf7_enabled'] );
            unset( $_POST['vmcf7'] );
        }

        /**
         * Test VMCF7_Flavor features.
         */
        public function test_flavor_integration() {
            // Test field_key method.
            $this->assertEquals( 'vmcf7_yourname_required', VMCF7_Flavor::field_key( 'your-name', 'required' ) );

            // Save translations.
            VMCF7_Flavor::save_translation( 123, 'your-name', 'required', 'de', 'Name erforderlich', false ); // status reviewed
            VMCF7_Flavor::save_translation( 123, 'your-name', 'invalid', 'de', 'Falsches Format', true );    // status translated

            $this->assertEquals( 'Name erforderlich', VMCF7_Flavor::get_translation( 123, 'your-name', 'required', 'de' ) );
            $this->assertEquals( 'Falsches Format', VMCF7_Flavor::get_translation( 123, 'your-name', 'invalid', 'de' ) );

            // Test get_all_translations.
            $all = VMCF7_Flavor::get_all_translations( 123, 'de' );
            $this->assertEquals( 'Name erforderlich', $all['vmcf7_yourname_required'] );

            // Test delete_translation.
            VMCF7_Flavor::delete_translation( 123, 'your-name', 'required', 'de' );
            $this->assertNull( VMCF7_Flavor::get_translation( 123, 'your-name', 'required', 'de' ) );
        }

        /**
         * Test PENDING translation handling and request caching in VMCF7_Flavor.
         */
        public function test_flavor_pending_translation_and_caching() {
            // Set up active language.
            \Flavor\Flv_Languages::$current = 'de';
            \Flavor\Flv_Languages::$default = 'en';

            $db_key = 'post_meta_123_vmcf7_yourname_required_de';
            \Flavor\Flv_DB::$records[ $db_key ] = array(
                'translation' => 'Pending text',
                'status'      => 'pending',
            );

            // Expect action 'vmcf7_debug' to be fired when pending translation is accessed.
            \Brain\Monkey\Actions\expectDone( 'vmcf7_debug' )
                ->once()
                ->with( \Mockery::on( function( $msg ) {
                    return str_contains( $msg, 'Pending translation accessed' );
                } ) );

            // 1. Test that we get the pending translation and trigger the debug hook.
            $translation = VMCF7_Flavor::get_translation( 123, 'your-name', 'required', 'de' );
            $this->assertEquals( 'Pending text', $translation );

            // 2. Test request caching. Verify DB is NOT queried again.
            // Temporarily break DB to confirm cache is used.
            unset( \Flavor\Flv_DB::$records[ $db_key ] );

            $translation_cached = VMCF7_Flavor::get_translation( 123, 'your-name', 'required', 'de' );
            $this->assertEquals( 'Pending text', $translation_cached );
        }

        /**
         * Test default invalid message helper returns expected message for time field.
         */
        public function test_get_default_invalid_message() {
            $admin = new VMCF7_Admin();
            $this->assertEquals( 'Please enter a valid time', $admin->get_default_invalid_message( 'time' ) );
            $this->assertEquals( 'Please enter a valid email address', $admin->get_default_invalid_message( 'email' ) );
        }
    }
}
