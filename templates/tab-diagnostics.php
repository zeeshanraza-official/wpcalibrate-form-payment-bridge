<?php
/**
 * Diagnostics & Orders tab template.
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

$health     = $template_data['health_checker']->get_system_health( $template_data['repository'] );
$settings   = $template_data['repository']->get_settings();
$wc_log_url = admin_url( 'admin.php?page=wc-status&tab=logs' );
?>

<div class="wpcalibrate-fpb-tab-diagnostics">
	<!-- System Health Panel -->
	<div class="wpcalibrate-fpb-box">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'System & Dependency Health', 'wpcalibrate-form-payment-bridge' ); ?></h3>
		
		<table class="widefat striped wpcalibrate-fpb-status-table" style="max-width: 800px;">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'PHP Version', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<code><?php echo esc_html( $health['php_version'] ); ?></code>
						<?php if ( $health['php_compatible'] ) : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'Compatible (>= 8.2)', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php else : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger"><?php esc_html_e( 'Requires PHP 8.2+', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WordPress Core', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td><code><?php echo esc_html( $health['wp_version'] ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WooCommerce Dependency', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<?php if ( $health['wc_active'] ) : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'Active', 'wpcalibrate-form-payment-bridge' ); ?> &mdash; <?php echo esc_html( $health['wc_version'] ); ?></span>
						<?php else : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger"><?php esc_html_e( 'Inactive or Missing', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Forminator Dependency', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<?php if ( $health['forminator_active'] ) : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'Active', 'wpcalibrate-form-payment-bridge' ); ?> &mdash; <?php echo esc_html( $health['forminator_version'] ); ?></span>
						<?php else : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger"><?php esc_html_e( 'Inactive or Missing', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WooCommerce HPOS Status', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<?php if ( $health['hpos_enabled'] ) : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'High-Performance Order Storage Enabled', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php else : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Standard Post Tables Active', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Guest Checkout Allowed', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<?php if ( $health['guest_checkout'] ) : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'Enabled in WooCommerce', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php else : ?>
							<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--warning"><?php esc_html_e( 'Disabled (Guests will be prompted to log in to pay)', 'wpcalibrate-form-payment-bridge' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Available Payment Gateways', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<strong><?php echo esc_html( $health['available_gateways'] ); ?></strong> <?php esc_html_e( 'gateways active', 'wpcalibrate-form-payment-bridge' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- Form Mappings Health Checks -->
	<div class="wpcalibrate-fpb-box">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'Mapping Configuration Health', 'wpcalibrate-form-payment-bridge' ); ?></h3>

		<?php if ( empty( $health['mappings_health'] ) ) : ?>
			<p><?php esc_html_e( 'No mappings configured yet.', 'wpcalibrate-form-payment-bridge' ); ?></p>
		<?php else : ?>
			<table class="widefat striped wpcalibrate-fpb-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Mapping Name', 'wpcalibrate-form-payment-bridge' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wpcalibrate-form-payment-bridge' ); ?></th>
						<th><?php esc_html_e( 'Integrity & Warnings', 'wpcalibrate-form-payment-bridge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $health['mappings_health'] as $item ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $item['name'] ); ?></strong></td>
							<td>
								<?php if ( $item['enabled'] ) : ?>
									<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'Enabled', 'wpcalibrate-form-payment-bridge' ); ?></span>
								<?php else : ?>
									<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Disabled', 'wpcalibrate-form-payment-bridge' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $item['healthy'] ) : ?>
									<span style="color: #46b450;"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'All mapped fields verified.', 'wpcalibrate-form-payment-bridge' ); ?></span>
								<?php else : ?>
									<ul style="color: #dc3232; margin: 0; padding-left: 20px;">
										<?php foreach ( $item['issues'] as $issue ) : ?>
											<li><?php echo esc_html( $issue ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- Diagnostic & Retention Settings -->
	<div class="wpcalibrate-fpb-box">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'Diagnostics & Data Retention Settings', 'wpcalibrate-form-payment-bridge' ); ?></h3>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=diagnostics' ) ); ?>">
			<?php wp_nonce_field( 'wpcalibrate_fpb_diagnostics_nonce', 'wpcalibrate_fpb_nonce' ); ?>
			<input type="hidden" name="wpcalibrate_fpb_action" value="save_diagnostics">

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Diagnostic Logging', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enable_logging" value="1" <?php checked( ! empty( $settings['enable_logging'] ) ); ?>>
							<?php esc_html_e( 'Enable detailed diagnostic logging in WooCommerce logs', 'wpcalibrate-form-payment-bridge' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Logs events to the dedicated source "wpcalibrate-form-payment-bridge". Sensitive credentials and card data are automatically redacted.', 'wpcalibrate-form-payment-bridge' ); ?>
							<?php if ( function_exists( 'WC' ) ) : ?>
								<br><a href="<?php echo esc_url( $wc_log_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View WooCommerce Logs &rarr;', 'wpcalibrate-form-payment-bridge' ); ?></a>
							<?php endif; ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Data Retention on Uninstall', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="delete_on_uninstall" value="1" <?php checked( ! empty( $settings['delete_on_uninstall'] ) ); ?>>
							<strong style="color: #b32d2e;"><?php esc_html_e( 'Delete plugin settings on uninstall', 'wpcalibrate-form-payment-bridge' ); ?></strong>
						</label>
						<p class="description">
							<?php esc_html_e( 'Default is OFF. When enabled, removing this plugin will purge plugin settings and mappings. Operational WooCommerce orders, customer transactions, and Forminator submissions are ALWAYS preserved.', 'wpcalibrate-form-payment-bridge' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Diagnostic Settings', 'wpcalibrate-form-payment-bridge' ); ?></button>
			</p>
		</form>
	</div>
</div>
