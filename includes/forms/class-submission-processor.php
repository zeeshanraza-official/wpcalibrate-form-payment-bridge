<?php
/**
 * Submission processor orchestrating Forminator events and WooCommerce order creation.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Amounts\Amount_Resolver_Factory;
use WPCalibrate\FormPaymentBridge\Amounts\Amount_Resolution_Exception;
use WPCalibrate\FormPaymentBridge\Diagnostics\Logger;
use WPCalibrate\FormPaymentBridge\Mappings\Mapping_Repository;
use WPCalibrate\FormPaymentBridge\WooCommerce\Order_Linkage;
use WPCalibrate\FormPaymentBridge\WooCommerce\Order_Service;
use WPCalibrate\FormPaymentBridge\WooCommerce\Payment_Redirect;

/**
 * Class Submission_Processor
 *
 * Centralizes AJAX and page-reload Forminator submission handling.
 */
class Submission_Processor {

	/**
	 * Mapping repository.
	 *
	 * @var Mapping_Repository
	 */
	private Mapping_Repository $mapping_repository;

	/**
	 * Logger service.
	 *
	 * @var Logger
	 */
	private Logger $logger;

	/**
	 * Forminator adapter.
	 *
	 * @var Forminator_Adapter
	 */
	private Forminator_Adapter $adapter;

	/**
	 * Order linkage service.
	 *
	 * @var Order_Linkage
	 */
	private Order_Linkage $linkage;

	/**
	 * Order creation service.
	 *
	 * @var Order_Service
	 */
	private Order_Service $order_service;

	/**
	 * Payment redirect helper.
	 *
	 * @var Payment_Redirect
	 */
	private Payment_Redirect $redirect_service;

	/**
	 * Request-scoped storage for generated payment URLs indexed by entry ID.
	 *
	 * @var array<int, string>
	 */
	private array $resolved_payment_urls = [];

	/**
	 * Request-scoped storage for errors encountered during submission processing.
	 *
	 * @var array<int, string>
	 */
	private array $submission_errors = [];

	/**
	 * Constructor.
	 *
	 * @param Mapping_Repository|null $mapping_repository Mapping repo.
	 * @param Logger|null             $logger             Logger.
	 * @param Forminator_Adapter|null $adapter            Forminator adapter.
	 * @param Order_Linkage|null      $linkage            Linkage service.
	 * @param Order_Service|null      $order_service      Order service.
	 * @param Payment_Redirect|null   $redirect_service   Redirect helper.
	 */
	public function __construct(
		?Mapping_Repository $mapping_repository = null,
		?Logger $logger = null,
		?Forminator_Adapter $adapter = null,
		?Order_Linkage $linkage = null,
		?Order_Service $order_service = null,
		?Payment_Redirect $redirect_service = null
	) {
		$this->mapping_repository = $mapping_repository ?? new Mapping_Repository();
		$this->logger             = $logger ?? new Logger();
		$this->adapter            = $adapter ?? new Forminator_Adapter();
		$this->linkage            = $linkage ?? new Order_Linkage();
		$this->order_service      = $order_service ?? new Order_Service( $this->linkage );
		$this->redirect_service   = $redirect_service ?? new Payment_Redirect();
	}

