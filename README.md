# WPCalibrate Form Payment Bridge

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-8.0%2B-purple.svg)](https://woocommerce.com)
[![Forminator](https://img.shields.io/badge/Forminator-1.20%2B-orange.svg)](https://wpmudev.com/project/forminator-pro/)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777bb4.svg)](https://php.net)
[![HPOS](https://img.shields.io/badge/HPOS-Compatible-success.svg)](https://woocommerce.com)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**WPCalibrate Form Payment Bridge** bridges existing [Forminator](https://wordpress.org/plugins/forminator/) forms to native [WooCommerce](https://woocommerce.com/) checkout and payment gateway workflows.

Website owners design and embed Forminator forms normally using Forminator's visual builder, shortcodes, and blocks. When a configured form is submitted, WPCalibrate Form Payment Bridge creates an isolated unpaid order in WooCommerce and automatically redirects the customer to WooCommerce's native "Pay for Order" page (`/checkout/order-pay/`).

---

## 🌟 About

The bridge operates on a **strict non-invasive architecture**:
- **Never Touch Core Files**: Does not modify WordPress, WooCommerce, or Forminator core.
- **Never Handle Sensitive Card Data**: Payment credentials are never collected or processed on forms. Gateways process transactions directly on the WooCommerce native order-pay screen.
- **Cart Isolation**: Creates isolated orders via `wc_create_order()` CRUD APIs without contaminating the shopper's active cart.
- **Deterministic Idempotency**: Atomic transient locking prevents race conditions, double-clicks, or duplicate orders.

```
┌─────────────────────────────────┐
│     Existing Forminator Form    │
└────────────────┬────────────────┘
                 │ 1. Form Submission (AJAX or Page-Reload)
                 ▼
┌─────────────────────────────────┐
│   Server-Side Validation        │
│   & Amount Resolution           │
└────────────────┬────────────────┘
                 │ 2. Idempotent Order Creation
                 ▼
┌─────────────────────────────────┐
│   WooCommerce Pending Order     │
│   (Created via CRUD APIs)       │
└────────────────┬────────────────┘
                 │ 3. Automated Safe Redirect
                 ▼
┌─────────────────────────────────┐
│  Native Pay for Order Page      │
│  (/checkout/order-pay/)         │
└────────────────┬────────────────┘
                 │ 4. Payment Gateway Processing
                 ▼
┌─────────────────────────────────┐
│  Enabled Payment Gateways       │
│  (Stripe, PayPal, BACS, etc.)   │
└─────────────────────────────────┘
```

---

## 🚀 Features

- **Three Server-Side Payment Amount Modes**:
  1. **Fixed Amount**: Predefined static price configured per form.
  2. **Form Field / Calculation**: Dynamic amount derived server-side from Forminator Calculation or Number fields with decimal normalization.
  3. **Selectable Field Choices**: Administrator-configured price lookup mapping user selections (radios, selects, checkboxes) to verified prices without insecure client-side `eval()`.
- **Deterministic Idempotency**: Order linkage key format (`fpb_form_{form_id}_entry_{entry_id}`) prevents race conditions from double-clicks or repeated callbacks.
- **WooCommerce HPOS Compatible**: Built entirely on WooCommerce CRUD APIs and officially declares compatibility with High-Performance Order Storage (`custom_order_tables`).
- **Cart Isolation**: Orders are created directly without adding items to `WC()->cart`. Shopper cart items remain untouched.
- **Dual Submission Support**: Full compatibility with both Forminator AJAX submissions and page-reload submissions.
- **Customer & Billing Field Mapping**: Maps Forminator inputs (name, email, phone, company, addresses) directly into WooCommerce billing data.
- **Custom Order Meta**: Pass any Forminator field as custom metadata onto the WooCommerce order.
- **Shared Top-Level Menu**: Integrates under the shared `WPCalibrate` admin menu without duplicate top-level menus when multiple WPCalibrate plugins are installed.
- **Security Hardened**: Nonce verification, strict capability checks (`manage_options`), output escaping, and sensitive data redaction in diagnostic logs.

---

## 📋 Requirements

| Requirement | Minimum | Tested Up To |
|-------------|---------|--------------|
| **PHP** | 8.2 | 8.3 |
| **WordPress** | 6.0 | 7.1.3 |
| **WooCommerce** | 8.0 | 11.1.2 |
| **Forminator** | 1.20 | 1.57.3 |

---

## 💻 Installation & Setup

### Method 1: Upload via WordPress Admin
1. Download the latest release `wpcalibrate-form-payment-bridge-1.0.0.zip` from the [Releases](https://github.com/zeeshanraza-official/wpcalibrate-form-payment-bridge/releases) page.
2. Go to **Plugins > Add New Plugin > Upload Plugin** in your WordPress admin.
3. Select the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Method 2: Manual FTP / SFTP Installation
1. Extract `wpcalibrate-form-payment-bridge.zip`.
2. Upload the `wpcalibrate-form-payment-bridge` directory to `/wp-content/plugins/`.
3. Activate the plugin via **Plugins > Installed Plugins**.

---

## 📖 Documentation & Step-by-Step Guide

### Step 1: Create Your Forminator Form
1. In WordPress admin, navigate to **Forminator > Forms**.
2. Create or edit a form with your desired fields (e.g., Name, Email, Address, Calculation, or Choice fields).
3. Under **Edit Form > Behavior**, set **Submission Method** to AJAX or Page Reload.
4. Publish and embed the form on your website.

### Step 2: Configure a Payment Mapping
1. Go to **WPCalibrate > Form Payment Bridge**.
2. Click **Add Payment Form Mapping**.
3. Select your Forminator form from the dropdown.
4. Choose your **Amount Mode**:
   - **Fixed Amount**: Enter a static price (e.g. `25.00`).
   - **Form Field / Calculation**: Select a Forminator calculation or number field.
   - **Selectable Choices**: Choose a radio/select/checkbox field and enter prices for each authorized option.
5. Map customer billing fields (Email, Name, Phone, Address).
6. Enable the mapping and click **Save Payment Form Mapping**.

### Step 3: Test Payment Flow
1. Submit the form on the frontend.
2. Confirm instant redirection to `/checkout/order-pay/[order_id]/?pay_for_order=true`.
3. Complete payment with any enabled WooCommerce gateway.

---

## 🔄 Dashboard Updates

This repository is compatible with standard WordPress update managers (including [GitHub Updater](https://github.com/afragen/github-updater) and [Git Updater](https://git-updater.com/)).

When a new release tag is published on GitHub, WordPress will detect the update and allow you to update the plugin with a single click from the **Plugins** dashboard.

Plugin Headers configured:
```php
* GitHub Plugin URI: https://github.com/zeeshanraza-official/wpcalibrate-form-payment-bridge
* Primary Branch:    main
```

---

## 📝 Changelog

### [1.0.2] - 2026-10-07
- **Branding Logo Containment**: Fixed 400x400 menu icon overflow in the WordPress admin sidebar by introducing dedicated 20x20 and 40x40 icons (`icon-menu.png`) and strict global `admin_head` CSS constraints.
- **Mapping Deletion & Case-Insensitivity**: Fixed issue where deleted mappings still displayed due to ID case-sensitivity mismatch (`wp_generate_password` vs `sanitize_key`). Added case-insensitive fallback lookups, standardized new IDs to lowercase, exposed visible Delete buttons in both the list table and edit screens, and moved actions to `admin_init`.
- **Automated FTP Deployment**: Added automated deployment tools (`bin/deploy-ftp.ps1`, `bin/deploy-ftp.php`).

### [1.0.1] - 2026-10-07
- **Activation Fix**: Fixed autoloader interface slug resolution mismatch for `License_Provider_Interface` and `Amount_Resolver_Interface`.
- **Overview Tab**: Added comprehensive Overview tab with architecture lifecycle, step-by-step setup guide, and WooCommerce gateway best practices.

### [1.0.0] - 2026-10-07
- **Initial Release**: Core Forminator to WooCommerce payment bridge with Fixed, Field, and Selectable amount modes, deterministic idempotency, and HPOS compatibility.

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later** ([GPL-2.0-or-later](LICENSE.txt)).

Copyright (c) 2026 [WPCalibrate](https://wpcalibrate.com).
