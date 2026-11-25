<?php
/**
 * Regression tests for the 1.8.0 audit: CF7 6.2+ SWV API, rule evaluation edge
 * cases, save/sanitize, rule-set AJAX handlers, Flavor and Polylang bridges.
 *
 * @package ValidationMuse
 */

namespace {
    // The CF7 / Flavor stubs live at the top of VMCF7Test.php.
    require_once __DIR__ . '/VMCF7Test.php';

    /**
     * CF7 6.2+ style rule: immutable, no `properties` array, camelCase API.
     */
    final class Test_Modern_SWV_Rule {
        private $data;
        public function __construct( array $properties = array() ) {
            $this->data = $properties;
        }
        public function toArray(): array {
            return $this->data;
        }
    }

    /**
     * CF7 6.2+ style schema: `rules()` returns an array, `addRule()` appends.
     */
    class Test_Modern_SWV_Schema {
        protected array $rules = array();
        public function addRule( $rule ): bool {
            $this->rules[] = $rule;
            return true;
        }
        public function rules(): array {
            return $this->rules;
        }
    }

    /**
     * CF7 ≤ 6.1 style schema: `rules()` is a generator.
     */
    class Test_Generator_SWV_Schema {
        protected $rules = array();
        public function add_rule( $rule ) {
            $this->rules[] = $rule;
        }
        public function rules() {
            foreach ( $this->rules as $rule ) {
                yield $rule;
            }
        }
    }
}

namespace Flavor {
    if ( ! class_exists( __NAMESPACE__ . '\Flv_AI_Translator' ) ) {
        class Flv_AI_Translator {
            public static $last_texts = array();
            public static $response   = null;
            public function is_configured() {
                return true;
            }
            public function translate_batch( array $texts, string $lang ): array {
                self::$last_texts = $texts;
                if ( null !== self::$response ) {
                    return self::$response;
                }
                $out = array();
                foreach ( $texts as $key => $text ) {
                    $out[ $key ] = "[{$lang}] {$text}";
                }
                return $out;
            }
        }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use Brain\Monkey;
    use Brain\Monkey\Functions;
    use Brain\Monkey\Filters;

    class Hardening_Test extends TestCase {

        /** @var array Post meta fixture: form ID => ( meta key => value ). */
        private $meta = array();

        /** @var array Calls recorded by the update/delete_post_meta stubs. */
        private $saved   = array();
        private $deleted = array();

        protected function setUp(): void {
            parent::setUp();
            Monkey\setUp();

            $this->meta    = array();
            $this->saved   = array();
            $this->deleted = array();

            Functions\stubs( array(
                'get_option'         => array(),
                'update_option'      => true,
                // Unlike the legacy suite's stub this one keeps hyphens, as WordPress does.
                'sanitize_key'       => function( $key ) {
                    return is_scalar( $key ) ? preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ) : '';
                },
                'wp_unslash'         => function( $val ) {
                    return $val;
                },
                'wp_slash'           => function( $val ) {
                    return is_string( $val ) ? addslashes( $val ) : $val;
                },
                'wp_kses_post'       => function( $val ) {
                    return preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $val );
                },
                'absint'             => function( $val ) {
                    return abs( (int) $val );
                },
                'sanitize_text_field' => function( $val ) {
                    return trim( (string) $val );
                },
                'sanitize_file_name' => function( $val ) {
                    return (string) $val;
                },
                '__'                 => function( $text ) {
                    return $text;
                },
                'wp_strip_all_tags'  => function( $val ) {
                    return strip_tags( (string) $val );
                },
                'is_admin'           => false,
                'get_post_type'      => 'wpcf7_contact_form',
                'current_user_can'   => true,
                'check_ajax_referer' => true,
                'wp_verify_nonce'    => true,
            ) );

            Functions\when( 'get_post_meta' )->alias( function( $post_id, $key = '', $single = false ) {
                $rows = isset( $this->meta[ $post_id ] ) ? $this->meta[ $post_id ] : array();
                if ( '' === $key ) {
                    return array_map( function( $value ) {
                        return array( $value );
                    }, $rows );
                }
                return isset( $rows[ $key ] ) ? $rows[ $key ] : '';
            } );

            // The stubs behave like WordPress: update_post_meta() unslashes what it is given.
            Functions\when( 'update_post_meta' )->alias( function( $id, $key, $value ) {
                $value                      = is_string( $value ) ? stripslashes( $value ) : $value;
                $this->saved[ $id ][ $key ] = $value;
                $this->meta[ $id ][ $key ]  = $value;
                return true;
            } );

            Functions\when( 'delete_post_meta' )->alias( function( $id, $key ) {
                $this->deleted[ $id ][] = $key;
                unset( $this->meta[ $id ][ $key ] );
                return true;
            } );

            Functions\when( 'wp_send_json_success' )->alias( function( $data = null ) {
                throw new \RuntimeException( 'JSON_SUCCESS' );
            } );
            Functions\when( 'wp_send_json_error' )->alias( function( $data = null, $status = null ) {
                throw new \RuntimeException( 'JSON_ERROR:' . ( isset( $data['message'] ) ? $data['message'] : '' ) );
            } );

            \Flavor\Flv_DB::$records        = array();
            \Flavor\Flv_Languages::$current = 'en';
            \Flavor\Flv_Languages::$default = 'en';
            \Flavor\Flv_Languages::$targets = array();
            \Flavor\Flv_AI_Translator::$last_texts = array();
            \Flavor\Flv_AI_Translator::$response   = null;

            $GLOBALS['vmcf7_test_modern_swv'] = false;
            WPCF7_Submission::set_instance( null );
        }

