<?php
/**
 * Admin menu coordinator for WPCalibrate Form Payment Bridge.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Menu
 *
 * Manages parent WPCalibrate menu sharing and isolated submenu registration.
 */
class Menu {

	/**
	 * Top-level menu slug shared across WPCalibrate plugins.
	 */
	const PARENT_SLUG = 'wpcalibrate';

	/**
	 * Submenu page slug for this plugin.
	 */
	const SUBMENU_SLUG = 'wpcalibrate-form-payment-bridge';

	/**
	 * Settings controller.
	 *
	 * @var Settings_Controller
	 */
	private Settings_Controller $controller;

	/**
	 * Admin hook suffix for our page.
	 *
	 * @var string
	 */
	private string $page_hook = '';

	/**
	 * Constructor.
	 *
	 * @param Settings_Controller $controller Controller instance.
	 */
	public function __construct( Settings_Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Initialize menu hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_head', [ $this, 'output_menu_icon_styles' ] );
	}

	/**
	 * Output critical CSS in admin_head to strictly contain top-level menu icon dimensions.
	 *
	 * Prevents high-resolution branding icons from expanding beyond the standard 20x20px
	 * boundary across all WordPress admin screens.
	 *
	 * @return void
	 */
	public function output_menu_icon_styles(): void {
		?>
		<style id="wpcalibrate-menu-icon-styles">
			#adminmenu .toplevel_page_wpcalibrate div.wp-menu-image {
				display: flex;
				align-items: center;
				justify-content: center;
			}
			#adminmenu .toplevel_page_wpcalibrate div.wp-menu-image img {
				max-width: 20px !important;
				max-height: 20px !important;
				width: 20px !important;
				height: 20px !important;
				padding: 0 !important;
				margin: 0 !important;
				display: inline-block !important;
				vertical-align: middle !important;
				object-fit: contain !important;
			}
			#adminmenu .toplevel_page_wpcalibrate:hover div.wp-menu-image img,
			#adminmenu .toplevel_page_wpcalibrate.wp-has-current-submenu div.wp-menu-image img {
				opacity: 1 !important;
			}
		</style>
		<?php
	}

	/**
	 * Register menu items.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		global $menu, $admin_page_hooks;

		$capability = 'manage_options';

		// Check if top-level WPCalibrate menu is already registered
		$parent_exists = false;
		if ( isset( $admin_page_hooks[ self::PARENT_SLUG ] ) ) {
			$parent_exists = true;
		} elseif ( is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[2] ) && self::PARENT_SLUG === $item[2] ) {
					$parent_exists = true;
					break;
				}
			}
		}

		// Register parent menu if not already created by another WPCalibrate plugin
		if ( ! $parent_exists ) {
			$icon_path = WPCALIBRATE_FPB_DIR . 'branding/icon-menu.png';
			$icon_url  = file_exists( $icon_path )
				? WPCALIBRATE_FPB_URL . 'branding/icon-menu.png'
				: ( file_exists( WPCALIBRATE_FPB_DIR . 'branding/icon-white.png' )
					? WPCALIBRATE_FPB_URL . 'branding/icon-white.png'
					: 'dashicons-rest-api' );

			add_menu_page(
				__( 'WPCalibrate', 'wpcalibrate-form-payment-bridge' ),
				__( 'WPCalibrate', 'wpcalibrate-form-payment-bridge' ),
				$capability,
				self::PARENT_SLUG,
				[ $this, 'render_parent_overview' ],
				$icon_url,
				58
			);
		}

		// Register isolated plugin submenu
		$this->page_hook = (string) add_submenu_page(
			self::PARENT_SLUG,
			__( 'Form Payment Bridge &lsaquo; WPCalibrate', 'wpcalibrate-form-payment-bridge' ),
			__( 'Form Payment Bridge', 'wpcalibrate-form-payment-bridge' ),
			$capability,
			self::SUBMENU_SLUG,
			[ $this->controller, 'render_page' ]
		);
	}

	/**
	 * Render default parent page if WPCalibrate top-level menu was created by this plugin.
	 *
	 * @return void
	 */
	public function render_parent_overview(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpcalibrate-form-payment-bridge' ) );
		}

		// Redirect cleanly to Form Payment Bridge settings
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SUBMENU_SLUG ) );
		exit;
	}

	/**
	 * Enqueue admin scripts and stylesheets only on this plugin's settings page.
	 *
	 * @param string $hook_suffix Current admin screen hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Strictly verify screen belongs exclusively to this plugin
		if ( $hook_suffix !== $this->page_hook && ( ! isset( $_GET['page'] ) || self::SUBMENU_SLUG !== $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Enqueue CSS
		wp_enqueue_style(
			'wpcalibrate-fpb-admin-css',
			WPCALIBRATE_FPB_URL . 'assets/css/admin.css',
			[],
			WPCALIBRATE_FPB_VERSION
		);

		// Support RTL stylesheets if active
		if ( is_rtl() ) {
			wp_enqueue_style(
				'wpcalibrate-fpb-admin-rtl',
				WPCALIBRATE_FPB_URL . 'assets/css/admin-rtl.css',
				[ 'wpcalibrate-fpb-admin-css' ],
				WPCALIBRATE_FPB_VERSION
			);
		}

		// Enqueue JS
		wp_enqueue_script(
			'wpcalibrate-fpb-admin-js',
			WPCALIBRATE_FPB_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			WPCALIBRATE_FPB_VERSION,
			true
		);

		// Localize script with AJAX endpoint, nonces, and translatable strings
		wp_localize_script(
			'wpcalibrate-fpb-admin-js',
			'wpcalibrateFpbAdmin',
			[
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'wpcalibrate_fpb_admin_nonce' ),
				'confirmDel'  => __( 'Are you sure you want to delete this payment mapping? This cannot be undone.', 'wpcalibrate-form-payment-bridge' ),
				'loadingText' => __( 'Loading form fields...', 'wpcalibrate-form-payment-bridge' ),
			]
		);
	}
}
