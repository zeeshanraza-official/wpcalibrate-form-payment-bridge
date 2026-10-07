<?php
/**
 * Main admin shell template for WPCalibrate Form Payment Bridge.
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

$current_tab = $template_data['current_tab'];
$notices     = $template_data['notices'];
$brand_icon  = WPCALIBRATE_FPB_URL . 'branding/icon-dark.png';
?>

<div class="wrap wpcalibrate-fpb-wrap">
	<!-- Plugin Header -->
	<header class="wpcalibrate-fpb-header" role="banner">
		<div class="wpcalibrate-fpb-header__brand">
			<?php if ( file_exists( WPCALIBRATE_FPB_DIR . 'branding/icon-dark.png' ) ) : ?>
				<img src="<?php echo esc_url( $brand_icon ); ?>" alt="<?php esc_attr_e( 'WPCalibrate', 'wpcalibrate-form-payment-bridge' ); ?>" class="wpcalibrate-fpb-header__logo" width="40" height="40" />
			<?php endif; ?>
			<div class="wpcalibrate-fpb-header__titles">
				<h1 class="wpcalibrate-fpb-header__title"><?php esc_html_e( 'WPCalibrate Form Payment Bridge', 'wpcalibrate-form-payment-bridge' ); ?></h1>
				<span class="wpcalibrate-fpb-header__version">v<?php echo esc_html( WPCALIBRATE_FPB_VERSION ); ?></span>
			</div>
		</div>
		<div class="wpcalibrate-fpb-header__links">
			<a href="https://wpcalibrate.com" target="_blank" rel="noopener noreferrer" class="button button-secondary"><?php esc_html_e( 'Documentation', 'wpcalibrate-form-payment-bridge' ); ?></a>
			<a href="https://marketplace.wpcalibrate.com/" target="_blank" rel="noopener noreferrer" class="button button-primary"><?php esc_html_e( 'Marketplace', 'wpcalibrate-form-payment-bridge' ); ?></a>
		</div>
	</header>

	<!-- Admin Notices -->
	<?php if ( ! empty( $notices ) ) : ?>
		<div class="wpcalibrate-fpb-notices" role="region" aria-label="<?php esc_attr_e( 'Notices', 'wpcalibrate-form-payment-bridge' ); ?>">
			<?php foreach ( $notices as $notice ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
					<p><?php echo esc_html( $notice['message'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<!-- Navigation Tabs -->
	<nav class="nav-tab-wrapper wpcalibrate-fpb-nav-tabs" aria-label="<?php esc_attr_e( 'Plugin Sections', 'wpcalibrate-form-payment-bridge' ); ?>" role="tablist">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=overview' ) ); ?>"
		   class="nav-tab <?php echo 'overview' === $current_tab ? 'nav-tab-active' : ''; ?>"
		   role="tab"
		   aria-selected="<?php echo 'overview' === $current_tab ? 'true' : 'false'; ?>">
			<?php esc_html_e( 'Overview', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=mappings' ) ); ?>"
		   class="nav-tab <?php echo 'mappings' === $current_tab ? 'nav-tab-active' : ''; ?>"
		   role="tab"
		   aria-selected="<?php echo 'mappings' === $current_tab ? 'true' : 'false'; ?>">
			<?php esc_html_e( 'Form Mappings', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=diagnostics' ) ); ?>"
		   class="nav-tab <?php echo 'diagnostics' === $current_tab ? 'nav-tab-active' : ''; ?>"
		   role="tab"
		   aria-selected="<?php echo 'diagnostics' === $current_tab ? 'true' : 'false'; ?>">
			<?php esc_html_e( 'Orders & Diagnostics', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=license' ) ); ?>"
		   class="nav-tab <?php echo 'license' === $current_tab ? 'nav-tab-active' : ''; ?>"
		   role="tab"
		   aria-selected="<?php echo 'license' === $current_tab ? 'true' : 'false'; ?>">
			<?php esc_html_e( 'License', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . \WPCalibrate\FormPaymentBridge\Admin\Menu::SUBMENU_SLUG . '&tab=support' ) ); ?>"
		   class="nav-tab <?php echo 'support' === $current_tab ? 'nav-tab-active' : ''; ?>"
		   role="tab"
		   aria-selected="<?php echo 'support' === $current_tab ? 'true' : 'false'; ?>">
			<?php esc_html_e( 'Support', 'wpcalibrate-form-payment-bridge' ); ?>
		</a>
	</nav>

	<!-- Tab Content Area -->
	<main class="wpcalibrate-fpb-content" role="tabpanel">
		<?php
		switch ( $current_tab ) {
			case 'overview':
				require WPCALIBRATE_FPB_DIR . 'templates/tab-overview.php';
				break;

			case 'mappings':
				if ( in_array( $template_data['action'], [ 'add', 'edit' ], true ) ) {
					require WPCALIBRATE_FPB_DIR . 'templates/tab-mapping-edit.php';
				} else {
					require WPCALIBRATE_FPB_DIR . 'templates/tab-mappings.php';
				}
				break;

			case 'diagnostics':
				require WPCALIBRATE_FPB_DIR . 'templates/tab-diagnostics.php';
				break;

			case 'license':
				require WPCALIBRATE_FPB_DIR . 'templates/tab-license.php';
				break;

			case 'support':
				require WPCALIBRATE_FPB_DIR . 'templates/tab-support.php';
				break;
		}
		?>
	</main>
</div>
