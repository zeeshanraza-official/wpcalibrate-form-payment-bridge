<?php
/**
 * Settings controller handling admin routing, actions, and views.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\FormPaymentBridge\Forms\Forminator_Adapter;
use WPCalibrate\FormPaymentBridge\Mappings\Mapping_Repository;
use WPCalibrate\FormPaymentBridge\Mappings\Mapping_Validator;
use WPCalibrate\FormPaymentBridge\Diagnostics\Logger;
use WPCalibrate\FormPaymentBridge\Diagnostics\Health_Check;
use WPCalibrate\FormPaymentBridge\License\License_Provider_Interface;

/**
 * Class Settings_Controller
 */
class Settings_Controller {

	/**
	 * Mapping repository.
	 *
	 * @var Mapping_Repository
	 */
	private Mapping_Repository $repository;

	/**
	 * Logger service.
	 *
	 * @var Logger
	 */
	private Logger $logger;

	/**
	 * License provider.
	 *
	 * @var License_Provider_Interface
	 */
	private License_Provider_Interface $license_provider;

	/**
	 * Forminator adapter.
	 *
	 * @var Forminator_Adapter
	 */
	private Forminator_Adapter $forminator_adapter;

	/**
	 * Mapping validator.
	 *
	 * @var Mapping_Validator
	 */
	private Mapping_Validator $validator;

	/**
	 * Flash notices.
	 *
	 * @var array<array{type: string, message: string}>
	 */
	private array $notices = [];

	/**
	 * Validation errors during save.
	 *
	 * @var array<string, string>
	 */
	private array $form_errors = [];

	/**
	 * Constructor.
	 *
	 * @param Mapping_Repository          $repository       Mapping repo.
	 * @param Logger                      $logger           Logger service.
	 * @param License_Provider_Interface  $license_provider License provider.
	 */
	public function __construct(
		Mapping_Repository $repository,
		Logger $logger,
		License_Provider_Interface $license_provider
	) {
		$this->repository         = $repository;
		$this->logger             = $logger;
		$this->license_provider   = $license_provider;
		$this->forminator_adapter = new Forminator_Adapter();
		$this->validator          = new Mapping_Validator( $this->forminator_adapter );

		$this->register_ajax_handlers();
		add_action( 'admin_init', [ $this, 'handle_admin_actions' ] );
	}

	/**
	 * Register AJAX endpoints.
	 *
	 * @return void
	 */
	private function register_ajax_handlers(): void {
		add_action( 'wp_ajax_wpcalibrate_fpb_get_form_fields', [ $this, 'ajax_get_form_fields' ] );
	}

	/**
	 * AJAX endpoint to load fields for a chosen Forminator form.
	 *
	 * @return void
	 */
	public function ajax_get_form_fields(): void {
		check_ajax_referer( 'wpcalibrate_fpb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'wpcalibrate-form-payment-bridge' ) ], 403 );
		}

		$form_id = absint( $_GET['form_id'] ?? 0 );
		if ( $form_id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Form ID.', 'wpcalibrate-form-payment-bridge' ) ] );
		}

		$fields = $this->forminator_adapter->get_form_fields( $form_id );
		wp_send_json_success( [ 'fields' => $fields ] );
	}

	/**
	 * Handle admin GET and POST actions with capability and nonce verification.
	 *
	 * @return void
	 */
	public function handle_admin_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle GET actions (e.g. action=delete&id=map_xxx&_wpnonce=...)
		if ( 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$page   = sanitize_key( $_GET['page'] ?? '' );
			$action = sanitize_key( $_GET['action'] ?? '' );

			if ( Menu::SUBMENU_SLUG === $page && 'delete' === $action ) {
				$this->process_get_delete_mapping();
			}
			return;
		}

		// Handle POST form actions
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$action = sanitize_key( $_POST['wpcalibrate_fpb_action'] ?? '' );
			if ( empty( $action ) ) {
				return;
			}