        protected function tearDown(): void {
            $GLOBALS['vmcf7_test_modern_swv'] = false;
            unset( $_POST['form_id'], $_POST['from_form_id'], $_POST['to_form_id'], $_POST['vmcf7'], $_POST['vmcf7_nonce'], $_POST['vmcf7_enabled'], $_FILES['import_file'], $_GET['page'] );
            Monkey\tearDown();
            parent::tearDown();
        }

        /**
         * Run validate_field() for one tag against a submission.
         */
        private function validate( WPCF7_ContactForm $form, WPCF7_FormTag $tag, array $posted, $preset_error = null, array $files = array() ) {
            Functions\when( 'wpcf7_get_current_contact_form' )->justReturn( $form );

            $submission = new WPCF7_Submission();
            $submission->set_posted_data( $posted );
            $submission->set_uploaded_files( $files );
            WPCF7_Submission::set_instance( $submission );

            $result = new WPCF7_Validation();
            if ( null !== $preset_error ) {
                $result->invalidate( $tag, $preset_error );
            }

            $result  = ( new VMCF7_Loader() )->validate_field( $result, $tag );
            $invalid = $result->get_invalid_fields();

            return isset( $invalid[ $tag->name ] ) ? $invalid[ $tag->name ]['reason'] : null;
        }

        private function run_ajax( callable $handler ) {
            try {
                $handler();
            } catch ( \RuntimeException $e ) {
                return $e->getMessage();
            }
            return 'NO_RESPONSE';
        }

        /* ---------------------------------------------------------------
         * SWV schema injection
         * ------------------------------------------------------------- */

