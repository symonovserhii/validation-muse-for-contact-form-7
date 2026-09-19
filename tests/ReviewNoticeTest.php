<?php
/**
 * Class ReviewNoticeTest
 *
 * @package ValidationMuse
 */

namespace {
    use Brain\Monkey;
    use Brain\Monkey\Functions;
    use PHPUnit\Framework\TestCase;

    require_once dirname( __DIR__ ) . '/includes/class-vmcf7-review-notice.php';

    class ReviewNoticeTest extends TestCase {
        const NOW = 2000000000;

        protected function setUp(): void {
            parent::setUp();
            Monkey\setUp();
        }

        protected function tearDown(): void {
            Monkey\tearDown();
            parent::tearDown();
        }

        public function test_not_due_before_first_save() {
            $this->assertFalse( VMCF7_Review_Notice::is_due( array(), self::NOW ) );
        }

        public function test_not_due_within_first_week() {
            $this->assertFalse( VMCF7_Review_Notice::is_due( array( 'first_saved_at' => self::NOW - DAY_IN_SECONDS ), self::NOW ) );
        }

        public function test_due_after_a_week() {
            $this->assertTrue( VMCF7_Review_Notice::is_due( array( 'first_saved_at' => self::NOW - WEEK_IN_SECONDS - 1 ), self::NOW ) );
        }

        public function test_snooze_then_due_again() {
            $state = array( 'first_saved_at' => self::NOW - 20 * DAY_IN_SECONDS, 'snooze_until' => self::NOW + DAY_IN_SECONDS );
            $this->assertFalse( VMCF7_Review_Notice::is_due( $state, self::NOW ) );
            $this->assertTrue( VMCF7_Review_Notice::is_due( $state, self::NOW + 2 * DAY_IN_SECONDS ) );
        }

        public function test_never_due_once_done() {
            $this->assertFalse( VMCF7_Review_Notice::is_due( array( 'first_saved_at' => 1, 'status' => 'done' ), self::NOW ) );
        }

        public function test_first_save_is_recorded_once() {
            $saved = array();
            Functions\when( 'get_option' )->alias( function () use ( &$saved ) { return $saved; } );
            Functions\when( 'update_option' )->alias( function ( $name, $value ) use ( &$saved ) { $saved = $value; return true; } );

            VMCF7_Review_Notice::record_success();
            $this->assertGreaterThan( 0, $saved['first_saved_at'] );

            $saved['first_saved_at'] = 123;
            VMCF7_Review_Notice::record_success();
            $this->assertSame( 123, $saved['first_saved_at'] );
        }
    }
}