			switch ( $action ) {
				case 'save_mapping':
					$this->process_save_mapping();
					break;

				case 'delete_mapping':
					$this->process_delete_mapping();
					break;

				case 'toggle_mapping':
					$this->process_toggle_mapping();
					break;

				case 'save_diagnostics':
					$this->process_save_diagnostics();
					break;

				case 'save_license':
					$this->process_save_license();
					break;
			}
		}
	}

	/**
	 * Process saving a payment mapping.
	 *
	 * @return void
	 */
	private function process_save_mapping(): void {
		check_admin_referer( 'wpcalibrate_fpb_save_mapping_nonce', 'wpcalibrate_fpb_nonce' );

		$raw_data = $_POST['mapping'] ?? [];
		if ( ! is_array( $raw_data ) ) {
			return;
		}

		// Validate mapping
		$errors = $this->validator->validate( $raw_data );
		if ( ! empty( $errors ) ) {
			$this->form_errors = $errors;
			$this->notices[] = [
				'type'    => 'error',
				'message' => __( 'Please correct the highlighted errors before saving.', 'wpcalibrate-form-payment-bridge' ),
			];
			return;
		}

		$saved_id = $this->repository->save( $raw_data );

		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=mappings&saved=1&id=' . urlencode( $saved_id ) )
		);
		exit;
	}

	/**
	 * Process deleting a payment mapping via POST form submission.
	 *
	 * @return void
	 */
	private function process_delete_mapping(): void {
		check_admin_referer( 'wpcalibrate_fpb_delete_mapping_nonce', 'wpcalibrate_fpb_nonce' );

		$id = sanitize_key( (string) ( $_POST['mapping_id'] ?? '' ) );
		$deleted = false;
		if ( ! empty( $id ) ) {
			$deleted = $this->repository->delete( $id );
		}

		$flag = $deleted ? 'deleted=1' : 'delete_failed=1';
		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=mappings&' . $flag )
		);
		exit;
	}

	/**
	 * Process deleting a payment mapping via GET link with nonce.
	 *
	 * @return void
	 */
	private function process_get_delete_mapping(): void {
		$id = sanitize_key( (string) ( $_GET['id'] ?? '' ) );
		check_admin_referer( 'wpcalibrate_fpb_delete_' . $id );

		$deleted = false;
		if ( ! empty( $id ) ) {
			$deleted = $this->repository->delete( $id );
		}

		$flag = $deleted ? 'deleted=1' : 'delete_failed=1';
		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=mappings&' . $flag )
		);
		exit;
	}

	/**
	 * Process toggling mapping enabled state.
	 *
	 * @return void
	 */
	private function process_toggle_mapping(): void {
		check_admin_referer( 'wpcalibrate_fpb_toggle_mapping_nonce', 'wpcalibrate_fpb_nonce' );

		$id = sanitize_key( $_POST['mapping_id'] ?? '' );
		$mapping = $this->repository->get_by_id( $id );
		if ( $mapping ) {
			$mapping['enabled'] = empty( $mapping['enabled'] );
			$this->repository->save( $mapping );
		}

		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=mappings&toggled=1' )
		);
		exit;
	}

	/**
	 * Process saving diagnostic options.
	 *
	 * @return void
	 */
	private function process_save_diagnostics(): void {
		check_admin_referer( 'wpcalibrate_fpb_diagnostics_nonce', 'wpcalibrate_fpb_nonce' );

		$enable_logging = ! empty( $_POST['enable_logging'] );
		$delete_on_uninstall = ! empty( $_POST['delete_on_uninstall'] );

		$settings = $this->repository->get_settings();
		$settings['enable_logging'] = $enable_logging;
		$settings['delete_on_uninstall'] = $delete_on_uninstall;
		$this->repository->update_settings( $settings );

		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=diagnostics&saved=1' )
		);
		exit;
	}

	/**
	 * Process saving license key.
	 *
	 * @return void
	 */
	private function process_save_license(): void {
		check_admin_referer( 'wpcalibrate_fpb_license_nonce', 'wpcalibrate_fpb_nonce' );

		$key = sanitize_text_field( $_POST['license_key'] ?? '' );
		if ( ! empty( $key ) ) {
			$this->license_provider->save_key( $key );
		}

		// Attempt activation (will cleanly report unconfigured status)
		$result = $this->license_provider->activate();

		$param = $result['success'] ? 'activated=1' : 'license_status=blocked';
		wp_safe_redirect(
			admin_url( 'admin.php?page=' . Menu::SUBMENU_SLUG . '&tab=license&' . $param )
		);
		exit;
	}

	/**
	 * Render the admin settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpcalibrate-form-payment-bridge' ) );
		}

		// Collect query params
		$current_tab = sanitize_key( $_GET['tab'] ?? 'overview' );
		$valid_tabs  = [ 'overview', 'mappings', 'diagnostics', 'license', 'support' ];
		if ( ! in_array( $current_tab, $valid_tabs, true ) ) {
			$current_tab = 'overview';
		}

		$action = sanitize_key( $_GET['action'] ?? '' );
		$mapping_id = sanitize_key( $_GET['id'] ?? '' );

		// Load flash notices from GET flags
		if ( ! empty( $_GET['saved'] ) ) {
			$this->notices[] = [
				'type'    => 'success',
				'message' => __( 'Settings saved successfully.', 'wpcalibrate-form-payment-bridge' ),
			];
		}
		if ( ! empty( $_GET['deleted'] ) ) {
			$this->notices[] = [
				'type'    => 'success',
				'message' => __( 'Mapping deleted successfully.', 'wpcalibrate-form-payment-bridge' ),
			];
		}
		if ( ! empty( $_GET['delete_failed'] ) ) {
			$this->notices[] = [
				'type'    => 'error',
				'message' => __( 'Could not find or delete the specified mapping.', 'wpcalibrate-form-payment-bridge' ),
			];
		}
		if ( ! empty( $_GET['toggled'] ) ) {
			$this->notices[] = [
				'type'    => 'success',
				'message' => __( 'Mapping status updated.', 'wpcalibrate-form-payment-bridge' ),
			];
		}
		if ( isset( $_GET['license_status'] ) && 'blocked' === $_GET['license_status'] ) {
			$this->notices[] = [
				'type'    => 'info',
				'message' => __( 'License saved. Remote license integration is currently BLOCKED awaiting WPCalibrate provider API details. Core plugin features are fully active.', 'wpcalibrate-form-payment-bridge' ),
			];
		}

		$template_data = [
			'current_tab'         => $current_tab,
			'action'              => $action,
			'mapping_id'          => $mapping_id,
			'notices'             => $this->notices,
			'form_errors'         => $this->form_errors,
			'repository'          => $this->repository,
			'forminator_adapter'  => $this->forminator_adapter,
			'validator'           => $this->validator,
			'license_provider'    => $this->license_provider,
			'health_checker'      => new Health_Check(),
		];

		// Render main shell template
		require WPCALIBRATE_FPB_DIR . 'templates/admin-page.php';
	}
}
