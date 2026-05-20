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

	wp_enqueue_media();

	wp_enqueue_script(
		'ntg-turismo-popups-admin',
		NTG_TURISMO_POPUPS_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		NTG_TURISMO_POPUPS_VERSION,
		true
	);

	wp_localize_script(
		'ntg-turismo-popups-admin',
		'ntgPopupsAdmin',
		array(
			'title'  => esc_html__( 'Seleccionar imagen', 'ntg-turismo-popups' ),
			'button' => esc_html__( 'Usar esta imagen', 'ntg-turismo-popups' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ntg_turismo_popups_admin_assets' );


/**
 * Valida si la campaña está dentro del rango de fechas permitido.
 *
 * @param array<string, mixed> $settings Configuración guardada.
 * @return bool
 */
function ntg_turismo_popups_is_date_in_range( $settings ) {
	$timezone = wp_timezone();
	$now      = new DateTimeImmutable( 'now', $timezone );

	$start_raw = isset( $settings['campaign_start_date'] ) ? sanitize_text_field( (string) $settings['campaign_start_date'] ) : '';
	$end_raw   = isset( $settings['campaign_end_date'] ) ? sanitize_text_field( (string) $settings['campaign_end_date'] ) : '';

	if ( '' !== $start_raw ) {
		$start = date_create_immutable_from_format( 'Y-m-d', $start_raw, $timezone );
		if ( false !== $start && $now < $start->setTime( 0, 0, 0 ) ) {
			return false;
		}
	}

	if ( '' !== $end_raw ) {
		$end = date_create_immutable_from_format( 'Y-m-d', $end_raw, $timezone );
		if ( false !== $end && $now > $end->setTime( 23, 59, 59 ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Arma URL de WhatsApp para clic en imagen.
 *
 * @param array<string, mixed> $settings Configuración guardada.
 * @return string
 */
function ntg_turismo_popups_get_whatsapp_url( $settings ) {
	$number_raw = isset( $settings['whatsapp_number'] ) ? (string) $settings['whatsapp_number'] : '';
	$message    = isset( $settings['whatsapp_message'] ) ? sanitize_textarea_field( (string) $settings['whatsapp_message'] ) : '';
	$number     = preg_replace( '/\D+/', '', $number_raw );

	if ( empty( $number ) ) {
		return '';
	}

	$url = 'https://wa.me/' . $number;
	if ( '' !== $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}

	return $url;
}

/**
 * Devuelve datos sanitizados para render del popup frontend.
 *
 * @return array<string, mixed>|null
 */
function ntg_turismo_popups_get_frontend_data() {
	if ( is_admin() || ! function_exists( 'ntg_turismo_popups_get_settings' ) ) {
		return null;
	}

	$settings = ntg_turismo_popups_get_settings();

	if ( empty( $settings['active'] ) ) {
		return null;
	}

	$location = isset( $settings['display_location'] ) ? sanitize_text_field( (string) $settings['display_location'] ) : 'home';
	if ( 'home' === $location && ! is_front_page() ) {
		return null;
	}
	if ( ! in_array( $location, array( 'home', 'all' ), true ) ) {
		return null;
	}

	if ( ! ntg_turismo_popups_is_date_in_range( $settings ) ) {
		return null;
	}

	$desktop_id  = isset( $settings['desktop_image_id'] ) ? absint( $settings['desktop_image_id'] ) : 0;
	$mobile_id   = isset( $settings['mobile_image_id'] ) ? absint( $settings['mobile_image_id'] ) : 0;
	$desktop_url = $desktop_id ? wp_get_attachment_image_url( $desktop_id, 'full' ) : '';
	$mobile_url  = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'full' ) : '';

	if ( empty( $desktop_url ) && empty( $mobile_url ) ) {
		return null;
	}

	$frequency = isset( $settings['display_frequency'] ) ? sanitize_text_field( (string) $settings['display_frequency'] ) : 'always';
	if ( ! in_array( $frequency, array( 'always', 'session' ), true ) ) {
		$frequency = 'always';
	}

	$whatsapp_url = '';
	if ( ! empty( $settings['image_click_whatsapp'] ) ) {
		$whatsapp_url = ntg_turismo_popups_get_whatsapp_url( $settings );
	}

	return array(
		'desktop_url'       => $desktop_url,
		'mobile_url'        => $mobile_url,
		'campaign_title'    => isset( $settings['campaign_title'] ) ? sanitize_text_field( (string) $settings['campaign_title'] ) : '',
		'show_close_button' => ! empty( $settings['show_close_button'] ),
		'display_frequency' => $frequency,
		'whatsapp_url'      => $whatsapp_url,
	);
}

/**
 * Encola assets del popup en frontend.
 */
function ntg_turismo_popups_frontend_assets() {
	$data = ntg_turismo_popups_get_frontend_data();
	if ( null === $data ) {
		return;
	}

	wp_enqueue_style(
		'ntg-turismo-popups-frontend',
		NTG_TURISMO_POPUPS_URL . 'assets/css/frontend.css',
		array(),
		NTG_TURISMO_POPUPS_VERSION
	);

	wp_enqueue_script(
		'ntg-turismo-popups-frontend',
		NTG_TURISMO_POPUPS_URL . 'assets/js/frontend.js',
		array(),
		NTG_TURISMO_POPUPS_VERSION,
		true
	);

	wp_localize_script(
		'ntg-turismo-popups-frontend',
		'ntgPopupsFrontend',
		array(
			'displayFrequency' => $data['display_frequency'],
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ntg_turismo_popups_frontend_assets' );

/**
 * Imprime markup del popup en wp_footer.
 */
function ntg_turismo_popups_render_frontend_popup() {
	$data = ntg_turismo_popups_get_frontend_data();
	if ( null === $data ) {
		return;
	}

	$alt = '' !== $data['campaign_title'] ? $data['campaign_title'] : esc_html__( 'Promoción', 'ntg-turismo-popups' );

	?>
	<div class="ntg-popups-overlay" data-ntg-popup>
		<div class="ntg-popups-modal" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $alt ); ?>">
			<?php if ( $data['show_close_button'] ) : ?>
				<button type="button" class="ntg-popups-close" data-ntg-popup-close aria-label="<?php echo esc_attr__( 'Cerrar', 'ntg-turismo-popups' ); ?>">&times;</button>
			<?php endif; ?>

			<?php if ( ! empty( $data['whatsapp_url'] ) ) : ?>
				<a class="ntg-popups-image-link" href="<?php echo esc_url( $data['whatsapp_url'] ); ?>" target="_blank" rel="noopener noreferrer">
			<?php else : ?>
				<div class="ntg-popups-image-wrap">
			<?php endif; ?>

				<picture>
					<?php if ( ! empty( $data['mobile_url'] ) ) : ?>
						<source media="(max-width: 767px)" srcset="<?php echo esc_url( $data['mobile_url'] ); ?>" />
					<?php endif; ?>
					<?php if ( ! empty( $data['desktop_url'] ) ) : ?>
						<source media="(min-width: 768px)" srcset="<?php echo esc_url( $data['desktop_url'] ); ?>" />
					<?php endif; ?>
					<img class="ntg-popups-image" src="<?php echo esc_url( ! empty( $data['desktop_url'] ) ? $data['desktop_url'] : $data['mobile_url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
				</picture>

			<?php if ( ! empty( $data['whatsapp_url'] ) ) : ?>
				</a>
			<?php else : ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'ntg_turismo_popups_render_frontend_popup' );
