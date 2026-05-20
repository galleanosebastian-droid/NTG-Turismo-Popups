<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NTG_POPUPS_OPTION_KEY', 'ntg_popups_settings' );
define( 'NTG_POPUPS_SETTINGS_PAGE', 'ntg-turismo-popups' );

/**
 * Crea el menú principal en el admin de WordPress.
 */
function ntg_turismo_popups_register_admin_menu() {
	add_menu_page(
		esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ),
		esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ),
		'manage_options',
		NTG_POPUPS_SETTINGS_PAGE,
		'ntg_turismo_popups_render_admin_page',
		'dashicons-megaphone',
		56
	);
}
add_action( 'admin_menu', 'ntg_turismo_popups_register_admin_menu' );

/**
 * Procesa guardado del formulario con admin-post.php.
 */
function ntg_turismo_popups_handle_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tenés permisos para realizar esta acción.', 'ntg-turismo-popups' ) );
	}

	check_admin_referer( 'ntg_popups_save_settings' );

	$raw_settings = isset( $_POST[ NTG_POPUPS_OPTION_KEY ] ) ? wp_unslash( $_POST[ NTG_POPUPS_OPTION_KEY ] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$settings     = ntg_turismo_popups_sanitize_settings( $raw_settings );

	update_option( NTG_POPUPS_OPTION_KEY, $settings );

	$redirect_url = add_query_arg(
		array(
			'page'    => NTG_POPUPS_SETTINGS_PAGE,
			'updated' => '1',
		),
		admin_url( 'admin.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_ntg_popups_save_settings', 'ntg_turismo_popups_handle_save_settings' );

/**
 * Valores por defecto de configuración.
 *
 * @return array<string, mixed>
 */
function ntg_turismo_popups_get_default_settings() {
	return array(
		'active'           => 0,
		'campaign_title'   => '',
		'desktop_image_id' => 0,
		'mobile_image_id'  => 0,
		'whatsapp_number'  => '',
		'whatsapp_message' => '',
		'show_close_button'   => 0,
		'image_click_whatsapp' => 0,
	);
}

/**
 * Obtiene settings guardados fusionados con defaults.
 *
 * @return array<string, mixed>
 */
function ntg_turismo_popups_get_settings() {
	$saved = get_option( NTG_POPUPS_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, ntg_turismo_popups_get_default_settings() );
}

/**
 * Sanitiza todos los campos de configuración.
 *
 * @param array<string, mixed> $input Datos recibidos del formulario.
 * @return array<string, mixed>
 */
function ntg_turismo_popups_sanitize_settings( $input ) {
	$defaults = ntg_turismo_popups_get_default_settings();
	$input    = is_array( $input ) ? $input : array();

	$output = array();

	$output['active'] = isset( $input['active'] ) ? 1 : 0;

	$output['campaign_title'] = isset( $input['campaign_title'] )
		? sanitize_text_field( wp_unslash( $input['campaign_title'] ) )
		: $defaults['campaign_title'];

	$output['desktop_image_id'] = isset( $input['desktop_image_id'] )
		? absint( $input['desktop_image_id'] )
		: $defaults['desktop_image_id'];

	$output['mobile_image_id'] = isset( $input['mobile_image_id'] )
		? absint( $input['mobile_image_id'] )
		: $defaults['mobile_image_id'];

	$output['whatsapp_number'] = isset( $input['whatsapp_number'] )
		? sanitize_text_field( wp_unslash( $input['whatsapp_number'] ) )
		: $defaults['whatsapp_number'];

	$output['whatsapp_message'] = isset( $input['whatsapp_message'] )
		? sanitize_textarea_field( wp_unslash( $input['whatsapp_message'] ) )
		: $defaults['whatsapp_message'];

	$output['show_close_button'] = isset( $input['show_close_button'] ) ? 1 : 0;
	$output['image_click_whatsapp'] = isset( $input['image_click_whatsapp'] ) ? 1 : 0;

	return $output;
}

/**
 * Texto descriptivo de la sección.
 */
function ntg_turismo_popups_render_section_description() {
	echo '<p>' . esc_html__( 'Configurá los datos de la campaña promocional desde este panel.', 'ntg-turismo-popups' ) . '</p>';
}

/**
 * Renderiza cada campo de configuración.
 *
 * @param array<string, string> $args Argumentos del campo.
 */
function ntg_turismo_popups_render_field( $args ) {
	$settings  = ntg_turismo_popups_get_settings();
	$field_key = isset( $args['field_key'] ) ? $args['field_key'] : '';

	switch ( $field_key ) {
		case 'active':
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[active]" value="1" <?php checked( ! empty( $settings['active'] ) ); ?> />
				<?php echo esc_html__( 'Habilitar pop-up promocional', 'ntg-turismo-popups' ); ?>
			</label>
			<?php
			break;

		case 'campaign_title':
			?>
			<input type="text" class="regular-text" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[campaign_title]" value="<?php echo esc_attr( $settings['campaign_title'] ); ?>" />
			<?php
			break;

		case 'desktop_image_id':
		case 'mobile_image_id':
			$field_name = NTG_POPUPS_OPTION_KEY . '[' . $field_key . ']';
			$image_id   = absint( $settings[ $field_key ] );
			$preview    = $image_id ? wp_get_attachment_image( $image_id, 'medium', false, array( 'class' => 'ntg-popups-image-preview' ) ) : '';
			?>
			<div class="ntg-popups-media-field" data-target="<?php echo esc_attr( $field_key ); ?>">
				<input type="hidden" class="ntg-popups-media-id" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $image_id ); ?>" />
				<button type="button" class="button ntg-popups-select-media">
					<?php echo esc_html__( 'Seleccionar imagen', 'ntg-turismo-popups' ); ?>
				</button>
				<button type="button" class="button-link-delete ntg-popups-remove-media" <?php echo $image_id ? '' : 'style="display:none;"'; ?>>
					<?php echo esc_html__( 'Quitar imagen', 'ntg-turismo-popups' ); ?>
				</button>
				<div class="ntg-popups-media-preview"><?php echo wp_kses_post( $preview ); ?></div>
			</div>
			<?php
			break;

		case 'whatsapp_number':
			?>
			<input type="text" class="regular-text" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" />
			<?php
			break;

		case 'whatsapp_message':
			?>
			<textarea class="large-text" rows="4" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[whatsapp_message]"><?php echo esc_textarea( $settings['whatsapp_message'] ); ?></textarea>
			<?php
			break;
		case 'show_close_button':
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[show_close_button]" value="1" <?php checked( ! empty( $settings['show_close_button'] ) ); ?> />
				<?php echo esc_html__( 'Mostrar botón de cerrar', 'ntg-turismo-popups' ); ?>
			</label>
			<?php
			break;
		case 'image_click_whatsapp':
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[image_click_whatsapp]" value="1" <?php checked( ! empty( $settings['image_click_whatsapp'] ) ); ?> />
				<?php echo esc_html__( 'Abrir WhatsApp al hacer clic en toda la imagen', 'ntg-turismo-popups' ); ?>
			</label>
			<?php
			break;
	}
}

/**
 * Render principal del panel de administración.
 */
function ntg_turismo_popups_render_admin_page() {
	?>
	<div class="wrap ntg-turismo-popups-admin">
		<h1><?php echo esc_html__( 'NTG Turismo Popups', 'ntg-turismo-popups' ); ?></h1>
		<?php if ( isset( $_GET['updated'] ) && '1' === $_GET['updated'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html__( 'Configuración guardada correctamente.', 'ntg-turismo-popups' ); ?></p>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ntg_popups_save_settings' ); ?>
			<input type="hidden" name="action" value="ntg_popups_save_settings" />
			<?php ntg_turismo_popups_render_section_description(); ?>
			<table class="form-table" role="presentation">
				<tbody>
				<?php
				$fields = array(
					'active'               => esc_html__( 'Activar pop-up', 'ntg-turismo-popups' ),
					'campaign_title'       => esc_html__( 'Título interno de campaña', 'ntg-turismo-popups' ),
					'desktop_image_id'     => esc_html__( 'Imagen desktop', 'ntg-turismo-popups' ),
					'mobile_image_id'      => esc_html__( 'Imagen mobile', 'ntg-turismo-popups' ),
					'whatsapp_number'      => esc_html__( 'Número de WhatsApp', 'ntg-turismo-popups' ),
					'whatsapp_message'     => esc_html__( 'Mensaje prearmado de WhatsApp', 'ntg-turismo-popups' ),
					'show_close_button'    => esc_html__( 'Mostrar botón de cerrar', 'ntg-turismo-popups' ),
					'image_click_whatsapp' => esc_html__( 'Abrir WhatsApp al hacer clic en toda la imagen', 'ntg-turismo-popups' ),
				);

				foreach ( $fields as $field_key => $field_label ) :
					?>
					<tr>
						<th scope="row"><label><?php echo esc_html( $field_label ); ?></label></th>
						<td><?php ntg_turismo_popups_render_field( array( 'field_key' => $field_key ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( esc_html__( 'Guardar cambios', 'ntg-turismo-popups' ) ); ?>
		</form>
	</div>
	<?php
}
