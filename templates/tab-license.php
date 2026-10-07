<?php
/**
 * License tab template.
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

$provider   = $template_data['license_provider'];
$masked_key = $provider->get_masked_key();
$status     = $provider->get_status();
?>

<div class="wpcalibrate-fpb-tab-license">
	<div class="wpcalibrate-fpb-box" style="max-width: 750px;">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'WPCalibrate License Management', 'wpcalibrate-form-payment-bridge' ); ?></h3>

		<!-- Blocked Provider Notice -->
		<div class="notice notice-warning inline" style="margin: 15px 0 20px 0; padding: 12px;">
			<p>
				<strong><?php esc_html_e( 'Integration Status: BLOCKED: LICENSING API DETAILS REQUIRED', 'wpcalibrate-form-payment-bridge' ); ?></strong><br>
				<?php esc_html_e( 'Remote license provider API endpoints and authentication details have not yet been provided. In accordance with WPCalibrate engineering standards, remote validation is not simulated or falsified. Core plugin features remain fully operational and testable.', 'wpcalibrate-form-payment-bridge' ); ?>
			</p>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=license' ) ); ?>">
			<?php wp_nonce_field( 'wpcalibrate_fpb_license_nonce', 'wpcalibrate_fpb_nonce' ); ?>
			<input type="hidden" name="wpcalibrate_fpb_action" value="save_license">

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'License Status', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--warning">
							<?php esc_html_e( 'Unconfigured (Provider API Required)', 'wpcalibrate-form-payment-bridge' ); ?>
						</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="wpcalibrate_fpb_license_key"><?php esc_html_e( 'License Key', 'wpcalibrate-form-payment-bridge' ); ?></label>
					</th>
					<td>
						<?php if ( ! empty( $masked_key ) ) : ?>
							<p style="margin: 0 0 8px 0;">
								<strong><?php esc_html_e( 'Current Key:', 'wpcalibrate-form-payment-bridge' ); ?></strong> <code><?php echo esc_html( $masked_key ); ?></code>
							</p>
						<?php endif; ?>
						<input type="password" id="wpcalibrate_fpb_license_key" name="license_key" class="regular-text" placeholder="<?php esc_attr_e( 'Enter your license key', 'wpcalibrate-form-payment-bridge' ); ?>" autocomplete="off">
						<p class="description">
							<?php esc_html_e( 'Enter your license key from your WPCalibrate account.', 'wpcalibrate-form-payment-bridge' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Renewal / Purchase', 'wpcalibrate-form-payment-bridge' ); ?></th>
					<td>
						<a href="<?php echo esc_url( $provider->get_renewal_url() ?? 'https://marketplace.wpcalibrate.com/' ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
							<?php esc_html_e( 'Visit WPCalibrate Marketplace &rarr;', 'wpcalibrate-form-payment-bridge' ); ?>
						</a>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save License Key', 'wpcalibrate-form-payment-bridge' ); ?></button>
			</p>
		</form>
	</div>
</div>
