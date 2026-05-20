<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ntg_turismo_popups_register_admin_menu() {
	add_menu_page(
		esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ),
		esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ),
		'manage_options',
		'ntg-turismo-popups',
		'ntg_turismo_popups_render_admin_page',
		'dashicons-megaphone',
		56
	);
}
add_action( 'admin_menu', 'ntg_turismo_popups_register_admin_menu' );

function ntg_turismo_popups_render_admin_page() {
	?>
	<div class="wrap ntg-turismo-popups-admin">
		<h1><?php echo esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ); ?></h1>
		<p><?php echo esc_html__( 'Plugin instalado y listo para gestionar pop-ups promocionales.', 'ntg-turismo-popups' ); ?></p>
	</div>
	<?php
}
