<?php
/**
 * Fired during plugin activation.
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
 * Class Activator
 *
 * Handles activation tasks safely.
 */
class Activator {

	/**
	 * Run activation logic.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Verify minimum PHP requirement
		if ( ! Dependencies::is_php_compatible() ) {
			if ( ! function_exists( 'deactivate_plugins' ) && defined( 'ABSPATH' ) && file_exists( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( plugin_basename( WPCALIBRATE_FPB_FILE ) );
			}
			wp_die(
				sprintf(
					/* translators: 1: Required PHP version, 2: Current PHP version */
					esc_html__( 'WPCalibrate Form Payment Bridge requires PHP version %1$s or higher. Your server is running PHP version %2$s.', 'wpcalibrate-form-payment-bridge' ),
					esc_html( Dependencies::MIN_PHP_VERSION ),
					esc_html( PHP_VERSION )
				),
				esc_html__( 'Plugin Activation Error', 'wpcalibrate-form-payment-bridge' ),
				[ 'back_link' => true ]
			);
		}

		// Initialize default settings option if not existing
		$default_settings = [
			'version'              => WPCALIBRATE_FPB_VERSION,
			'schema_version'       => '1.0.0',
			'delete_on_uninstall'  => false,
			'enable_logging'       => false,
			'mappings'             => [],
		];

		if ( false === get_option( 'wpcalibrate_fpb_settings' ) ) {
			add_option( 'wpcalibrate_fpb_settings', $default_settings, '', 'no' );
		}

		// Record initial activation timestamp
		if ( false === get_option( 'wpcalibrate_fpb_installed_at' ) ) {
			add_option( 'wpcalibrate_fpb_installed_at', current_time( 'mysql' ), '', 'no' );
		}
	}
}
