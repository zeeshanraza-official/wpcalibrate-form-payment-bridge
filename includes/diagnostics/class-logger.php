<?php
/**
 * Logger service for WPCalibrate Form Payment Bridge.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Logger
 *
 * Safe diagnostics logging using WooCommerce Logger with sensitive data redaction.
 */
class Logger {

	/**
	 * Log source handle.
	 */
	const SOURCE = 'wpcalibrate-form-payment-bridge';

	/**
	 * Keys to sanitize/mask in context arrays.
	 *
	 * @var array<string>
	 */
	private array $sensitive_keys = [
		'password',
		'secret',
		'license_key',
		'token',
		'card',
		'cvv',
		'ccv',
		'auth',
		'key',
	];

	/**
	 * Check if debug logging is enabled in plugin settings.
	 *
	 * @return bool
	 */
	public function is_logging_enabled(): bool {
		$settings = get_option( 'wpcalibrate_fpb_settings', [] );
		return ! empty( $settings['enable_logging'] );
	}

	/**
	 * Log an error message.
	 *
	 * @param string               $message Message.
	 * @param array<string, mixed> $context Contextual data.
	 * @return void
	 */
	public function error( string $message, array $context = [] ): void {
		$this->log( 'error', $message, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string               $message Message.
	 * @param array<string, mixed> $context Contextual data.
	 * @return void
	 */
	public function warning( string $message, array $context = [] ): void {
		$this->log( 'warning', $message, $context );
	}

	/**
	 * Log an informational message.
	 *
	 * @param string               $message Message.
	 * @param array<string, mixed> $context Contextual data.
	 * @return void
	 */
	public function info( string $message, array $context = [] ): void {
		if ( $this->is_logging_enabled() ) {
			$this->log( 'info', $message, $context );
		}
	}

	/**
	 * Internal log dispatch.
	 *
	 * @param string               $level   Severity level.
	 * @param string               $message Message.
	 * @param array<string, mixed> $context Context.
	 * @return void
	 */
	protected function log( string $level, string $message, array $context = [] ): void {
		$clean_context = $this->redact_context( $context );
		$clean_context['source'] = self::SOURCE;

		if ( function_exists( 'wc_get_logger' ) ) {
			$wc_logger = wc_get_logger();
			if ( $wc_logger ) {
				$wc_logger->log( $level, $message, $clean_context );
				return;
			}
		}

		// Fallback to PHP error_log if WC logger is not active
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Redact message just in case
			$log_entry = sprintf(
				'[%s] [%s] %s %s',
				self::SOURCE,
				strtoupper( $level ),
				$message,
				! empty( $clean_context ) ? wp_json_encode( $clean_context ) : ''
			);
			error_log( $log_entry ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Redact sensitive fields recursively.
	 *
	 * @param array<string, mixed> $context Raw context.
	 * @return array<string, mixed> Redacted context.
	 */
	public function redact_context( array $context ): array {
		$redacted = [];

		foreach ( $context as $k => $v ) {
			$key_lower = strtolower( (string) $k );
			$is_sensitive = false;

			foreach ( $this->sensitive_keys as $sens ) {
				if ( false !== strpos( $key_lower, $sens ) ) {
					$is_sensitive = true;
					break;
				}
			}

			if ( $is_sensitive ) {
				$redacted[ $k ] = '***REDACTED***';
			} elseif ( is_array( $v ) ) {
				$redacted[ $k ] = $this->redact_context( $v );
			} elseif ( is_scalar( $v ) ) {
				$redacted[ $k ] = $v;
			}
		}

		return $redacted;
	}
}
