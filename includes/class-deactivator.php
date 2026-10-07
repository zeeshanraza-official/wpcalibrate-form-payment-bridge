<?php
/**
 * Fired during plugin deactivation.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Deactivator
 *
 * Handles deactivation tasks cleanly while preserving user data.
 */
class Deactivator {

	/**
	 * Run deactivation logic.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Clean up any transient locks created by this plugin
		global $wpdb;
		if ( isset( $wpdb ) && ! empty( $wpdb->options ) ) {
			// Delete transient locks
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$wpdb->esc_like( '_transient_wpcalibrate_fpb_lock_' ) . '%'
				)
			);
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$wpdb->esc_like( '_transient_timeout_wpcalibrate_fpb_lock_' ) . '%'
				)
			);
		}

		// Configuration, mappings, WooCommerce orders, and Forminator entries are preserved!
	}
}
