<?php
/**
 * System and integration health diagnostics.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Dependencies;
use WPCalibrate\FormPaymentBridge\Mappings\Mapping_Repository;
use WPCalibrate\FormPaymentBridge\Mappings\Mapping_Validator;

/**
 * Class Health_Check
 *
 * Inspects environment, dependencies, and configuration health.
 */
class Health_Check {

	/**
	 * Run complete health check.
	 *
	 * @param Mapping_Repository $repository Mapping repository.
	 * @return array<string, mixed>
	 */
	public function get_system_health( Mapping_Repository $repository ): array {
		global $wp_version;

		$hpos_enabled = false;
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			if ( method_exists( '\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) ) {
				$hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
			}
		}

		$guest_checkout = 'yes' === get_option( 'woocommerce_enable_guest_checkout', 'yes' );

		$available_gateways = 0;
		if ( function_exists( 'WC' ) && isset( WC()->payment_gateways ) ) {
			$gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$available_gateways = is_array( $gateways ) ? count( $gateways ) : 0;
		}

		$validator = new Mapping_Validator();
		$mappings = $repository->get_all();
		$mapping_health = [];

		foreach ( $mappings as $id => $mapping ) {
			$issues = $validator->check_health( $mapping );
			$mapping_health[ $id ] = [
				'id'      => $id,
				'name'    => $mapping['name'] ?? '',
				'enabled' => ! empty( $mapping['enabled'] ),
				'issues'  => $issues,
				'healthy' => empty( $issues ),
			];
		}

		return [
			'php_version'        => PHP_VERSION,
			'php_compatible'     => Dependencies::is_php_compatible(),
			'wp_version'         => $wp_version ?? 'Unknown',
			'wc_active'          => Dependencies::is_woocommerce_active(),
			'wc_version'         => Dependencies::get_woocommerce_version() ?? 'N/A',
			'forminator_active'  => Dependencies::is_forminator_active(),
			'forminator_version' => Dependencies::get_forminator_version() ?? 'N/A',
			'hpos_enabled'       => $hpos_enabled,
			'guest_checkout'     => $guest_checkout,
			'available_gateways' => $available_gateways,
			'mapping_count'      => count( $mappings ),
			'mappings_health'    => $mapping_health,
		];
	}
}
