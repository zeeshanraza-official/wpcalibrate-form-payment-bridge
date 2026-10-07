<?php
/**
 * Field amount resolver for Forminator calculation and number fields.
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
 * Class Field_Amount_Resolver
 */
class Field_Amount_Resolver implements Amount_Resolver_Interface {

	/**
	 * {@inheritdoc}
	 */
	public function resolve( array $mapping, ?object $entry, Forminator_Adapter $adapter ): float {
		$field_id = trim( (string) ( $mapping['amount_field_id'] ?? '' ) );

		if ( empty( $field_id ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'No amount field specified in mapping ID %s', sanitize_text_field( $mapping['id'] ?? 'unknown' ) )
			);
		}

		if ( ! $entry ) {
			throw new Amount_Resolution_Exception(
				'Cannot resolve field amount without a Forminator entry object.'
			);
		}

		$raw_value = $adapter->get_entry_field_value( $entry, $field_id );

		if ( null === $raw_value || '' === $raw_value ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Field "%s" has no submitted value in entry', sanitize_text_field( $field_id ) ),
				__( 'The payment amount field was empty in the submitted form.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		// Normalize value if wrapped in array
		if ( is_array( $raw_value ) ) {
			$raw_value = $raw_value['value'] ?? ( reset( $raw_value ) ?: '' );
		}

		// Clean up common formatted string inputs (e.g. currency signs, commas)
		$clean_string = trim( (string) $raw_value );
		$clean_string = preg_replace( '/[^\d\.\,\-]/', '', $clean_string );

		// If comma is used as decimal separator (e.g. European formats 25,50), normalize
		if ( substr_count( $clean_string, ',' ) === 1 && substr_count( $clean_string, '.' ) === 0 ) {
			$clean_string = str_replace( ',', '.', $clean_string );
		} else {
			// Strip thousand separator commas
			$clean_string = str_replace( ',', '', $clean_string );
		}

		if ( ! is_numeric( $clean_string ) ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Non-numeric amount "%s" received from field "%s"', sanitize_text_field( (string) $raw_value ), sanitize_text_field( $field_id ) ),
				__( 'The submitted payment amount is invalid.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		$amount = (float) $clean_string;

		if ( is_nan( $amount ) || is_infinite( $amount ) || $amount <= 0 ) {
			throw new Amount_Resolution_Exception(
				sprintf( 'Calculated amount %.2f is not positive in field "%s"', $amount, sanitize_text_field( $field_id ) ),
				__( 'The calculated payment amount must be greater than zero.', 'wpcalibrate-form-payment-bridge' )
			);
		}

		if ( function_exists( 'wc_format_decimal' ) ) {
			$amount = (float) wc_format_decimal( $amount );
		}

		return round( $amount, 4 );
	}
}
