<?php
/**
 * Migration manager for WPCalibrate Form Payment Bridge.
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
 * Class Migrations
 *
 * Manages schema and option migrations across versions.
 */
class Migrations {

	/**
	 * Current target schema version.
	 *
	 * @var string
	 */
	const CURRENT_SCHEMA_VERSION = '1.0.0';

	/**
	 * Run upgrade migrations if necessary.
	 *
	 * @return void
	 */
	public static function run_migrations(): void {
		$settings = get_option( 'wpcalibrate_fpb_settings', [] );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$installed_schema = $settings['schema_version'] ?? '0.0.0';

		if ( version_compare( $installed_schema, self::CURRENT_SCHEMA_VERSION, '<' ) ) {
			$success = self::migrate( $installed_schema, self::CURRENT_SCHEMA_VERSION, $settings );
			if ( $success ) {
				$settings['schema_version'] = self::CURRENT_SCHEMA_VERSION;
				$settings['version'] = WPCALIBRATE_FPB_VERSION;
				update_option( 'wpcalibrate_fpb_settings', $settings );
			}
		}
	}

	/**
	 * Perform migration steps.
	 *
	 * @param string               $from_version Current version.
	 * @param string               $to_version   Target version.
	 * @param array<string, mixed> $settings     Settings reference.
	 * @return bool
	 */
	private static function migrate( string $from_version, string $to_version, array &$settings ): bool {
		// Version 1.0.0 baseline normalization
		if ( version_compare( $from_version, '1.0.0', '<' ) ) {
			if ( ! isset( $settings['mappings'] ) || ! is_array( $settings['mappings'] ) ) {
				$settings['mappings'] = [];
			}
			if ( ! isset( $settings['delete_on_uninstall'] ) ) {
				$settings['delete_on_uninstall'] = false;
			}
			if ( ! isset( $settings['enable_logging'] ) ) {
				$settings['enable_logging'] = false;
			}
		}

		return true;
	}
}
