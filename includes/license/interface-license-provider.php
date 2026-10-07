<?php
/**
 * Contract for WPCalibrate license provider integration.
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
 * Interface License_Provider_Interface
 */
interface License_Provider_Interface {

	/**
	 * Check if a remote licensing provider has been configured.
	 *
	 * @return bool
	 */
	public function is_provider_configured(): bool;

	/**
	 * Get current license status.
	 *
	 * @return string 'unconfigured' | 'inactive' | 'active' | 'expired'
	 */
	public function get_status(): string;

	/**
	 * Get license key (masked for admin UI display).
	 *
	 * @return string
	 */
	public function get_masked_key(): string;

	/**
	 * Save/update license key.
	 *
	 * @param string $key Plaintext key.
	 * @return bool
	 */
	public function save_key( string $key ): bool;

	/**
	 * Activate license against provider.
	 *
	 * @return array{success: bool, message: string}
	 */
	public function activate(): array;

	/**
	 * Deactivate license against provider.
	 *
	 * @return array{success: bool, message: string}
	 */
	public function deactivate(): array;

	/**
	 * Get license expiration date.
	 *
	 * @return string|null
	 */
	public function get_expiration_date(): ?string;

	/**
	 * Get license renewal URL.
	 *
	 * @return string|null
	 */
	public function get_renewal_url(): ?string;
}