        /**
         * CF7 6.2+ rules are immutable and have no `properties` array: the custom
         * messages must still reach the schema (they did not before 1.8.0).
         */
        public function test_swv_injection_works_with_cf7_62_immutable_rules() {
            $GLOBALS['vmcf7_test_modern_swv'] = true;

            $form       = new WPCF7_ContactForm( 7 );
            $form->tags = array(
                new WPCF7_FormTag( 'your-email', 'email', true ),
                new WPCF7_FormTag( 'message', 'textarea', true ),
            );
            $form->props = array( 'form' => '<label>E-mail *</label>[email* your-email]' );

            $this->meta[7] = array(
                '_vmcf7_enabled'                => '1',
                '_vmcf7_your-email_required'    => '{field_label} is required',
                '_vmcf7_your-email_invalid'     => 'Bad e-mail',
                '_vmcf7_message_min_length'     => '10',
                '_vmcf7_message_max_length'     => '500',
                '_vmcf7_message_length_message' => 'Between {min} and {max}',
            );

            $required = new Test_Modern_SWV_Rule( array( 'rule' => 'required', 'field' => 'your-email', 'error' => 'CF7 required' ) );
            $email    = new Test_Modern_SWV_Rule( array( 'rule' => 'email', 'field' => 'your-email', 'error' => 'CF7 email' ) );
            $untouched = new Test_Modern_SWV_Rule( array( 'rule' => 'required', 'field' => 'message', 'error' => 'CF7 required' ) );
            $maxlength = new Test_Modern_SWV_Rule( array( 'rule' => 'maxlength', 'field' => 'message', 'error' => 'CF7 long', 'threshold' => '2000' ) );

            $schema = new Test_Modern_SWV_Schema();
            foreach ( array( $required, $email, $untouched, $maxlength ) as $rule ) {
                $schema->addRule( $rule );
            }

            ( new VMCF7_Loader() )->add_swv_rules( $schema, $form );

            $rules = array_map( function( $rule ) {
                return $rule->toArray();
            }, $schema->rules() );

            $this->assertSame(
                array(
                    array( 'rule' => 'required', 'field' => 'your-email', 'error' => 'E-mail is required' ),
                    array( 'rule' => 'email', 'field' => 'your-email', 'error' => 'Bad e-mail' ),
                    array( 'rule' => 'required', 'field' => 'message', 'error' => 'CF7 required' ),
                    array( 'rule' => 'maxlength', 'field' => 'message', 'error' => 'Between 10 and 500', 'threshold' => '500' ),
                    array( 'rule' => 'minlength', 'field' => 'message', 'threshold' => '10', 'error' => 'Between 10 and 500' ),
                ),
                $rules
            );

            // Immutable originals are left alone; a rule without a custom message is kept as is.
            $this->assertSame( 'CF7 required', $required->toArray()['error'] );
            $this->assertSame( $untouched, $schema->rules()[2] );
        }

        /**
         * CF7 ≤ 6.1: rules() is a generator and rules are changed in place.
         */
        public function test_swv_injection_still_works_with_legacy_generator_schema() {
            $form       = new WPCF7_ContactForm( 8 );
            $form->tags = array( new WPCF7_FormTag( 'age', 'number', true ) );

            $this->meta[8] = array(
                '_vmcf7_enabled'     => '1',
                '_vmcf7_age_invalid' => 'Not a valid age',
            );

            $schema = new Test_Generator_SWV_Schema();
            $schema->add_rule( new \Contactable\SWV\Rule( 'number', array( 'field' => 'age', 'error' => 'CF7 number' ) ) );
            $schema->add_rule( new \Contactable\SWV\Rule( 'maxnumber', array( 'field' => 'age', 'error' => 'CF7 max', 'threshold' => '99' ) ) );

            ( new VMCF7_Loader() )->add_swv_rules( $schema, $form );

            $rules = iterator_to_array( $schema->rules(), false );
            $this->assertSame( 'Not a valid age', $rules[0]->to_array()['error'] );
            // Range errors take the invalid message too, as they do server-side.
            $this->assertSame( 'Not a valid age', $rules[1]->to_array()['error'] );
            $this->assertSame( '99', $rules[1]->to_array()['threshold'] );
        }

        /**
         * A file field gets no minlength/maxlength rule, and a zero length is "not set".
         */
        public function test_swv_length_rules_skip_file_fields_and_zero_lengths() {
            $GLOBALS['vmcf7_test_modern_swv'] = true;

            $form       = new WPCF7_ContactForm( 9 );
            $form->tags = array(
                new WPCF7_FormTag( 'cv', 'file' ),
                new WPCF7_FormTag( 'note', 'text' ),
            );

            $this->meta[9] = array(
                '_vmcf7_enabled'         => '1',
                '_vmcf7_cv_min_length'   => '5',
                '_vmcf7_note_max_length' => '0',
            );

            $schema = new Test_Modern_SWV_Schema();
            ( new VMCF7_Loader() )->add_swv_rules( $schema, $form );

            $this->assertSame( array(), $schema->rules() );
        }

        /* ---------------------------------------------------------------
         * Rule evaluation
         * ------------------------------------------------------------- */

        public function test_invalid_regex_is_ignored_instead_of_rejecting_everything() {
            $this->assertFalse( VMCF7_Rules::is_valid_regex( '([a-z' ) );
            $this->assertTrue( VMCF7_Rules::evaluate_regex( 'anything', '([a-z' ) );
            $this->assertFalse( VMCF7_Rules::is_valid_regex( array( 'x' ) ) );
        }

