<?php
/**
 * Unconfigured license provider placeholder adapter.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Unconfigured_License_Provider
 *
 * Implements clean adapter boundary without fabricating remote endpoints or validation.
 * Status: BLOCKED: LICENSING API DETAILS REQUIRED.
 */
class Unconfigured_License_Provider implements License_Provider_Interface {

	/**
	 * Status constant for unconfigured provider.
	 */
	const STATUS_UNCONFIGURED = 'unconfigured';

	/**
	 * Option key for stored license data.
	 */
	const OPTION_KEY = 'wpcalibrate_fpb_license_data';

	/**
	 * {@inheritdoc}
	 */
	public function is_provider_configured(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_status(): string {
		return self::STATUS_UNCONFIGURED;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_masked_key(): string {
		$data = get_option( self::OPTION_KEY, [] );
		$key = (string) ( $data['key'] ?? '' );
		if ( empty( $key ) ) {
			return '';
		}

		$len = strlen( $key );
		if ( $len <= 8 ) {
			return str_repeat( '*', $len );
		}

		return substr( $key, 0, 4 ) . str_repeat( '*', $len - 8 ) . substr( $key, -4 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_key( string $key ): bool {
		$clean_key = sanitize_text_field( trim( $key ) );
		$data = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		$data['key'] = $clean_key;
		$data['updated_at'] = current_time( 'mysql' );

		return update_option( self::OPTION_KEY, $data, 'no' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function activate(): array {
		// BLOCKED: LICENSING API DETAILS REQUIRED.
		// Do not fabricate remote calls, responses, or signatures.
		return [
			'success' => false,
			'message' => __( 'Remote license integration is currently BLOCKED awaiting WPCalibrate provider API details. Core plugin functionality remains fully operational.', 'wpcalibrate-form-payment-bridge' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate(): array {
		return [
			'success' => false,
			'message' => __( 'Remote license integration is currently BLOCKED awaiting WPCalibrate provider API details.', 'wpcalibrate-form-payment-bridge' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_expiration_date(): ?string {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_renewal_url(): ?string {
		return 'https://marketplace.wpcalibrate.com/';
	}
}
