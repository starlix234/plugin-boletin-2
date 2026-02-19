<?php
/*
Plugin Name: Newestler
Description: Generador automático de ediciones/boletines desde WordPress (skeleton inicial).
Version: 0.1.0
Author: Constanza Leiva
Text Domain: newestler
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/** Constantes útiles */
define( 'NEWESTLER_VERSION', '0.1.0' );
define( 'NEWESTLER_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEWESTLER_URL', plugin_dir_url( __FILE__ ) );

/** Includes mínimos (no deben imprimir nada) */
if ( file_exists( NEWESTLER_DIR . 'includes/helpers.php' ) ) {
    require_once NEWESTLER_DIR . 'includes/helpers.php';
}
if ( file_exists( NEWESTLER_DIR . 'public/class-newestler-public.php' ) ) {
    require_once NEWESTLER_DIR . 'public/class-newestler-public.php';
}

/** Inicia la parte pública (shortcode, assets) */
function newestler_init_public() {
    if ( class_exists( 'Newestler_Public' ) ) {
        // Llamar a instance() para inicializar la clase. Evitar imprimir aquí.
        Newestler_Public::instance();
    }
}
add_action( 'plugins_loaded', 'newestler_init_public' );

/** Carga admin si corresponde (sin imprimir) */
if ( is_admin() && file_exists( NEWESTLER_DIR . 'admin/class-newestler-admin.php' ) ) {
    require_once NEWESTLER_DIR . 'admin/class-newestler-admin.php';
}

/** Registro del bloque (editor + estilo) */
add_action( 'init', 'newestler_register_block' );

function newestler_register_block() {

    wp_register_script(
        'newestler-block-editor',
        NEWESTLER_URL . 'blocks/boletin/editor.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-block-editor' ),
        NEWESTLER_VERSION,
        true
    );

    wp_register_style(
        'newestler-block-style',
        NEWESTLER_URL . 'blocks/boletin/style.css',
        array(),
        NEWESTLER_VERSION
    );

    // register_block_type acepta la ruta absoluta al folder del bloque
    register_block_type( NEWESTLER_DIR . 'blocks/boletin', array(
        'render_callback' => 'newestler_render_block',
    ) );
}

function newestler_render_block( $attributes ) {

    $atts = array();

    if ( ! empty( $attributes['categoria'] ) ) {
        $atts[] = 'categoria="' . esc_attr( $attributes['categoria'] ) . '"';
    }

    if ( ! empty( $attributes['start_date'] ) ) {
        $atts[] = 'start_date="' . esc_attr( $attributes['start_date'] ) . '"';
    }

    if ( ! empty( $attributes['end_date'] ) ) {
        $atts[] = 'end_date="' . esc_attr( $attributes['end_date'] ) . '"';
    }

    if ( ! empty( $attributes['limit'] ) ) {
        $atts[] = 'limit="' . intval( $attributes['limit'] ) . '"';
    }

    return do_shortcode( '[newestler_boletin ' . implode( ' ', $atts ) . ']' );
}
?>