<?php
/**
 * Adapter for Forminator API interactions.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Forminator_Adapter
 *
 * Encapsulates Forminator core calls and provides normalized abstractions.
 */
class Forminator_Adapter {

	/**
	 * Check if Forminator API is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return class_exists( '\Forminator_API' );
	}

	/**
	 * Get all active Forminator forms.
	 *
	 * @param int $limit Maximum forms to return.
	 * @return array<int, array{id: int, title: string}>
	 */
	public function get_forms( int $limit = 200 ): array {
		if ( ! $this->is_available() ) {
			return [];
		}

		$forms_list = [];
		try {
			$forms = \Forminator_API::get_forms( null, 1, $limit );
			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$id = 0;
					$title = '';

					if ( is_object( $form ) ) {
						$id = (int) ( $form->id ?? 0 );
						$settings = $form->settings ?? [];
						$title = is_array( $settings ) ? ( $settings['form_name'] ?? '' ) : '';
						if ( empty( $title ) && isset( $form->name ) ) {
							$title = $form->name;
						}
					} elseif ( is_array( $form ) ) {
						$id = (int) ( $form['id'] ?? 0 );
						$title = (string) ( $form['name'] ?? '' );
					}

					if ( $id > 0 ) {
						$forms_list[] = [
							'id'    => $id,
							'title' => ! empty( $title ) ? $title : sprintf( __( 'Form #%d', 'wpcalibrate-form-payment-bridge' ), $id ),
						];
					}
				}
			}
		} catch ( \Throwable $e ) {
			// Fail gracefully
		}

		return $forms_list;
	}

	/**
	 * Get a single Forminator form object.
	 *
	 * @param int $form_id Forminator form ID.
	 * @return object|null
	 */
	public function get_form( int $form_id ): ?object {
		if ( ! $this->is_available() || $form_id <= 0 ) {
			return null;
		}

		try {
			$form = \Forminator_API::get_form( $form_id );
			return is_object( $form ) ? $form : null;
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Get fields of a Forminator form normalized for mapping UI.
	 *
	 * @param int $form_id Forminator form ID.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_form_fields( int $form_id ): array {
		if ( ! $this->is_available() || $form_id <= 0 ) {
			return [];
		}

		$normalized = [];

		try {
			$fields = [];
			if ( method_exists( '\Forminator_API', 'get_form_fields' ) ) {
				$fields = \Forminator_API::get_form_fields( $form_id );
			}

			if ( empty( $fields ) ) {
				$form = $this->get_form( $form_id );
				if ( $form ) {
					if ( method_exists( $form, 'get_fields' ) ) {
						$fields = $form->get_fields();
					} elseif ( isset( $form->fields ) && is_array( $form->fields ) ) {
						$fields = $form->fields;
					}
				}
			}

			if ( is_array( $fields ) ) {
				foreach ( $fields as $field ) {
					$id = '';
					$type = '';
					$label = '';
					$options = [];

					if ( is_object( $field ) ) {
						$id = (string) ( $field->slug ?? ( $field->element_id ?? '' ) );
						$type = (string) ( $field->type ?? '' );
						$raw = $field->raw ?? [];
						$label = is_array( $raw ) ? ( $raw['field_label'] ?? ( $raw['title'] ?? '' ) ) : '';
						if ( empty( $label ) && isset( $field->title ) ) {
							$label = (string) $field->title;
						}
						if ( ! empty( $field->options ) && is_array( $field->options ) ) {
							foreach ( $field->options as $opt ) {
								$opt_label = is_array( $opt ) ? ( $opt['label'] ?? '' ) : ( $opt->label ?? '' );
								$opt_value = is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( $opt->value ?? '' );
								$options[] = [
									'label' => (string) $opt_label,
									'value' => (string) $opt_value,
								];
							}
						}
					} elseif ( is_array( $field ) ) {
						$id = (string) ( $field['element_id'] ?? ( $field['slug'] ?? '' ) );
						$type = (string) ( $field['type'] ?? '' );
						$label = (string) ( $field['field_label'] ?? ( $field['title'] ?? '' ) );
						if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
							foreach ( $field['options'] as $opt ) {
								$options[] = [
									'label' => (string) ( is_array( $opt ) ? ( $opt['label'] ?? '' ) : ( $opt->label ?? '' ) ),
									'value' => (string) ( is_array( $opt ) ? ( $opt['value'] ?? '' ) : ( $opt->value ?? '' ) ),
								];
							}
						}
					}

					if ( ! empty( $id ) ) {
						$normalized[] = [
							'id'      => $id,
							'type'    => $type,
							'label'   => ! empty( $label ) ? $label : $id,
							'options' => $options,
						];
					}
				}
			}
		} catch ( \Throwable $e ) {
			// Fail gracefully
		}

		return $normalized;
	}

	/**
	 * Get a submission entry by form ID and entry ID.
	 *
	 * @param int $form_id  Form ID.
	 * @param int $entry_id Entry ID.
	 * @return object|null
	 */
	public function get_entry( int $form_id, int $entry_id ): ?object {
		if ( ! $this->is_available() || $form_id <= 0 || $entry_id <= 0 ) {
			return null;
		}

		try {
			$entry = \Forminator_API::get_entry( $form_id, $entry_id );
			return is_object( $entry ) ? $entry : null;
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Extract a field's submitted value from a Forminator entry.
	 *
	 * @param object|null $entry    Forminator entry object.
	 * @param string      $field_id Field ID slug (e.g. text-1, calculation-1).
	 * @return mixed
	 */
	public function get_entry_field_value( ?object $entry, string $field_id ) {
		if ( ! $entry || empty( $field_id ) ) {
			return null;
		}

		// Try entry->get_meta() method
		if ( method_exists( $entry, 'get_meta' ) ) {
			$val = $entry->get_meta( $field_id, null );
			if ( null !== $val ) {
				return $val;
			}
		}

		// Try direct meta_data array property
		if ( isset( $entry->meta_data ) && is_array( $entry->meta_data ) ) {
			if ( isset( $entry->meta_data[ $field_id ] ) ) {
				$field_meta = $entry->meta_data[ $field_id ];
				if ( is_array( $field_meta ) && array_key_exists( 'value', $field_meta ) ) {
					return $field_meta['value'];
				}
				return $field_meta;
			}
		}

		return null;
	}

	/**
	 * Build a safe admin entry link.
	 *
	 * @param int $form_id  Form ID.
	 * @param int $entry_id Entry ID.
	 * @return string Safe admin URL.
	 */
	public function get_entry_admin_url( int $form_id, int $entry_id ): string {
		$form_id = absint( $form_id );
		$entry_id = absint( $entry_id );
		if ( $form_id <= 0 || $entry_id <= 0 ) {
			return '';
		}

		return admin_url(
			sprintf(
				'admin.php?page=forminator-entries&form_type=forminator_forms&form_id=%d&entry_id=%d',
				$form_id,
				$entry_id
			)
		);
	}
}
