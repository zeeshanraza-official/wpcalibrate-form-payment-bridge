<?php
/**
 * WooCommerce order creation service using CRUD APIs.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Forms\Forminator_Adapter;

/**
 * Class Order_Service
 *
 * Creates and updates WooCommerce orders via official CRUD methods without touching the shopper cart.
 */
class Order_Service {

	/**
	 * Order linkage helper.
	 *
	 * @var Order_Linkage
	 */
	private Order_Linkage $order_linkage;

	/**
	 * Constructor.
	 *
	 * @param Order_Linkage|null $order_linkage Optional linkage instance.
	 */
	public function __construct( ?Order_Linkage $order_linkage = null ) {
		$this->order_linkage = $order_linkage ?? new Order_Linkage();
	}

	/**
	 * Create an unpaid WooCommerce order for a Forminator submission.
	 *
	 * @param array<string, mixed> $mapping         Mapping configuration.
	 * @param float                $amount          Authoritative server-side amount.
	 * @param object|null          $entry           Forminator submission entry.
	 * @param Forminator_Adapter   $adapter         Forminator adapter.
	 * @param string               $idempotency_key Deterministic idempotency key.
	 * @param int                  $form_id         Form ID.
	 * @param int                  $entry_id        Entry ID.
	 * @return \WC_Order
	 * @throws \RuntimeException If order creation fails.
	 */
	public function create_payment_order(
		array $mapping,
		float $amount,
		?object $entry,
		Forminator_Adapter $adapter,
		string $idempotency_key,
		int $form_id,
		int $entry_id
	): \WC_Order {
		if ( ! function_exists( 'wc_create_order' ) ) {
			throw new \RuntimeException( 'WooCommerce order creation API is not available.' );
		}

		$customer_id = get_current_user_id();

		// Create base order in pending status
		$order = wc_create_order( [
			'status'      => 'pending',
			'customer_id' => $customer_id > 0 ? $customer_id : 0,
		] );

		if ( ! $order || is_wp_error( $order ) ) {
			$error_msg = is_wp_error( $order ) ? $order->get_error_message() : 'Unknown error';
			throw new \RuntimeException( sprintf( 'Failed to create WooCommerce order: %s', $error_msg ) );
		}

		// Add line item
		$product_id = absint( $mapping['product_id'] ?? 0 );
		$product = ( $product_id > 0 && function_exists( 'wc_get_product' ) ) ? wc_get_product( $product_id ) : null;

		$item_name = ! empty( $mapping['order_item_name'] )
			? sanitize_text_field( $mapping['order_item_name'] )
			: __( 'Form Payment', 'wpcalibrate-form-payment-bridge' );

		if ( $product && is_a( $product, '\WC_Product' ) ) {
			$order->add_product( $product, 1, [
				'subtotal' => $amount,
				'total'    => $amount,
			] );
		} else {
			// Create custom line item without requiring public catalog product
			$item = new \WC_Order_Item_Product();
			$item->set_name( $item_name );
			$item->set_quantity( 1 );
			$item->set_subtotal( $amount );
			$item->set_total( $amount );
			$order->add_item( $item );
		}

		// Map billing fields from submission entry
		$this->apply_billing_data( $order, $mapping, $entry, $adapter );

		// Map customer note if configured
		$note_field = $mapping['customer_note_field'] ?? '';
		if ( ! empty( $note_field ) && $entry ) {
			$note_val = $adapter->get_entry_field_value( $entry, $note_field );
			if ( ! empty( $note_val ) && is_scalar( $note_val ) ) {
				$order->set_customer_note( sanitize_textarea_field( (string) $note_val ) );
			}
		}

		// Map custom metadata fields if configured
		if ( ! empty( $mapping['custom_meta_fields'] ) && is_array( $mapping['custom_meta_fields'] ) && $entry ) {
			foreach ( $mapping['custom_meta_fields'] as $meta_config ) {
				if ( is_array( $meta_config ) && ! empty( $meta_config['field_id'] ) && ! empty( $meta_config['meta_key'] ) ) {
					$field_val = $adapter->get_entry_field_value( $entry, $meta_config['field_id'] );
					if ( null !== $field_val ) {
						$meta_key = sanitize_key( $meta_config['meta_key'] );
						$order->update_meta_data(
							$meta_key,
							is_scalar( $field_val ) ? sanitize_text_field( (string) $field_val ) : $field_val
						);
					}
				}
			}
		}

		// Store integration trace metadata
		$this->order_linkage->link_order( $order, $idempotency_key, $mapping, $form_id, $entry_id );

		// Set order note explaining origin
		$order->add_order_note(
			sprintf(
				/* translators: 1: Form ID, 2: Entry ID */
				__( 'Order initialized via WPCalibrate Form Payment Bridge from Forminator form #%1$d (entry #%2$d).', 'wpcalibrate-form-payment-bridge' ),
				$form_id,
				$entry_id
			),
			false
		);

		// Calculate totals and ensure pending status
		$order->calculate_totals();
		$order->set_status( 'pending' );
		$order->save();

		return $order;
	}

