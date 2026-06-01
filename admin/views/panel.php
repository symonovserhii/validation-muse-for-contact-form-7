<?php
/**
 * Admin panel template for custom validation settings.
 *
 * @package ValidationMuse
 * @since   1.0.0
 *
 * @var int    $form_id The form ID.
 * @var array  $vmcf7_fields  Array of form fields.
 * @var string $enabled Whether custom validation is enabled.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="vmcf7-panel">
	<div class="vmcf7-enable-toggle">
		<label for="vmcf7-enabled">
			<input type="checkbox"
				   id="vmcf7-enabled"
				   name="vmcf7_enabled"
				   value="1"
				   <?php checked( $enabled, '1' ); ?>>
			<strong><?php esc_html_e( 'Enable custom validation messages for this form', 'validation-muse-for-contact-form-7' ); ?></strong>
		</label>
	</div>

	<?php if ( ! empty( $vmcf7_fields ) ) : ?>
		<!-- Sharing & Templates Toolbar -->
		<div class="vmcf7-toolbar">
			<div class="vmcf7-toolbar-left">
				<button type="button" id="vmcf7-export-btn" class="button button-secondary" data-form-id="<?php echo esc_attr( $form_id ); ?>">
					<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export JSON', 'validation-muse-for-contact-form-7' ); ?>
				</button>
				<button type="button" id="vmcf7-import-btn" class="button button-secondary">
					<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import JSON', 'validation-muse-for-contact-form-7' ); ?>
				</button>
				<input type="file" id="vmcf7-import-file" class="vmcf7-hidden" accept=".json">

				<button type="button" id="vmcf7-copy-btn" class="button button-secondary">
					<span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Copy Rules...', 'validation-muse-for-contact-form-7' ); ?>
				</button>
				
				<span class="vmcf7-copy-select-wrap vmcf7-hidden">
					<select id="vmcf7-copy-source-form">
						<option value=""><?php esc_html_e( '-- Source Form --', 'validation-muse-for-contact-form-7' ); ?></option>
						<?php
						$all_forms = get_posts( array(
							'post_type'      => 'wpcf7_contact_form',
							'posts_per_page' => -1,
							'exclude'        => array( $form_id ),
						) );
						foreach ( $all_forms as $f ) {
							printf( '<option value="%d">%s</option>', absint( $f->ID ), esc_html( $f->post_title ) );
						}
						?>
					</select>
					<button type="button" id="vmcf7-copy-confirm-btn" class="button button-primary" data-target-id="<?php echo esc_attr( $form_id ); ?>">
						<?php esc_html_e( 'Copy', 'validation-muse-for-contact-form-7' ); ?>
					</button>
				</span>
			</div>
			<div class="vmcf7-toolbar-right">
				<button type="button" class="button button-secondary vmcf7-bulk-btn" data-action="save_default" data-form-id="<?php echo esc_attr( $form_id ); ?>" title="<?php esc_attr_e( 'Save these rules as a reusable global default template', 'validation-muse-for-contact-form-7' ); ?>">
					<?php esc_html_e( 'Save as Default', 'validation-muse-for-contact-form-7' ); ?>
				</button>
				<button type="button" class="button button-secondary vmcf7-bulk-btn" data-action="apply_default" data-form-id="<?php echo esc_attr( $form_id ); ?>" title="<?php esc_attr_e( 'Apply the saved global default template to this form', 'validation-muse-for-contact-form-7' ); ?>">
					<?php esc_html_e( 'Apply Default', 'validation-muse-for-contact-form-7' ); ?>
				</button>
				<button type="button" class="button button-secondary vmcf7-bulk-btn" data-action="bulk_apply_all" data-form-id="<?php echo esc_attr( $form_id ); ?>" title="<?php esc_attr_e( 'Bulk copy these rules to all other contact forms', 'validation-muse-for-contact-form-7' ); ?>">
					<?php esc_html_e( 'Apply to All Forms', 'validation-muse-for-contact-form-7' ); ?>
				</button>
			</div>
		</div>

		<div class="vmcf7-sharing-notice vmcf7-hidden"></div>

		<!-- Rules Table -->
		<table class="vmcf7-fields-table" role="grid" aria-label="<?php esc_attr_e( 'Custom validation messages', 'validation-muse-for-contact-form-7' ); ?>">
			<thead>
				<tr>
					<th scope="col" class="vmcf7-col-25"><?php esc_html_e( 'Field', 'validation-muse-for-contact-form-7' ); ?></th>
					<th scope="col" class="vmcf7-col-37"><?php esc_html_e( 'Required Message', 'validation-muse-for-contact-form-7' ); ?></th>
					<th scope="col" class="vmcf7-col-37"><?php esc_html_e( 'Invalid Format Message', 'validation-muse-for-contact-form-7' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $vmcf7_fields as $vmcf7_field ) : ?>
					<tr class="vmcf7-field-row" data-field="<?php echo esc_attr( $vmcf7_field['name'] ); ?>">
						<td>
							<div class="vmcf7-field-name">
								<strong><?php echo esc_html( $vmcf7_field['name'] ); ?></strong>
								<button type="button" class="vmcf7-toggle-advanced button button-link" aria-expanded="false" title="<?php esc_attr_e( 'Show Regex / Length / Conditional validation rules', 'validation-muse-for-contact-form-7' ); ?>">
									<span class="dashicons dashicons-arrow-down-alt2"></span> <?php esc_html_e( 'Rules', 'validation-muse-for-contact-form-7' ); ?>
								</button>
							</div>
							<div class="vmcf7-field-type"><?php echo esc_html( $vmcf7_field['type'] ); ?></div>
						</td>
						<td>
							<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required" class="screen-reader-text">
								<?php
								printf(
									/* translators: %s: field name */
									esc_html__( 'Required message for %s', 'validation-muse-for-contact-form-7' ),
									esc_html( $vmcf7_field['name'] )
								);
								?>
							</label>
							<input type="text"
								   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required"
								   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][required]"
								   value="<?php echo esc_attr( $vmcf7_field['required_message'] ); ?>"
								   class="large-text vmcf7-msg-input"
								   data-rule-type="required"
								   placeholder="<?php esc_attr_e( 'This field is required', 'validation-muse-for-contact-form-7' ); ?>">
							<div class="vmcf7-lint-warning vmcf7-hidden"></div>
						</td>
						<td>
							<?php if ( in_array( $vmcf7_field['type'], array( 'email', 'url', 'tel', 'number', 'range', 'date', 'time' ), true ) ) : ?>
								<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-invalid" class="screen-reader-text">
									<?php
									printf(
										/* translators: %s: field name */
										esc_html__( 'Invalid format message for %s', 'validation-muse-for-contact-form-7' ),
										esc_html( $vmcf7_field['name'] )
									);
									?>
								</label>
								<input type="text"
									   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-invalid"
									   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][invalid]"
									   value="<?php echo esc_attr( $vmcf7_field['invalid_message'] ); ?>"
									   class="large-text vmcf7-msg-input"
									   data-rule-type="invalid"
									   placeholder="<?php echo esc_attr( $this->get_default_invalid_message( $vmcf7_field['type'] ) ); ?>">
								<div class="vmcf7-lint-warning vmcf7-hidden"></div>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'Format validation not supported.', 'validation-muse-for-contact-form-7' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					
					<!-- Advanced Rules Drawer -->
					<tr class="vmcf7-advanced-row" id="vmcf7-advanced-<?php echo esc_attr( $vmcf7_field['name'] ); ?>">
						<td colspan="3">
							<div class="vmcf7-advanced-rules-container">
								<div class="vmcf7-rule-grid">
									<!-- Regex Group -->
									<div class="vmcf7-rule-group">
										<h4><?php esc_html_e( 'Custom Regex Validation', 'validation-muse-for-contact-form-7' ); ?></h4>
										<div class="vmcf7-input-wrap">
											<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-regex"><?php esc_html_e( 'Pattern (without delimiters)', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text" 
												   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-regex" 
												   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][regex]" 
												   value="<?php echo esc_attr( $vmcf7_field['regex'] ); ?>" 
												   placeholder="e.g. ^[0-9]{5}$">
										</div>
										<div class="vmcf7-input-wrap">
											<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-regex-message"><?php esc_html_e( 'Error Message', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text" 
												   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-regex-message" 
												   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][regex_message]" 
												   value="<?php echo esc_attr( $vmcf7_field['regex_message'] ); ?>" 
												   class="vmcf7-msg-input"
												   data-rule-type="regex_message"
												   placeholder="<?php esc_attr_e( 'Invalid format', 'validation-muse-for-contact-form-7' ); ?>">
											<div class="vmcf7-lint-warning vmcf7-hidden"></div>
										</div>
									</div>

									<!-- Length Group -->
									<div class="vmcf7-rule-group">
										<h4><?php esc_html_e( 'Length Restriction', 'validation-muse-for-contact-form-7' ); ?></h4>
										<div class="vmcf7-flex-inputs">
											<div class="vmcf7-input-wrap short">
												<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-min-length"><?php esc_html_e( 'Min Characters', 'validation-muse-for-contact-form-7' ); ?></label>
												<input type="number" 
													   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-min-length" 
													   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][min_length]" 
													   value="<?php echo esc_attr( $vmcf7_field['min_length'] ); ?>" 
													   min="0">
											</div>
											<div class="vmcf7-input-wrap short">
												<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-max-length"><?php esc_html_e( 'Max Characters', 'validation-muse-for-contact-form-7' ); ?></label>
												<input type="number" 
													   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-max-length" 
													   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][max_length]" 
													   value="<?php echo esc_attr( $vmcf7_field['max_length'] ); ?>" 
													   min="0">
											</div>
										</div>
										<div class="vmcf7-input-wrap">
											<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-length-message"><?php esc_html_e( 'Error Message', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text" 
												   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-length-message" 
												   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][length_message]" 
												   value="<?php echo esc_attr( $vmcf7_field['length_message'] ); ?>" 
												   class="vmcf7-msg-input"
												   data-rule-type="length_message"
												   placeholder="<?php esc_attr_e( 'Must be between {min} and {max} characters', 'validation-muse-for-contact-form-7' ); ?>">
											<div class="vmcf7-lint-warning vmcf7-hidden"></div>
										</div>
									</div>

									<!-- Conditional Required-If Group -->
									<div class="vmcf7-rule-group">
										<h4><?php esc_html_e( 'Conditional Validation (Required-If)', 'validation-muse-for-contact-form-7' ); ?></h4>
										<div class="vmcf7-input-wrap">
											<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required-if-field"><?php esc_html_e( 'Companion Field Name', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text" 
												   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required-if-field" 
												   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][required_if_field]" 
												   value="<?php echo esc_attr( $vmcf7_field['required_if_field'] ); ?>" 
												   placeholder="e.g. newsletter-checkbox">
										</div>
										<div class="vmcf7-input-wrap">
											<label for="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required-if-message"><?php esc_html_e( 'Error Message', 'validation-muse-for-contact-form-7' ); ?></label>
											<input type="text" 
												   id="vmcf7-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-required-if-message" 
												   name="vmcf7[<?php echo esc_attr( $vmcf7_field['name'] ); ?>][required_if_message]" 
												   value="<?php echo esc_attr( $vmcf7_field['required_if_message'] ); ?>" 
												   class="vmcf7-msg-input"
												   data-rule-type="required_if_message"
												   placeholder="<?php esc_attr_e( 'This field is required', 'validation-muse-for-contact-form-7' ); ?>">
											<div class="vmcf7-lint-warning vmcf7-hidden"></div>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Live Preview Card Section -->
		<div class="vmcf7-live-preview-section">
			<h3><?php esc_html_e( 'Live Message Preview', 'validation-muse-for-contact-form-7' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Select a field to preview how its custom messages will render (evaluates placeholder tokens like {field_label}, {min}, {max}).', 'validation-muse-for-contact-form-7' ); ?></p>
			
			<div class="vmcf7-preview-selector-wrap">
				<select id="vmcf7-preview-field-selector">
					<option value=""><?php esc_html_e( '-- Choose Field --', 'validation-muse-for-contact-form-7' ); ?></option>
					<?php foreach ( $vmcf7_fields as $vmcf7_field ) : ?>
						<option value="<?php echo esc_attr( $vmcf7_field['name'] ); ?>" data-type="<?php echo esc_attr( $vmcf7_field['type'] ); ?>">
							<?php echo esc_html( $vmcf7_field['name'] ); ?> (<?php echo esc_html( $vmcf7_field['type'] ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="vmcf7-preview-card vmcf7-hidden">
				<div class="vmcf7-preview-label">
					<strong><?php esc_html_e( 'Extracted Field Label:', 'validation-muse-for-contact-form-7' ); ?></strong> 
					<span id="vmcf7-preview-label-text"></span>
				</div>
				<div class="vmcf7-preview-errors">
					<div class="vmcf7-preview-err-row" data-msg-type="required">
						<strong><?php esc_html_e( 'Required Field:', 'validation-muse-for-contact-form-7' ); ?></strong>
						<span class="wpcf7-not-valid-tip"></span>
					</div>
					<div class="vmcf7-preview-err-row" data-msg-type="invalid">
						<strong><?php esc_html_e( 'Invalid Format:', 'validation-muse-for-contact-form-7' ); ?></strong>
						<span class="wpcf7-not-valid-tip"></span>
					</div>
					<div class="vmcf7-preview-err-row vmcf7-hidden" data-msg-type="regex_message">
						<strong><?php esc_html_e( 'Custom Regex Match Fails:', 'validation-muse-for-contact-form-7' ); ?></strong>
						<span class="wpcf7-not-valid-tip"></span>
					</div>
					<div class="vmcf7-preview-err-row vmcf7-hidden" data-msg-type="length_message">
						<strong><?php esc_html_e( 'Min/Max Length Violated:', 'validation-muse-for-contact-form-7' ); ?></strong>
						<span class="wpcf7-not-valid-tip"></span>
					</div>
					<div class="vmcf7-preview-err-row vmcf7-hidden" data-msg-type="required_if_message">
						<strong><?php esc_html_e( 'Conditional (Required-If) Fails:', 'validation-muse-for-contact-form-7' ); ?></strong>
						<span class="wpcf7-not-valid-tip"></span>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div class="vmcf7-no-fields">
			<?php esc_html_e( 'No supported fields found in this form layout.', 'validation-muse-for-contact-form-7' ); ?>
		</div>
	<?php endif; ?>

	<?php
	// Flavor translation section.
	$vmcf7_flavor_languages = VMCF7_Flavor::get_target_languages();
	if ( ! empty( $vmcf7_fields ) && ! empty( $vmcf7_flavor_languages ) ) :
	?>
		<div class="vmcf7-translations">
			<h3><?php esc_html_e( 'Message Translations (Flavor)', 'validation-muse-for-contact-form-7' ); ?></h3>
			<div class="vmcf7-lang-tabs">
				<?php
				$vmcf7_first_tab = true;
				foreach ( $vmcf7_flavor_languages as $vmcf7_lang_code => $vmcf7_lang_data ) :
				?>
					<button type="button"
							class="vmcf7-lang-tab<?php echo esc_attr( $vmcf7_first_tab ? ' active' : '' ); ?>"
							data-lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>">
						<?php echo esc_html( $vmcf7_lang_data['native_name'] ); ?>
					</button>
				<?php
					$vmcf7_first_tab = false;
				endforeach;
				?>

				<?php if ( VMCF7_Flavor::is_ai_available() ) : ?>
					<button type="button"
							class="vmcf7-ai-translate button button-secondary"
							data-form-id="<?php echo esc_attr( $form_id ); ?>">
						<?php esc_html_e( 'AI Translate', 'validation-muse-for-contact-form-7' ); ?>
					</button>
				<?php endif; ?>
			</div>

			<?php
			$vmcf7_first_panel = true;
			foreach ( $vmcf7_flavor_languages as $vmcf7_lang_code => $vmcf7_lang_data ) :
			?>
				<div class="vmcf7-lang-panel<?php echo esc_attr( $vmcf7_first_panel ? ' active' : '' ); ?>"
					 data-lang="<?php echo esc_attr( $vmcf7_lang_code ); ?>">
					<table class="vmcf7-fields-table vmcf7-translation-table" role="grid"
						   aria-label="<?php echo esc_attr( $vmcf7_lang_data['name'] ); ?> translations">
						<thead>
							<tr>
								<th scope="col" class="vmcf7-col-25"><?php esc_html_e( 'Field', 'validation-muse-for-contact-form-7' ); ?></th>
								<th scope="col" class="vmcf7-col-37"><?php esc_html_e( 'Required Message', 'validation-muse-for-contact-form-7' ); ?></th>
								<th scope="col" class="vmcf7-col-37"><?php esc_html_e( 'Invalid Format Message', 'validation-muse-for-contact-form-7' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $vmcf7_fields as $vmcf7_field ) : ?>
								<?php
								$vmcf7_trans           = isset( $vmcf7_field['translations'][ $vmcf7_lang_code ] ) ? $vmcf7_field['translations'][ $vmcf7_lang_code ] : array();
								$vmcf7_req_trans       = isset( $vmcf7_trans['required'] ) ? $vmcf7_trans['required'] : '';
								$vmcf7_inv_trans       = isset( $vmcf7_trans['invalid'] ) ? $vmcf7_trans['invalid'] : '';
								$vmcf7_regex_trans     = isset( $vmcf7_trans['regex_message'] ) ? $vmcf7_trans['regex_message'] : '';
								$vmcf7_len_trans       = isset( $vmcf7_trans['length_message'] ) ? $vmcf7_trans['length_message'] : '';
								$vmcf7_req_if_trans    = isset( $vmcf7_trans['required_if_message'] ) ? $vmcf7_trans['required_if_message'] : '';
								$vmcf7_req_placeholder = $vmcf7_field['required_message'];
								$vmcf7_inv_placeholder = $vmcf7_field['invalid_message'];
								?>
								<tr class="vmcf7-trans-row" data-field="<?php echo esc_attr( $vmcf7_field['name'] ); ?>">
									<td>
										<div class="vmcf7-field-name">
											<strong><?php echo esc_html( $vmcf7_field['name'] ); ?></strong>
											<button type="button" class="vmcf7-toggle-advanced-trans button button-link" aria-expanded="false" title="<?php esc_attr_e( 'Translate Regex / Length / Conditional error messages', 'validation-muse-for-contact-form-7' ); ?>">
												<span class="dashicons dashicons-arrow-down-alt2"></span> <?php esc_html_e( 'Rules', 'validation-muse-for-contact-form-7' ); ?>
											</button>
										</div>
										<div class="vmcf7-field-type"><?php echo esc_html( $vmcf7_field['type'] ); ?></div>
									</td>
									<td>
										<input type="text"
											   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][required]"
											   value="<?php echo esc_attr( $vmcf7_req_trans ); ?>"
											   class="large-text vmcf7-msg-input"
											   data-rule-type="required"
											   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], 'required' ) ); ?>"
											   placeholder="<?php echo esc_attr( $vmcf7_req_placeholder ? 'EN: ' . $vmcf7_req_placeholder : '' ); ?>">
										<div class="vmcf7-lint-warning vmcf7-hidden"></div>
									</td>
									<td>
										<?php if ( in_array( $vmcf7_field['type'], array( 'email', 'url', 'tel', 'number', 'range', 'date', 'time' ), true ) ) : ?>
											<input type="text"
												   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][invalid]"
												   value="<?php echo esc_attr( $vmcf7_inv_trans ); ?>"
												   class="large-text vmcf7-msg-input"
												   data-rule-type="invalid"
												   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], 'invalid' ) ); ?>"
												   placeholder="<?php echo esc_attr( $vmcf7_inv_placeholder ? 'EN: ' . $vmcf7_inv_placeholder : '' ); ?>">
											<div class="vmcf7-lint-warning vmcf7-hidden"></div>
										<?php else : ?>
											<span class="description"><?php esc_html_e( 'Format translation not supported.', 'validation-muse-for-contact-form-7' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>

								<!-- Collapsible translations for custom rules -->
								<tr class="vmcf7-advanced-trans-row" id="vmcf7-advanced-trans-<?php echo esc_attr( $vmcf7_field['name'] ); ?>-<?php echo esc_attr( $vmcf7_lang_code ); ?>">
									<td colspan="3">
										<div class="vmcf7-advanced-rules-container translation-rules">
											<div class="vmcf7-rule-grid">
												<!-- Regex -->
												<div class="vmcf7-rule-group">
													<h4><?php esc_html_e( 'Translate Custom Regex Error', 'validation-muse-for-contact-form-7' ); ?></h4>
													<div class="vmcf7-input-wrap">
														<input type="text" 
															   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][regex_message]" 
															   value="<?php echo esc_attr( $vmcf7_regex_trans ); ?>" 
															   class="vmcf7-msg-input"
															   data-rule-type="regex_message"
															   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], 'regex_message' ) ); ?>"
															   placeholder="<?php echo esc_attr( $vmcf7_field['regex_message'] ? 'EN: ' . $vmcf7_field['regex_message'] : '' ); ?>">
														<div class="vmcf7-lint-warning vmcf7-hidden"></div>
													</div>
												</div>
												<!-- Length -->
												<div class="vmcf7-rule-group">
													<h4><?php esc_html_e( 'Translate Length Error', 'validation-muse-for-contact-form-7' ); ?></h4>
													<div class="vmcf7-input-wrap">
														<input type="text" 
															   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][length_message]" 
															   value="<?php echo esc_attr( $vmcf7_len_trans ); ?>" 
															   class="vmcf7-msg-input"
															   data-rule-type="length_message"
															   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], 'length_message' ) ); ?>"
															   placeholder="<?php echo esc_attr( $vmcf7_field['length_message'] ? 'EN: ' . $vmcf7_field['length_message'] : '' ); ?>">
														<div class="vmcf7-lint-warning vmcf7-hidden"></div>
													</div>
												</div>
												<!-- Required-If -->
												<div class="vmcf7-rule-group">
													<h4><?php esc_html_e( 'Translate Conditional Error', 'validation-muse-for-contact-form-7' ); ?></h4>
													<div class="vmcf7-input-wrap">
														<input type="text" 
															   name="vmcf7_translations[<?php echo esc_attr( $vmcf7_lang_code ); ?>][<?php echo esc_attr( $vmcf7_field['name'] ); ?>][required_if_message]" 
															   value="<?php echo esc_attr( $vmcf7_req_if_trans ); ?>" 
															   class="vmcf7-msg-input"
															   data-rule-type="required_if_message"
															   data-flavor-key="<?php echo esc_attr( VMCF7_Flavor::field_key( $vmcf7_field['name'], 'required_if_message' ) ); ?>"
															   placeholder="<?php echo esc_attr( $vmcf7_field['required_if_message'] ? 'EN: ' . $vmcf7_field['required_if_message'] : '' ); ?>">
														<div class="vmcf7-lint-warning vmcf7-hidden"></div>
													</div>
												</div>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php
				$vmcf7_first_panel = false;
			endforeach;
			?>
		</div>
	<?php endif; ?>

	<?php wp_nonce_field( 'vmcf7_save_messages', 'vmcf7_nonce' ); ?>
</div>