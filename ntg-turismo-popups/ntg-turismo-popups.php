<?php
/**
 * Plugin Name: NTG Turismo Popups
 * Description: Gestor de pop-ups promocionales para NTG Turismo.
 * Version: 1.0.0
 * Author: NTG Turismo
 * Text Domain: ntg-turismo-popups
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
				'Desde este panel podrás crear y administrar pop-ups promocionales para campañas de NTG Turismo.',
				'ntg-turismo-popups'
			);
			?>
		</p>
	</div>
	<?php
}
