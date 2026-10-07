<?php
/**
 * Selection amount resolver for Forminator select, radio, and checkbox fields.
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
 * Class Selection_Amount_Resolver
 */
class Selection_Amount_Resolver implements Amount_Resolver_Interface {

	/**
	 * {@inheritdoc}
	 */
	public function resolve( array $mapping, ?object $entry, Forminator_Adapter $adapter ): float {
		$field_id = trim( (string) ( $mapping['selectable_field_id'] ?? '' ) );

		if ( empty( $field_id ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'No selectable field configured in mapping ID %s', sanitize_text_field( $mapping['id'] ?? 'unknown' ) )
			);
		}

		if ( ! $entry ) {
			throw new Amount_Resolution_Exception(
				'Cannot resolve selection amount without a Forminator entry object.'
			);
		}

		$configured_mappings = $mapping['selectable_mappings'] ?? [];
		if ( empty( $configured_mappings ) || ! is_array( $configured_mappings ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'No selectable price mappings configured for field "%s" in mapping ID %s', sanitize_text_field( $field_id ), sanitize_text_field( $mapping['id'] ?? 'unknown' ) )
			);
		}

		// Build lookup map: option_value => float amount
		$price_map = [];
		foreach ( $configured_mappings as $item ) {
			if ( is_array( $item ) && isset( $item['option_value'] ) && isset( $item['amount'] ) ) {
				$val = trim( (string) $item['option_value'] );
				$amt = (float) $item['amount'];
				if ( '' !== $val && $amt >= 0 ) {
					$price_map[ $val ] = $amt;
				}
			}
		}

		$submitted_raw = $adapter->get_entry_field_value( $entry, $field_id );

		if ( null === $submitted_raw || '' === $submitted_raw ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Selectable field "%s" had no selection in entry', sanitize_text_field( $field_id ) ),
				__( 'Please select a valid payment option.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		// Normalize submitted values to an array of string values
		$selected_values = [];
		if ( is_array( $submitted_raw ) ) {
			// Could be array of values or nested value array
			if ( isset( $submitted_raw['value'] ) ) {
				$submitted_raw = $submitted_raw['value'];
			}
			if ( is_array( $submitted_raw ) ) {
				foreach ( $submitted_raw as $v ) {
					if ( is_scalar( $v ) && '' !== trim( (string) $v ) ) {
						$selected_values[] = trim( (string) $v );
					}
				}
			} elseif ( is_scalar( $submitted_raw ) ) {
				$selected_values[] = trim( (string) $submitted_raw );
			}
		} elseif ( is_scalar( $submitted_raw ) ) {
			$selected_values[] = trim( (string) $submitted_raw );
		}

		if ( empty( $selected_values ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Selectable field "%s" contained no valid options', sanitize_text_field( $field_id ) ),
				__( 'Please select a valid payment option.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		$total = 0.0;

		foreach ( $selected_values as $val ) {
			// Strictly validate choice against configured allowlist
			if ( ! array_key_exists( $val, $price_map ) ) {
				throw new Amount_Resolution_Exception(
					sprintf( 'Submitted choice "%s" for field "%s" is not in the configured price mapping allowlist', sanitize_text_field( $val ), sanitize_text_field( $field_id ) ),
					__( 'One of the selected payment options is invalid or no longer available.', 'wpcalibrate-form-payment-bridge' )
				);
			}

			$total += $price_map[ $val ];
		}

		if ( is_nan( $total ) || is_infinite( $total ) || $total <= 0 ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Calculated total %.2f for selectable field "%s" is not positive', $total, sanitize_text_field( $field_id ) ),
				__( 'The calculated total must be greater than zero.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		if ( function_exists( 'wc_format_decimal' ) ) {
			$total = (float) wc_format_decimal( $total );
		}

		return round( $total, 4 );
	}
}
