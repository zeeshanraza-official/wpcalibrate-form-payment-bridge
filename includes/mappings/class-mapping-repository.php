<?php
/**
 * Repository for managing Form Payment Bridge mappings.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Mappings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mapping_Repository
 *
 * Isolated persistent storage and retrieval for form-to-payment mappings.
 */
class Mapping_Repository {

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'wpcalibrate_fpb_settings';

	/**
	 * Retrieve all stored settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings(): array {
		$defaults = [
			'version'             => WPCALIBRATE_FPB_VERSION,
			'schema_version'      => '1.0.0',
			'delete_on_uninstall' => false,
			'enable_logging'      => false,
			'license_key'         => '',
			'mappings'            => [],
		];

		$saved = get_option( self::OPTION_NAME, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}

		return array_merge( $defaults, $saved );
	}

	/**
	 * Update stored settings.
	 *
	 * @param array<string, mixed> $settings Settings array.
	 * @return bool
	 */
	public function update_settings( array $settings ): bool {
		$current = $this->get_settings();
		$updated = array_merge( $current, $settings );
		return update_option( self::OPTION_NAME, $updated );
	}

	/**
	 * Get all configured mappings.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_all(): array {
		$settings = $this->get_settings();
		return is_array( $settings['mappings'] ) ? $settings['mappings'] : [];
	}

	/**
	 * Get a single mapping by ID.
	 *
	 * @param string $id Mapping ID.
	 * @return array<string, mixed>|null
	 */
	public function get_by_id( string $id ): ?array {
		$mappings = $this->get_all();
		if ( isset( $mappings[ $id ] ) ) {
			return $mappings[ $id ];
		}

		// Fallback case-insensitive match for resilience against sanitized keys
		$lower = strtolower( $id );
		foreach ( $mappings as $key => $mapping ) {
			if ( strtolower( (string) $key ) === $lower ) {
				return $mapping;
			}
		}

		return null;
	}

	/**
	 * Find an active/enabled mapping for a given Forminator form ID.
	 *
	 * @param int $form_id Forminator form ID.
	 * @return array<string, mixed>|null
	 */
	public function get_enabled_mapping_by_form_id( int $form_id ): ?array {
		$mappings = $this->get_all();
		foreach ( $mappings as $mapping ) {
			if ( ! empty( $mapping['enabled'] ) && (int) ( $mapping['form_id'] ?? 0 ) === (int) $form_id ) {
				return $mapping;
			}
		}
		return null;
	}

