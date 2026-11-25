<?php
/**
 * Admin panel template for custom validation settings.
 *
 * Input names (`vmcf7_enabled`, `vmcf7[field][type]`,
 * `vmcf7_translations[lang][field][type]`, `vmcf7_nonce`) are the contract with
 * VMCF7_Admin::save_messages() — do not rename them.
 *
 * @package ValidationMuse
 * @since   1.0.0
 *
 * @var int    $form_id       The form ID (0 for a form that has not been saved yet).
 * @var array  $vmcf7_fields  Array of form fields.
 * @var string $enabled       Whether custom validation is enabled.
 * @var array  $vmcf7_string_translation WPML / Polylang string-translation screens (empty when not applicable).
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vmcf7_form_id     = absint( $form_id );
$vmcf7_is_saved    = $vmcf7_form_id > 0;
$vmcf7_field_count = count( $vmcf7_fields );

// Field types with a built-in "is this a valid value" check, i.e. the ones that take an "invalid" message.
$vmcf7_format_types = array( 'email', 'url', 'tel', 'number', 'range', 'date', 'time', 'quiz', 'file' );

// Wording of the "invalid" message per field type: array( input label, hint, short caption ).
$vmcf7_invalid_copy = array(
	'default' => array(
		__( 'Message when the format is wrong', 'validation-muse-for-contact-form-7' ),
		__( 'Shown when the visitor types something that does not fit this kind of field, for example an email address without @. Leave empty to keep the standard message.', 'validation-muse-for-contact-form-7' ),
		__( 'If the format is wrong', 'validation-muse-for-contact-form-7' ),
	),
	'quiz'    => array(
		__( 'Message when the answer is wrong', 'validation-muse-for-contact-form-7' ),
		__( 'Shown when the visitor gives the wrong answer to the quiz question. Leave empty to keep the standard message.', 'validation-muse-for-contact-form-7' ),
		__( 'If the answer is wrong', 'validation-muse-for-contact-form-7' ),
	),
	'file'    => array(
		__( 'Message when the file is too big or the wrong type', 'validation-muse-for-contact-form-7' ),
		__( 'Shown when the visitor picks a file that is larger than allowed or of a type you do not accept. Leave empty to keep the standard message.', 'validation-muse-for-contact-form-7' ),
		__( 'If the file is too big or the wrong type', 'validation-muse-for-contact-form-7' ),
	),
);

if ( ! isset( $vmcf7_string_translation ) || ! is_array( $vmcf7_string_translation ) ) {
	$vmcf7_string_translation = array();
}

// Plain-language names for the field types.
$vmcf7_type_labels = array(
	'text'       => __( 'Text', 'validation-muse-for-contact-form-7' ),
	'email'      => __( 'Email', 'validation-muse-for-contact-form-7' ),
	'url'        => __( 'Web address', 'validation-muse-for-contact-form-7' ),
	'tel'        => __( 'Phone', 'validation-muse-for-contact-form-7' ),
	'number'     => __( 'Number', 'validation-muse-for-contact-form-7' ),
	'range'      => __( 'Slider', 'validation-muse-for-contact-form-7' ),
	'date'       => __( 'Date', 'validation-muse-for-contact-form-7' ),
	'time'       => __( 'Time', 'validation-muse-for-contact-form-7' ),
	'textarea'   => __( 'Long text', 'validation-muse-for-contact-form-7' ),
	'select'     => __( 'Drop-down', 'validation-muse-for-contact-form-7' ),
	'checkbox'   => __( 'Checkboxes', 'validation-muse-for-contact-form-7' ),
	'radio'      => __( 'Radio buttons', 'validation-muse-for-contact-form-7' ),
	'file'       => __( 'File upload', 'validation-muse-for-contact-form-7' ),
	'acceptance' => __( 'Acceptance', 'validation-muse-for-contact-form-7' ),
	'quiz'       => __( 'Quiz', 'validation-muse-for-contact-form-7' ),
);

// "When is this message shown" captions, shared by the preview and the translations.
$vmcf7_when_labels = array(
	'required'            => __( 'If left empty', 'validation-muse-for-contact-form-7' ),
	'invalid'             => __( 'If the format is wrong', 'validation-muse-for-contact-form-7' ),
	'length_message'      => __( 'If too short or too long', 'validation-muse-for-contact-form-7' ),
	'regex_message'       => __( 'If the pattern does not match', 'validation-muse-for-contact-form-7' ),
	'required_if_message' => __( 'If empty while the other field is filled in', 'validation-muse-for-contact-form-7' ),
);

// Message type => key of the saved source message in $vmcf7_field.
$vmcf7_source_keys = array(
	'required'            => 'required_message',
	'invalid'             => 'invalid_message',
	'length_message'      => 'length_message',
	'regex_message'       => 'regex_message',
	'required_if_message' => 'required_if_message',
);

// Every saved setting of a field; used to tell "customized" from "default".
$vmcf7_setting_keys = array( 'required_message', 'invalid_message', 'regex', 'regex_message', 'min_length', 'max_length', 'length_message', 'required_if_field', 'required_if_message' );

// Ready-made patterns. Matching is unanchored server-side, so each one anchors itself.
$vmcf7_regex_presets = array(
	'^[0-9]+$'                => __( 'Digits only', 'validation-muse-for-contact-form-7' ),
	'^[0-9]{5}$'              => __( 'Exactly 5 digits (e.g. a ZIP code)', 'validation-muse-for-contact-form-7' ),
	'^[\p{L} \'-]+$'          => __( 'Letters only (names)', 'validation-muse-for-contact-form-7' ),
	'^[A-Za-z0-9_-]+$'        => __( 'Letters, digits, dash and underscore', 'validation-muse-for-contact-form-7' ),
	'^\+?[0-9 ()-]{7,20}$'    => __( 'Phone number', 'validation-muse-for-contact-form-7' ),
	'^(?![\s\S]*(https?:|www\.))' => __( 'No links allowed', 'validation-muse-for-contact-form-7' ),
);

$vmcf7_other_forms = array();
if ( $vmcf7_is_saved && $vmcf7_field_count ) {
	$vmcf7_other_forms = get_posts(
		array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
			'exclude'        => array( $vmcf7_form_id ),
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
}
$vmcf7_other_count = count( $vmcf7_other_forms );

$vmcf7_customized_count = 0;
foreach ( $vmcf7_fields as $vmcf7_field ) {
	foreach ( $vmcf7_setting_keys as $vmcf7_key ) {
		if ( isset( $vmcf7_field[ $vmcf7_key ] ) && '' !== (string) $vmcf7_field[ $vmcf7_key ] ) {
			++$vmcf7_customized_count;
			break;
		}
	}
}

// Strings used by admin/js/vmcf7-admin.js.
$vmcf7_i18n = array(
	'stateCustom'        => __( 'Customized', 'validation-muse-for-contact-form-7' ),
	'stateDefault'       => __( 'Default messages', 'validation-muse-for-contact-form-7' ),
	/* translators: 1: number of customized fields, 2: total number of fields. */
	'countSummary'       => __( '%1$s of %2$s fields customized', 'validation-muse-for-contact-form-7' ),
	'cf7Default'         => __( 'Contact Form 7’s standard message', 'validation-muse-for-contact-form-7' ),
	'defaultRequired'    => __( 'This field is required', 'validation-muse-for-contact-form-7' ),
	'defaultFormat'      => __( 'Invalid format.', 'validation-muse-for-contact-form-7' ),
	'defaultLength'      => __( 'Invalid length.', 'validation-muse-for-contact-form-7' ),
	/* translators: %d: number of characters. */
	'lintLong'           => __( 'This message is very long (%d characters). Short messages are easier to read.', 'validation-muse-for-contact-form-7' ),
	/* translators: %s: list of HTML tags. */
	'lintHtml'           => __( 'These HTML tags are not allowed and will be removed when you save: %s', 'validation-muse-for-contact-form-7' ),
	'lintMinMax'         => __( 'This field has no length limit yet (Extra rules → Length limit), so {min} and {max} will be left blank.', 'validation-muse-for-contact-form-7' ),
	'lintRange'          => __( 'The minimum is larger than the maximum, so no value can ever pass.', 'validation-muse-for-contact-form-7' ),
	'lintRegex'          => __( 'This pattern may be invalid. Please test it on the live form before relying on it.', 'validation-muse-for-contact-form-7' ),
	'testMatch'          => __( 'Accepted — no error would be shown.', 'validation-muse-for-contact-form-7' ),
	'testNoMatch'        => __( 'Rejected — the visitor would see the error message.', 'validation-muse-for-contact-form-7' ),
	'testUnavailable'    => __( 'This pattern cannot be tested here.', 'validation-muse-for-contact-form-7' ),
	'working'            => __( 'Working…', 'validation-muse-for-contact-form-7' ),
	'cancel'             => __( 'Cancel', 'validation-muse-for-contact-form-7' ),
	'reloading'          => __( 'Reloading the page…', 'validation-muse-for-contact-form-7' ),
	'requestFailed'      => __( 'Something went wrong. Please try again.', 'validation-muse-for-contact-form-7' ),
	'networkError'       => __( 'Network error. Please check your internet connection and try again.', 'validation-muse-for-contact-form-7' ),
	'selectSource'       => __( 'Please choose a form to copy from first.', 'validation-muse-for-contact-form-7' ),
	/* translators: %s: form title. */
	'confirmCopy'        => __( 'Replace all validation settings of this form with the ones from “%s”? Everything set up here, including unsaved changes, will be overwritten.', 'validation-muse-for-contact-form-7' ),
	'confirmCopyYes'     => __( 'Yes, copy and replace', 'validation-muse-for-contact-form-7' ),
	/* translators: %s: file name. */
	'confirmImport'      => __( 'Replace all validation settings of this form with the contents of “%s”? Everything set up here, including unsaved changes, will be overwritten.', 'validation-muse-for-contact-form-7' ),
	'confirmImportYes'   => __( 'Yes, load the file', 'validation-muse-for-contact-form-7' ),
	'importNotJson'      => __( 'Please choose a .json file that was downloaded from this plugin.', 'validation-muse-for-contact-form-7' ),
	'confirmApply'       => __( 'Replace all validation settings of this form with your saved template? Everything set up here, including unsaved changes, will be overwritten.', 'validation-muse-for-contact-form-7' ),
	'confirmApplyYes'    => __( 'Yes, use the template', 'validation-muse-for-contact-form-7' ),
	'confirmSave'        => __( 'Save this form’s settings as your template? A template you saved earlier will be replaced.', 'validation-muse-for-contact-form-7' ),
	'confirmSaveYes'     => __( 'Yes, save as template', 'validation-muse-for-contact-form-7' ),
	'confirmAll'         => sprintf(
		/* translators: %d: number of other contact forms. */
		_n(
			'Overwrite the validation settings of your %d other contact form with the saved settings of this form? This cannot be undone.',
			'Overwrite the validation settings of all %d other contact forms with the saved settings of this form? This cannot be undone.',
			max( 1, $vmcf7_other_count ),
			'validation-muse-for-contact-form-7'
		),
		$vmcf7_other_count
	),
	'confirmAllYes'      => __( 'Yes, overwrite all other forms', 'validation-muse-for-contact-form-7' ),
	'translating'        => __( 'Translating…', 'validation-muse-for-contact-form-7' ),
	'translateFailed'    => __( 'Translation failed. Please try again.', 'validation-muse-for-contact-form-7' ),
	/* translators: %d: number of translated messages. */
	'translateDone'      => __( '%d messages translated. Check them, then save the form to keep them.', 'validation-muse-for-contact-form-7' ),
);
?>

