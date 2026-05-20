<?php
/**
 * Plugin Name: NTG Turismo Popups
 * Plugin URI: https://ntgturismo.com.ar
 * Description: Gestor de pop-ups promocionales para NTG Turismo.
 * Version: 1.0.0
 * Author: NTG Turismo
 * License: GPL2
 * Text Domain: ntg-turismo-popups
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NTG_TURISMO_POPUPS_VERSION', '1.0.0' );
define( 'NTG_TURISMO_POPUPS_FILE', __FILE__ );
define( 'NTG_TURISMO_POPUPS_DIR', plugin_dir_path( __FILE__ ) );
define( 'NTG_TURISMO_POPUPS_URL', plugin_dir_url( __FILE__ ) );

require_once NTG_TURISMO_POPUPS_DIR . 'admin/admin-page.php';

function ntg_turismo_popups_admin_assets( $hook ) {
	if ( 'toplevel_page_ntg-turismo-popups' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'ntg-turismo-popups-admin',
		NTG_TURISMO_POPUPS_URL . 'assets/css/admin.css',
		array(),
		NTG_TURISMO_POPUPS_VERSION
	);

	wp_enqueue_script(
		'ntg-turismo-popups-admin',
		NTG_TURISMO_POPUPS_URL . 'assets/js/admin.js',
		array(),
		NTG_TURISMO_POPUPS_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'ntg_turismo_popups_admin_assets' );
