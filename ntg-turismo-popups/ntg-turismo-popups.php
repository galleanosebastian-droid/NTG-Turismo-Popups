<?php
/**
 * Plugin Name: NTG Turismo Popups
 * Description: Plugin base para gestionar pop-ups promocionales de NTG Turismo.
 * Version: 1.0.0
 * Author: NTG Turismo
 * Text Domain: ntg-turismo-popups
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NTG_POPUPS_VERSION', '1.0.0' );
define( 'NTG_POPUPS_PLUGIN_FILE', __FILE__ );
define( 'NTG_POPUPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NTG_POPUPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Hook de activación del plugin.
 *
 * @return void
 */
function ntg_popups_activate() {
	if ( false === get_option( 'ntg_popups_enabled' ) ) {
		add_option( 'ntg_popups_enabled', 'yes' );
	}
}
register_activation_hook( __FILE__, 'ntg_popups_activate' );

/**
 * Hook de desactivación del plugin.
 *
 * @return void
 */
function ntg_popups_deactivate() {
	// Reservado para tareas de limpieza temporal al desactivar.
}
register_deactivation_hook( __FILE__, 'ntg_popups_deactivate' );

/**
 * Registra el menú de administración del plugin.
 *
 * @return void
 */
function ntg_popups_register_admin_menu() {
	add_menu_page(
		esc_html__( 'NTG Popups', 'ntg-turismo-popups' ),
		esc_html__( 'NTG Popups', 'ntg-turismo-popups' ),
		'manage_options',
		'ntg-turismo-popups',
		'ntg_popups_render_admin_page',
		'dashicons-megaphone',
		56
	);
}
add_action( 'admin_menu', 'ntg_popups_register_admin_menu' );

/**
 * Renderiza la pantalla principal del plugin en el administrador.
 *
 * @return void
 */
function ntg_popups_render_admin_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Gestor de Pop-ups Promocionales', 'ntg-turismo-popups' ); ?></h1>
		<p>
			<?php
			echo esc_html__(
				'Plugin base instalado correctamente. Desde aquí podrás administrar futuros pop-ups promocionales para campañas de NTG Turismo.',
				'ntg-turismo-popups'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Shortcode base para pruebas rápidas en frontend.
 * Uso: [ntg_turismo_popup]
 *
 * @return string
 */
function ntg_popups_shortcode() {
	if ( 'yes' !== get_option( 'ntg_popups_enabled', 'yes' ) ) {
		return '';
	}

	return '<div class="ntg-turismo-popup-placeholder">NTG Turismo Popups activo.</div>';
}
add_shortcode( 'ntg_turismo_popup', 'ntg_popups_shortcode' );
