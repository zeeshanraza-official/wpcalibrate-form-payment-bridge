<?php
/**
 * Overview tab template.
 *
 * Provides a comprehensive guide covering the plugin's purpose, method, architecture, and step-by-step usage.
 *
 * @package           WPCalibrate\FormPaymentBridge
 * @author            WPCalibrate <support@wpcalibrate.com>
 * @license           GPL-2.0-or-later
 *
 * @var array<string, mixed> $template_data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$health       = $template_data['health_checker']->get_system_health( $template_data['repository'] );
$mappings     = $template_data['repository']->get_all();
$active_count = 0;
foreach ( $mappings as $m ) {
	if ( ! empty( $m['enabled'] ) ) {
		$active_count++;
	}
}

$add_mapping_url = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings&action=add' );
$mappings_url    = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' );
$diagnostics_url = admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=diagnostics' );
?>

<div class="wpcalibrate-fpb-tab-overview">

	<!-- Hero Banner -->
	<section class="wpcalibrate-fpb-hero" aria-labelledby="hero-title">
		<div class="wpcalibrate-fpb-hero__content">
			<span class="wpcalibrate-fpb-hero__badge"><?php esc_html_e( 'Official Documentation & Guide', 'wpcalibrate-form-payment-bridge' ); ?></span>
			<h2 id="hero-title" class="wpcalibrate-fpb-hero__title"><?php esc_html_e( 'Connect Forminator Forms to Native WooCommerce Checkout', 'wpcalibrate-form-payment-bridge' ); ?></h2>
			<p class="wpcalibrate-fpb-hero__desc">
				<?php esc_html_e( 'WPCalibrate Form Payment Bridge bridges custom Forminator forms directly to WooCommerce order processing. Allow visitors to submit your forms, automatically generate isolated pending orders, and securely redirect them to WooCommerce\'s native Pay for Order page.', 'wpcalibrate-form-payment-bridge' ); ?>
			</p>
			<div class="wpcalibrate-fpb-hero__actions">
				<a href="<?php echo esc_url( $add_mapping_url ); ?>" class="button button-primary button-hero">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle; margin-top: -2px;"></span>
					<?php esc_html_e( 'Add Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ); ?>
				</a>
				<a href="<?php echo esc_url( $mappings_url ); ?>" class="button button-secondary button-hero">
					<?php esc_html_e( 'View Existing Mappings', 'wpcalibrate-form-payment-bridge' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Status Metrics Cards Grid -->
	<section class="wpcalibrate-fpb-cards-grid" aria-label="<?php esc_attr_e( 'System Status Overview', 'wpcalibrate-form-payment-bridge' ); ?>">
		<div class="wpcalibrate-fpb-card">
			<div class="wpcalibrate-fpb-card__icon dashicons dashicons-forms"></div>
			<div class="wpcalibrate-fpb-card__body">
				<h3 class="wpcalibrate-fpb-card__title"><?php esc_html_e( 'Active Mappings', 'wpcalibrate-form-payment-bridge' ); ?></h3>
				<p class="wpcalibrate-fpb-card__value"><?php echo esc_html( $active_count ); ?> <span class="wpcalibrate-fpb-card__sub">/ <?php echo esc_html( count( $mappings ) ); ?> <?php esc_html_e( 'total', 'wpcalibrate-form-payment-bridge' ); ?></span></p>
			</div>
		</div>

		<div class="wpcalibrate-fpb-card">
			<div class="wpcalibrate-fpb-card__icon dashicons dashicons-cart"></div>
			<div class="wpcalibrate-fpb-card__body">
				<h3 class="wpcalibrate-fpb-card__title"><?php esc_html_e( 'WooCommerce Status', 'wpcalibrate-form-payment-bridge' ); ?></h3>
				<p class="wpcalibrate-fpb-card__value">
					<?php if ( $health['wc_active'] ) : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php echo esc_html( $health['wc_version'] ); ?></span>
					<?php else : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger"><?php esc_html_e( 'Inactive', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="wpcalibrate-fpb-card">
			<div class="wpcalibrate-fpb-card__icon dashicons dashicons-feedback"></div>
			<div class="wpcalibrate-fpb-card__body">
				<h3 class="wpcalibrate-fpb-card__title"><?php esc_html_e( 'Forminator Status', 'wpcalibrate-form-payment-bridge' ); ?></h3>
				<p class="wpcalibrate-fpb-card__value">
					<?php if ( $health['forminator_active'] ) : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php echo esc_html( $health['forminator_version'] ); ?></span>
					<?php else : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--danger"><?php esc_html_e( 'Inactive', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="wpcalibrate-fpb-card">
			<div class="wpcalibrate-fpb-card__icon dashicons dashicons-database"></div>
			<div class="wpcalibrate-fpb-card__body">
				<h3 class="wpcalibrate-fpb-card__title"><?php esc_html_e( 'Storage Engine', 'wpcalibrate-form-payment-bridge' ); ?></h3>
				<p class="wpcalibrate-fpb-card__value">
					<?php if ( $health['hpos_enabled'] ) : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--success"><?php esc_html_e( 'HPOS Compatible', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<?php else : ?>
						<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Standard Post Tables', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</section>

	<!-- Section 1: Purpose -->
	<section class="wpcalibrate-fpb-box" aria-labelledby="purpose-heading">
		<h3 id="purpose-heading" class="wpcalibrate-fpb-box__title">
			<span class="dashicons dashicons-info" style="color: #2271b1; vertical-align: middle;"></span>
			<?php esc_html_e( 'Plugin Purpose', 'wpcalibrate-form-payment-bridge' ); ?>
		</h3>
		<p class="wpcalibrate-fpb-paragraph">
			<?php esc_html_e( 'Forminator provides a rich visual form builder with conditional logic, multi-step sections, custom calculations, and flexible embeds. WooCommerce is the industry-standard eCommerce platform with robust payment gateways, tax calculation, transaction accounting, customer accounts, and order management.', 'wpcalibrate-form-payment-bridge' ); ?>
		</p>
		<p class="wpcalibrate-fpb-paragraph">
			<strong><?php esc_html_e( 'The Bridge Philosophy:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
			<?php esc_html_e( 'This plugin bridges the two systems without altering core files, overriding form builders, or collecting credit card details. Forminator handles form rendering and submission validation; WooCommerce handles gateways, payments, receipts, refunds, and order status fulfillment. The payment bridge connects them through an isolated, idempotent workflow.', 'wpcalibrate-form-payment-bridge' ); ?>
		</p>

		<div class="wpcalibrate-fpb-features-grid">
			<div class="wpcalibrate-fpb-feature-item">
				<div class="wpcalibrate-fpb-feature-item__icon dashicons dashicons-shield"></div>
				<div class="wpcalibrate-fpb-feature-item__content">
					<h4><?php esc_html_e( 'Zero Card Data Handled', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Credit cards are never collected or processed on the form. Payments are handled entirely by WooCommerce payment gateways on the native Pay for Order page.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-feature-item">
				<div class="wpcalibrate-fpb-feature-item__icon dashicons dashicons-randomize"></div>
				<div class="wpcalibrate-fpb-feature-item__content">
					<h4><?php esc_html_e( 'Cart Isolation', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'The form order is created directly via WooCommerce CRUD APIs. Any items currently in the visitor\'s shopping cart remain untouched and isolated.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-feature-item">
				<div class="wpcalibrate-fpb-feature-item__icon dashicons dashicons-update"></div>
				<div class="wpcalibrate-fpb-feature-item__content">
					<h4><?php esc_html_e( 'Deterministic Idempotency', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Double-clicks, network retries, and browser refreshes are deduplicated using atomic locking. Exactly one WooCommerce order is created per Forminator submission.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-feature-item">
				<div class="wpcalibrate-fpb-feature-item__icon dashicons dashicons-rest-api"></div>
				<div class="wpcalibrate-fpb-feature-item__content">
					<h4><?php esc_html_e( 'HPOS & Legacy Compatible', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Fully declared and verified compatible with WooCommerce High-Performance Order Storage (HPOS) as well as traditional WordPress post table storage.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Section 2: Method & Architectural Flow -->
	<section class="wpcalibrate-fpb-box" aria-labelledby="method-heading">
		<h3 id="method-heading" class="wpcalibrate-fpb-box__title">
			<span class="dashicons dashicons-networking" style="color: #2271b1; vertical-align: middle;"></span>
			<?php esc_html_e( 'Method & Architectural Flow', 'wpcalibrate-form-payment-bridge' ); ?>
		</h3>
		<p class="wpcalibrate-fpb-paragraph">
			<?php esc_html_e( 'The plugin implements a clean 5-stage lifecycle that ensures data integrity and security at every phase:', 'wpcalibrate-form-payment-bridge' ); ?>
		</p>

		<div class="wpcalibrate-fpb-flow">
			<div class="wpcalibrate-fpb-flow-step">
				<div class="wpcalibrate-fpb-flow-step__badge">1</div>
				<div class="wpcalibrate-fpb-flow-step__content">
					<h4><?php esc_html_e( 'Form Submission & Validation', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'The visitor fills and submits the Forminator form (AJAX or Page-Reload). Forminator validates required fields, verifies CSRF nonces, and saves the submission entry.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-flow-arrow dashicons dashicons-arrow-right-alt"></div>

			<div class="wpcalibrate-fpb-flow-step">
				<div class="wpcalibrate-fpb-flow-step__badge">2</div>
				<div class="wpcalibrate-fpb-flow-step__content">
					<h4><?php esc_html_e( 'Server-Side Amount Resolution', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'WPCalibrate intercepts the saved entry, verifies that the form is enabled, and calculates the monetary total server-side based on the configured mode. Unsafe client values are rejected.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-flow-arrow dashicons dashicons-arrow-right-alt"></div>

			<div class="wpcalibrate-fpb-flow-step">
				<div class="wpcalibrate-fpb-flow-step__badge">3</div>
				<div class="wpcalibrate-fpb-flow-step__content">
					<h4><?php esc_html_e( 'Idempotent Order Creation', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'An unpaid order in pending payment status is created using WooCommerce CRUD APIs. Billing fields and integration trace metadata (form ID, entry ID) are mapped.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-flow-arrow dashicons dashicons-arrow-right-alt"></div>

			<div class="wpcalibrate-fpb-flow-step">
				<div class="wpcalibrate-fpb-flow-step__badge">4</div>
				<div class="wpcalibrate-fpb-flow-step__content">
					<h4><?php esc_html_e( 'Secure Pay-for-Order Redirect', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'The customer is redirected directly to WooCommerce\'s native checkout payment page (/checkout/order-pay/ID/?pay_for_order=true&key=KEY) with order security key verification.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-flow-arrow dashicons dashicons-arrow-right-alt"></div>

			<div class="wpcalibrate-fpb-flow-step">
				<div class="wpcalibrate-fpb-flow-step__badge">5</div>
				<div class="wpcalibrate-fpb-flow-step__content">
					<h4><?php esc_html_e( 'Gateway Processing & Fulfillment', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'The customer chooses from your enabled payment gateways (Stripe, PayPal, BACS, etc.). The gateway processes payment and automatically updates the order status to Processing or Completed.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Section 3: How to Use (Step-by-Step Guide) -->
	<section class="wpcalibrate-fpb-box" aria-labelledby="how-to-use-heading">
		<h3 id="how-to-use-heading" class="wpcalibrate-fpb-box__title">
			<span class="dashicons dashicons-list-view" style="color: #2271b1; vertical-align: middle;"></span>
			<?php esc_html_e( 'How to Use (Step-by-Step Guide)', 'wpcalibrate-form-payment-bridge' ); ?>
		</h3>

		<div class="wpcalibrate-fpb-guide-steps">
			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">1</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 1: Build Your Form in Forminator', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'In your WordPress admin, go to Forminator > Forms and create your form. Add fields for customer information such as Name, Email, Phone, and Address. If your form uses dynamic amounts, add a Number, Calculation, Radio, Select, or Checkbox field. Finally, embed the form on your website page using Forminator\'s block or shortcode.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">2</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 2: Add a Payment Form Mapping', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Navigate to WPCalibrate > Form Payment Bridge in your WordPress admin menu. Click on the "Form Mappings" tab and choose "Add Payment Form Mapping".', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">3</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 3: Select Your Form & Enter Order Item Name', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Give your mapping an internal label (e.g. "Workshop Registration Payment") and select your Forminator form from the dropdown. The plugin automatically fetches and prepares the field selectors. Enter an Order Item Name (e.g. "Conference Ticket") which will appear on the customer\'s WooCommerce invoice.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">4</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 4: Select Your Payment Amount Mode', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Choose one of the three payment amount modes described below (Fixed Amount, Form Field/Calculation, or Selectable Choices) and configure its values.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">5</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 5: Map Customer Billing Fields', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Link your Forminator form fields to the corresponding WooCommerce billing fields (Email Address, First Name, Last Name, Phone, Address Line 1, City, State, etc.). Customer Email is required when enabling the mapping to ensure order confirmation emails reach the customer.', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>

			<div class="wpcalibrate-fpb-guide-step">
				<div class="wpcalibrate-fpb-guide-step__number">6</div>
				<div class="wpcalibrate-fpb-guide-step__body">
					<h4><?php esc_html_e( 'Step 6: Enable & Save', 'wpcalibrate-form-payment-bridge' ); ?></h4>
					<p><?php esc_html_e( 'Toggle the "Enable Mapping" switch to ON and click "Save Payment Form Mapping". Your form is now live and connected to WooCommerce checkout!', 'wpcalibrate-form-payment-bridge' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Section 4: The Three Payment Amount Modes -->
	<section class="wpcalibrate-fpb-box" aria-labelledby="modes-heading">
		<h3 id="modes-heading" class="wpcalibrate-fpb-box__title">
			<span class="dashicons dashicons-money-alt" style="color: #2271b1; vertical-align: middle;"></span>
			<?php esc_html_e( 'Payment Amount Modes Explained', 'wpcalibrate-form-payment-bridge' ); ?>
		</h3>
		<p class="wpcalibrate-fpb-paragraph">
			<?php esc_html_e( 'Each form mapping supports one authoritative server-side amount calculation mode at a time:', 'wpcalibrate-form-payment-bridge' ); ?>
		</p>

		<div class="wpcalibrate-fpb-modes-grid">
			<!-- Mode 1 -->
			<div class="wpcalibrate-fpb-mode-card">
				<div class="wpcalibrate-fpb-mode-card__header">
					<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Mode 1', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<h4 class="wpcalibrate-fpb-mode-card__title"><?php esc_html_e( 'Fixed Amount', 'wpcalibrate-form-payment-bridge' ); ?></h4>
				</div>
				<p class="wpcalibrate-fpb-mode-card__desc">
					<?php esc_html_e( 'A static price defined by the administrator in the mapping settings. Every submission of this form charges the exact same fixed amount in store currency.', 'wpcalibrate-form-payment-bridge' ); ?>
				</p>
				<div class="wpcalibrate-fpb-mode-card__example">
					<strong><?php esc_html_e( 'Best For:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Standard flat-rate registration fees (e.g. $50.00)', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'Fixed deposits or booking retainers', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'Single-tier service consultations', 'wpcalibrate-form-payment-bridge' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Mode 2 -->
			<div class="wpcalibrate-fpb-mode-card">
				<div class="wpcalibrate-fpb-mode-card__header">
					<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Mode 2', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<h4 class="wpcalibrate-fpb-mode-card__title"><?php esc_html_e( 'Form Field / Calculation', 'wpcalibrate-form-payment-bridge' ); ?></h4>
				</div>
				<p class="wpcalibrate-fpb-mode-card__desc">
					<?php esc_html_e( 'The payment amount is read directly from a Forminator Calculation or Number field submitted by the visitor, verified server-side, and sanitized.', 'wpcalibrate-form-payment-bridge' ); ?>
				</p>
				<div class="wpcalibrate-fpb-mode-card__example">
					<strong><?php esc_html_e( 'Best For:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Dynamic formulas calculated in Forminator (e.g. quantity &times; rate)', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'Custom quote forms or variable service estimators', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'User-entered donation amounts', 'wpcalibrate-form-payment-bridge' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Mode 3 -->
			<div class="wpcalibrate-fpb-mode-card">
				<div class="wpcalibrate-fpb-mode-card__header">
					<span class="wpcalibrate-fpb-badge wpcalibrate-fpb-badge--info"><?php esc_html_e( 'Mode 3', 'wpcalibrate-form-payment-bridge' ); ?></span>
					<h4 class="wpcalibrate-fpb-mode-card__title"><?php esc_html_e( 'Selectable Choices', 'wpcalibrate-form-payment-bridge' ); ?></h4>
				</div>
				<p class="wpcalibrate-fpb-mode-card__desc">
					<?php esc_html_e( 'An administrator price table maps individual options (Radio, Select dropdown, or Checkbox) to specific prices. The server validates choices against an allowlist without eval().', 'wpcalibrate-form-payment-bridge' ); ?>
				</p>
				<div class="wpcalibrate-fpb-mode-card__example">
					<strong><?php esc_html_e( 'Best For:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Tiered tickets (e.g. Standard: $25, VIP: $75)', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'Multi-select add-on services with checkbox summation', 'wpcalibrate-form-payment-bridge' ); ?></li>
						<li><?php esc_html_e( 'Forms where field values are text codes rather than raw numbers', 'wpcalibrate-form-payment-bridge' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<!-- Section 5: Best Practices & Store Requirements -->
	<section class="wpcalibrate-fpb-box" aria-labelledby="best-practices-heading">
		<h3 id="best-practices-heading" class="wpcalibrate-fpb-box__title">
			<span class="dashicons dashicons-admin-settings" style="color: #2271b1; vertical-align: middle;"></span>
			<?php esc_html_e( 'Important Best Practices & WooCommerce Settings', 'wpcalibrate-form-payment-bridge' ); ?>
		</h3>

		<div class="wpcalibrate-fpb-tips-list">
			<div class="wpcalibrate-fpb-tip">
				<strong><?php esc_html_e( '1. Enable Guest Checkout in WooCommerce:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
				<p><?php esc_html_e( 'If your Forminator forms are open to public/guest visitors, ensure "Allow customers to place orders without an account" is checked in WooCommerce > Settings > Accounts & Privacy. Otherwise, guest visitors will be asked to log in before viewing the Pay for Order page.', 'wpcalibrate-form-payment-bridge' ); ?></p>
			</div>

			<div class="wpcalibrate-fpb-tip">
				<strong><?php esc_html_e( '2. Payment Gateways Must Support Order Pay:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
				<p><?php esc_html_e( 'The native Pay for Order page displays all payment gateways active in WooCommerce > Settings > Payments that support direct order payment (e.g. Stripe, PayPal, BACS, Cash on Delivery). Gateways that require standard cart checkouts exclusively will not be shown.', 'wpcalibrate-form-payment-bridge' ); ?></p>
			</div>

			<div class="wpcalibrate-fpb-tip">
				<strong><?php esc_html_e( '3. Diagnostic Logging & Data Retention:', 'wpcalibrate-form-payment-bridge' ); ?></strong>
				<p><?php esc_html_e( 'You can enable detailed diagnostic logging under the "Orders & Diagnostics" tab. Sensitive credentials, passwords, and payment details are automatically redacted. When uninstalling the plugin, operational WooCommerce orders are ALWAYS preserved.', 'wpcalibrate-form-payment-bridge' ); ?></p>
			</div>
		</div>

		<div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f0f0f1; display: flex; gap: 12px; flex-wrap: wrap;">
			<a href="<?php echo esc_url( $add_mapping_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Add Payment Form Mapping &rarr;', 'wpcalibrate-form-payment-bridge' ); ?>
			</a>
			<a href="<?php echo esc_url( $diagnostics_url ); ?>" class="button button-secondary">
				<?php esc_html_e( 'View System Diagnostics', 'wpcalibrate-form-payment-bridge' ); ?>
			</a>
		</div>
	</section>

</div>
