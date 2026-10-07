<?php
/**
 * Exception thrown when amount cannot be resolved or is invalid.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 */

namespace WPCalibrate\FormPaymentBridge\Amounts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Amount_Resolution_Exception
 */
class Amount_Resolution_Exception extends \RuntimeException {

	/**
	 * Safe customer-facing message.
	 *
	 * @var string
	 */
	protected string $user_message;

	/**
	 * Constructor.
	 *
	 * @param string          $internal_message Diagnostic message for logs.
	 * @param string          $user_message     Safe user-facing message.
	 * @param int             $code             Error code.
	 * @param \Throwable|null $previous         Previous exception.
	 */
	public function __construct(
		string $internal_message,
		string $user_message = '',
		int $code = 0,
		?\Throwable $previous = null
	) {
		parent::__construct( $internal_message, $code, $previous );
		$this->user_message = ! empty( $user_message )
			? $user_message
			: __( 'The payment amount could not be processed. Please check your submission or contact support.', 'wpcalibrate-form-payment-bridge' );
	}

	/**
	 * Get safe user-facing message.
	 *
	 * @return string
	 */
	public function get_user_message(): string {
		return $this->user_message;
	}
}