        public function test_regex_with_tilde_and_backslashes() {
            $this->assertTrue( VMCF7_Rules::evaluate_regex( 'a~b', '^a~b$' ) );
            $this->assertTrue( VMCF7_Rules::evaluate_regex( 'a~b', '^a\~b$' ) );
            $this->assertFalse( VMCF7_Rules::evaluate_regex( 'a-b', '^a\~b$' ) );
            $this->assertTrue( VMCF7_Rules::evaluate_regex( '+38 (044) 123', '^\+?[\d\s()]+$' ) );
        }

        public function test_rules_accept_list_values() {
            $this->assertTrue( VMCF7_Rules::evaluate_regex( array( 'red', '', 'blue' ), '^[a-z]+$' ) );
            $this->assertFalse( VMCF7_Rules::evaluate_regex( array( 'red', 'Blue 2' ), '^[a-z]+$' ) );

            $this->assertTrue( VMCF7_Rules::evaluate_length( array( 'ab', 'cd' ), 3, 4 )['valid'] );
            $this->assertSame( 'max', VMCF7_Rules::evaluate_length( array( 'ab', 'cde' ), '', 4 )['type'] );
        }

        public function test_parse_key() {
            $this->assertSame( array( 'field' => 'phone', 'type' => 'required_if_message' ), VMCF7_Rules::parse_key( '_vmcf7_phone_required_if_message' ) );
            $this->assertSame( array( 'field' => 'your-name', 'type' => 'regex' ), VMCF7_Rules::parse_key( '_vmcf7_your-name_regex' ) );
            $this->assertSame( array( 'field' => 'a', 'type' => 'length_message' ), VMCF7_Rules::parse_key( 'vmcf7_a_length_message', true ) );
            $this->assertNull( VMCF7_Rules::parse_key( 'vmcf7_a_regex', true ) );
            $this->assertNull( VMCF7_Rules::parse_key( '_vmcf7_enabled' ) );
            $this->assertNull( VMCF7_Rules::parse_key( '_vmcf7_Bad Key_required' ) );
            $this->assertNull( VMCF7_Rules::parse_key( array() ) );
        }

        /* ---------------------------------------------------------------
         * validate_field()
         * ------------------------------------------------------------- */

        /**
         * A checkbox posts an array: a regex or length rule on it used to be a TypeError.
         */
        public function test_checkbox_array_value_with_text_rules_does_not_fatal() {
            $form = new WPCF7_ContactForm( 10 );
            $tag  = new WPCF7_FormTag( 'colors', 'checkbox' );

            $this->meta[10] = array(
                '_vmcf7_enabled'              => '1',
                '_vmcf7_colors_regex'         => '^[a-z]+$',
                '_vmcf7_colors_regex_message' => 'Lowercase only',
                '_vmcf7_colors_max_length'    => '6',
            );

            $this->assertNull( $this->validate( $form, $tag, array( 'colors' => array( 'red', 'sky' ) ) ) );
            $this->assertSame( 'Lowercase only', $this->validate( $form, $tag, array( 'colors' => array( 'red', 'Sky' ) ) ) );
            $this->assertSame( 'Invalid length.', $this->validate( $form, $tag, array( 'colors' => array( 'red', 'green' ) ) ) );
        }

        /**
         * A file's value is a temp path: text rules must not run on it, but the
         * custom invalid message still replaces CF7's file error.
         */
        public function test_file_field_ignores_text_rules() {
            $form = new WPCF7_ContactForm( 11 );
            $tag  = new WPCF7_FormTag( 'cv', 'file' );

            $this->meta[11] = array(
                '_vmcf7_enabled'       => '1',
                '_vmcf7_cv_regex'      => '^\d+$',
                '_vmcf7_cv_max_length' => '3',
                '_vmcf7_cv_invalid'    => 'PDF only',
            );

            $files = array( 'cv' => array( '/tmp/wpcf7/cv.pdf' ) );

            $this->assertNull( $this->validate( $form, $tag, array(), null, $files ) );
            $this->assertSame( 'PDF only', $this->validate( $form, $tag, array(), 'CF7 file type error', $files ) );
        }

