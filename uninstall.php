<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Retrieve settings
$settings = get_option( 'wpcalibrate_fpb_settings', [] );
$delete_on_uninstall = ! empty( $settings['delete_on_uninstall'] );

// Operational WooCommerce orders, customer data, and Forminator forms/entries are NEVER deleted!
if ( $delete_on_uninstall ) {
	// Delete plugin options
	delete_option( 'wpcalibrate_fpb_settings' );
	delete_option( 'wpcalibrate_fpb_installed_at' );
	delete_option( 'wpcalibrate_fpb_license_data' );

	// Delete all transient locks created exclusively by this plugin
	global $wpdb;
	if ( isset( $wpdb ) && ! empty( $wpdb->options ) ) {
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
}
