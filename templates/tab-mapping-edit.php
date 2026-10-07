<?php
/**
 * Add / Edit Form Payment Mapping template.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 *
 * @var array<string, mixed> $template_data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id          = $template_data['mapping_id'];
$repository  = $template_data['repository'];
$adapter     = $template_data['forminator_adapter'];
$errors      = $template_data['form_errors'] ?? [];

$mapping = [];
if ( ! empty( $id ) ) {
	$mapping = $repository->get_by_id( $id ) ?? [];
}

// Populate defaults
$defaults = [
	'id'                  => $id,
	'enabled'             => true,
	'name'                => '',
	'form_id'             => 0,
	'amount_mode'         => 'fixed',
	'fixed_amount'        => '0.00',
	'amount_field_id'     => '',
	'selectable_field_id' => '',
	'selectable_mappings' => [],
	'order_item_name'     => __( 'Form Payment', 'wpcalibrate-form-payment-bridge' ),
	'product_id'          => 0,
	'first_name_field'    => '',
	'last_name_field'     => '',
	'email_field'         => '',
	'phone_field'         => '',
	'company_field'       => '',
	'address_1_field'     => '',
	'address_2_field'     => '',
	'city_field'          => '',
	'state_field'         => '',
	'postcode_field'      => '',
	'country_field'       => '',
	'customer_note_field' => '',
	'custom_meta_fields'  => [],
];
$data = array_merge( $defaults, $mapping );

$forms = $adapter->get_forms();
$current_form_id = absint( $data['form_id'] );
$form_fields = $current_form_id > 0 ? $adapter->get_form_fields( $current_form_id ) : [];

$cancel_url = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' );
?>

<div class="wpcalibrate-fpb-tab-mapping-edit">
	<div class="wpcalibrate-fpb-toolbar">
		<h2 class="wpcalibrate-fpb-toolbar__title">
			<?php echo ! empty( $id ) ? esc_html__( 'Edit Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ) : esc_html__( 'Add Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
		</h2>
		<a href="<?php echo esc_url( $cancel_url ); ?>" class="button button-secondary"><?php esc_html_e( '&larr; Back to Mappings', 'wpcalibrate-form-payment-bridge' ); ?></a>
	</div>

	<?php if ( ! empty( $errors ) ) : ?>
		<div class="notice notice-error wpcalibrate-fpb-notice">
			<p><strong><?php esc_html_e( 'Please correct the following errors:', 'wpcalibrate-form-payment-bridge' ); ?></strong></p>
			<ul>
				<?php foreach ( $errors as $err ) : ?>
					<li><?php echo esc_html( $err ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' ) ); ?>" class="wpcalibrate-fpb-form">
		<?php wp_nonce_field( 'wpcalibrate_fpb_save_mapping_nonce', 'wpcalibrate_fpb_nonce' ); ?>
		<input type="hidden" name="wpcalibrate_fpb_action" value="save_mapping">
		<input type="hidden" name="mapping[id]" value="<?php echo esc_attr( $data['id'] ); ?>">

		<!-- Card 1: Basic Information -->
		<div class="wpcalibrate-fpb-box">
			<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( '1. Form & Status', 'wpcalibrate-form-payment-bridge' ); ?></h3>
			
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_enabled"><?php esc_html_e( 'Enable Mapping', 'wpcalibrate-form-payment-bridge' ); ?></label>
					</th>
					<td>
						<label class="wpcalibrate-fpb-switch">
							<input type="checkbox" id="wpcalibrate_fpb_enabled" name="mapping[enabled]" value="1" <?php checked( ! empty( $data['enabled'] ) ); ?>>
							<span class="wpcalibrate-fpb-slider"></span>
						</label>
						<p class="description"><?php esc_html_e( 'When enabled, valid submissions of this form will automatically create a pending WooCommerce order.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_name"><?php esc_html_e( 'Mapping Name', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text" id="wpcalibrate_fpb_name" name="mapping[name]" class="regular-text" value="<?php echo esc_attr( $data['name'] ); ?>" required placeholder="<?php esc_attr_e( 'e.g. Workshop Registration Payment', 'wpcalibrate-form-payment-bridge' ); ?>">
						<p class="description"><?php esc_html_e( 'Internal label to identify this mapping in your dashboard.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_form_id"><?php esc_html_e( 'Forminator Form', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<select id="wpcalibrate_fpb_form_id" name="mapping[form_id]" class="regular-text" required>
							<option value=""><?php esc_html_e( '-- Select Forminator Form --', 'wpcalibrate-form-payment-bridge' ); ?></option>
							<?php foreach ( $forms as $f ) : ?>
								<option value="<?php echo esc_attr( $f['id'] ); ?>" <?php selected( $current_form_id, $f['id'] ); ?>>
									#<?php echo esc_html( $f['id'] ); ?> &mdash; <?php echo esc_html( $f['title'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Changing form will automatically update the field selectors below.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_item_name"><?php esc_html_e( 'Order Item Name', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text" id="wpcalibrate_fpb_item_name" name="mapping[order_item_name]" class="regular-text" value="<?php echo esc_attr( $data['order_item_name'] ); ?>" required placeholder="<?php esc_attr_e( 'e.g. Event Ticket', 'wpcalibrate-form-payment-bridge' ); ?>">
						<p class="description"><?php esc_html_e( 'The item name displayed on the customer checkout page and order receipt.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_product_id"><?php esc_html_e( 'WooCommerce Product Mapping (Optional)', 'wpcalibrate-form-payment-bridge' ); ?></label>
					</th>
					<td>
						<input type="number" id="wpcalibrate_fpb_product_id" name="mapping[product_id]" class="small-text" value="<?php echo esc_attr( $data['product_id'] > 0 ? $data['product_id'] : '' ); ?>" min="0" placeholder="0">
						<p class="description"><?php esc_html_e( 'Optional WooCommerce Product ID if you wish to associate order lines with an existing catalog product. Leave empty for custom standalone line item.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<!-- Card 2: Payment Amount Configuration -->
		<div class="wpcalibrate-fpb-box">
			<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( '2. Payment Amount Mode', 'wpcalibrate-form-payment-bridge' ); ?></h3>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Amount Source', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Amount Source Mode', 'wpcalibrate-form-payment-bridge' ); ?></span></legend>
							<label style="margin-right: 20px;">
								<input type="radio" name="mapping[amount_mode]" value="fixed" class="wpcalibrate-fpb-amount-mode-radio" <?php checked( 'fixed', $data['amount_mode'] ); ?>>
								<strong><?php esc_html_e( 'Fixed Amount', 'wpcalibrate-form-payment-bridge' ); ?></strong>
								<span class="description" style="display:block; margin-left: 20px;"><?php esc_html_e( 'A predefined static price configured below.', 'wpcalibrate-form-payment-bridge' ); ?></span>
							</label>
							<br>
							<label style="margin-right: 20px;">
								<input type="radio" name="mapping[amount_mode]" value="field" class="wpcalibrate-fpb-amount-mode-radio" <?php checked( 'field', $data['amount_mode'] ); ?>>
								<strong><?php esc_html_e( 'Form Field / Calculation', 'wpcalibrate-form-payment-bridge' ); ?></strong>
								<span class="description" style="display:block; margin-left: 20px;"><?php esc_html_e( 'Amount extracted server-side from a Forminator Calculation or Number field.', 'wpcalibrate-form-payment-bridge' ); ?></span>
							</label>
							<br>
							<label>
								<input type="radio" name="mapping[amount_mode]" value="selectable" class="wpcalibrate-fpb-amount-mode-radio" <?php checked( 'selectable', $data['amount_mode'] ); ?>>
								<strong><?php esc_html_e( 'Selectable Field Choices', 'wpcalibrate-form-payment-bridge' ); ?></strong>
								<span class="description" style="display:block; margin-left: 20px;"><?php esc_html_e( 'Price determined by user selection (radio, select, or checkbox) with administrator price mappings.', 'wpcalibrate-form-payment-bridge' ); ?></span>
							</label>
						</fieldset>
					</td>
				</tr>

				<!-- Mode 1: Fixed Amount Input -->
				<tr class="wpcalibrate-fpb-mode-panel wpcalibrate-fpb-mode-panel--fixed" style="<?php echo 'fixed' === $data['amount_mode'] ? '' : 'display:none;'; ?>">
					<th scope="row">
						<label for="wpcalibrate_fpb_fixed_amount"><?php esc_html_e( 'Fixed Amount', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="number" step="0.01" min="0.01" id="wpcalibrate_fpb_fixed_amount" name="mapping[fixed_amount]" class="regular-text" value="<?php echo esc_attr( $data['fixed_amount'] ); ?>">
						<p class="description"><?php esc_html_e( 'Amount in your WooCommerce store currency.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<!-- Mode 2: Field Amount Input -->
				<tr class="wpcalibrate-fpb-mode-panel wpcalibrate-fpb-mode-panel--field" style="<?php echo 'field' === $data['amount_mode'] ? '' : 'display:none;'; ?>">
					<th scope="row">
						<label for="wpcalibrate_fpb_amount_field_id"><?php esc_html_e( 'Amount Field', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<select id="wpcalibrate_fpb_amount_field_id" name="mapping[amount_field_id]" class="regular-text wpcalibrate-fpb-field-select" data-selected="<?php echo esc_attr( $data['amount_field_id'] ); ?>">
							<option value=""><?php esc_html_e( '-- Select Calculation/Number Field --', 'wpcalibrate-form-payment-bridge' ); ?></option>
							<?php foreach ( $form_fields as $field ) : ?>
								<option value="<?php echo esc_attr( $field['id'] ); ?>" <?php selected( $data['amount_field_id'], $field['id'] ); ?>>
									<?php echo esc_html( $field['label'] ); ?> (<?php echo esc_html( $field['id'] ); ?>)
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Values are validated strictly server-side from Forminator submitted entries.', 'wpcalibrate-form-payment-bridge' ); ?></p>
					</td>
				</tr>

				<!-- Mode 3: Selectable Field Choices -->
				<tr class="wpcalibrate-fpb-mode-panel wpcalibrate-fpb-mode-panel--selectable" style="<?php echo 'selectable' === $data['amount_mode'] ? '' : 'display:none;'; ?>">
					<th scope="row">
						<label for="wpcalibrate_fpb_selectable_field_id"><?php esc_html_e( 'Selectable Field', 'wpcalibrate-form-payment-bridge' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<select id="wpcalibrate_fpb_selectable_field_id" name="mapping[selectable_field_id]" class="regular-text wpcalibrate-fpb-field-select" data-selected="<?php echo esc_attr( $data['selectable_field_id'] ); ?>">
							<option value=""><?php esc_html_e( '-- Select Radio, Select, or Checkbox Field --', 'wpcalibrate-form-payment-bridge' ); ?></option>
							<?php foreach ( $form_fields as $field ) : ?>
								<option value="<?php echo esc_attr( $field['id'] ); ?>" <?php selected( $data['selectable_field_id'], $field['id'] ); ?>>
									<?php echo esc_html( $field['label'] ); ?> (<?php echo esc_html( $field['id'] ); ?>)
								</option>
							<?php endforeach; ?>
						</select>

						<div class="wpcalibrate-fpb-choice-mappings" style="margin-top: 15px;">
							<h4><?php esc_html_e( 'Choice-to-Price Mapping', 'wpcalibrate-form-payment-bridge' ); ?></h4>
							<table class="widefat wpcalibrate-fpb-choice-table" style="max-width: 600px;">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Submitted Value / Option Value', 'wpcalibrate-form-payment-bridge' ); ?></th>
										<th><?php esc_html_e( 'Amount', 'wpcalibrate-form-payment-bridge' ); ?></th>
										<th style="width: 50px;"></th>
									</tr>
								</thead>
								<tbody id="wpcalibrate-fpb-choice-rows">
									<?php
									$choices = ! empty( $data['selectable_mappings'] ) && is_array( $data['selectable_mappings'] )
										? $data['selectable_mappings']
										: [ [ 'option_value' => '', 'amount' => '0.00' ] ];
									foreach ( $choices as $index => $c ) :
									?>
										<tr class="wpcalibrate-fpb-choice-row">
											<td>
												<input type="text" name="mapping[selectable_mappings][<?php echo esc_attr( $index ); ?>][option_value]" value="<?php echo esc_attr( $c['option_value'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Option value', 'wpcalibrate-form-payment-bridge' ); ?>" class="regular-text" style="width: 100%;">
											</td>
											<td>
												<input type="number" step="0.01" min="0" name="mapping[selectable_mappings][<?php echo esc_attr( $index ); ?>][amount]" value="<?php echo esc_attr( $c['amount'] ?? '0.00' ); ?>" placeholder="0.00" class="regular-text" style="width: 100%;">
											</td>
											<td>
												<button type="button" class="button button-link-delete wpcalibrate-fpb-remove-choice-btn">&times;</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p style="margin-top: 10px;">
								<button type="button" class="button button-secondary" id="wpcalibrate-fpb-add-choice-btn">
									<?php esc_html_e( '+ Add Choice Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
								</button>
							</p>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<!-- Card 3: Customer & Billing Fields -->
		<div class="wpcalibrate-fpb-box">
			<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( '3. Customer & Billing Details Mapping', 'wpcalibrate-form-payment-bridge' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Map fields from your Forminator form to populate WooCommerce billing details.', 'wpcalibrate-form-payment-bridge' ); ?></p>

			<table class="form-table" role="presentation">
				<?php
				$billing_fields = [
					'first_name_field'    => [ __( 'First Name', 'wpcalibrate-form-payment-bridge' ), false ],
					'last_name_field'     => [ __( 'Last Name', 'wpcalibrate-form-payment-bridge' ), false ],
					'email_field'         => [ __( 'Email Address', 'wpcalibrate-form-payment-bridge' ), true ],
					'phone_field'         => [ __( 'Phone Number', 'wpcalibrate-form-payment-bridge' ), false ],
					'company_field'       => [ __( 'Company', 'wpcalibrate-form-payment-bridge' ), false ],
					'address_1_field'     => [ __( 'Address Line 1', 'wpcalibrate-form-payment-bridge' ), false ],
					'address_2_field'     => [ __( 'Address Line 2', 'wpcalibrate-form-payment-bridge' ), false ],
					'city_field'          => [ __( 'City', 'wpcalibrate-form-payment-bridge' ), false ],
					'state_field'         => [ __( 'State / Province', 'wpcalibrate-form-payment-bridge' ), false ],
					'postcode_field'      => [ __( 'Postcode / ZIP', 'wpcalibrate-form-payment-bridge' ), false ],
					'country_field'       => [ __( 'Country', 'wpcalibrate-form-payment-bridge' ), false ],
					'customer_note_field' => [ __( 'Customer Note', 'wpcalibrate-form-payment-bridge' ), false ],
				];

				foreach ( $billing_fields as $field_key => $field_info ) :
					$field_label = $field_info[0];
					$is_req      = $field_info[1];
				?>
					<tr>
						<th scope="row">
							<label for="wpcalibrate_fpb_<?php echo esc_attr( $field_key ); ?>">
								<?php echo esc_html( $field_label ); ?>
								<?php if ( $is_req ) : ?>
									<span class="required" title="<?php esc_attr_e( 'Required for enabled payment forms', 'wpcalibrate-form-payment-bridge' ); ?>">*</span>
								<?php endif; ?>
							</label>
						</th>
						<td>
							<select id="wpcalibrate_fpb_<?php echo esc_attr( $field_key ); ?>" name="mapping[<?php echo esc_attr( $field_key ); ?>]" class="regular-text wpcalibrate-fpb-field-select" data-selected="<?php echo esc_attr( $data[ $field_key ] ); ?>">
								<option value=""><?php esc_html_e( '-- Not Mapped --', 'wpcalibrate-form-payment-bridge' ); ?></option>
								<?php foreach ( $form_fields as $f ) : ?>
									<option value="<?php echo esc_attr( $f['id'] ); ?>" <?php selected( $data[ $field_key ], $f['id'] ); ?>>
										<?php echo esc_html( $f['label'] ); ?> (<?php echo esc_html( $f['id'] ); ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

		<!-- Card 4: Custom Order Metadata -->
		<div class="wpcalibrate-fpb-box">
			<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( '4. Internal Order Metadata (Optional)', 'wpcalibrate-form-payment-bridge' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Store additional Forminator form values as custom WooCommerce order meta keys.', 'wpcalibrate-form-payment-bridge' ); ?></p>

			<table class="widefat wpcalibrate-fpb-meta-table" style="max-width: 700px; margin-top: 10px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Forminator Field', 'wpcalibrate-form-payment-bridge' ); ?></th>
						<th><?php esc_html_e( 'Order Meta Key (e.g. attendee_badge_name)', 'wpcalibrate-form-payment-bridge' ); ?></th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody id="wpcalibrate-fpb-meta-rows">
					<?php
					$meta_fields = ! empty( $data['custom_meta_fields'] ) && is_array( $data['custom_meta_fields'] )
						? $data['custom_meta_fields']
						: [];
					foreach ( $meta_fields as $m_idx => $m_item ) :
					?>
						<tr class="wpcalibrate-fpb-meta-row">
							<td>
								<select name="mapping[custom_meta_fields][<?php echo esc_attr( $m_idx ); ?>][field_id]" class="wpcalibrate-fpb-field-select" style="width: 100%;" data-selected="<?php echo esc_attr( $m_item['field_id'] ?? '' ); ?>">
									<option value=""><?php esc_html_e( '-- Select Field --', 'wpcalibrate-form-payment-bridge' ); ?></option>
									<?php foreach ( $form_fields as $f ) : ?>
										<option value="<?php echo esc_attr( $f['id'] ); ?>" <?php selected( $m_item['field_id'] ?? '', $f['id'] ); ?>>
											<?php echo esc_html( $f['label'] ); ?> (<?php echo esc_html( $f['id'] ); ?>)
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
								<input type="text" name="mapping[custom_meta_fields][<?php echo esc_attr( $m_idx ); ?>][meta_key]" value="<?php echo esc_attr( $m_item['meta_key'] ?? '' ); ?>" class="regular-text" style="width: 100%;" placeholder="meta_key">
							</td>
							<td>
								<button type="button" class="button button-link-delete wpcalibrate-fpb-remove-meta-btn">&times;</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p style="margin-top: 10px;">
				<button type="button" class="button button-secondary" id="wpcalibrate-fpb-add-meta-btn">
					<?php esc_html_e( '+ Add Metadata Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
				</button>
			</p>
		</div>

		<!-- Submit & Delete Buttons -->
		<div class="submit" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 15px; border-top: 1px solid #c3c4c7;">
			<div style="display: flex; gap: 10px;">
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ); ?></button>
				<a href="<?php echo esc_url( $cancel_url ); ?>" class="button button-secondary button-large"><?php esc_html_e( 'Cancel', 'wpcalibrate-form-payment-bridge' ); ?></a>
			</div>
			<?php if ( ! empty( $id ) ) : ?>
				<div>
					<?php
					$delete_url = wp_nonce_url(
						admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings&action=delete&id=' . urlencode( $id ) ),
						'wpcalibrate_fpb_delete_' . $id
					);
					?>
					<a href="<?php echo esc_url( $delete_url ); ?>" class="button button-link-delete" style="color: #b32d2e; border: 1px solid #d63638; padding: 6px 14px; text-decoration: none; border-radius: 3px; font-weight: 500;" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this payment mapping? This cannot be undone.', 'wpcalibrate-form-payment-bridge' ) ); ?>');">
						<?php esc_html_e( 'Delete Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</form>
</div>
