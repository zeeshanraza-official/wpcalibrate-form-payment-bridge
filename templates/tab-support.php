<?php
/**
 * Support tab template.
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
?>

<div class="wpcalibrate-fpb-tab-support">
	<div class="wpcalibrate-fpb-box" style="max-width: 750px;">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'WPCalibrate Support & Inquiries', 'wpcalibrate-form-payment-bridge' ); ?></h3>
		<p><?php esc_html_e( 'Need help configuring your Forminator payment forms or integrating with your WooCommerce payment gateways? Reach out directly to our dedicated engineering support team.', 'wpcalibrate-form-payment-bridge' ); ?></p>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<span class="dashicons dashicons-email" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Email Support', 'wpcalibrate-form-payment-bridge' ); ?>
				</th>
				<td>
					<a href="mailto:support@wpcalibrate.com"><code>support@wpcalibrate.com</code></a>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<span class="dashicons dashicons-admin-site-alt3" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Official Website', 'wpcalibrate-form-payment-bridge' ); ?>
				</th>
				<td>
					<a href="<?php echo esc_url( 'https://wpcalibrate.com' ); ?>" target="_blank" rel="noopener noreferrer">
						https://wpcalibrate.com &rarr;
					</a>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Marketplace', 'wpcalibrate-form-payment-bridge' ); ?>
				</th>
				<td>
					<a href="<?php echo esc_url( 'https://marketplace.wpcalibrate.com/' ); ?>" target="_blank" rel="noopener noreferrer">
						https://marketplace.wpcalibrate.com/ &rarr;
					</a>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<span class="dashicons dashicons-phone" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Phone / WhatsApp', 'wpcalibrate-form-payment-bridge' ); ?>
				</th>
				<td>
					<a href="tel:+447474795976"><code>+447474795976</code></a>
				</td>
			</tr>
		</table>
	</div>

	<div class="wpcalibrate-fpb-box" style="max-width: 750px; margin-top: 20px;">
		<h3 class="wpcalibrate-fpb-box__title"><?php esc_html_e( 'Quick Debug Details for Support', 'wpcalibrate-form-payment-bridge' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Copy these details when submitting a technical support inquiry:', 'wpcalibrate-form-payment-bridge' ); ?></p>
		<textarea readonly style="width: 100%; height: 120px; font-family: monospace; font-size: 12px; background: #f8f9fa;">
Plugin: WPCalibrate Form Payment Bridge v<?php echo esc_html( WPCALIBRATE_FPB_VERSION ); ?>&#10;
WordPress: <?php global $wp_version; echo esc_html( $wp_version ?? 'Unknown' ); ?>&#10;
PHP: <?php echo esc_html( PHP_VERSION ); ?>&#10;
WooCommerce: <?php echo esc_html( \WPCalibrate\FormPaymentBridge\Dependencies::get_woocommerce_version() ?? 'N/A' ); ?>&#10;
Forminator: <?php echo esc_html( \WPCalibrate\FormPaymentBridge\Dependencies::get_forminator_version() ?? 'N/A' ); ?>&#10;
HPOS: <?php echo class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 'Yes' : 'No'; ?>
		</textarea>
	</div>
</div>