	/**
	 * Save or update a mapping.
	 *
	 * @param array<string, mixed> $data Mapping attributes.
	 * @return string ID of the saved mapping.
	 */
	public function save( array $data ): string {
		$settings = $this->get_settings();
		$mappings = $settings['mappings'];

		$id = ! empty( $data['id'] ) ? sanitize_key( (string) $data['id'] ) : '';
		if ( empty( $id ) ) {
			// Generate strictly lowercase alphanumeric ID to prevent case-sensitivity mismatches
			$id = 'map_' . strtolower( wp_generate_password( 12, false, false ) );
		} else {
			// Match existing mapping key case-insensitively if present
			$lower = strtolower( $id );
			foreach ( array_keys( $mappings ) as $key ) {
				if ( strtolower( (string) $key ) === $lower ) {
					$id = (string) $key;
					break;
				}
			}
		}

		$now = current_time( 'mysql' );

		$defaults = [
			'id'                   => $id,
			'enabled'              => false,
			'name'                 => '',
			'form_id'              => 0,
			'amount_mode'          => 'fixed', // 'fixed', 'field', 'selectable'
			'fixed_amount'         => '0.00',
			'amount_field_id'      => '',
			'selectable_field_id'  => '',
			'selectable_mappings'  => [], // [ ['option_value' => 'standard', 'amount' => '25.00'], ... ]
			'order_item_name'      => '',
			'product_id'           => 0,
			'first_name_field'     => '',
			'last_name_field'      => '',
			'email_field'          => '',
			'phone_field'          => '',
			'company_field'        => '',
			'address_1_field'      => '',
			'address_2_field'      => '',
			'city_field'           => '',
			'state_field'          => '',
			'postcode_field'       => '',
			'country_field'        => '',
			'customer_note_field'  => '',
			'custom_meta_fields'   => [], // [ ['field_id' => '...', 'meta_key' => '...'], ... ]
			'created_at'           => $now,
			'updated_at'           => $now,
		];

		$existing = $mappings[ $id ] ?? [];
		$merged = array_merge( $defaults, $existing, $data );
		$merged['id'] = $id;
		$merged['updated_at'] = $now;

		// Clean and normalize attributes
		$merged['enabled']             = (bool) $merged['enabled'];
		$merged['name']                = sanitize_text_field( (string) $merged['name'] );
		$merged['form_id']             = absint( $merged['form_id'] );
		$merged['amount_mode']         = in_array( $merged['amount_mode'], [ 'fixed', 'field', 'selectable' ], true ) ? $merged['amount_mode'] : 'fixed';
		$merged['fixed_amount']        = (string) ( function_exists( 'wc_format_decimal' ) ? wc_format_decimal( $merged['fixed_amount'] ) : floatval( $merged['fixed_amount'] ) );
		$merged['amount_field_id']     = sanitize_text_field( (string) $merged['amount_field_id'] );
		$merged['selectable_field_id'] = sanitize_text_field( (string) $merged['selectable_field_id'] );
		$merged['order_item_name']     = sanitize_text_field( (string) $merged['order_item_name'] );
		$merged['product_id']          = absint( $merged['product_id'] );
		$merged['first_name_field']    = sanitize_text_field( (string) $merged['first_name_field'] );
		$merged['last_name_field']     = sanitize_text_field( (string) $merged['last_name_field'] );
		$merged['email_field']         = sanitize_text_field( (string) $merged['email_field'] );
		$merged['phone_field']         = sanitize_text_field( (string) $merged['phone_field'] );
		$merged['company_field']       = sanitize_text_field( (string) $merged['company_field'] );
		$merged['address_1_field']     = sanitize_text_field( (string) $merged['address_1_field'] );
		$merged['address_2_field']     = sanitize_text_field( (string) $merged['address_2_field'] );
		$merged['city_field']          = sanitize_text_field( (string) $merged['city_field'] );
		$merged['state_field']         = sanitize_text_field( (string) $merged['state_field'] );
		$merged['postcode_field']      = sanitize_text_field( (string) $merged['postcode_field'] );
		$merged['country_field']       = sanitize_text_field( (string) $merged['country_field'] );
		$merged['customer_note_field'] = sanitize_text_field( (string) $merged['customer_note_field'] );

		// Clean selectable mappings
		$clean_selectables = [];
		if ( ! empty( $merged['selectable_mappings'] ) && is_array( $merged['selectable_mappings'] ) ) {
			foreach ( $merged['selectable_mappings'] as $item ) {
				if ( is_array( $item ) && isset( $item['option_value'] ) && isset( $item['amount'] ) ) {
					$clean_selectables[] = [
						'option_value' => sanitize_text_field( (string) $item['option_value'] ),
						'amount'       => (string) ( function_exists( 'wc_format_decimal' ) ? wc_format_decimal( $item['amount'] ) : floatval( $item['amount'] ) ),
					];
				}
			}
		}
		$merged['selectable_mappings'] = $clean_selectables;

		// Clean custom meta fields
		$clean_meta = [];
		if ( ! empty( $merged['custom_meta_fields'] ) && is_array( $merged['custom_meta_fields'] ) ) {
			foreach ( $merged['custom_meta_fields'] as $item ) {
				if ( is_array( $item ) && ! empty( $item['field_id'] ) && ! empty( $item['meta_key'] ) ) {
					$clean_meta[] = [
						'field_id' => sanitize_text_field( (string) $item['field_id'] ),
						'meta_key' => sanitize_key( (string) $item['meta_key'] ),
					];
				}
			}
		}
		$merged['custom_meta_fields'] = $clean_meta;

		$mappings[ $id ] = $merged;
		$settings['mappings'] = $mappings;
		$this->update_settings( $settings );

		return $id;
	}

	/**
	 * Delete a mapping by ID.
	 *
	 * @param string $id Mapping ID.
	 * @return bool
	 */
	public function delete( string $id ): bool {
		$settings = $this->get_settings();
		$target_key = null;

		if ( isset( $settings['mappings'][ $id ] ) ) {
			$target_key = $id;
		} else {
			// Fallback case-insensitive search for resilience against sanitized IDs
			$lower = strtolower( $id );
			foreach ( array_keys( $settings['mappings'] ) as $key ) {
				if ( strtolower( (string) $key ) === $lower ) {
					$target_key = (string) $key;
					break;
				}
			}
		}

		if ( null !== $target_key ) {
			unset( $settings['mappings'][ $target_key ] );
			return update_option( self::OPTION_NAME, $settings );
		}

		return false;
	}
}
