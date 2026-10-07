<?php
/**
 * Fixed amount resolver.
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
 * Class Fixed_Amount_Resolver
 */
class Fixed_Amount_Resolver implements Amount_Resolver_Interface {

	/**
	 * {@inheritdoc}
	 */
	public function resolve( array $mapping, ?object $entry, Forminator_Adapter $adapter ): float {
		$raw = $mapping['fixed_amount'] ?? '';

		if ( ! is_numeric( $raw ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Fixed amount "%s" is non-numeric in mapping ID %s', sanitize_text_field( (string) $raw ), sanitize_text_field( $mapping['id'] ?? 'unknown' ) )
			);
		}

		$amount = (float) $raw;

		if ( is_nan( $amount ) || is_infinite( $amount ) || $amount <= 0 ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Fixed amount %.2f is not a positive valid number in mapping ID %s', $amount, sanitize_text_field( $mapping['id'] ?? 'unknown' ) )
			);
		}

		if ( function_exists( 'wc_format_decimal' ) ) {
			$amount = (float) wc_format_decimal( $amount );
		}

		return round( $amount, 4 );
	}
}
