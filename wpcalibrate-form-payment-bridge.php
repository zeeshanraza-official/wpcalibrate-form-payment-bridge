<?php
/**
 * Plugin Name:       WPCalibrate Form Payment Bridge
 * Plugin URI:        https://marketplace.wpcalibrate.com/
 * Description:       Connect Forminator forms to WooCommerce checkout and redirect customers seamlessly to native WooCommerce payment processing.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            WPCalibrate
 * Author URI:        https://wpcalibrate.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcalibrate-form-payment-bridge
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   11.1.2
 * GitHub Plugin URI: https://github.com/zeeshanraza-official/wpcalibrate-form-payment-bridge
 * Primary Branch:    main
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @copyright         2026 WPCalibrate
 * @license           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'WPCALIBRATE_FPB_VERSION', '1.0.0' );
define( 'WPCALIBRATE_FPB_FILE', __FILE__ );
define( 'WPCALIBRATE_FPB_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCALIBRATE_FPB_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCALIBRATE_FPB_BASENAME', plugin_basename( __FILE__ ) );

// Load dependency checker and core classes
require_once WPCALIBRATE_FPB_DIR . 'includes/class-dependencies.php';
require_once WPCALIBRATE_FPB_DIR . 'includes/class-plugin.php';
require_once WPCALIBRATE_FPB_DIR . 'includes/class-activator.php';
require_once WPCALIBRATE_FPB_DIR . 'includes/class-deactivator.php';
require_once WPCALIBRATE_FPB_DIR . 'includes/class-migrations.php';

/**
 * Declare WooCommerce HPOS (High-Performance Order Storage) compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );

/**
 * Plugin activation hook.
 */
register_activation_hook( __FILE__, [ '\WPCalibrate\FormPaymentBridge\Activator', 'activate' ] );

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, [ '\WPCalibrate\FormPaymentBridge\Deactivator', 'deactivate' ] );

/**
 * Initialize the plugin.
 */
function wpcalibrate_fpb_init(): void {
	// Run migrations if needed
	\WPCalibrate\FormPaymentBridge\Migrations::run_migrations();

	// Initialize plugin coordinator
	\WPCalibrate\FormPaymentBridge\Plugin::get_instance()->init();
}
add_action( 'plugins_loaded', 'wpcalibrate_fpb_init' );
