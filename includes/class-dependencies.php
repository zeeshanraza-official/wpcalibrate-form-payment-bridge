<?php
/**
 * Dependency checker for WPCalibrate Form Payment Bridge.
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
 * Class Dependencies
 *
 * Checks system and runtime dependencies without causing fatal errors.
 */
class Dependencies {

	/**
	 * Minimum required PHP version.
	 *
	 * @var string
	 */
	const MIN_PHP_VERSION = '8.2.0';

	/**
	 * Minimum required WordPress version.
	 *
	 * @var string
	 */
	const MIN_WP_VERSION = '6.0.0';

	/**
	 * Minimum required WooCommerce version.
	 *
	 * @var string
	 */
	const MIN_WC_VERSION = '8.0.0';

	/**
	 * Minimum required Forminator version.
	 *
	 * @var string
	 */
	const MIN_FORMINATOR_VERSION = '1.20.0';

	/**
	 * Check if PHP meets minimum requirement.
	 *
	 * @return bool
	 */
	public static function is_php_compatible(): bool {
		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
	}

	/**
	 * Check if WordPress meets minimum requirement.
	 *
	 * @return bool
	 */
	public static function is_wp_compatible(): bool {
		global $wp_version;
		if ( empty( $wp_version ) ) {
			return true;
		}
		return version_compare( $wp_version, self::MIN_WP_VERSION, '>=' );
	}

	/**
	 * Check if WooCommerce is installed and active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active(): bool {
		return class_exists( '\WooCommerce' ) || function_exists( 'WC' );
	}

	/**
	 * Check if Forminator is installed and active.
	 *
	 * @return bool
	 */
	public static function is_forminator_active(): bool {
		return class_exists( '\Forminator' ) || class_exists( '\Forminator_API' );
	}

	/**
	 * Get WooCommerce version.
	 *
	 * @return string|null
	 */
	public static function get_woocommerce_version(): ?string {
		if ( defined( 'WC_VERSION' ) ) {
			return WC_VERSION;
		}
		if ( function_exists( 'WC' ) && isset( WC()->version ) ) {
			return WC()->version;
		}
		return null;
	}

	/**
	 * Get Forminator version.
	 *
	 * @return string|null
	 */
	public static function get_forminator_version(): ?string {
		if ( defined( 'FORMINATOR_VERSION' ) ) {
			return FORMINATOR_VERSION;
		}
		return null;
	}

	/**
	 * Check if all runtime dependencies are fully met.
	 *
	 * @return bool
	 */
	public static function are_dependencies_satisfied(): bool {
		return self::is_php_compatible()
			&& self::is_woocommerce_active()
			&& self::is_forminator_active();
	}

	/**
	 * Get list of unmet dependency error messages.
	 *
	 * @return array<string>
	 */
	public static function get_unmet_dependencies_messages(): array {
		$messages = [];

		if ( ! self::is_php_compatible() ) {
			$messages[] = sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				__( 'WPCalibrate Form Payment Bridge requires PHP version %1$s or higher. Your server is running PHP version %2$s.', 'wpcalibrate-form-payment-bridge' ),
				self::MIN_PHP_VERSION,
				PHP_VERSION
			);
		}

		if ( ! self::is_woocommerce_active() ) {
			$messages[] = sprintf(
				/* translators: %s: Plugin name */
				__( 'WPCalibrate Form Payment Bridge requires %s to be installed and active.', 'wpcalibrate-form-payment-bridge' ),
				'<strong>WooCommerce</strong>'
			);
		}

		if ( ! self::is_forminator_active() ) {
			$messages[] = sprintf(
				/* translators: %s: Plugin name */
				__( 'WPCalibrate Form Payment Bridge requires %s to be installed and active.', 'wpcalibrate-form-payment-bridge' ),
				'<strong>Forminator</strong>'
			);
		}

		return $messages;
	}

	/**
	 * Register admin notice when dependencies are not met.
	 *
	 * @return void
	 */
	public static function register_admin_notices(): void {
		if ( ! is_admin() ) {
			return;
		}

		$messages = self::get_unmet_dependencies_messages();
		if ( empty( $messages ) ) {
			return;
		}

		add_action( 'admin_notices', function() use ( $messages ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			?>
			<div class="notice notice-error is-dismissible">
				<p><strong><?php esc_html_e( 'WPCalibrate Form Payment Bridge Notice:', 'wpcalibrate-form-payment-bridge' ); ?></strong></p>
				<ul style="list-style-type: disc; margin-left: 20px;">
					<?php foreach ( $messages as $message ) : ?>
						<li><?php echo wp_kses_post( $message ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		} );
	}
}
