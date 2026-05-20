<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug del grupo/opciones.
 */
define( 'NTG_POPUPS_SETTINGS_GROUP', 'ntg_popups_settings_group' );
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
 * Registra opción, sección y campos usando Settings API.
 */
function ntg_turismo_popups_register_settings() {
	register_setting(
		NTG_POPUPS_SETTINGS_GROUP,
		NTG_POPUPS_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ntg_turismo_popups_sanitize_settings',
			'default'           => ntg_turismo_popups_get_default_settings(),
		)
	);

	add_settings_section(
		'ntg_popups_main_section',
		esc_html__( 'Configuración de campaña', 'ntg-turismo-popups' ),
		'ntg_turismo_popups_render_section_description',
		NTG_POPUPS_SETTINGS_PAGE
	);

	$fields = array(
		'active'           => esc_html__( 'Activar pop-up', 'ntg-turismo-popups' ),
		'campaign_title'   => esc_html__( 'Título interno de campaña', 'ntg-turismo-popups' ),
		'desktop_image_id' => esc_html__( 'Imagen desktop', 'ntg-turismo-popups' ),
		'mobile_image_id'  => esc_html__( 'Imagen mobile', 'ntg-turismo-popups' ),
		'whatsapp_number'  => esc_html__( 'Número de WhatsApp', 'ntg-turismo-popups' ),
		'whatsapp_message' => esc_html__( 'Mensaje prearmado de WhatsApp', 'ntg-turismo-popups' ),
		'ntg_popups_show_in'                 => esc_html__( 'Mostrar en', 'ntg-turismo-popups' ),
		'ntg_popups_page_ids'                => esc_html__( 'Páginas específicas', 'ntg-turismo-popups' ),
		'ntg_popups_post_ids'                => esc_html__( 'Entradas específicas', 'ntg-turismo-popups' ),
		'ntg_popups_frequency'               => esc_html__( 'Frecuencia', 'ntg-turismo-popups' ),
		'ntg_popups_frequency_days'          => esc_html__( 'Cantidad de días', 'ntg-turismo-popups' ),
		'ntg_popups_campaign_start_date'     => esc_html__( 'Fecha de inicio de campaña', 'ntg-turismo-popups' ),
		'ntg_popups_campaign_end_date'       => esc_html__( 'Fecha de fin de campaña', 'ntg-turismo-popups' ),
		'ntg_popups_show_close_button'       => esc_html__( 'Botón de cerrar', 'ntg-turismo-popups' ),
		'ntg_popups_image_click_to_whatsapp' => esc_html__( 'Click en imagen hacia WhatsApp', 'ntg-turismo-popups' ),
	);

	foreach ( $fields as $field_key => $field_label ) {
		add_settings_field(
			'ntg_popups_' . $field_key,
			$field_label,
			'ntg_turismo_popups_render_field',
			NTG_POPUPS_SETTINGS_PAGE,
			'ntg_popups_main_section',
			array(
				'field_key' => $field_key,
			)
		);
	}
}
add_action( 'admin_init', 'ntg_turismo_popups_register_settings' );

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
		'ntg_popups_show_in'                 => 'home',
		'ntg_popups_page_ids'                => array(),
		'ntg_popups_post_ids'                => array(),
		'ntg_popups_frequency'               => 'always',
		'ntg_popups_frequency_days'          => 0,
		'ntg_popups_campaign_start_date'     => '',
		'ntg_popups_campaign_end_date'       => '',
		'ntg_popups_show_close_button'       => 0,
		'ntg_popups_image_click_to_whatsapp' => 0,
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
 * Sanitiza una fecha en formato Y-m-d o devuelve cadena vacía.
 *
 * @param mixed $date Valor recibido.
 * @return string
 */