	/**
	 * Register Forminator action and filter hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// AJAX form submission after save
		add_action( 'forminator_form_after_save_entry', [ $this, 'handle_form_submission' ], 10, 2 );

		// Page reload form submission after handle
		add_action( 'forminator_form_after_handle_submit', [ $this, 'handle_form_submission' ], 10, 2 );

		// AJAX response filter to inject redirect URL
		add_filter( 'forminator_form_ajax_submit_response', [ $this, 'filter_ajax_response' ], 20, 2 );

		// Non-AJAX response filter
		add_filter( 'forminator_form_submit_response', [ $this, 'filter_submit_response' ], 20, 2 );

		// Hook WooCommerce admin order view data
		Order_Service::register_admin_order_meta();
	}

	/**
	 * Centralized handler for both AJAX and Page-Reload submissions.
	 *
	 * @param int                  $form_id  Forminator form ID.
	 * @param array<string, mixed> $response Forminator submission response array.
	 * @return void
	 */
	public function handle_form_submission( $form_id, $response ): void {
		$form_id = absint( $form_id );
		if ( $form_id <= 0 || ! is_array( $response ) ) {
			return;
		}

		// Only process if Forminator marked submission as successful
		if ( empty( $response['success'] ) ) {
			return;
		}

		// Verify form is mapped and enabled in plugin
		$mapping = $this->mapping_repository->get_enabled_mapping_by_form_id( $form_id );
		if ( ! $mapping ) {
			return; // Not a payment-enabled form
		}

		$entry_id = absint( $response['entry_id'] ?? 0 );
		if ( $entry_id <= 0 ) {
			$this->logger->error(
				sprintf( 'Submission for mapped form #%d succeeded but no valid entry_id was returned', $form_id ),
				[ 'form_id' => $form_id ]
			);
			return;
		}

		$idempotency_key = $this->linkage->generate_key( $form_id, $entry_id );

		// Check for existing linked WooCommerce order (Idempotency)
		$existing_order = $this->linkage->get_existing_order( $idempotency_key );
		if ( $existing_order ) {
			try {
				$payment_url = $this->redirect_service->get_payment_url( $existing_order );
				$this->resolved_payment_urls[ $entry_id ] = $payment_url;
				$this->logger->info(
					sprintf( 'Reusing existing WooCommerce order #%d for entry #%d', $existing_order->get_id(), $entry_id ),
					[ 'order_id' => $existing_order->get_id(), 'entry_id' => $entry_id ]
				);
				return;
			} catch ( \Throwable $e ) {
				$this->logger->error(
					sprintf( 'Failed to retrieve payment URL from existing order #%d: %s', $existing_order->get_id(), $e->getMessage() ),
					[ 'order_id' => $existing_order->get_id() ]
				);
			}
		}

		// Acquire atomic lock to prevent concurrent double-clicks or repeated webhooks
		if ( ! $this->linkage->acquire_lock( $idempotency_key ) ) {
			// Lock is active: wait momentarily or reuse order if created
			usleep( 250000 ); // 250ms
			$retry_order = $this->linkage->get_existing_order( $idempotency_key );
			if ( $retry_order ) {
				$this->resolved_payment_urls[ $entry_id ] = $this->redirect_service->get_payment_url( $retry_order );
			}
			return;
		}

		try {
			// Retrieve Forminator entry model
			$entry = $this->adapter->get_entry( $form_id, $entry_id );

			// Resolve payment amount server-side
			$resolver = Amount_Resolver_Factory::create( $mapping );
			$amount   = $resolver->resolve( $mapping, $entry, $this->adapter );

			// Create unpaid WooCommerce order
			$order = $this->order_service->create_payment_order(
				$mapping,
				$amount,
				$entry,
				$this->adapter,
				$idempotency_key,
				$form_id,
				$entry_id
			);

			// Generate checkout payment URL
			$payment_url = $this->redirect_service->get_payment_url( $order );
			$this->resolved_payment_urls[ $entry_id ] = $payment_url;

			$this->logger->info(
				sprintf( 'Successfully created WooCommerce order #%d for Forminator entry #%d', $order->get_id(), $entry_id ),
				[
					'order_id'   => $order->get_id(),
					'form_id'    => $form_id,
					'entry_id'   => $entry_id,
					'amount'     => $amount,
					'mapping_id' => $mapping['id'],
				]
			);
		} catch ( Amount_Resolution_Exception $e ) {
			$this->logger->error(
				sprintf( 'Amount resolution failed for form #%d, entry #%d: %s', $form_id, $entry_id, $e->getMessage() ),
				[ 'form_id' => $form_id, 'entry_id' => $entry_id ]
			);
			$this->submission_errors[ $entry_id ] = $e->get_user_message();
		} catch ( \Throwable $e ) {
			$this->logger->error(
				sprintf( 'Order creation failed for form #%d, entry #%d: %s', $form_id, $entry_id, $e->getMessage() ),
				[ 'form_id' => $form_id, 'entry_id' => $entry_id ]
			);
			$this->submission_errors[ $entry_id ] = __( 'An error occurred while creating your order. Please try again or contact support.', 'wpcalibrate-form-payment-bridge' );
		} finally {
			$this->linkage->release_lock( $idempotency_key );
		}
	}

	/**
	 * Filter Forminator AJAX submit response.
	 *
	 * @param array<string, mixed> $response Response array.
	 * @param int                  $form_id  Form ID.
	 * @return array<string, mixed>
	 */
	public function filter_ajax_response( $response, $form_id ): array {
		if ( ! is_array( $response ) ) {
			return $response;
		}

		$entry_id = absint( $response['entry_id'] ?? 0 );

		// Check if we encountered an error during order processing
		if ( $entry_id > 0 && isset( $this->submission_errors[ $entry_id ] ) ) {
			$response['success'] = false;
			$response['message'] = esc_html( $this->submission_errors[ $entry_id ] );
			return $response;
		}

		// Check for payment redirect URL
		if ( $entry_id > 0 && isset( $this->resolved_payment_urls[ $entry_id ] ) ) {
			return $this->redirect_service->apply_ajax_redirect( $response, $this->resolved_payment_urls[ $entry_id ] );
		}

		// Fallback: check if single resolved URL in this request
		if ( count( $this->resolved_payment_urls ) === 1 ) {
			$single_url = reset( $this->resolved_payment_urls );
			return $this->redirect_service->apply_ajax_redirect( $response, $single_url );
		}

		return $response;
	}

	/**
	 * Filter Forminator non-AJAX submit response.
	 *
	 * @param array<string, mixed> $response Response array.
	 * @param int                  $form_id  Form ID.
	 * @return array<string, mixed>
	 */
	public function filter_submit_response( $response, $form_id ): array {
		if ( ! is_array( $response ) ) {
			return $response;
		}

		$entry_id = absint( $response['entry_id'] ?? 0 );

		if ( $entry_id > 0 && isset( $this->resolved_payment_urls[ $entry_id ] ) ) {
			$response['url'] = esc_url_raw( $this->resolved_payment_urls[ $entry_id ] );
			$response['redirect'] = true;
			$response['behav'] = 'redirect';
		} elseif ( count( $this->resolved_payment_urls ) === 1 ) {
			$single_url = reset( $this->resolved_payment_urls );
			$response['url'] = esc_url_raw( $single_url );
			$response['redirect'] = true;
			$response['behav'] = 'redirect';
		}

		return $response;
	}

	/**
	 * Direct processing method for programmatic or test invocation.
	 *
	 * @param int   $form_id  Form ID.
	 * @param int   $entry_id Entry ID.
	 * @param array $response Optional response array.
	 * @return string|null Generated payment URL or null on failure.
	 */
	public function process_submission( int $form_id, int $entry_id, array $response = [] ): ?string {
		if ( empty( $response ) ) {
			$response = [
				'success'  => true,
				'entry_id' => $entry_id,
			];
		}
		$this->handle_form_submission( $form_id, $response );
		return $this->resolved_payment_urls[ $entry_id ] ?? null;
	}
}
