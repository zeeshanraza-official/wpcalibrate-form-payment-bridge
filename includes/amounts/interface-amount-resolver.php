<?php
/**
 * Interface for payment amount resolvers.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Amounts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Forms\Forminator_Adapter;

/**
 * Interface Amount_Resolver_Interface
 */
interface Amount_Resolver_Interface {

	/**
	 * Calculate or extract monetary amount for a form submission.
	 *
	 * @param array<string, mixed> $mapping Form payment bridge mapping configuration.
	 * @param object|null          $entry   Forminator submission entry model.
	 * @param Forminator_Adapter   $adapter Forminator adapter.
	 * @return float Monetary amount.
	 * @throws Amount_Resolution_Exception If amount cannot be resolved or is invalid.
	 */
	public function resolve( array $mapping, ?object $entry, Forminator_Adapter $adapter ): float;
}
