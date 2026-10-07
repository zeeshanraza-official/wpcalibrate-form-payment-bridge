=== WPCalibrate Form Payment Bridge ===
Contributors: wpcalibrate
Donate link: https://wpcalibrate.com/
Tags: forminator, woocommerce, payment, bridge, pay-for-order
Requires at least: 6.0
Tested up to: 7.1.3
Requires PHP: 8.2
WC requires at least: 8.0
WC tested up to: 11.1.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect Forminator forms to WooCommerce payment processing and seamlessly redirect customers to native WooCommerce Pay for Order checkout.

== Description ==

**WPCalibrate Form Payment Bridge** bridges existing Forminator forms to native WooCommerce checkout and payment gateway workflows.

Website owners design and embed Forminator forms normally using Forminator's native form builder, shortcodes, and blocks. When a configured form is submitted, WPCalibrate Form Payment Bridge creates an isolated unpaid order in WooCommerce and automatically redirects the customer to WooCommerce's native "Pay for Order" page.

Payments are handled exclusively by your enabled WooCommerce payment gateways (e.g. Stripe, PayPal, BACS, or manual gateways). This plugin never collects, tokenizes, or stores credit card numbers.

### Key Features

* **Three Server-Side Payment Amount Modes**:
  1. *Fixed Amount*: Predefined static price configured per form.
  2. *Form Field / Calculation*: Dynamic amount derived server-side from Forminator Calculation or Number fields.
  3. *Selectable Field Choices*: Administrator-configured price lookup mapping user selections (radios, selects, checkboxes) to verified prices without insecure client-side eval.
* **Deterministic Idempotency & Duplicate Prevention**: Double-clicks, network retries, and simultaneous callbacks are safely deduplicated. Exactly one WooCommerce order is created per Forminator submission.
* **WooCommerce HPOS Compatible**: Built entirely on WooCommerce CRUD APIs and officially declared compatible with High-Performance Order Storage (HPOS).
* **Cart Isolation**: Created orders do not contaminate existing customer cart contents.
* **Both AJAX and Page-Reload Form Support**: Works seamlessly regardless of Forminator submission behavior settings.
* **Customer & Billing Field Mapping**: Maps Forminator inputs (name, email, phone, company, addresses) directly into WooCommerce billing data.
* **Isolated Administration**: Cleanly integrates into a shared top-level WPCalibrate menu without menu duplication or colliding with other WPCalibrate plugins.
* **Strict Security Standards**: Nonce verification, capability checks, strict server-side validation, output escaping, and sensitive data redaction in diagnostic logs.

== Installation ==

1. Ensure **WooCommerce** (8.0+) and **Forminator** (1.20+) are installed and active.
2. Upload the `wpcalibrate-form-payment-bridge` folder to your `/wp-content/plugins/` directory, or upload `wpcalibrate-form-payment-bridge.zip` via the WordPress admin.
3. Activate the plugin through the **Plugins** screen in WordPress.
4. Navigate to **WPCalibrate > Form Payment Bridge** in your WordPress admin.
5. Click **Add Payment Form Mapping** to connect your existing Forminator forms to WooCommerce.

== Usage Workflow ==

1. **Build Form**: Create your form in Forminator and embed it on any page or post.
2. **Configure Mapping**: In WPCalibrate > Form Payment Bridge, choose your form, configure the payment amount mode, map customer billing fields, and enable the mapping.
3. **Customer Submits**: When a visitor fills and submits the form, Forminator performs its normal validation and saves the entry.
4. **Order Creation**: WPCalibrate Form Payment Bridge calculates the amount server-side and creates a pending payment order in WooCommerce.
5. **Redirect & Pay**: The customer is directed to WooCommerce's native Pay for Order page where they select from your enabled payment gateways and complete payment.
6. **Order Fulfillment**: WooCommerce and your gateway handle payment confirmation, receipts, and order statuses as normal.

== Frequently Asked Questions ==

= Does this plugin process credit cards directly? =
No. Credit cards and payment credentials are never handled by this plugin. All payment processing occurs on WooCommerce's native Pay for Order page using your installed and configured WooCommerce payment gateways.

= Is WooCommerce HPOS supported? =
Yes. WPCalibrate Form Payment Bridge interacts with WooCommerce orders strictly through official CRUD APIs (`wc_create_order()`, `WC_Order`, etc.) and declares full compatibility with High-Performance Order Storage (`custom_order_tables`).

= How does this affect Cart and Checkout Blocks? =
This plugin directs customers to the native WooCommerce order-pay endpoint (`/checkout/order-pay/{id}/?key={key}`), which is rendered by WooCommerce's order-pay template. It does not replace or disrupt block-based cart or checkout pages configured for standard catalog store purchases.

= Can guests pay without logging in? =
Yes, provided "Allow customers to place orders without an account" is enabled in WooCommerce Settings > Accounts & Privacy. If guest checkout is disabled in WooCommerce, guests will be prompted to log in or register before completing payment on the order-pay page.

= What happens on plugin uninstall? =
By default, plugin settings and mappings are preserved so you do not accidentally lose your configuration. If you enable the "Delete plugin settings on uninstall" option in Diagnostics, settings and temporary locks will be removed. Operational WooCommerce orders, customer transactions, and Forminator entries are NEVER deleted on uninstall.

= What is the status of License Management? =
The license interface is built with a clean provider boundary. Because official remote licensing provider API details are pending, remote activation is marked as `BLOCKED: LICENSING API DETAILS REQUIRED`. In accordance with engineering standards, remote validation is never simulated or faked. All core features remain 100% active and testable.

== Changelog ==

= 1.0.0 =
* Initial release.
* Support for Fixed, Field/Calculation, and Selectable payment amount modes.
* Full Forminator AJAX and Page-Reload submission integration.
* Deterministic duplicate prevention and idempotency.
* WooCommerce CRUD and HPOS compatibility.
* Isolated WPCalibrate admin menu and diagnostic logging.