        /**
         * An invalid stored pattern and a max length of 0 must not block submissions;
         * a visitor's backslashes are not stripped before matching.
         */
        public function test_broken_settings_do_not_block_and_backslashes_survive() {
            $form = new WPCF7_ContactForm( 12 );

            $this->meta[12] = array(
                '_vmcf7_enabled'         => '1',
                '_vmcf7_code_regex'      => '([a-z',
                '_vmcf7_code_max_length' => '0',
                '_vmcf7_path_regex'      => '^[a-z]+\\\\[a-z]+$',
            );

            $this->assertNull( $this->validate( $form, new WPCF7_FormTag( 'code', 'text' ), array( 'code' => 'abc' ) ) );
            $this->assertNull( $this->validate( $form, new WPCF7_FormTag( 'path', 'text' ), array( 'path' => 'dir\\file' ) ) );
        }

        /**
         * Acceptance tags are not "required" tags, yet CF7 rejects them when unticked:
         * the custom required message applies to any empty field CF7 already rejected.
         */
        public function test_required_message_replaces_error_on_empty_non_required_tag() {
            $form = new WPCF7_ContactForm( 13 );
            $tag  = new WPCF7_FormTag( 'agree', 'acceptance', false );

            $this->meta[13] = array(
                '_vmcf7_enabled'        => '1',
                '_vmcf7_agree_required' => 'Please accept the terms',
            );

            $this->assertSame( 'Please accept the terms', $this->validate( $form, $tag, array( 'agree' => '' ), 'You must accept the terms.' ) );
            // Not rejected by CF7 and not required: stays valid.
            $this->assertNull( $this->validate( $form, $tag, array( 'agree' => '' ) ) );
        }

        /**
         * The companion is stored lowercased; the posted data uses the template's casing.
         */
        public function test_required_if_finds_companion_with_capitals_and_expands_tokens() {
            $form       = new WPCF7_ContactForm( 14 );
            $phone      = new WPCF7_FormTag( 'phone', 'tel' );
            $form->tags = array( new WPCF7_FormTag( 'Call-Me', 'checkbox' ), $phone );

            $this->meta[14] = array(
                '_vmcf7_enabled'                   => '1',
                '_vmcf7_phone_required_if_field'   => 'call-me',
                '_vmcf7_phone_required_if_message' => 'Phone needed (max {max})',
                '_vmcf7_phone_max_length'          => '15',
            );

            $this->assertSame( 'Phone needed (max 15)', $this->validate( $form, $phone, array( 'Call-Me' => array( 'yes' ), 'phone' => '' ) ) );
            $this->assertNull( $this->validate( $form, $phone, array( 'Call-Me' => array( '' ), 'phone' => '' ) ) );
        }

        public function test_label_lookup_uses_the_bracketed_tag() {
            $form        = new WPCF7_ContactForm( 15 );
            $form->props = array(
                'form' => "<label>Your name (required) [text* your-name]</label>\n<label>Company</label>[text* name]\n<label>Phone:<br />[tel phone]</label>",
            );

            $this->meta[15] = array(
                '_vmcf7_enabled'        => '1',
                '_vmcf7_name_required'  => '{field_label}!',
                '_vmcf7_phone_required' => '{field_label}!',
            );

            // "name" must not pick up the label that merely contains the word "name".
            $this->assertSame( 'Company!', $this->validate( $form, new WPCF7_FormTag( 'name', 'text', true ), array( 'name' => '' ) ) );
            $this->assertSame( 'Phone!', $this->validate( $form, new WPCF7_FormTag( 'phone', 'tel', true ), array( 'phone' => '' ) ) );
        }

        /**
         * A tag without its own label must not take the previous field's
         * wrapping label: <label>Message [textarea msg]</label>[text* agree].
         */
        public function test_label_lookup_skips_previous_fields_label() {
            $form        = new WPCF7_ContactForm( 18 );
            $form->props = array(
                'form' => "<label>Message [textarea your-message]</label>\n[text* agree]",
            );

            $this->meta[18] = array(
                '_vmcf7_enabled'        => '1',
                '_vmcf7_agree_required' => '{field_label}!',
            );

            $this->assertSame( 'Agree!', $this->validate( $form, new WPCF7_FormTag( 'agree', 'text', true ), array( 'agree' => '' ) ) );
        }