<div class="vmcf7-panel<?php echo '1' === (string) $enabled ? '' : ' is-off'; ?>"
	 data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>"
	 data-i18n="<?php echo esc_attr( wp_json_encode( $vmcf7_i18n ) ); ?>">

	<div class="vmcf7-intro">
		<h2><?php esc_html_e( 'Custom Validation', 'validation-muse-for-contact-form-7' ); ?></h2>
		<p class="vmcf7-intro__text">
			<?php esc_html_e( 'Choose what visitors read when they fill in a field incorrectly, and add extra checks such as a minimum length. Fields you leave untouched keep Contact Form 7’s standard messages.', 'validation-muse-for-contact-form-7' ); ?>
		</p>
	</div>

	<div class="vmcf7-master vmcf7-enable-toggle">
		<label class="vmcf7-switch" for="vmcf7-enabled">
			<input type="checkbox"
				   class="vmcf7-switch__input"
				   id="vmcf7-enabled"
				   name="vmcf7_enabled"
				   value="1"
				   aria-describedby="vmcf7-enabled-desc"
				   <?php checked( $enabled, '1' ); ?>>
			<span class="vmcf7-switch__track" aria-hidden="true"></span>
			<span class="vmcf7-switch__text"><?php esc_html_e( 'Use custom validation on this form', 'validation-muse-for-contact-form-7' ); ?></span>
		</label>
		<p class="vmcf7-master__desc" id="vmcf7-enabled-desc">
			<span class="vmcf7-master__on"><?php esc_html_e( 'On — the messages and rules below are used on your site.', 'validation-muse-for-contact-form-7' ); ?></span>
			<span class="vmcf7-master__off"><?php esc_html_e( 'Off — visitors see Contact Form 7’s standard messages. Your settings below are kept, so you can switch this back on at any time.', 'validation-muse-for-contact-form-7' ); ?></span>
		</p>
	</div>

	<?php if ( ! $vmcf7_field_count ) : ?>

		<div class="vmcf7-empty">
			<span class="dashicons dashicons-feedback" aria-hidden="true"></span>
			<p class="vmcf7-empty__title"><?php esc_html_e( 'This form has no fields yet', 'validation-muse-for-contact-form-7' ); ?></p>
			<p><?php esc_html_e( 'Add fields on the “Form” tab and save the form. They will then appear here, ready for your own messages and rules.', 'validation-muse-for-contact-form-7' ); ?></p>
		</div>

	<?php else : ?>

		<div class="vmcf7-filterbar">
			<div class="vmcf7-filterbar__group">
				<?php if ( $vmcf7_field_count > 5 ) : ?>
					<label class="screen-reader-text" for="vmcf7-search"><?php esc_html_e( 'Find a field', 'validation-muse-for-contact-form-7' ); ?></label>
					<input type="search"
						   id="vmcf7-search"
						   class="vmcf7-filterbar__search"
						   placeholder="<?php esc_attr_e( 'Find a field…', 'validation-muse-for-contact-form-7' ); ?>"
						   autocomplete="off">
				<?php endif; ?>
				<?php if ( $vmcf7_field_count > 1 ) : ?>
					<label class="vmcf7-filterbar__check" for="vmcf7-only-custom">
						<input type="checkbox" id="vmcf7-only-custom">
						<?php esc_html_e( 'Only show customized fields', 'validation-muse-for-contact-form-7' ); ?>
					</label>
				<?php endif; ?>
			</div>
			<div class="vmcf7-filterbar__group">
				<span class="vmcf7-filterbar__count" id="vmcf7-count" aria-live="polite">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: number of customized fields, 2: total number of fields. */
							__( '%1$s of %2$s fields customized', 'validation-muse-for-contact-form-7' ),
							number_format_i18n( $vmcf7_customized_count ),
							number_format_i18n( $vmcf7_field_count )
						)
					);
					?>
				</span>
				<?php if ( $vmcf7_field_count > 1 ) : ?>
					<button type="button" class="button-link vmcf7-expand-all"><?php esc_html_e( 'Open all', 'validation-muse-for-contact-form-7' ); ?></button>
					<button type="button" class="button-link vmcf7-collapse-all"><?php esc_html_e( 'Close all', 'validation-muse-for-contact-form-7' ); ?></button>
				<?php endif; ?>
			</div>
		</div>

		<div class="vmcf7-fields">
			<?php
			foreach ( $vmcf7_fields as $vmcf7_index => $vmcf7_field ) :
				$vmcf7_name       = $vmcf7_field['name'];
				$vmcf7_type       = $vmcf7_field['type'];
				$vmcf7_id         = 'vmcf7-' . $vmcf7_name;
				$vmcf7_input      = 'vmcf7[' . $vmcf7_name . ']';
				$vmcf7_has_format = in_array( $vmcf7_type, $vmcf7_format_types, true );
				$vmcf7_type_label = isset( $vmcf7_type_labels[ $vmcf7_type ] ) ? $vmcf7_type_labels[ $vmcf7_type ] : $vmcf7_type;
				$vmcf7_required   = ! empty( $vmcf7_field['required'] );
				$vmcf7_label      = isset( $vmcf7_field['label'] ) ? (string) $vmcf7_field['label'] : '';
				$vmcf7_inv_copy   = isset( $vmcf7_invalid_copy[ $vmcf7_type ] ) ? $vmcf7_invalid_copy[ $vmcf7_type ] : $vmcf7_invalid_copy['default'];
				// File uploads take no text rules (length / pattern); a saved pattern that does not compile is ignored on the site.
				$vmcf7_text_rules    = ! isset( $vmcf7_field['text_rules'] ) || $vmcf7_field['text_rules'];
				$vmcf7_regex_invalid = ! empty( $vmcf7_field['regex_invalid'] );

				$vmcf7_is_custom = false;
				foreach ( $vmcf7_setting_keys as $vmcf7_key ) {
					if ( isset( $vmcf7_field[ $vmcf7_key ] ) && '' !== (string) $vmcf7_field[ $vmcf7_key ] ) {
						$vmcf7_is_custom = true;
						break;
					}
				}

				$vmcf7_has_extra = '' !== (string) $vmcf7_field['regex'] || '' !== (string) $vmcf7_field['regex_message']
					|| '' !== (string) $vmcf7_field['min_length'] || '' !== (string) $vmcf7_field['max_length'] || '' !== (string) $vmcf7_field['length_message']
					|| '' !== (string) $vmcf7_field['required_if_field'] || '' !== (string) $vmcf7_field['required_if_message'];

				// Open the first card when nothing is customized yet, so the layout explains itself.
				$vmcf7_open = ( 0 === $vmcf7_index && 0 === $vmcf7_customized_count ) || 1 === $vmcf7_field_count;
				?>
				<div class="vmcf7-field<?php echo $vmcf7_is_custom ? ' is-custom' : ''; ?><?php echo $vmcf7_open ? ' is-open' : ''; ?>"
					 data-field="<?php echo esc_attr( $vmcf7_name ); ?>"
					 data-type="<?php echo esc_attr( $vmcf7_type ); ?>"
					 data-format="<?php echo $vmcf7_has_format ? '1' : '0'; ?>"
					 data-label="<?php echo esc_attr( $vmcf7_label ); ?>"
					 data-search="<?php echo esc_attr( strtolower( $vmcf7_label . ' ' . $vmcf7_name . ' ' . $vmcf7_type . ' ' . $vmcf7_type_label ) ); ?>">

					<h3 class="vmcf7-field__heading">
						<button type="button"
								class="vmcf7-field__toggle"
								aria-expanded="<?php echo $vmcf7_open ? 'true' : 'false'; ?>"
								aria-controls="<?php echo esc_attr( $vmcf7_id ); ?>-body">
							<span class="dashicons dashicons-arrow-right-alt2 vmcf7-field__chevron" aria-hidden="true"></span>
							<span class="vmcf7-field__title">
								<span class="vmcf7-field__label"><?php echo esc_html( $vmcf7_label ); ?></span>
								<code class="vmcf7-field__name"><?php echo esc_html( $vmcf7_name ); ?></code>
							</span>
							<span class="vmcf7-field__badges">
								<span class="vmcf7-badge" title="<?php echo esc_attr( $vmcf7_type ); ?>"><?php echo esc_html( $vmcf7_type_label ); ?></span>
								<?php if ( $vmcf7_required ) : ?>
									<span class="vmcf7-badge vmcf7-badge--required"><?php esc_html_e( 'Required', 'validation-muse-for-contact-form-7' ); ?></span>
								<?php else : ?>
									<span class="vmcf7-badge vmcf7-badge--optional"><?php esc_html_e( 'Optional', 'validation-muse-for-contact-form-7' ); ?></span>
								<?php endif; ?>
							</span>
							<span class="vmcf7-field__state"><?php echo $vmcf7_is_custom ? esc_html__( 'Customized', 'validation-muse-for-contact-form-7' ) : esc_html__( 'Default messages', 'validation-muse-for-contact-form-7' ); ?></span>
						</button>
					</h3>

					<div class="vmcf7-field__body" id="<?php echo esc_attr( $vmcf7_id ); ?>-body"<?php echo $vmcf7_open ? '' : ' hidden'; ?>>
						<div class="vmcf7-field__main">

							<!-- Message: empty field -->
							<div class="vmcf7-rule">
								<label class="vmcf7-rule__title" for="<?php echo esc_attr( $vmcf7_id ); ?>-required"><?php esc_html_e( 'Message when the field is left empty', 'validation-muse-for-contact-form-7' ); ?></label>
								<input type="text"
									   id="<?php echo esc_attr( $vmcf7_id ); ?>-required"
									   name="<?php echo esc_attr( $vmcf7_input ); ?>[required]"
									   value="<?php echo esc_attr( $vmcf7_field['required_message'] ); ?>"
									   class="vmcf7-input vmcf7-msg-input"
									   data-rule-type="required"
									   aria-describedby="<?php echo esc_attr( $vmcf7_id ); ?>-required-hint"
									   placeholder="<?php esc_attr_e( 'e.g. Please fill in {field_label}', 'validation-muse-for-contact-form-7' ); ?>">
								<p class="vmcf7-hint" id="<?php echo esc_attr( $vmcf7_id ); ?>-required-hint">
									<?php if ( $vmcf7_required ) : ?>
										<?php esc_html_e( 'Shown when a visitor skips this required field. Leave empty to keep the standard message.', 'validation-muse-for-contact-form-7' ); ?>
									<?php else : ?>
										<?php esc_html_e( 'This field is optional in your form, so this message is not shown yet. To make the field required, add * after its type on the “Form” tab (for example [text* your-name]).', 'validation-muse-for-contact-form-7' ); ?>
									<?php endif; ?>
								</p>
								<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
							</div>

							<?php if ( $vmcf7_has_format ) : ?>
								<!-- Message: wrong format -->
								<div class="vmcf7-rule">
									<label class="vmcf7-rule__title" for="<?php echo esc_attr( $vmcf7_id ); ?>-invalid"><?php echo esc_html( $vmcf7_inv_copy[0] ); ?></label>
									<input type="text"
										   id="<?php echo esc_attr( $vmcf7_id ); ?>-invalid"
										   name="<?php echo esc_attr( $vmcf7_input ); ?>[invalid]"
										   value="<?php echo esc_attr( $vmcf7_field['invalid_message'] ); ?>"
										   class="vmcf7-input vmcf7-msg-input"
										   data-rule-type="invalid"
										   aria-describedby="<?php echo esc_attr( $vmcf7_id ); ?>-invalid-hint"
										   placeholder="<?php echo esc_attr( sprintf( /* translators: %s: example message. */ __( 'e.g. %s', 'validation-muse-for-contact-form-7' ), $this->get_default_invalid_message( $vmcf7_type ) ) ); ?>">
									<p class="vmcf7-hint" id="<?php echo esc_attr( $vmcf7_id ); ?>-invalid-hint">
										<?php echo esc_html( $vmcf7_inv_copy[1] ); ?>
									</p>
									<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
								</div>
							<?php endif; ?>

							<!-- Extra rules -->
							<div class="vmcf7-extra<?php echo $vmcf7_has_extra ? ' is-open' : ''; ?>">
								<button type="button"
										class="vmcf7-extra__toggle"
										aria-expanded="<?php echo $vmcf7_has_extra ? 'true' : 'false'; ?>"
										aria-controls="<?php echo esc_attr( $vmcf7_id ); ?>-extra">
									<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
									<span class="vmcf7-extra__label"><?php esc_html_e( 'Extra rules', 'validation-muse-for-contact-form-7' ); ?></span>
									<span class="vmcf7-extra__sub"><?php echo $vmcf7_text_rules ? esc_html__( 'length limit, allowed characters, required only sometimes', 'validation-muse-for-contact-form-7' ) : esc_html__( 'required only sometimes', 'validation-muse-for-contact-form-7' ); ?></span>
								</button>

								<div class="vmcf7-extra__body" id="<?php echo esc_attr( $vmcf7_id ); ?>-extra"<?php echo $vmcf7_has_extra ? '' : ' hidden'; ?>>

									<?php if ( $vmcf7_text_rules ) : ?>
									<!-- Length -->
									<fieldset class="vmcf7-rule vmcf7-rule--boxed">
										<legend class="vmcf7-rule__title"><?php esc_html_e( 'Length limit', 'validation-muse-for-contact-form-7' ); ?></legend>
										<p class="vmcf7-hint"><?php esc_html_e( 'How many characters the answer may have. Fill in one or both; leave empty for no limit.', 'validation-muse-for-contact-form-7' ); ?></p>
										<div class="vmcf7-inline">
											<div class="vmcf7-inline__item">
												<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-min-length"><?php esc_html_e( 'At least', 'validation-muse-for-contact-form-7' ); ?></label>
												<input type="number"
													   id="<?php echo esc_attr( $vmcf7_id ); ?>-min-length"
													   name="<?php echo esc_attr( $vmcf7_input ); ?>[min_length]"
													   value="<?php echo esc_attr( $vmcf7_field['min_length'] ); ?>"
													   class="vmcf7-input vmcf7-input--num vmcf7-rule-input"
													   data-rule-type="min_length"
													   min="0" step="1" inputmode="numeric">
											</div>
											<div class="vmcf7-inline__item">
												<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-max-length"><?php esc_html_e( 'At most', 'validation-muse-for-contact-form-7' ); ?></label>
												<input type="number"
													   id="<?php echo esc_attr( $vmcf7_id ); ?>-max-length"
													   name="<?php echo esc_attr( $vmcf7_input ); ?>[max_length]"
													   value="<?php echo esc_attr( $vmcf7_field['max_length'] ); ?>"
													   class="vmcf7-input vmcf7-input--num vmcf7-rule-input"
													   data-rule-type="max_length"
													   min="0" step="1" inputmode="numeric">
											</div>
											<span class="vmcf7-inline__unit"><?php esc_html_e( 'characters', 'validation-muse-for-contact-form-7' ); ?></span>
										</div>
										<div class="vmcf7-lint-warning vmcf7-lint-range vmcf7-hidden" role="status"></div>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-length-message"><?php esc_html_e( 'Message when the answer is too short or too long', 'validation-muse-for-contact-form-7' ); ?></label>
										<input type="text"
											   id="<?php echo esc_attr( $vmcf7_id ); ?>-length-message"
											   name="<?php echo esc_attr( $vmcf7_input ); ?>[length_message]"
											   value="<?php echo esc_attr( $vmcf7_field['length_message'] ); ?>"
											   class="vmcf7-input vmcf7-msg-input"
											   data-rule-type="length_message"
											   placeholder="<?php esc_attr_e( 'e.g. Please use between {min} and {max} characters', 'validation-muse-for-contact-form-7' ); ?>">
										<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
									</fieldset>

									<!-- Pattern -->
									<fieldset class="vmcf7-rule vmcf7-rule--boxed">
										<legend class="vmcf7-rule__title">
											<?php esc_html_e( 'Allowed characters (pattern)', 'validation-muse-for-contact-form-7' ); ?>
											<span class="vmcf7-badge vmcf7-badge--muted"><?php esc_html_e( 'Advanced', 'validation-muse-for-contact-form-7' ); ?></span>
										</legend>
										<p class="vmcf7-hint"><?php esc_html_e( 'Only accept answers that match a pattern (a “regular expression”). Not sure how to write one? Pick a ready-made example.', 'validation-muse-for-contact-form-7' ); ?></p>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-regex-preset"><?php esc_html_e( 'Ready-made examples', 'validation-muse-for-contact-form-7' ); ?></label>
										<select id="<?php echo esc_attr( $vmcf7_id ); ?>-regex-preset" class="vmcf7-regex-preset">
											<option value=""><?php esc_html_e( '— Choose an example —', 'validation-muse-for-contact-form-7' ); ?></option>
											<?php foreach ( $vmcf7_regex_presets as $vmcf7_preset => $vmcf7_preset_label ) : ?>
												<option value="<?php echo esc_attr( $vmcf7_preset ); ?>"><?php echo esc_html( $vmcf7_preset_label ); ?></option>
											<?php endforeach; ?>
										</select>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-regex"><?php esc_html_e( 'Pattern', 'validation-muse-for-contact-form-7' ); ?></label>
										<input type="text"
											   id="<?php echo esc_attr( $vmcf7_id ); ?>-regex"
											   name="<?php echo esc_attr( $vmcf7_input ); ?>[regex]"
											   value="<?php echo esc_attr( $vmcf7_field['regex'] ); ?>"
											   class="vmcf7-input vmcf7-input--code vmcf7-rule-input"
											   data-rule-type="regex"
											   spellcheck="false" autocomplete="off" autocapitalize="off"
											   aria-describedby="<?php echo esc_attr( $vmcf7_id ); ?>-regex-hint"
											   placeholder="^[0-9]{5}$">
										<p class="vmcf7-hint" id="<?php echo esc_attr( $vmcf7_id ); ?>-regex-hint"><?php esc_html_e( 'Write it without the surrounding slashes. Important: the pattern only has to be found somewhere in the answer, so [0-9]+ also accepts “abc1”. To check the whole answer, start with ^ and end with $, like ^[0-9]+$. Leave empty for no pattern check.', 'validation-muse-for-contact-form-7' ); ?></p>
										<?php if ( $vmcf7_regex_invalid ) : ?>
											<div class="vmcf7-lint-warning vmcf7-regex-saved-invalid"><?php esc_html_e( 'The saved pattern is not valid, so it is currently ignored on your site. Correct it or clear it.', 'validation-muse-for-contact-form-7' ); ?></div>
										<?php endif; ?>
										<div class="vmcf7-lint-warning vmcf7-lint-regex vmcf7-hidden" role="status"></div>

										<div class="vmcf7-tester">
											<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-regex-test"><?php esc_html_e( 'Try it: type an example answer', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text"
												   id="<?php echo esc_attr( $vmcf7_id ); ?>-regex-test"
												   class="vmcf7-input vmcf7-regex-test"
												   autocomplete="off">
											<p class="vmcf7-tester__result" role="status" aria-live="polite"></p>
										</div>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-regex-message"><?php esc_html_e( 'Message when the answer does not match', 'validation-muse-for-contact-form-7' ); ?></label>
										<input type="text"
											   id="<?php echo esc_attr( $vmcf7_id ); ?>-regex-message"
											   name="<?php echo esc_attr( $vmcf7_input ); ?>[regex_message]"
											   value="<?php echo esc_attr( $vmcf7_field['regex_message'] ); ?>"
											   class="vmcf7-input vmcf7-msg-input"
											   data-rule-type="regex_message"
											   placeholder="<?php esc_attr_e( 'e.g. Please use digits only', 'validation-muse-for-contact-form-7' ); ?>">
										<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
									</fieldset>

									<?php endif; ?>

									<!-- Required-if -->
									<fieldset class="vmcf7-rule vmcf7-rule--boxed">
										<legend class="vmcf7-rule__title"><?php esc_html_e( 'Required only sometimes', 'validation-muse-for-contact-form-7' ); ?></legend>
										<p class="vmcf7-hint"><?php esc_html_e( 'Make this field required only when the visitor has filled in (or ticked) another field. Example: ask for a phone number only if “Call me back” is ticked.', 'validation-muse-for-contact-form-7' ); ?></p>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-required-if-field"><?php esc_html_e( 'Required when this field is filled in', 'validation-muse-for-contact-form-7' ); ?></label>
										<select id="<?php echo esc_attr( $vmcf7_id ); ?>-required-if-field"
												name="<?php echo esc_attr( $vmcf7_input ); ?>[required_if_field]"
												class="vmcf7-rule-input"
												data-rule-type="required_if_field">
											<option value=""><?php esc_html_e( '— Not used —', 'validation-muse-for-contact-form-7' ); ?></option>
											<?php
											$vmcf7_companion_found = '' === (string) $vmcf7_field['required_if_field'];
											foreach ( $vmcf7_fields as $vmcf7_other ) :
												if ( $vmcf7_other['name'] === $vmcf7_name ) {
													continue;
												}
												if ( $vmcf7_other['name'] === $vmcf7_field['required_if_field'] ) {
													$vmcf7_companion_found = true;
												}
												?>
												<option value="<?php echo esc_attr( $vmcf7_other['name'] ); ?>"<?php selected( $vmcf7_field['required_if_field'], $vmcf7_other['name'] ); ?>>
													<?php echo esc_html( $vmcf7_other['name'] ); ?>
												</option>
											<?php endforeach; ?>
											<?php if ( ! $vmcf7_companion_found ) : ?>
												<option value="<?php echo esc_attr( $vmcf7_field['required_if_field'] ); ?>" selected>
													<?php
													/* translators: %s: field name. */
													echo esc_html( sprintf( __( '%s (not found in this form)', 'validation-muse-for-contact-form-7' ), $vmcf7_field['required_if_field'] ) );
													?>
												</option>
											<?php endif; ?>
										</select>

										<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_id ); ?>-required-if-message"><?php esc_html_e( 'Message when it is then left empty', 'validation-muse-for-contact-form-7' ); ?></label>
										<input type="text"
											   id="<?php echo esc_attr( $vmcf7_id ); ?>-required-if-message"
											   name="<?php echo esc_attr( $vmcf7_input ); ?>[required_if_message]"
											   value="<?php echo esc_attr( $vmcf7_field['required_if_message'] ); ?>"
											   class="vmcf7-input vmcf7-msg-input"
											   data-rule-type="required_if_message"
											   placeholder="<?php esc_attr_e( 'e.g. Please add your phone number so we can call you back', 'validation-muse-for-contact-form-7' ); ?>">
										<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
									</fieldset>
								</div>
							</div>

							<div class="vmcf7-field__footer">
								<button type="button" class="button-link button-link-delete vmcf7-clear-field">
									<?php esc_html_e( 'Clear all settings of this field', 'validation-muse-for-contact-form-7' ); ?>
								</button>
							</div>
						</div>

						<div class="vmcf7-field__aside">
							<div class="vmcf7-preview" aria-live="polite">
								<p class="vmcf7-aside-title"><?php esc_html_e( 'What visitors will see', 'validation-muse-for-contact-form-7' ); ?></p>
								<div class="vmcf7-preview__mock" aria-hidden="true">
									<span class="vmcf7-preview__label"></span>
									<span class="vmcf7-preview__input"></span>
								</div>
								<?php foreach ( $vmcf7_when_labels as $vmcf7_msg_type => $vmcf7_when ) : ?>
									<?php
									if ( 'invalid' === $vmcf7_msg_type && ! $vmcf7_has_format ) {
										continue;
									}
									?>
									<div class="vmcf7-preview__row" data-msg-type="<?php echo esc_attr( $vmcf7_msg_type ); ?>">
										<span class="vmcf7-preview__when"><?php echo esc_html( 'invalid' === $vmcf7_msg_type ? $vmcf7_inv_copy[2] : $vmcf7_when ); ?></span>
										<span class="vmcf7-preview__tip"></span>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="vmcf7-tokens">
								<p class="vmcf7-aside-title"><?php esc_html_e( 'Smart words', 'validation-muse-for-contact-form-7' ); ?></p>
								<p class="vmcf7-hint"><?php esc_html_e( 'Click one to add it to the message you are writing. It is replaced automatically on your site.', 'validation-muse-for-contact-form-7' ); ?></p>
								<ul class="vmcf7-tokens__list">
									<li>
										<button type="button" class="vmcf7-token" data-token="{field_label}">{field_label}</button>
										<span><?php esc_html_e( 'the field’s label, e.g. “Your email”', 'validation-muse-for-contact-form-7' ); ?></span>
									</li>
									<li>
										<button type="button" class="vmcf7-token" data-token="{min}">{min}</button>
										<span><?php esc_html_e( 'the “at least” number from Length limit', 'validation-muse-for-contact-form-7' ); ?></span>
									</li>
									<li>
										<button type="button" class="vmcf7-token" data-token="{max}">{max}</button>
										<span><?php esc_html_e( 'the “at most” number from Length limit', 'validation-muse-for-contact-form-7' ); ?></span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<p class="vmcf7-fields__none vmcf7-hidden"><?php esc_html_e( 'No fields match. Clear the search or untick “Only show customized fields”.', 'validation-muse-for-contact-form-7' ); ?></p>
		</div>

		<p class="vmcf7-footnote">
			<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
			<?php esc_html_e( 'Changes take effect after you save the form. Added a new field on the “Form” tab? Save the form and it will show up here.', 'validation-muse-for-contact-form-7' ); ?>
		</p>

		<?php if ( ! empty( $vmcf7_string_translation ) ) : ?>
			<div class="vmcf7-card vmcf7-wpml-note">
				<div class="vmcf7-card__header">
					<h3 class="vmcf7-card__title">
						<span class="dashicons dashicons-translation" aria-hidden="true"></span>
						<?php esc_html_e( 'Translations', 'validation-muse-for-contact-form-7' ); ?>
					</h3>
					<p class="vmcf7-hint"><?php esc_html_e( 'Your messages are registered for translation each time you save the form. Save first, then translate them here:', 'validation-muse-for-contact-form-7' ); ?></p>
					<ul class="vmcf7-wpml-note__list">
						<?php foreach ( $vmcf7_string_translation as $vmcf7_screen ) : ?>
							<li>
								<strong><?php echo esc_html( $vmcf7_screen['name'] ); ?>:</strong>
								<a href="<?php echo esc_url( $vmcf7_screen['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $vmcf7_screen['path'] ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'validation-muse-for-contact-form-7' ); ?></span></a>
								<?php
								/* translators: %s: text domain / string group name. */
								echo esc_html( sprintf( __( '(look for the group “%s”)', 'validation-muse-for-contact-form-7' ), 'validation-muse-for-contact-form-7' ) );
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<?php
		// Flavor translation section.
		$vmcf7_flavor_languages = VMCF7_Flavor::get_target_languages();
		if ( ! empty( $vmcf7_flavor_languages ) ) :
			$vmcf7_ai_available = $vmcf7_is_saved && VMCF7_Flavor::is_ai_available();
			?>
			<div class="vmcf7-card vmcf7-translations">
				<div class="vmcf7-card__header">
					<h3 class="vmcf7-card__title">
						<span class="dashicons dashicons-translation" aria-hidden="true"></span>
						<?php esc_html_e( 'Translations', 'validation-muse-for-contact-form-7' ); ?>
					</h3>
					<p class="vmcf7-hint"><?php esc_html_e( 'Your messages in the other languages of your site (via the Flavor plugin). A message without a translation is shown in the original language.', 'validation-muse-for-contact-form-7' ); ?></p>
				</div>

				<div class="vmcf7-lang-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Languages', 'validation-muse-for-contact-form-7' ); ?>">
					<?php
					$vmcf7_first_tab = true;
					foreach ( $vmcf7_flavor_languages as $vmcf7_lang_code => $vmcf7_lang_data ) :
						$vmcf7_lang_label = ! empty( $vmcf7_lang_data['native_name'] ) ? $vmcf7_lang_data['native_name'] : $vmcf7_lang_code;
						?>
						<button type="button"
								role="tab"
								id="vmcf7-lang-tab-<?php echo esc_attr( $vmcf7_lang_code ); ?>"
								class="vmcf7-lang-tab<?php echo $vmcf7_first_tab ? ' active' : ''; ?>"
								aria-selected="<?php echo $vmcf7_first_tab ? 'true' : 'false'; ?>"
								aria-controls="vmcf7-lang-panel-<?php echo esc_attr( $vmcf7_lang_code ); ?>"
								tabindex="<?php echo $vmcf7_first_tab ? '0' : '-1'; ?>"
								data-lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>">
							<?php echo esc_html( $vmcf7_lang_label ); ?>
						</button>
						<?php
						$vmcf7_first_tab = false;
					endforeach;
					?>
				</div>

				<?php
				$vmcf7_first_panel = true;
				foreach ( $vmcf7_flavor_languages as $vmcf7_lang_code => $vmcf7_lang_data ) :
					$vmcf7_lang_label = ! empty( $vmcf7_lang_data['native_name'] ) ? $vmcf7_lang_data['native_name'] : $vmcf7_lang_code;

					// Work out which slots have something to translate.
					$vmcf7_lang_rows   = array();
					$vmcf7_active_rows = 0;
					foreach ( $vmcf7_fields as $vmcf7_field ) {
						$vmcf7_trans = isset( $vmcf7_field['translations'][ $vmcf7_lang_code ] ) ? $vmcf7_field['translations'][ $vmcf7_lang_code ] : array();
						$vmcf7_items = array();
						$vmcf7_live  = 0;
						foreach ( $vmcf7_source_keys as $vmcf7_msg_type => $vmcf7_source_key ) {
							$vmcf7_value  = isset( $vmcf7_trans[ $vmcf7_msg_type ] ) ? (string) $vmcf7_trans[ $vmcf7_msg_type ] : '';
							$vmcf7_source = isset( $vmcf7_field[ $vmcf7_source_key ] ) ? (string) $vmcf7_field[ $vmcf7_source_key ] : '';
							if ( 'invalid' === $vmcf7_msg_type && '' === $vmcf7_value && ! in_array( $vmcf7_field['type'], $vmcf7_format_types, true ) ) {
								continue;
							}
							$vmcf7_idle = '' === $vmcf7_value && '' === $vmcf7_source;
							if ( ! $vmcf7_idle ) {
								++$vmcf7_live;
							}
							$vmcf7_items[ $vmcf7_msg_type ] = array(
								'value'  => $vmcf7_value,
								'source' => $vmcf7_source,
								'idle'   => $vmcf7_idle,
							);
						}
						if ( $vmcf7_live ) {
							++$vmcf7_active_rows;
						}
						$vmcf7_lang_rows[] = array(
							'field' => $vmcf7_field,
							'items' => $vmcf7_items,
							'idle'  => 0 === $vmcf7_live,
						);
					}
					?>
					<div class="vmcf7-lang-panel<?php echo $vmcf7_first_panel ? ' active' : ''; ?>"
						 role="tabpanel"
						 id="vmcf7-lang-panel-<?php echo esc_attr( $vmcf7_lang_code ); ?>"
						 aria-labelledby="vmcf7-lang-tab-<?php echo esc_attr( $vmcf7_lang_code ); ?>"
						 data-lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>"
						 <?php echo $vmcf7_first_panel ? '' : 'hidden'; ?>>

						<div class="vmcf7-lang-panel__bar">
							<label class="vmcf7-filterbar__check" for="vmcf7-show-all-<?php echo esc_attr( $vmcf7_lang_code ); ?>">
								<input type="checkbox" class="vmcf7-show-all-slots" id="vmcf7-show-all-<?php echo esc_attr( $vmcf7_lang_code ); ?>">
								<?php esc_html_e( 'Also show messages that have no original text yet', 'validation-muse-for-contact-form-7' ); ?>
							</label>
							<?php if ( $vmcf7_ai_available ) : ?>
								<button type="button"
										class="button button-secondary vmcf7-ai-translate"
										data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>"
										data-lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>">
									<span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span>
									<?php
									/* translators: %s: language name. */
									echo esc_html( sprintf( __( 'Translate into %s automatically', 'validation-muse-for-contact-form-7' ), $vmcf7_lang_label ) );
									?>
								</button>
							<?php endif; ?>
						</div>
						<?php if ( $vmcf7_ai_available ) : ?>
							<p class="vmcf7-hint"><?php esc_html_e( 'Automatic translation uses the messages from the last time you saved the form, and replaces what is typed below. Nothing is stored until you save.', 'validation-muse-for-contact-form-7' ); ?></p>
						<?php endif; ?>
						<div class="vmcf7-lang-panel__feedback" aria-live="polite"></div>

						<p class="vmcf7-lang-panel__empty<?php echo $vmcf7_active_rows ? ' vmcf7-hidden' : ''; ?>">
							<?php esc_html_e( 'Nothing to translate yet. Write your messages in the fields above and save the form — they will then appear here.', 'validation-muse-for-contact-form-7' ); ?>
						</p>

						<?php foreach ( $vmcf7_lang_rows as $vmcf7_row ) : ?>
							<?php
							$vmcf7_field      = $vmcf7_row['field'];
							$vmcf7_type_label = isset( $vmcf7_type_labels[ $vmcf7_field['type'] ] ) ? $vmcf7_type_labels[ $vmcf7_field['type'] ] : $vmcf7_field['type'];
							?>
							<div class="vmcf7-trans-row<?php echo $vmcf7_row['idle'] ? ' is-idle' : ''; ?>" data-field="<?php echo esc_attr( $vmcf7_field['name'] ); ?>">
								<div class="vmcf7-trans-row__head">
									<span class="vmcf7-field__label"><?php echo esc_html( isset( $vmcf7_field['label'] ) ? $vmcf7_field['label'] : '' ); ?></span>
									<code class="vmcf7-field__name"><?php echo esc_html( $vmcf7_field['name'] ); ?></code>
									<span class="vmcf7-badge" title="<?php echo esc_attr( $vmcf7_field['type'] ); ?>"><?php echo esc_html( $vmcf7_type_label ); ?></span>
								</div>
								<div class="vmcf7-trans-row__items">
									<?php foreach ( $vmcf7_row['items'] as $vmcf7_msg_type => $vmcf7_item ) : ?>
										<?php $vmcf7_trans_id = 'vmcf7-t-' . $vmcf7_lang_code . '-' . $vmcf7_field['name'] . '-' . $vmcf7_msg_type; ?>
										<div class="vmcf7-trans-item<?php echo $vmcf7_item['idle'] ? ' is-idle' : ''; ?>">
											<label class="vmcf7-sublabel" for="<?php echo esc_attr( $vmcf7_trans_id ); ?>"><?php echo esc_html( 'invalid' === $vmcf7_msg_type && isset( $vmcf7_invalid_copy[ $vmcf7_field['type'] ] ) ? $vmcf7_invalid_copy[ $vmcf7_field['type'] ][2] : $vmcf7_when_labels[ $vmcf7_msg_type ] ); ?></label>
											<input type="text"
												   id="<?php echo esc_attr( $vmcf7_trans_id ); ?>"
												   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][<?php echo esc_attr( $vmcf7_msg_type ); ?>]"
												   value="<?php echo esc_attr( $vmcf7_item['value'] ); ?>"
												   class="vmcf7-input vmcf7-msg-input"
												   data-rule-type="<?php echo esc_attr( $vmcf7_msg_type ); ?>"
												   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], $vmcf7_msg_type ) ); ?>"
												   lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>">
											<p class="vmcf7-hint vmcf7-trans-item__source">
												<?php if ( '' !== $vmcf7_item['source'] ) : ?>
													<span class="vmcf7-trans-item__source-label"><?php esc_html_e( 'Original:', 'validation-muse-for-contact-form-7' ); ?></span>
													<?php echo esc_html( $vmcf7_item['source'] ); ?>
												<?php else : ?>
													<?php esc_html_e( 'No original message written yet.', 'validation-muse-for-contact-form-7' ); ?>
												<?php endif; ?>
											</p>
											<div class="vmcf7-lint-warning vmcf7-hidden" role="status"></div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<?php
					$vmcf7_first_panel = false;
				endforeach;
				?>
			</div>
		<?php endif; ?>

		<!-- Tools: copy, back up, reuse -->
		<div class="vmcf7-card vmcf7-tools vmcf7-toolbar">
			<div class="vmcf7-card__header">
				<h3 class="vmcf7-card__title">
					<button type="button" class="vmcf7-tools__toggle" aria-expanded="false" aria-controls="vmcf7-tools-body">
						<span class="dashicons dashicons-arrow-right-alt2 vmcf7-tools__chevron" aria-hidden="true"></span>
						<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
						<?php esc_html_e( 'Copy, back up and reuse', 'validation-muse-for-contact-form-7' ); ?>
						<span class="vmcf7-tools__sub"><?php esc_html_e( 'move your messages and rules between forms or sites', 'validation-muse-for-contact-form-7' ); ?></span>
					</button>
				</h3>
			</div>

			<div class="vmcf7-tools__body" id="vmcf7-tools-body" hidden>
				<?php if ( ! $vmcf7_is_saved ) : ?>
					<p class="vmcf7-tools__note"><?php esc_html_e( 'Save this form first. These tools become available once the form exists.', 'validation-muse-for-contact-form-7' ); ?></p>
				<?php else : ?>
					<p class="vmcf7-tools__note">
						<?php esc_html_e( 'These tools work with the last saved version of your forms.', 'validation-muse-for-contact-form-7' ); ?>
						<strong class="vmcf7-tools__unsaved vmcf7-hidden"><?php esc_html_e( 'You have unsaved changes on this tab — save the form first if you want them included.', 'validation-muse-for-contact-form-7' ); ?></strong>
					</p>

					<div class="vmcf7-sharing-notice vmcf7-tools__feedback" aria-live="polite"></div>

					<div class="vmcf7-tool" data-tool="copy">
						<div class="vmcf7-tool__text">
							<p class="vmcf7-tool__title"><?php esc_html_e( 'Copy from another form', 'validation-muse-for-contact-form-7' ); ?></p>
							<p class="vmcf7-hint"><?php esc_html_e( 'Take the messages and rules of another form and use them here. Works best when both forms use the same field names.', 'validation-muse-for-contact-form-7' ); ?></p>
						</div>
						<div class="vmcf7-tool__actions">
							<?php if ( $vmcf7_other_count ) : ?>
								<label class="screen-reader-text" for="vmcf7-copy-source-form"><?php esc_html_e( 'Form to copy from', 'validation-muse-for-contact-form-7' ); ?></label>
								<select id="vmcf7-copy-source-form">
									<option value=""><?php esc_html_e( '— Choose a form —', 'validation-muse-for-contact-form-7' ); ?></option>
									<?php foreach ( $vmcf7_other_forms as $vmcf7_other_form ) : ?>
										<option value="<?php echo esc_attr( absint( $vmcf7_other_form->ID ) ); ?>"><?php echo esc_html( '' !== $vmcf7_other_form->post_title ? $vmcf7_other_form->post_title : '#' . absint( $vmcf7_other_form->ID ) ); ?></option>
									<?php endforeach; ?>
								</select>
								<button type="button" id="vmcf7-copy-confirm-btn" class="button" data-target-id="<?php echo esc_attr( $vmcf7_form_id ); ?>">
									<?php esc_html_e( 'Copy to this form', 'validation-muse-for-contact-form-7' ); ?>
								</button>
							<?php else : ?>
								<span class="vmcf7-hint"><?php esc_html_e( 'You have no other contact forms yet.', 'validation-muse-for-contact-form-7' ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="vmcf7-tool" data-tool="file">
						<div class="vmcf7-tool__text">
							<p class="vmcf7-tool__title"><?php esc_html_e( 'Back up or move to another site', 'validation-muse-for-contact-form-7' ); ?></p>
							<p class="vmcf7-hint"><?php esc_html_e( 'Download this form’s messages and rules as a file, or load a file you downloaded earlier.', 'validation-muse-for-contact-form-7' ); ?></p>
						</div>
						<div class="vmcf7-tool__actions">
							<button type="button" id="vmcf7-export-btn" class="button" data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>">
								<span class="dashicons dashicons-download" aria-hidden="true"></span>
								<?php esc_html_e( 'Download file', 'validation-muse-for-contact-form-7' ); ?>
							</button>
							<button type="button" id="vmcf7-import-btn" class="button">
								<span class="dashicons dashicons-upload" aria-hidden="true"></span>
								<?php esc_html_e( 'Load from file…', 'validation-muse-for-contact-form-7' ); ?>
							</button>
							<input type="file" id="vmcf7-import-file" class="vmcf7-hidden" accept=".json,application/json" tabindex="-1" aria-hidden="true">
						</div>
					</div>

					<div class="vmcf7-tool" data-tool="template">
						<div class="vmcf7-tool__text">
							<p class="vmcf7-tool__title"><?php esc_html_e( 'Your template', 'validation-muse-for-contact-form-7' ); ?></p>
							<p class="vmcf7-hint"><?php esc_html_e( 'Keep one favourite set of messages and rules as a template, then reuse it on any form with one click.', 'validation-muse-for-contact-form-7' ); ?></p>
						</div>
						<div class="vmcf7-tool__actions">
							<button type="button" class="button vmcf7-bulk-btn" data-action="save_default" data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>">
								<?php esc_html_e( 'Save this form as template', 'validation-muse-for-contact-form-7' ); ?>
							</button>
							<button type="button" class="button vmcf7-bulk-btn" data-action="apply_default" data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>">
								<?php esc_html_e( 'Use template on this form', 'validation-muse-for-contact-form-7' ); ?>
							</button>
						</div>
					</div>

					<div class="vmcf7-tool vmcf7-tool--danger" data-tool="all">
						<div class="vmcf7-tool__text">
							<p class="vmcf7-tool__title"><?php esc_html_e( 'Use on all forms', 'validation-muse-for-contact-form-7' ); ?></p>
							<p class="vmcf7-hint"><?php esc_html_e( 'Copy this form’s messages and rules to every other contact form on this site. Their current validation settings are overwritten.', 'validation-muse-for-contact-form-7' ); ?></p>
						</div>
						<div class="vmcf7-tool__actions">
							<?php if ( $vmcf7_other_count ) : ?>
								<button type="button" class="button vmcf7-bulk-btn vmcf7-button-danger" data-action="bulk_apply_all" data-form-id="<?php echo esc_attr( $vmcf7_form_id ); ?>">
									<?php esc_html_e( 'Copy to all other forms…', 'validation-muse-for-contact-form-7' ); ?>
								</button>
							<?php else : ?>
								<span class="vmcf7-hint"><?php esc_html_e( 'You have no other contact forms yet.', 'validation-muse-for-contact-form-7' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php endif; ?>

	<?php wp_nonce_field( 'vmcf7_save_messages', 'vmcf7_nonce' ); ?>
</div>
