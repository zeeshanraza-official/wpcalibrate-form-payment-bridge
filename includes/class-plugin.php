<?php
/**
 * Main Plugin class for WPCalibrate Form Payment Bridge.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 *
 * Core coordinator and service container.
 */
class Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Settings repository.
	 *
	 * @var Mappings\Mapping_Repository|null
	 */
	private ?Mappings\Mapping_Repository $mapping_repository = null;

	/**
	 * Logger service.
	 *
	 * @var Diagnostics\Logger|null
	 */
	private ?Diagnostics\Logger $logger = null;

	/**
	 * Submission processor service.
	 *
	 * @var Forms\Submission_Processor|null
	 */
	private ?Forms\Submission_Processor $submission_processor = null;

	/**
	 * Admin menu coordinator.
	 *
	 * @var Admin\Menu|null
	 */
	private ?Admin\Menu $admin_menu = null;

	/**
	 * Settings controller.
	 *
	 * @var Admin\Settings_Controller|null
	 */
	private ?Admin\Settings_Controller $settings_controller = null;

	/**
	 * License provider.
	 *
	 * @var License\License_Provider_Interface|null
	 */
	private ?License\License_Provider_Interface $license_provider = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_autoloader();
	}

	/**
	 * Register PSR-4 compatible class autoloader for plugin namespace.
	 *
	 * @return void
	 */
	private function setup_autoloader(): void {
		spl_autoload_register( [ $this, 'autoload' ] );
	}

	/**
	 * Autoload handler.
	 *
	 * @param string $class Class name.
	 * @return void
	 */
	public function autoload( string $class ): void {
		$prefix = 'WPCalibrate\\FormPaymentBridge\\';
		$base_dir = WPCALIBRATE_FPB_DIR . 'includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$parts = explode( '\\', $relative_class );
		$class_name = array_pop( $parts );

		// Subdirectory path
		$sub_path = '';
		if ( ! empty( $parts ) ) {
			$sub_path = strtolower( implode( '/', $parts ) ) . '/';
		}

		// Convert ClassName or Class_Name to kebab-case
		$file_slug = strtolower( str_replace( '_', '-', preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class_name ) ) );
		// Normalize any double dashes
		$file_slug = preg_replace( '/-+/', '-', $file_slug );
		$stripped_slug = preg_replace( '/-interface$/', '', $file_slug );

		// Candidate files: class-*, interface-*, or trait-*
		$candidates = [
			$base_dir . $sub_path . 'class-' . $file_slug . '.php',
			$base_dir . $sub_path . 'interface-' . $file_slug . '.php',
			$base_dir . $sub_path . 'interface-' . $stripped_slug . '.php',
			$base_dir . $sub_path . 'trait-' . $file_slug . '.php',
		];

		foreach ( $candidates as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
	}

	/**
	 * Initialize plugin hooks and components.
	 *
	 * @return void
	 */
	public function init(): void {
		// Load plugin text domain
		add_action( 'init', [ $this, 'load_textdomain' ] );

		// Check runtime dependencies
		if ( ! Dependencies::are_dependencies_satisfied() ) {
			Dependencies::register_admin_notices();
			// We still allow admin menus to register so settings are not broken/inaccessible
			if ( is_admin() ) {
				$this->init_admin();
			}
			return;
		}

		// Initialize core services
		$this->init_services();

		// Initialize admin if in admin context
		if ( is_admin() ) {
			$this->init_admin();
		}

		// Initialize Forminator integration hooks
		$this->init_form_integration();
	}

	/**
	 * Load plugin textdomain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'wpcalibrate-form-payment-bridge',
			false,
			dirname( plugin_basename( WPCALIBRATE_FPB_FILE ) ) . '/languages'
		);
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	private function init_services(): void {
		$this->logger = new Diagnostics\Logger();
		$this->mapping_repository = new Mappings\Mapping_Repository();
		$this->license_provider = new License\Unconfigured_License_Provider();
		$this->submission_processor = new Forms\Submission_Processor(
			$this->mapping_repository,
			$this->logger
		);
	}

	/**
	 * Initialize administration hooks and pages.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		if ( null === $this->mapping_repository ) {
			$this->mapping_repository = new Mappings\Mapping_Repository();
		}
		if ( null === $this->logger ) {
			$this->logger = new Diagnostics\Logger();
		}
		if ( null === $this->license_provider ) {
			$this->license_provider = new License\Unconfigured_License_Provider();
		}

		$this->settings_controller = new Admin\Settings_Controller(
			$this->mapping_repository,
			$this->logger,
			$this->license_provider
		);

		$this->admin_menu = new Admin\Menu( $this->settings_controller );
		$this->admin_menu->init();

		// Add settings link to Plugins list
		add_filter( 'plugin_action_links_' . WPCALIBRATE_FPB_BASENAME, [ $this, 'add_plugin_action_links' ] );
	}

	/**
	 * Initialize Forminator submission integration hooks.
	 *
	 * @return void
	 */
	private function init_form_integration(): void {
		if ( $this->submission_processor ) {
			$this->submission_processor->register_hooks();
		}
	}

	/**
	 * Add custom links on the WordPress plugins list page.
	 *
	 * @param array<string, string> $links Existing action links.
	 * @return array<string, string> Modified links.
	 */
	public function add_plugin_action_links( array $links ): array {
		$settings_url = admin_url( 'admin.php?page=wpcalibrate-form-payment-bridge' );
		$custom_links = [
			'settings' => sprintf( '<a href="%s">%s</a>', esc_url( $settings_url ), esc_html__( 'Settings', 'wpcalibrate-form-payment-bridge' ) ),
			'support'  => sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( 'https://wpcalibrate.com' ), esc_html__( 'Support', 'wpcalibrate-form-payment-bridge' ) ),
		];
		return array_merge( $custom_links, $links );
	}

	/**
	 * Get mapping repository.
	 *
	 * @return Mappings\Mapping_Repository|null
	 */
	public function get_mapping_repository(): ?Mappings\Mapping_Repository {
		return $this->mapping_repository;
	}

	/**
	 * Get logger.
	 *
	 * @return Diagnostics\Logger|null
	 */
	public function get_logger(): ?Diagnostics\Logger {
		return $this->logger;
	}

	/**
	 * Get submission processor.
	 *
	 * @return Forms\Submission_Processor|null
	 */
	public function get_submission_processor(): ?Forms\Submission_Processor {
		return $this->submission_processor;
	}

	/**
	 * Get license provider.
	 *
	 * @return License\License_Provider_Interface|null
	 */
	public function get_license_provider(): ?License\License_Provider_Interface {
		return $this->license_provider;
	}
}