        public function test_default_message_and_placeholder_filters() {
            $form = new WPCF7_ContactForm( 16 );
            $tag  = new WPCF7_FormTag( 'city', 'text', true );

            $this->meta[16] = array( '_vmcf7_enabled' => '1' );

            Filters\expectApplied( 'vmcf7_default_message' )
                ->andReturnUsing( function( $message, $form_id, $field, $type ) {
                    return 'required' === $type ? 'Fill in {field_name}' : $message;
                } );

            Filters\expectApplied( 'vmcf7_placeholders' )
                ->andReturnUsing( function( $replacements, $message, $form, $tag ) {
                    $replacements['{field_name}'] = $tag->name;
                    return $replacements;
                } );

            $this->assertSame( 'Fill in city', $this->validate( $form, $tag, array( 'city' => '' ) ) );
        }

        /* ---------------------------------------------------------------
         * Save / sanitize
         * ------------------------------------------------------------- */

        public function test_sanitize_rule_value_edge_cases() {
            $admin = new VMCF7_Admin();

            $this->assertSame( '', $admin->sanitize_rule_value( 'regex', array( 'x' ) ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'required', array( 'x' ) ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'min_length', null ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'max_length', '0' ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'max_length', '-5' ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'min_length', 'abc' ) );
            $this->assertSame( 12, $admin->sanitize_rule_value( 'min_length', ' 12 ' ) );
            $this->assertSame( '', $admin->sanitize_rule_value( 'required', "  \n " ) );
            $this->assertSame( '^\d+$', $admin->sanitize_rule_value( 'regex', ' ^\d+$ ' ) );
        }

        /**
         * update_post_meta() unslashes: without wp_slash() `^\d{3}$` was stored as `^d{3}$`.
         */
        public function test_save_keeps_backslashes_in_regex() {
            $_POST['vmcf7_nonce']   = 'n';
            $_POST['vmcf7_enabled'] = '1';
            $_POST['vmcf7']         = array(
                'zip' => array(
                    'regex'      => '^\d{3}-\d{2}$',
                    'max_length' => '0',
                    'required'   => array( 'nested' ),
                ),
            );

            Functions\when( 'get_option' )->justReturn( array() );

            ( new VMCF7_Admin() )->save_messages( new WPCF7_ContactForm( 20 ) );

            $this->assertSame( '^\d{3}-\d{2}$', $this->saved[20]['_vmcf7_zip_regex'] );
            $this->assertArrayNotHasKey( '_vmcf7_zip_max_length', $this->saved[20] );
            $this->assertArrayNotHasKey( '_vmcf7_zip_required', $this->saved[20] );
            $this->assertContains( '_vmcf7_zip_max_length', $this->deleted[20] );
        }

        /* ---------------------------------------------------------------
         * Rule-set AJAX handlers
         * ------------------------------------------------------------- */

        /**
         * Copying a form onto itself cleared it, then copied the now-empty set.
         */
        public function test_copy_onto_itself_is_refused_and_keeps_rules() {
            $this->meta[30] = array(
                '_vmcf7_enabled'       => '1',
                '_vmcf7_name_required' => 'Name!',
            );

            $_POST['from_form_id'] = 30;
            $_POST['to_form_id']   = 30;

            $admin = new VMCF7_Admin();
            $this->assertStringStartsWith( 'JSON_ERROR', $this->run_ajax( array( $admin, 'ajax_copy_rules' ) ) );
            $this->assertSame( array(), $this->deleted );
            $this->assertSame( 'Name!', $this->meta[30]['_vmcf7_name_required'] );
        }

        public function test_copy_between_forms_keeps_backslashes_and_drops_junk() {
            $this->meta[31] = array(
                '_vmcf7_enabled'        => '1',
                '_vmcf7_zip_regex'      => '^\d+$',
                '_vmcf7_zip_min_length' => '0',
                '_vmcf7_zip_unknown'    => 'junk',
            );
            $this->meta[32] = array( '_vmcf7_old_required' => 'old' );

            $_POST['from_form_id'] = 31;
            $_POST['to_form_id']   = 32;

            $admin = new VMCF7_Admin();
            $this->assertSame( 'JSON_SUCCESS', $this->run_ajax( array( $admin, 'ajax_copy_rules' ) ) );

            $this->assertSame(
                array(
                    '_vmcf7_enabled'   => '1',
                    '_vmcf7_zip_regex' => '^\d+$',
                ),
                $this->meta[32]
            );
        }

