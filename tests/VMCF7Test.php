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
        public $props = array();
        public $tags = array();
        public function __construct( $id ) {
            $this->id = $id;
        }
        public function id() {
            return $this->id;
        }
        public function prop( $name ) {
            return isset( $this->props[ $name ] ) ? $this->props[ $name ] : '';
        }
        public function scan_form_tags() {
            return $this->tags;
        }
    }

    class WPCF7_FormTag {
        public $name;
        public $basetype;
        private $required;
        public $options = array();
        public function __construct( $name, $basetype, $required = false ) {
            $this->name     = $name;
            $this->basetype = $basetype;
            $this->required = $required;
        }
        public function is_required() {
            return $this->required;
        }
        public function get_id_option() {
            return isset( $this->options['id'] ) ? $this->options['id'] : '';
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

    class Test_SWV_Schema {
        private $rules = array();
        public function __construct( $rules = array() ) {
            $this->rules = $rules;
        }
        public function rules() {
            return $this->rules;
        }
        public function add_rule( $rule ) {
            $this->rules[] = $rule;
        }
    }

    if ( ! function_exists( 'wpcf7_swv_create_rule' ) ) {
        function wpcf7_swv_create_rule( $name, $args ) {
            return new \Contactable\SWV\Rule( $name, $args );
        }
    }
}

namespace Contactable\SWV {
    class Rule {
        protected $properties = array();
        public function __construct( $name, $properties = array() ) {
            $this->properties = array_merge( array( 'rule' => $name ), $properties );
        }
        public function to_array() {
            return $this->properties;
        }
        public function get_property( $name ) {
            return isset( $this->properties[ $name ] ) ? $this->properties[ $name ] : null;
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
                'esc_attr' => function( $val ) {
                    return htmlspecialchars( (string) $val, ENT_QUOTES, 'UTF-8' );
                },
                'esc_html' => function( $val ) {
                    return htmlspecialchars( (string) $val, ENT_QUOTES, 'UTF-8' );
                },
                'wp_strip_all_tags' => function( $val ) {
                    return strip_tags( (string) $val );
                },
                'is_admin' => function() {
                    return false;
                },
                'wp_script_is' => function( $handle, $list = 'enqueued' ) {
                    return false;
                },
                'wp_enqueue_script' => function( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {},
                'wp_enqueue_style' => function( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {},
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

        /**
         * Test regex validation evaluator.
         */
        public function test_regex_validation() {
            $this->assertTrue( \VMCF7_Rules::evaluate_regex( '12345', '^[0-9]+$' ) );
            $this->assertFalse( \VMCF7_Rules::evaluate_regex( 'abc', '^[0-9]+$' ) );
            $this->assertFalse( \VMCF7_Rules::evaluate_regex( '', '^[0-9]+$' ) );
        }

        /**
         * Test length validation evaluator.
         */
        public function test_length_validation() {
            $res = \VMCF7_Rules::evaluate_length( 'hello', 3, 10 );
            $this->assertTrue( $res['valid'] );

            $res = \VMCF7_Rules::evaluate_length( 'hi', 3, 10 );
            $this->assertFalse( $res['valid'] );
            $this->assertEquals( 'min', $res['type'] );

            $res = \VMCF7_Rules::evaluate_length( 'hello world', 3, 5 );
            $this->assertFalse( $res['valid'] );
            $this->assertEquals( 'max', $res['type'] );
        }

        /**
         * Test token expansion and label extraction.
         */
        public function test_token_expansion() {
            $loader = new VMCF7_Loader();
            $form   = new WPCF7_ContactForm( 123 );
            $tag    = new WPCF7_FormTag( 'your-name', 'text' );

            $form->props = array(
                'form' => '<label> Your Custom Name <br /> [text your-name] </label>'
            );

            \Brain\Monkey\Functions\expect( 'wpcf7_get_current_contact_form' )
                ->andReturn( $form );

            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturnUsing( function( $post_id, $key, $single ) {
                    if ( '_vmcf7_enabled' === $key ) {
                        return '1';
                    }
                    if ( '_vmcf7_yourname_required' === $key ) {
                        return '{field_label} is required!';
                    }
                    return '';
                } );

            $submission = new WPCF7_Submission();
            $submission->set_posted_data( array( 'your-name' => '' ) );
            WPCF7_Submission::set_instance( $submission );

            $result = new WPCF7_Validation();
            $result = $loader->validate_field( $result, $tag );

            $invalid_fields = $result->get_invalid_fields();
            $this->assertArrayNotHasKey( 'your-name', $invalid_fields );
            // Wait, tag is not required in constructor ($required = false), but loader checks empty.
            // Oh, since tag is not required, loader doesn't flag it as invalid for normal required check,
            // EXCEPT if tag is required, or it's a required-if field.
            // Let's test with a required tag.
            $tag_req = new WPCF7_FormTag( 'your-name', 'text', true );
            $result_req = new WPCF7_Validation();
            $result_req = $loader->validate_field( $result_req, $tag_req );
            $invalid_fields_req = $result_req->get_invalid_fields();
            $this->assertArrayHasKey( 'your-name', $invalid_fields_req );
            $this->assertEquals( 'Your Custom Name is required!', $invalid_fields_req['your-name']['reason'] );
        }

        /**
         * Test conditional required-if validation logic.
         */
        public function test_required_if_validation() {
            $loader = new VMCF7_Loader();
            $form   = new WPCF7_ContactForm( 123 );
            $tag    = new WPCF7_FormTag( 'your-email', 'email' );

            $form->props = array(
                'form' => '[email your-email]'
            );

            \Brain\Monkey\Functions\expect( 'wpcf7_get_current_contact_form' )
                ->andReturn( $form );

            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturnUsing( function( $post_id, $key, $single ) {
                    if ( '_vmcf7_enabled' === $key ) {
                        return '1';
                    }
                    if ( '_vmcf7_youremail_required_if_field' === $key ) {
                        return 'subscribe-checkbox';
                    }
                    if ( '_vmcf7_youremail_required_if_message' === $key ) {
                        return 'Email is required if you subscribe!';
                    }
                    return '';
                } );

            $submission = new WPCF7_Submission();
            // Companion field is empty -> not required
            $submission->set_posted_data( array( 'your-email' => '', 'subscribe-checkbox' => '' ) );
            WPCF7_Submission::set_instance( $submission );

            $result = new WPCF7_Validation();
            $result = $loader->validate_field( $result, $tag );
            $this->assertArrayNotHasKey( 'your-email', $result->get_invalid_fields() );

            // Companion field is filled -> required -> invalid because empty
            $submission = new WPCF7_Submission();
            $submission->set_posted_data( array( 'your-email' => '', 'subscribe-checkbox' => '1' ) );
            WPCF7_Submission::set_instance( $submission );

            $result = new WPCF7_Validation();
            $result = $loader->validate_field( $result, $tag );
            $invalid_fields = $result->get_invalid_fields();
            $this->assertArrayHasKey( 'your-email', $invalid_fields );
            $this->assertEquals( 'Email is required if you subscribe!', $invalid_fields['your-email']['reason'] );
        }

        /**
         * Test WPML/Polylang i18n compatibility.
         */
        public function test_i18n_compat_no_op() {
            $compat = new VMCF7_I18n_Compat();

            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturn( 'Original message' );

            // When filters run without any active multilanguage plugins, they should return original messages.
            $translated = $compat->translate_message( 'Original message', 123, 'test-field', 'required' );
            $this->assertEquals( 'Original message', $translated );
        }

        /**
         * Test SWV rules injection.
         */
        public function test_add_swv_rules() {
            $loader = new VMCF7_Loader();
            $form   = new WPCF7_ContactForm( 123 );
            $tag    = new WPCF7_FormTag( 'your-phone', 'tel' );

            $form->tags = array( $tag );

            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->andReturnUsing( function( $post_id, $key, $single ) {
                    if ( '_vmcf7_enabled' === $key ) {
                        return '1';
                    }
                    if ( '_vmcf7_yourphone_required' === $key ) {
                        return 'Phone required!';
                    }
                    if ( '_vmcf7_yourphone_min_length' === $key ) {
                        return '5';
                    }
                    if ( '_vmcf7_yourphone_max_length' === $key ) {
                        return '15';
                    }
                    if ( '_vmcf7_yourphone_length_message' === $key ) {
                        return 'Length error {min}-{max}';
                    }
                    return '';
                } );

            // Let's create an existing required rule
            $existing_req_rule = new \Contactable\SWV\Rule( 'required', array(
                'field' => 'your-phone',
                'error' => 'Default required error',
            ) );

            $schema = new \Test_SWV_Schema( array( $existing_req_rule ) );

            $loader->add_swv_rules( $schema, $form );

            $rules = $schema->rules();
            $this->assertCount( 3, $rules ); // existing overridden required, plus added minlength and maxlength

            // Check overridden required error message
            $req_props = $rules[0]->to_array();
            $this->assertEquals( 'Phone required!', $req_props['error'] );

            // Check added minlength rule
            $min_props = $rules[1]->to_array();
            $this->assertEquals( 'minlength', $min_props['rule'] );
            $this->assertEquals( 'your-phone', $min_props['field'] );
            $this->assertEquals( '5', $min_props['threshold'] );
            $this->assertEquals( 'Length error 5-15', $min_props['error'] );

            // Check added maxlength rule
            $max_props = $rules[2]->to_array();
            $this->assertEquals( 'maxlength', $max_props['rule'] );
            $this->assertEquals( 'your-phone', $max_props['field'] );
            $this->assertEquals( '15', $max_props['threshold'] );
            $this->assertEquals( 'Length error 5-15', $max_props['error'] );
        }

        /**
         * Test import/export round-trip.
         */
        public function test_import_export_round_trip() {
            $admin = new VMCF7_Admin();
            $form_id = 456;

            // 1. Prepare simulated rules meta in DB.
            $original_rules = array(
                '_vmcf7_enabled' => array( '1' ),
                '_vmcf7_yourname_required' => array( 'Name is required!' ),
                '_vmcf7_yourname_regex' => array( '^[a-zA-Z]+$' ),
                '_vmcf7_yourname_min_length' => array( '3' ),
                '_vmcf7_yourname_max_length' => array( '20' ),
                '_vmcf7_yourname_required_if_field' => array( 'somefield' ),
            );

            // Mock get_post_meta to return these rules.
            \Brain\Monkey\Functions\expect( 'get_post_meta' )
                ->with( $form_id )
                ->andReturn( $original_rules );

            // 2. Perform the export step (simulate ajax_export_rules array construction).
            $meta = $original_rules;
            $rules = array();
            foreach ( $meta as $key => $values ) {
                if ( str_starts_with( $key, '_vmcf7_' ) ) {
                    $rules[ $key ] = isset( $values[0] ) ? $values[0] : '';
                }
            }
            $export_data = array(
                'rules'        => $rules,
                'translations' => array(),
            );

            // 3. Write exported data to a JSON temp file.
            $temp_file = tempnam( sys_get_temp_dir(), 'vmcf7_import_test' );
            file_put_contents( $temp_file, json_encode( $export_data ) );

            $_FILES['import_file'] = array(
                'name'     => 'export.json',
                'type'     => 'application/json',
                'tmp_name' => $temp_file,
                'error'    => 0,
                'size'     => filesize( $temp_file ),
            );

            $_POST['form_id'] = $form_id;

            // 4. Mock functions for the import.
            \Brain\Monkey\Functions\expect( 'check_ajax_referer' )
                ->once()
                ->with( 'vmcf7_ajax_nonce', 'nonce' )
                ->andReturn( true );

            \Brain\Monkey\Functions\expect( 'current_user_can' )
                ->once()
                ->with( 'wpcf7_edit_contact_forms' )
                ->andReturn( true );

            $deleted_keys = array();
            \Brain\Monkey\Functions\expect( 'delete_post_meta' )
                ->andReturnUsing( function( $id, $key ) use ( &$deleted_keys ) {
                    $deleted_keys[] = $key;
                    return true;
                } );

            $saved_meta = array();
            \Brain\Monkey\Functions\expect( 'update_post_meta' )
                ->andReturnUsing( function( $id, $key, $value ) use ( &$saved_meta ) {
                    $saved_meta[ $key ] = $value;
                    return true;
                } );

            \Brain\Monkey\Functions\expect( 'wp_send_json_success' )
                ->once()
                ->andReturnUsing( function( $response ) {
                    throw new \Exception( 'JSON_SUCCESS' );
                } );

            // 5. Execute import and assert success.
            try {
                $admin->ajax_import_rules();
                $this->fail( 'Expected ajax_import_rules to call wp_send_json_success.' );
            } catch ( \Exception $e ) {
                $this->assertEquals( 'JSON_SUCCESS', $e->getMessage() );
            }

            // 6. Assertions.
            // Check that old rules were cleared.
            $this->assertContains( '_vmcf7_enabled', $deleted_keys );
            $this->assertContains( '_vmcf7_yourname_required', $deleted_keys );

            // Check that rules were successfully re-imported and sanitized.
            $this->assertEquals( '1', $saved_meta['_vmcf7_enabled'] );
            $this->assertEquals( 'Name is required!', $saved_meta['_vmcf7_yourname_required'] );
            $this->assertEquals( '^[a-zA-Z]+$', $saved_meta['_vmcf7_yourname_regex'] );
            $this->assertEquals( 3, $saved_meta['_vmcf7_yourname_min_length'] );
            $this->assertEquals( 20, $saved_meta['_vmcf7_yourname_max_length'] );
            $this->assertEquals( 'somefield', $saved_meta['_vmcf7_yourname_required_if_field'] );

            // Clean up files and superglobals.
            if ( file_exists( $temp_file ) ) {
                unlink( $temp_file );
            }
            unset( $_FILES['import_file'] );
            unset( $_POST['form_id'] );
        }
    }
}
