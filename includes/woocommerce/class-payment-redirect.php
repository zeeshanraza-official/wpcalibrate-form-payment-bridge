<?php
/**
 * Payment redirection handler.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Payment_Redirect
 *
 * Validates and produces safe redirect destinations for the WooCommerce order-pay endpoint.
 */
class Payment_Redirect {

	/**
	 * Get validated checkout payment URL from order.
	 *
	 * @param \WC_Order $order WooCommerce order.
	 * @return string Safe validated payment URL.
	 * @throws \RuntimeException If URL generation fails.
	 */
	public function get_payment_url( \WC_Order $order ): string {
		$url = $order->get_checkout_payment_url();

		if ( empty( $url ) ) {
			throw new \RuntimeException( 'Failed to generate checkout payment URL from order.' );
		}

		$sanitized_url = wp_sanitize_redirect( $url );
		$validated_url = wp_validate_redirect( $sanitized_url, '' );

		// If wp_validate_redirect fails (e.g. protocol mismatch in dev), verify domain
		if ( empty( $validated_url ) ) {
			$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
			$order_host = wp_parse_url( $sanitized_url, PHP_URL_HOST );
			if ( $site_host === $order_host ) {
				$validated_url = $sanitized_url;
			}
		}

		if ( empty( $validated_url ) ) {
			throw new \RuntimeException( 'Generated payment URL failed security validation.' );
		}

		return $validated_url;
	}

	/**
	 * Apply payment redirect to Forminator AJAX response array.
	 *
	 * @param array<string, mixed> $response Existing Forminator response.
	 * @param string               $payment_url Validated payment URL.
	 * @return array<string, mixed> Modified response.
	 */
	public function apply_ajax_redirect( array $response, string $payment_url ): array {
		$response['url'] = esc_url_raw( $payment_url );
		$response['redirect'] = true;
		$response['success'] = true;
		$response['behav'] = 'redirect';
		if ( empty( $response['message'] ) ) {
			$response['message'] = __( 'Redirecting to payment...', 'wpcalibrate-form-payment-bridge' );
		}
		return $response;
	}

	/**
	 * Perform safe redirect for page reload submissions.
	 *
	 * @param string $payment_url Validated payment URL.
	 * @return void
	 */
	public function perform_safe_redirect( string $payment_url ): void {
		wp_safe_redirect( $payment_url );
		exit;
	}
}