        public function test_copy_rejects_posts_that_are_not_forms() {
            Functions\when( 'get_post_type' )->alias( function( $id ) {
                return 41 === $id ? 'page' : 'wpcf7_contact_form';
            } );

            $this->meta[40] = array( '_vmcf7_enabled' => '1' );
            $this->meta[41] = array( '_vmcf7_keep' => 'x' );

            $_POST['from_form_id'] = 40;
            $_POST['to_form_id']   = 41;

            $admin = new VMCF7_Admin();
            $this->assertStringStartsWith( 'JSON_ERROR', $this->run_ajax( array( $admin, 'ajax_copy_rules' ) ) );
            $this->assertSame( array(), $this->deleted );
        }

        /**
         * A hand-edited import file must not fatal or write arbitrary keys.
         */
        public function test_import_survives_malformed_rule_values() {
            $file = tempnam( sys_get_temp_dir(), 'vmcf7_hardening' );
            file_put_contents(
                $file,
                json_encode(
                    array(
                        'rules' => array(
                            '_vmcf7_enabled'            => 1,
                            '_vmcf7_name_required'      => array( 'nested' => 'array' ),
                            '_vmcf7_name_regex'         => '^\w+$',
                            '_vmcf7_name_max_length'    => '-3',
                            '_vmcf7_Na me_required'     => 'bad field name',
                            'vmcf7_name_invalid'        => 'missing underscore prefix',
                            '_wp_page_template'         => 'evil.php',
                            '_vmcf7_email_invalid'      => 'Bad <script>x()</script>mail',
                        ),
                    )
                )
            );

            $_POST['form_id']      = 50;
            $_FILES['import_file'] = array(
                'name'     => 'rules.json',
                'type'     => 'application/json',
                'tmp_name' => $file,
                'error'    => 0,
                'size'     => filesize( $file ),
            );

            $this->meta[50] = array( '_vmcf7_stale_required' => 'gone' );

            $admin    = new VMCF7_Admin();
            $response = $this->run_ajax( array( $admin, 'ajax_import_rules' ) );
            unlink( $file );

            $this->assertSame( 'JSON_SUCCESS', $response );
            $this->assertSame(
                array(
                    '_vmcf7_enabled'       => '1',
                    '_vmcf7_name_regex'    => '^\w+$',
                    '_vmcf7_email_invalid' => 'Bad mail',
                ),
                $this->meta[50]
            );
        }

        public function test_import_rejects_rules_that_are_not_a_list() {
            $file = tempnam( sys_get_temp_dir(), 'vmcf7_hardening' );
            file_put_contents( $file, json_encode( array( 'rules' => 'nope' ) ) );

            $_POST['form_id']      = 51;
            $_FILES['import_file'] = array(
                'name'     => 'rules.json',
                'tmp_name' => $file,
                'error'    => 0,
            );
            $this->meta[51] = array( '_vmcf7_name_required' => 'kept' );

            $admin    = new VMCF7_Admin();
            $response = $this->run_ajax( array( $admin, 'ajax_import_rules' ) );
            unlink( $file );

            $this->assertStringStartsWith( 'JSON_ERROR', $response );
            $this->assertSame( 'kept', $this->meta[51]['_vmcf7_name_required'] );
        }

        /* ---------------------------------------------------------------
         * Flavor / Polylang bridges
         * ------------------------------------------------------------- */

