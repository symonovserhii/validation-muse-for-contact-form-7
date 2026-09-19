<?php
/**
 * One-time, dismissible request for a WordPress.org review.
 *
 * Shown only after the user has saved a custom validation message and a week
 * has passed, only to users who can edit Contact Form 7 forms, and only on the
 * Contact Form 7 screens. "Maybe later" snoozes it for 30 days; "Don't ask
 * again" hides it for good. Nothing is sent anywhere — the state lives in one
 * local option.
 *
 * @package ValidationMuse
 * @since   1.6.5
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review request admin notice.
 *
 * @since 1.6.5
 */
class VMCF7_Review_Notice {

	const OPTION     = 'vmcf7_review_state';
	const ACTION     = 'vmcf7_review_action';
	const REVIEW_URL = 'https://wordpress.org/support/plugin/validation-muse-for-contact-form-7/reviews/#new-post';

	/**
	 * Register hooks.
	 *
	 * @since 1.6.5
	 * @return void
	 */
	public function register() {
		add_action( 'admin_notices', array( $this, 'render' ) );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_action' ) );
	}

	/**
	 * Remember when the user first saved a custom message (the "it's in use" signal).
	 *
	 * @since 1.6.5
	 * @return void
	 */
	public static function record_success() {
		$state = self::get_state();

		if ( empty( $state['first_saved_at'] ) ) {
			$state['first_saved_at'] = time();
			update_option( self::OPTION, $state, false );
		}
	}

	/**
	 * Pure decision: should the notice be visible at $now for this state?
	 *
	 * @since 1.6.5
	 * @param array $state Stored state.
	 * @param int   $now   Current timestamp.
	 * @return bool
	 */
	public static function is_due( array $state, $now ) {
		if ( isset( $state['status'] ) && 'done' === $state['status'] ) {
			return false;
		}

		$first_saved_at = isset( $state['first_saved_at'] ) ? (int) $state['first_saved_at'] : 0;
		if ( $first_saved_at <= 0 || $now < $first_saved_at + WEEK_IN_SECONDS ) {
			return false;
		}

		$snooze_until = isset( $state['snooze_until'] ) ? (int) $state['snooze_until'] : 0;

		return $now >= $snooze_until;
	}

	/**
	 * Print the notice on Contact Form 7 screens.
	 *
	 * @since 1.6.5
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->id, array( 'toplevel_page_wpcf7', 'contact_page_wpcf7-new' ), true ) ) {
			return;
		}

		if ( ! self::is_due( self::get_state(), time() ) ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Enjoying Validation Muse?', 'validation-muse-for-contact-form-7' ); ?></strong>
				<?php esc_html_e( 'Your custom validation messages have been in use for a while. If the plugin saves you time, a short review on WordPress.org helps other sites find it.', 'validation-muse-for-contact-form-7' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'validation-muse-for-contact-form-7' ); ?></a>
				<a class="button" href="<?php echo esc_url( $this->action_url( 'later' ) ); ?>"><?php esc_html_e( 'Maybe later', 'validation-muse-for-contact-form-7' ); ?></a>
				<a class="button-link" href="<?php echo esc_url( $this->action_url( 'done' ) ); ?>"><?php esc_html_e( "Already did / don't ask again", 'validation-muse-for-contact-form-7' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle "Maybe later" / "Don't ask again".
	 *
	 * @since 1.6.5
	 * @return void
	 */
	public function handle_action() {
		if ( ! current_user_can( 'wpcf7_edit_contact_forms' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'validation-muse-for-contact-form-7' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::ACTION );

		$choice = isset( $_GET['choice'] ) ? sanitize_key( wp_unslash( $_GET['choice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified by check_admin_referer() above.
		$state  = self::get_state();

		if ( 'done' === $choice ) {
			$state['status'] = 'done';
		} elseif ( 'later' === $choice ) {
			$state['snooze_until'] = time() + 30 * DAY_IN_SECONDS;
		}

		update_option( self::OPTION, $state, false );

		$back = wp_get_referer();
		wp_safe_redirect( $back ? $back : admin_url( 'admin.php?page=wpcf7' ) );
		exit;
	}

	/**
	 * Nonce-protected admin-post URL for a choice.
	 *
	 * @since 1.6.5
	 * @param string $choice 'later' or 'done'.
	 * @return string
	 */
	private function action_url( $choice ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => self::ACTION,
					'choice' => $choice,
				),
				admin_url( 'admin-post.php' )
			),
			self::ACTION
		);
	}

	/**
	 * Stored state.
	 *
	 * @since 1.6.5
	 * @return array
	 */
	private static function get_state() {
		$state = get_option( self::OPTION, array() );

		return is_array( $state ) ? $state : array();
	}
}
