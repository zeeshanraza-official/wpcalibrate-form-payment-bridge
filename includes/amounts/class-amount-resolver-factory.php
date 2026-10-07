<?php
/**
 * Factory for creating amount resolvers.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Amounts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Amount_Resolver_Factory
 */
class Amount_Resolver_Factory {

	/**
	 * Create amount resolver based on mapping configuration.
	 *
	 * @param array<string, mixed> $mapping Mapping settings.
	 * @return Amount_Resolver_Interface
	 * @throws \InvalidArgumentException If mode is unknown.
	 */
	public static function create( array $mapping ): Amount_Resolver_Interface {
		$mode = $mapping['amount_mode'] ?? 'fixed';

		switch ( $mode ) {
			case 'fixed':
				return new Fixed_Amount_Resolver();

			case 'field':
				return new Field_Amount_Resolver();

			case 'selectable':
				return new Selection_Amount_Resolver();

			default:
				throw new \InvalidArgumentException(
					sprintf( 'Unsupported amount mode: %s', esc_html( (string) $mode ) )
				);
		}
	}
}