function ntg_turismo_popups_sanitize_date( $date ) {
	if ( ! is_string( $date ) ) {
		return '';
	}

	$date = sanitize_text_field( wp_unslash( $date ) );
	if ( '' === $date ) {
		return '';
	}

	$dt = DateTime::createFromFormat( 'Y-m-d', $date );
	if ( false === $dt ) {
		return '';
	}

	$errors = DateTime::getLastErrors();
	if ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) {
		return '';
	}

	return $dt->format( 'Y-m-d' );
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
	$output   = array();

	$output['active'] = isset( $input['active'] ) ? 1 : 0;

	$output['campaign_title'] = isset( $input['campaign_title'] ) && is_scalar( $input['campaign_title'] )
		? sanitize_text_field( wp_unslash( (string) $input['campaign_title'] ) )
		: $defaults['campaign_title'];

	$output['desktop_image_id'] = isset( $input['desktop_image_id'] )
		? absint( $input['desktop_image_id'] )
		: $defaults['desktop_image_id'];

	$output['mobile_image_id'] = isset( $input['mobile_image_id'] )
		? absint( $input['mobile_image_id'] )
		: $defaults['mobile_image_id'];

	$output['whatsapp_number'] = isset( $input['whatsapp_number'] ) && is_scalar( $input['whatsapp_number'] )
		? sanitize_text_field( wp_unslash( (string) $input['whatsapp_number'] ) )
		: $defaults['whatsapp_number'];

	$output['whatsapp_message'] = isset( $input['whatsapp_message'] ) && is_scalar( $input['whatsapp_message'] )
		? sanitize_textarea_field( wp_unslash( (string) $input['whatsapp_message'] ) )
		: $defaults['whatsapp_message'];

	$show_in_allowed = array( 'home', 'all_pages', 'specific_pages', 'specific_posts', 'specific_pages_posts' );
	$show_in_raw     = isset( $input['ntg_popups_show_in'] ) && is_scalar( $input['ntg_popups_show_in'] ) ? (string) $input['ntg_popups_show_in'] : '';
	$show_in_value   = sanitize_key( wp_unslash( $show_in_raw ) );
	$output['ntg_popups_show_in'] = in_array( $show_in_value, $show_in_allowed, true ) ? $show_in_value : $defaults['ntg_popups_show_in'];

	$output['ntg_popups_page_ids'] = array();
	if ( isset( $input['ntg_popups_page_ids'] ) && is_array( $input['ntg_popups_page_ids'] ) ) {
		$output['ntg_popups_page_ids'] = array_values( array_filter( array_map( 'absint', wp_unslash( $input['ntg_popups_page_ids'] ) ) ) );
	}

	$output['ntg_popups_post_ids'] = array();
	if ( isset( $input['ntg_popups_post_ids'] ) && is_array( $input['ntg_popups_post_ids'] ) ) {
		$output['ntg_popups_post_ids'] = array_values( array_filter( array_map( 'absint', wp_unslash( $input['ntg_popups_post_ids'] ) ) ) );
	}

	$frequency_allowed = array( 'always', 'session_once', 'days_once' );
	$frequency_raw     = isset( $input['ntg_popups_frequency'] ) && is_scalar( $input['ntg_popups_frequency'] ) ? (string) $input['ntg_popups_frequency'] : '';
	$frequency_value   = sanitize_key( wp_unslash( $frequency_raw ) );
	$output['ntg_popups_frequency'] = in_array( $frequency_value, $frequency_allowed, true ) ? $frequency_value : $defaults['ntg_popups_frequency'];

	$output['ntg_popups_frequency_days'] = isset( $input['ntg_popups_frequency_days'] ) && is_scalar( $input['ntg_popups_frequency_days'] )
		? max( 0, absint( $input['ntg_popups_frequency_days'] ) )
		: $defaults['ntg_popups_frequency_days'];

	$output['ntg_popups_campaign_start_date'] = isset( $input['ntg_popups_campaign_start_date'] )
		? ntg_turismo_popups_sanitize_date( $input['ntg_popups_campaign_start_date'] )
		: $defaults['ntg_popups_campaign_start_date'];

	$output['ntg_popups_campaign_end_date'] = isset( $input['ntg_popups_campaign_end_date'] )
		? ntg_turismo_popups_sanitize_date( $input['ntg_popups_campaign_end_date'] )
		: $defaults['ntg_popups_campaign_end_date'];

	$output['ntg_popups_show_close_button']       = isset( $input['ntg_popups_show_close_button'] ) ? 1 : 0;
	$output['ntg_popups_image_click_to_whatsapp'] = isset( $input['ntg_popups_image_click_to_whatsapp'] ) ? 1 : 0;

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

		case 'ntg_popups_show_in':
			$show_in_options = array(
				'home'                 => esc_html__( 'Solo Home', 'ntg-turismo-popups' ),
				'all_pages'            => esc_html__( 'Todas las páginas', 'ntg-turismo-popups' ),
				'specific_pages'       => esc_html__( 'Páginas específicas', 'ntg-turismo-popups' ),
				'specific_posts'       => esc_html__( 'Entradas específicas', 'ntg-turismo-popups' ),
				'specific_pages_posts' => esc_html__( 'Páginas y entradas específicas', 'ntg-turismo-popups' ),
			);
			?>
			<select name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_show_in]">
				<?php foreach ( $show_in_options as $show_in_value => $show_in_label ) : ?>
					<option value="<?php echo esc_attr( $show_in_value ); ?>" <?php selected( $settings['ntg_popups_show_in'], $show_in_value ); ?>>
						<?php echo esc_html( $show_in_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			break;

		case 'ntg_popups_page_ids':
			$selected_pages = isset( $settings['ntg_popups_page_ids'] ) && is_array( $settings['ntg_popups_page_ids'] ) ? $settings['ntg_popups_page_ids'] : array();
			$pages          = get_pages(
				array(
					'post_status' => 'publish',
					'sort_column' => 'post_title',
					'sort_order'  => 'ASC',
				)
			);
			?>
			<select multiple="multiple" class="large-text" size="8" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_page_ids][]">
				<?php foreach ( $pages as $page ) : ?>
					<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( in_array( (int) $page->ID, $selected_pages, true ) ); ?>>
						<?php echo esc_html( $page->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php echo esc_html__( 'Mantené presionada la tecla Ctrl (o Cmd en Mac) para seleccionar múltiples páginas.', 'ntg-turismo-popups' ); ?></p>
			<?php
			break;

		case 'ntg_popups_post_ids':
			$selected_posts = isset( $settings['ntg_popups_post_ids'] ) && is_array( $settings['ntg_popups_post_ids'] ) ? $settings['ntg_popups_post_ids'] : array();
			$posts          = get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
			?>
			<select multiple="multiple" class="large-text" size="8" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_post_ids][]">
				<?php foreach ( $posts as $post_item ) : ?>
					<option value="<?php echo esc_attr( $post_item->ID ); ?>" <?php selected( in_array( (int) $post_item->ID, $selected_posts, true ) ); ?>>
						<?php echo esc_html( $post_item->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php echo esc_html__( 'Mantené presionada la tecla Ctrl (o Cmd en Mac) para seleccionar múltiples entradas.', 'ntg-turismo-popups' ); ?></p>
			<?php
			break;

		case 'ntg_popups_frequency':
			$frequency_options = array(
				'always'       => esc_html__( 'Mostrar siempre', 'ntg-turismo-popups' ),
				'session_once' => esc_html__( 'Mostrar una vez por sesión', 'ntg-turismo-popups' ),
				'days_once'    => esc_html__( 'Mostrar una vez cada X días', 'ntg-turismo-popups' ),
			);
			?>
			<select name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_frequency]">
				<?php foreach ( $frequency_options as $frequency_value => $frequency_label ) : ?>
					<option value="<?php echo esc_attr( $frequency_value ); ?>" <?php selected( $settings['ntg_popups_frequency'], $frequency_value ); ?>>
						<?php echo esc_html( $frequency_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			break;

		case 'ntg_popups_frequency_days':
			?>
			<input type="number" min="0" class="small-text" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_frequency_days]" value="<?php echo esc_attr( $settings['ntg_popups_frequency_days'] ); ?>" />
			<?php
			break;

		case 'ntg_popups_campaign_start_date':
		case 'ntg_popups_campaign_end_date':
			?>
			<input type="date" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $settings[ $field_key ] ); ?>" />
			<?php
			break;

		case 'ntg_popups_show_close_button':
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_show_close_button]" value="1" <?php checked( ! empty( $settings['ntg_popups_show_close_button'] ) ); ?> />
				<?php echo esc_html__( 'Mostrar botón de cerrar', 'ntg-turismo-popups' ); ?>
			</label>
			<?php
			break;

		case 'ntg_popups_image_click_to_whatsapp':
			?>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( NTG_POPUPS_OPTION_KEY ); ?>[ntg_popups_image_click_to_whatsapp]" value="1" <?php checked( ! empty( $settings['ntg_popups_image_click_to_whatsapp'] ) ); ?> />
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
		<form method="post" action="options.php">
			<?php
			settings_fields( NTG_POPUPS_SETTINGS_GROUP );
			do_settings_sections( NTG_POPUPS_SETTINGS_PAGE );
			submit_button( esc_html__( 'Guardar cambios', 'ntg-turismo-popups' ) );
			?>
		</form>
	</div>
	<?php
}
