<?php
/**
 * Mappings list template.
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

$mappings  = $template_data['repository']->get_all();
$validator = $template_data['validator'];
$adapter   = $template_data['forminator_adapter'];

$add_url = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings&action=add' );
?>

<div class="wpcalibrate-fpb-tab-mappings">
	<div class="wpcalibrate-fpb-toolbar">
		<h2 class="wpcalibrate-fpb-toolbar__title"><?php esc_html_e( 'Form Payment Mappings', 'wpcalibrate-form-payment-bridge' ); ?></h2>
		<a href="<?php echo esc_url( $add_url ); ?>" class="button button-primary">
			<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle; margin-top: -2px;"></span>
			<?php esc_html_e( 'Add Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
	</div>

	<?php if ( empty( $mappings ) ) : ?>
		<div class="wpcalibrate-fpb-empty-state">
			<span class="dashicons dashicons-forms wpcalibrate-fpb-empty-state__icon"></span>
			<h3><?php esc_html_e( 'No Form Payment Mappings Configured', 'wpcalibrate-form-payment-bridge' ); ?></h3>
			<p><?php esc_html_e( 'Connect your existing Forminator forms to WooCommerce by adding your first payment mapping.', 'wpcalibrate-form-payment-bridge' ); ?></p>
			<a href="<?php echo esc_url( $add_url ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Create Your First Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped wpcalibrate-fpb-table">
			<thead>
				<tr>
					<th scope="col" style="width: 80px;"><?php esc_html_e( 'Status', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Mapping Name', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Forminator Form', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Amount Mode', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Order Item Name', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Health', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<th scope="col" style="width: 140px; text-align: right;"><?php esc_html_e( 'Actions', 'wpcalibrate-form-payment-bridge' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $mappings as $id => $mapping ) :
					$edit_url = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings&action=edit&id=' . urlencode( $id ) );
					$issues   = $validator->check_health( $mapping );
					$is_healthy = empty( $issues );
					$mode_labels = [
						'fixed'      => __( 'Fixed Amount', 'wpcalibrate-form-payment-bridge' ),
						'field'      => __( 'Form Field / Calculation', 'wpcalibrate-form-payment-bridge' ),
						'selectable' => __( 'Selectable Choices', 'wpcalibrate-form-payment-bridge' ),
					];
					$mode_label = $mode_labels[ $mapping['amount_mode'] ?? 'fixed' ] ?? $mapping['amount_mode'];
					$is_enabled = ! empty( $mapping['enabled'] );
				?>
					<tr>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' ) ); ?>">
								<?php wp_nonce_field( 'wpcalibrate_fpb_toggle_mapping_nonce', 'wpcalibrate_fpb_nonce' ); ?>
								<input type="hidden" name="wpcalibrate_fpb_action" value="toggle_mapping">
								<input type="hidden" name="mapping_id" value="<?php echo esc_attr( $id ); ?>">
								<button type="submit" class="button button-small <?php echo $is_enabled ? 'button-primary' : ''; ?>" title="<?php echo $is_enabled ? esc_attr__( 'Click to disable', 'wpcalibrate-form-payment-bridge' ) : esc_attr__( 'Click to enable', 'wpcalibrate-form-payment-bridge' ); ?>">
									<?php echo $is_enabled ? esc_html__( 'Active', 'wpcalibrate-form-payment-bridge' ) : esc_html__( 'Inactive', 'wpcalibrate-form-payment-bridge' ); ?>
								</button>
							</form>
						</td>
						<td>
							<strong><a href="<?php echo esc_url( $edit_url ); ?>" class="row-title"><?php echo esc_html( $mapping['name'] ); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'wpcalibrate-form-payment-bridge' ); ?></a> | </span>
								<span class="trash">
									<?php
									$del_nonce_url = wp_nonce_url(
										admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings&action=delete&id=' . urlencode( $id ) ),
										'wpcalibrate_fpb_delete_' . $id
									);
									?>
									<a href="<?php echo esc_url( $del_nonce_url ); ?>" class="text-danger" style="color: #b32d2e;" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this payment mapping? This cannot be undone.', 'wpcalibrate-form-payment-bridge' ) ); ?>');"><?php esc_html_e( 'Delete', 'wpcalibrate-form-payment-bridge' ); ?></a>
								</span>
							</div>
						</td>
						<td>
							<?php
							$form_id = absint( $mapping['form_id'] ?? 0 );
							$form_obj = $adapter->get_form( $form_id );
							$form_name = '';
							if ( $form_obj ) {
								$form_settings = $form_obj->settings ?? [];
								$form_name = is_array( $form_settings ) ? ( $form_settings['form_name'] ?? '' ) : '';
							}
							?>
							#<?php echo esc_html( $form_id ); ?>
							<?php if ( ! empty( $form_name ) ) : ?>
								&mdash; <em><?php echo esc_html( $form_name ); ?></em>
							<?php endif; ?>
						</td>
						<td>
							<span class="wpcalibrate-fpb-tag"><?php echo esc_html( $mode_label ); ?></span>
							<?php if ( 'fixed' === ( $mapping['amount_mode'] ?? '' ) ) : ?>
								<br><small><?php echo esc_html( function_exists( 'wc_price' ) ? wp_strip_all_tags( wc_price( $mapping['fixed_amount'] ?? 0 ) ) : ( $mapping['fixed_amount'] ?? '0' ) ); ?></small>
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( $mapping['order_item_name'] ?? __( 'Form Payment', 'wpcalibrate-form-payment-bridge' ) ); ?>
						</td>
						<td>
							<?php if ( $is_healthy ) : ?>
								<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success" title="<?php esc_attr_e( 'Configuration healthy', 'wpcalibrate-form-payment-bridge' ); ?>">
									<span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Healthy', 'wpcalibrate-form-payment-bridge' ); ?>
								</span>
							<?php else : ?>
								<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger" title="<?php echo esc_attr( implode( '; ', $issues ) ); ?>">
									<span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Warning', 'wpcalibrate-form-payment-bridge' ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td style="text-align: right; white-space: nowrap;">
							<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'wpcalibrate-form-payment-bridge' ); ?></a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' ) ); ?>" style="display:inline-block; margin-left: 4px;" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this payment mapping? This cannot be undone.', 'wpcalibrate-form-payment-bridge' ) ); ?>');">
								<?php wp_nonce_field( 'wpcalibrate_fpb_delete_mapping_nonce', 'wpcalibrate_fpb_nonce' ); ?>
								<input type="hidden" name="wpcalibrate_fpb_action" value="delete_mapping">
								<input type="hidden" name="mapping_id" value="<?php echo esc_attr( $id ); ?>">
								<button type="submit" class="button button-small" style="color: #b32d2e; border-color: #d63638;"><?php esc_html_e( 'Delete', 'wpcalibrate-form-payment-bridge' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
