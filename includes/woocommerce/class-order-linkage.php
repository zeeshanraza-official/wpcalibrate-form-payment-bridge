<?php
/**
 * Idempotency and order linkage service.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order_Linkage
 *
 * Ensures exactly one WooCommerce order per Forminator submission and prevents race conditions.
 */
class Order_Linkage {

	/**
	 * Meta key for idempotency tracking.
	 */
	const META_IDEMPOTENCY_KEY = '_wpcalibrate_fpb_idempotency_key';

	/**
	 * Meta key for mapping ID.
	 */
	const META_MAPPING_ID = '_wpcalibrate_fpb_mapping_id';

	/**
	 * Meta key for Forminator form ID.
	 */
	const META_FORM_ID = '_wpcalibrate_fpb_form_id';

	/**
	 * Meta key for Forminator entry ID.
	 */
	const META_ENTRY_ID = '_wpcalibrate_fpb_entry_id';

	/**
	 * Generate a deterministic idempotency key.
	 *
	 * @param int $form_id  Forminator form ID.
	 * @param int $entry_id Forminator entry ID.
	 * @return string
	 */
	public function generate_key( int $form_id, int $entry_id ): string {
		return sprintf( 'fpb_form_%d_entry_%d', $form_id, $entry_id );
	}

	/**
	 * Acquire an atomic processing lock for a submission.
	 *
	 * @param string $key         Idempotency key.
	 * @param int    $ttl_seconds Lock time-to-live in seconds.
	 * @return bool True if lock acquired, false if already held.
	 */
	public function acquire_lock( string $key, int $ttl_seconds = 30 ): bool {
		$lock_key = 'wpcalibrate_fpb_lock_' . md5( $key );
		if ( false !== get_transient( $lock_key ) ) {
			return false;
		}

		set_transient( $lock_key, time(), $ttl_seconds );
		return true;
	}

	/**
	 * Release the processing lock.
	 *
	 * @param string $key Idempotency key.
	 * @return void
	 */
	public function release_lock( string $key ): void {
		$lock_key = 'wpcalibrate_fpb_lock_' . md5( $key );
		delete_transient( $lock_key );
	}

	/**
	 * Retrieve existing WooCommerce order linked to this idempotency key.
	 *
	 * @param string $key Idempotency key.
	 * @return \WC_Order|null
	 */
	public function get_existing_order( string $key ): ?\WC_Order {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return null;
		}

		$orders = wc_get_orders( [
			'limit'        => 1,
			'meta_key'     => self::META_IDEMPOTENCY_KEY,
			'meta_value'   => $key,
			'meta_compare' => '=',
			'return'       => 'objects',
		] );

		if ( ! empty( $orders ) && is_array( $orders ) ) {
			$order = reset( $orders );
			return ( $order instanceof \WC_Order ) ? $order : null;
		}

		return null;
	}

	/**
	 * Store integration trace metadata on the WooCommerce order.
	 *
	 * @param \WC_Order            $order           WooCommerce order.
	 * @param string               $idempotency_key Idempotency key.
	 * @param array<string, mixed> $mapping         Mapping configuration.
	 * @param int                  $form_id         Form ID.
	 * @param int                  $entry_id        Entry ID.
	 * @return void
	 */
	public function link_order(
		\WC_Order $order,
		string $idempotency_key,
		array $mapping,
		int $form_id,
		int $entry_id
	): void {
		$order->update_meta_data( self::META_IDEMPOTENCY_KEY, $idempotency_key );
		$order->update_meta_data( self::META_MAPPING_ID, sanitize_text_field( (string) ( $mapping['id'] ?? '' ) ) );
		$order->update_meta_data( self::META_FORM_ID, $form_id );
		$order->update_meta_data( self::META_ENTRY_ID, $entry_id );
		$order->update_meta_data( '_wpcalibrate_fpb_amount_mode', sanitize_text_field( (string) ( $mapping['amount_mode'] ?? '' ) ) );
		$order->update_meta_data( '_wpcalibrate_fpb_version', WPCALIBRATE_FPB_VERSION );
		$order->update_meta_data( '_wpcalibrate_fpb_created_at', current_time( 'mysql' ) );
	}
}