        /**
         * Regex patterns, length limits and companion names were sent to the AI
         * translator and stored as "translations".
         */
        public function test_ai_translate_sends_messages_only() {
            \Flavor\Flv_Languages::$targets = array( 'de' => array() );

            $this->meta[60] = array(
                '_vmcf7_enabled'                 => '1',
                '_vmcf7_zip_required'            => 'Zip is required',
                '_vmcf7_zip_regex'               => '^\d{5}$',
                '_vmcf7_zip_regex_message'       => 'Five digits',
                '_vmcf7_zip_min_length'          => '5',
                '_vmcf7_zip_required_if_field'   => 'country',
            );

            \Flavor\Flv_AI_Translator::$response = array(
                'vmcf7_zip_required'      => 'PLZ ist erforderlich',
                'vmcf7_zip_regex_message' => '',
                'vmcf7_not_asked_for'     => 'ignored',
            );

            $result = VMCF7_Flavor::ai_translate_form( 60, 'de' );

            $this->assertSame(
                array( 'vmcf7_zip_required', 'vmcf7_zip_regex_message' ),
                array_keys( \Flavor\Flv_AI_Translator::$last_texts )
            );
            $this->assertSame( array( 'vmcf7_zip_required' => 'PLZ ist erforderlich' ), $result );
            $this->assertSame( array( 'vmcf7_zip_required' => 'PLZ ist erforderlich' ), VMCF7_Flavor::get_all_translations( 60, 'de' ) );
        }

        public function test_flavor_import_skips_settings_and_non_strings() {
            VMCF7_Flavor::import_language_set(
                61,
                'de',
                array(
                    'vmcf7_zip_required' => ' Pflichtfeld ',
                    'vmcf7_zip_regex'    => '^\d+$',
                    'vmcf7_zip_invalid'  => array( 'x' ),
                    'something_else'     => 'x',
                )
            );

            $this->assertSame( array( 'vmcf7_zip_required' => 'Pflichtfeld' ), VMCF7_Flavor::get_all_translations( 61, 'de' ) );
        }

        /**
         * WPML/Polylang strings must be registered after the new messages are saved
         * (both hooks sat on priority 10, i18n first), and Polylang needs them
         * registered on its own strings screen.
         */
        public function test_i18n_registers_after_save_and_on_polylang_screen() {
            Functions\when( 'is_admin' )->justReturn( true );

            $compat = new VMCF7_I18n_Compat();
            $compat->init();

            $this->assertSame( 20, has_action( 'wpcf7_save_contact_form', array( $compat, 'register_strings_on_save' ) ) );
            $this->assertNotFalse( has_action( 'admin_init', array( $compat, 'register_polylang_strings' ) ) );

            $this->meta[70] = array(
                '_vmcf7_enabled'       => '1',
                '_vmcf7_name_required' => 'Name is required',
                '_vmcf7_name_regex'    => '^\w+$',
            );

            $registered = array();
            Functions\when( 'pll_register_string' )->alias( function( $name, $string, $group ) use ( &$registered ) {
                $registered[ $name ] = $string;
            } );
            Functions\when( 'get_posts' )->justReturn( array( 70 ) );

            // Another admin screen: nothing is queried or registered.
            $_GET['page'] = 'wpcf7';
            $compat->register_polylang_strings();
            $this->assertSame( array(), $registered );

            $_GET['page'] = 'mlang_strings';
            $compat->register_polylang_strings();
            $this->assertSame( array( 'vmcf7_70_name_required' => 'Name is required' ), $registered );
        }

        /**
         * Polylang emulates icl_register_string() and keeps those strings for
         * good, so going through it listed every message twice. With Polylang
         * only its own API is used and the stored WPML-style copy is removed.
         */
        public function test_polylang_save_does_not_register_wpml_duplicate() {
            $calls = array();
            Functions\when( 'pll_register_string' )->alias( function( $name ) use ( &$calls ) {
                $calls[] = 'pll:' . $name;
            } );
            Functions\when( 'icl_register_string' )->alias( function( $context, $name ) use ( &$calls ) {
                $calls[] = 'icl:' . $name;
            } );
            Functions\when( 'icl_unregister_string' )->alias( function( $context, $name ) use ( &$calls ) {
                $calls[] = 'unregister:' . $name;
            } );

            $form       = new WPCF7_ContactForm( 71 );
            $form->tags = array( new WPCF7_FormTag( 'name', 'text', true ) );
            $this->meta[71] = array(
                '_vmcf7_enabled'       => '1',
                '_vmcf7_name_required' => 'Name is required',
            );

            ( new VMCF7_I18n_Compat() )->register_strings_on_save( $form );

            $this->assertSame( array( 'unregister:vmcf7_71_name_required', 'pll:vmcf7_71_name_required' ), $calls );
        }
    }
}
