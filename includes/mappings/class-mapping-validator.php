<?php
/**
 * Validator for Form Payment Bridge mappings.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Mappings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Forms\Forminator_Adapter;

/**
 * Class Mapping_Validator
 *
 * Validates mappings before persistence and checks configuration health.
 */
class Mapping_Validator {

	/**
	 * Forminator adapter.
	 *
	 * @var Forminator_Adapter
	 */
	private Forminator_Adapter $forminator_adapter;

	/**
	 * Constructor.
	 *
	 * @param Forminator_Adapter|null $forminator_adapter Optional adapter.
	 */
	public function __construct( ?Forminator_Adapter $forminator_adapter = null ) {
		$this->forminator_adapter = $forminator_adapter ?? new Forminator_Adapter();
	}

	/**
	 * Validate mapping data before saving.
	 *
	 * @param array<string, mixed> $data Mapping input.
	 * @return array<string, string> Array of field => error message.
	 */
	public function validate( array $data ): array {
		$errors = [];

		if ( empty( trim( (string) ( $data['name'] ?? '' ) ) ) ) {
			$errors['name'] = __( 'Mapping name is required.', 'wpcalibrate-form-payment-bridge' );
		}

		$form_id = absint( $data['form_id'] ?? 0 );
		if ( $form_id <= 0 ) {
			$errors['form_id'] = __( 'Please select a valid Forminator form.', 'wpcalibrate-form-payment-bridge' );
		}

		$item_name = trim( (string) ( $data['order_item_name'] ?? '' ) );
		if ( empty( $item_name ) ) {
			$errors['order_item_name'] = __( 'Order item name is required.', 'wpcalibrate-form-payment-bridge' );
		}

		$mode = (string) ( $data['amount_mode'] ?? 'fixed' );
		if ( ! in_array( $mode, [ 'fixed', 'field', 'selectable' ], true ) ) {
			$errors['amount_mode'] = __( 'Invalid payment amount mode.', 'wpcalibrate-form-payment-bridge' );
		}

		// Mode-specific validation
		if ( 'fixed' === $mode ) {
			$amount = (float) ( $data['fixed_amount'] ?? 0 );
			if ( $amount <= 0 ) {
				$errors['fixed_amount'] = __( 'Fixed amount must be greater than zero.', 'wpcalibrate-form-payment-bridge' );
			}
		} elseif ( 'field' === $mode ) {
			if ( empty( trim( (string) ( $data['amount_field_id'] ?? '' ) ) ) ) {
				$errors['amount_field_id'] = __( 'Please select a Forminator field for the payment amount.', 'wpcalibrate-form-payment-bridge' );
			}
		} elseif ( 'selectable' === $mode ) {
			if ( empty( trim( (string) ( $data['selectable_field_id'] ?? '' ) ) ) ) {
				$errors['selectable_field_id'] = __( 'Please select a Forminator selectable field.', 'wpcalibrate-form-payment-bridge' );
			}
			$options = $data['selectable_mappings'] ?? [];
			if ( empty( $options ) || ! is_array( $options ) ) {
				$errors['selectable_mappings'] = __( 'At least one choice mapping with a valid amount is required for selectable mode.', 'wpcalibrate-form-payment-bridge' );
			}
		}

		// Prevent saving an incomplete mapping as enabled
		$is_enabled = ! empty( $data['enabled'] );
		if ( $is_enabled ) {
			if ( empty( trim( (string) ( $data['email_field'] ?? '' ) ) ) ) {
				$errors['email_field'] = __( 'Customer email field mapping is required when enabling this payment form.', 'wpcalibrate-form-payment-bridge' );
			}

			// Verify form exists in Forminator if Forminator is active
			if ( $this->forminator_adapter->is_available() && $form_id > 0 ) {
				$form = $this->forminator_adapter->get_form( $form_id );
				if ( ! $form ) {
					$errors['form_id'] = __( 'The selected Forminator form was not found or has been deleted.', 'wpcalibrate-form-payment-bridge' );
				}
			}
		}

		return $errors;
	}

	/**
	 * Check health of an existing mapping.
	 *
	 * @param array<string, mixed> $mapping Stored mapping.
	 * @return array<string, string> List of warnings or errors.
	 */
	public function check_health( array $mapping ): array {
		$issues = [];
		$form_id = absint( $mapping['form_id'] ?? 0 );

		if ( ! $this->forminator_adapter->is_available() ) {
			$issues[] = __( 'Forminator plugin is not active.', 'wpcalibrate-form-payment-bridge' );
			return $issues;
		}

		$form = $this->forminator_adapter->get_form( $form_id );
		if ( ! $form ) {
			$issues[] = sprintf(
				/* translators: %d: Form ID */
				__( 'Forminator Form #%d does not exist or was deleted.', 'wpcalibrate-form-payment-bridge' ),
				$form_id
			);
			return $issues;
		}

		$fields = $this->forminator_adapter->get_form_fields( $form_id );
		$field_ids = array_map( function( $f ) {
			return $f['id'] ?? '';
		}, $fields );

		// Check amount field if applicable
		if ( 'field' === ( $mapping['amount_mode'] ?? '' ) ) {
			$amt_field = $mapping['amount_field_id'] ?? '';
			if ( ! in_array( $amt_field, $field_ids, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: Field ID */
					__( 'Mapped amount field "%s" no longer exists in Forminator form.', 'wpcalibrate-form-payment-bridge' ),
					esc_html( $amt_field )
				);
			}
		}

		// Check selectable field if applicable
		if ( 'selectable' === ( $mapping['amount_mode'] ?? '' ) ) {
			$sel_field = $mapping['selectable_field_id'] ?? '';
			if ( ! in_array( $sel_field, $field_ids, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: Field ID */
					__( 'Mapped selectable field "%s" no longer exists in Forminator form.', 'wpcalibrate-form-payment-bridge' ),
					esc_html( $sel_field )
				);
			}
		}

		// Check email field
		$email_field = $mapping['email_field'] ?? '';
		if ( ! empty( $email_field ) && ! in_array( $email_field, $field_ids, true ) ) {
			$issues[] = sprintf(
				/* translators: %s: Field ID */
				__( 'Mapped email field "%s" no longer exists in Forminator form.', 'wpcalibrate-form-payment-bridge' ),
				esc_html( $email_field )
			);
		}

		return $issues;
	}
}
