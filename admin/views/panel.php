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
			<?php esc_html_e( 'Enable custom validation messages for this form', 'validation-muse-for-contact-form-7' ); ?>
		</label>
	</div>

	<?php if ( ! empty( $vmcf7_fields ) ) : ?>
		<table class="vmcf7-fields-table" role="grid" aria-label="<?php esc_attr_e( 'Custom validation messages', 'validation-muse-for-contact-form-7' ); ?>">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Field', 'validation-muse-for-contact-form-7' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Required Message', 'validation-muse-for-contact-form-7' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Invalid Format Message', 'validation-muse-for-contact-form-7' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $vmcf7_fields as $vmcf7_field ) : ?>
					<tr>
						<td>
							<div class="vmcf7-field-name"><?php echo esc_html( $vmcf7_field['name'] ); ?></div>
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
								   class="large-text"
								   placeholder="<?php esc_attr_e( 'This field is required', 'validation-muse-for-contact-form-7' ); ?>">
						</td>
						<td>
							<?php if ( in_array( $vmcf7_field['type'], array( 'email', 'url', 'tel', 'number', 'range', 'date' ), true ) ) : ?>
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
									   class="large-text"
									   placeholder="<?php echo esc_attr( $this->get_default_invalid_message( $vmcf7_field['type'] ) ); ?>">
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="vmcf7-no-fields">
			<?php esc_html_e( 'No required fields found in this form.', 'validation-muse-for-contact-form-7' ); ?>
		</div>
	<?php endif; ?>

	<?php wp_nonce_field( 'vmcf7_save_messages', 'vmcf7_nonce' ); ?>
</div>