	/**
	 * Map submitted fields to WooCommerce order billing fields.
	 *
	 * @param \WC_Order            $order   WooCommerce order.
	 * @param array<string, mixed> $mapping Mapping config.
	 * @param object|null          $entry   Forminator entry.
	 * @param Forminator_Adapter   $adapter Forminator adapter.
	 * @return void
	 */
	private function apply_billing_data(
		\WC_Order $order,
		array $mapping,
		?object $entry,
		Forminator_Adapter $adapter
	): void {
		if ( ! $entry ) {
			return;
		}

		$first_name = $this->extract_string_value( $adapter, $entry, $mapping['first_name_field'] ?? '', 'first-name' );
		$last_name  = $this->extract_string_value( $adapter, $entry, $mapping['last_name_field'] ?? '', 'last-name' );
		$email      = $this->extract_string_value( $adapter, $entry, $mapping['email_field'] ?? '' );
		$phone      = $this->extract_string_value( $adapter, $entry, $mapping['phone_field'] ?? '' );
		$company    = $this->extract_string_value( $adapter, $entry, $mapping['company_field'] ?? '' );
		$address_1  = $this->extract_string_value( $adapter, $entry, $mapping['address_1_field'] ?? '', 'street_address' );
		$address_2  = $this->extract_string_value( $adapter, $entry, $mapping['address_2_field'] ?? '', 'address_line_2' );
		$city       = $this->extract_string_value( $adapter, $entry, $mapping['city_field'] ?? '', 'city' );
		$state      = $this->extract_string_value( $adapter, $entry, $mapping['state_field'] ?? '', 'state' );
		$postcode   = $this->extract_string_value( $adapter, $entry, $mapping['postcode_field'] ?? '', 'zip' );
		$country    = $this->extract_string_value( $adapter, $entry, $mapping['country_field'] ?? '', 'country' );

		if ( ! empty( $first_name ) ) {
			$order->set_billing_first_name( sanitize_text_field( $first_name ) );
		}
		if ( ! empty( $last_name ) ) {
			$order->set_billing_last_name( sanitize_text_field( $last_name ) );
		}
		if ( ! empty( $email ) ) {
			$order->set_billing_email( sanitize_email( $email ) );
		}
		if ( ! empty( $phone ) ) {
			$order->set_billing_phone( sanitize_text_field( $phone ) );
		}
		if ( ! empty( $company ) ) {
			$order->set_billing_company( sanitize_text_field( $company ) );
		}
		if ( ! empty( $address_1 ) ) {
			$order->set_billing_address_1( sanitize_text_field( $address_1 ) );
		}
		if ( ! empty( $address_2 ) ) {
			$order->set_billing_address_2( sanitize_text_field( $address_2 ) );
		}
		if ( ! empty( $city ) ) {
			$order->set_billing_city( sanitize_text_field( $city ) );
		}
		if ( ! empty( $state ) ) {
			$order->set_billing_state( sanitize_text_field( $state ) );
		}
		if ( ! empty( $postcode ) ) {
			$order->set_billing_postcode( sanitize_text_field( $postcode ) );
		}
		if ( ! empty( $country ) ) {
			$order->set_billing_country( sanitize_text_field( $country ) );
		}
	}

	/**
	 * Extract string value from entry with support for Forminator complex/nested fields.
	 *
	 * @param Forminator_Adapter $adapter   Adapter.
	 * @param object             $entry     Entry.
	 * @param string             $field_id  Field ID slug.
	 * @param string             $sub_key   Optional subkey if grouped field.
	 * @return string
	 */
	private function extract_string_value(
		Forminator_Adapter $adapter,
		object $entry,
		string $field_id,
		string $sub_key = ''
	): string {
		if ( empty( $field_id ) ) {
			return '';
		}

		$raw = $adapter->get_entry_field_value( $entry, $field_id );
		if ( null === $raw ) {
			return '';
		}

		if ( is_array( $raw ) ) {
			if ( ! empty( $sub_key ) && isset( $raw[ $sub_key ] ) ) {
				return trim( (string) $raw[ $sub_key ] );
			}
			if ( isset( $raw['value'] ) && is_scalar( $raw['value'] ) ) {
				return trim( (string) $raw['value'] );
			}
			$first = reset( $raw );
			return is_scalar( $first ) ? trim( (string) $first ) : '';
		}

		return is_scalar( $raw ) ? trim( (string) $raw ) : '';
	}

	/**
	 * Register WooCommerce order admin UI elements.
	 *
	 * @return void
	 */
	public static function register_admin_order_meta(): void {
		add_action( 'woocommerce_admin_order_data_after_billing_address', function( $order ) {
			if ( ! is_a( $order, '\WC_Order' ) ) {
				return;
			}
			$form_id = $order->get_meta( Order_Linkage::META_FORM_ID );
			$entry_id = $order->get_meta( Order_Linkage::META_ENTRY_ID );

			if ( empty( $form_id ) || empty( $entry_id ) ) {
				return;
			}

			$adapter = new Forminator_Adapter();
			$entry_url = $adapter->get_entry_admin_url( (int) $form_id, (int) $entry_id );
			?>
			<div class="wpcalibrate-fpb-order-meta" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #2271b1;">
				<h4 style="margin: 0 0 5px 0;"><?php esc_html_e( 'Form Payment Bridge Info', 'wpcalibrate-form-payment-bridge' ); ?></h4>
				<p style="margin: 0; font-size: 13px;">
					<strong><?php esc_html_e( 'Forminator Form:', 'wpcalibrate-form-payment-bridge' ); ?></strong> #<?php echo esc_html( $form_id ); ?><br>
					<strong><?php esc_html_e( 'Forminator Submission:', 'wpcalibrate-form-payment-bridge' ); ?></strong> #<?php echo esc_html( $entry_id ); ?>
					<?php if ( ! empty( $entry_url ) && current_user_can( 'manage_options' ) ) : ?>
						&mdash; <a href="<?php echo esc_url( $entry_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Forminator Entry', 'wpcalibrate-form-payment-bridge' ); ?> &rarr;</a>
					<?php endif; ?>
				</p>
			</div>
			<?php
		} );
	}
}
