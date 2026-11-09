<?php
/**
 * Guurti Shir – theme bootstrap.
 *
 * @package guurti-shir
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GUURTI_SHIR_VERSION', '1.2.0' );

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/contact-form.php';
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/branding.php';

/**
 * Theme supports.
 */
function guurti_shir_setup() {
	load_theme_textdomain( 'guurti-shir', get_template_directory() . '/languages' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_image_size( 'guurti-card', 800, 600, true );
	add_image_size( 'guurti-portrait', 640, 640, true );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'guurti_shir_setup' );

/**
 * Pattern categories.
 */
function guurti_shir_pattern_categories() {
	register_block_pattern_category( 'guurti-shir', array( 'label' => __( 'House of Elders', 'guurti-shir' ) ) );
}
add_action( 'init', 'guurti_shir_pattern_categories' );

/**
 * Front-end assets.
 */
function guurti_shir_assets() {
	$uri = get_template_directory_uri();
	$ver = GUURTI_SHIR_VERSION;

	wp_enqueue_style( 'guurti-shir', get_stylesheet_uri(), array(), $ver );

	$defer = array( 'strategy' => 'defer', 'in_footer' => true );
	wp_register_script( 'gsap', $uri . '/assets/vendor/gsap.min.js', array(), '3.12', $defer );
	wp_register_script( 'gsap-st', $uri . '/assets/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.12', $defer );
	wp_enqueue_script( 'guurti-main', $uri . '/assets/js/main.js', array( 'gsap', 'gsap-st' ), $ver, $defer );

	if ( is_front_page() ) {
		wp_enqueue_script( 'guurti-hero', $uri . '/assets/js/hero.js', array(), $ver, $defer );
		wp_add_inline_script(
			'guurti-hero',
			'window.GuurtiHero=' . wp_json_encode( array( 'module' => $uri . '/assets/js/hero-three.js?ver=' . $ver ) ) . ';',
			'before'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guurti_shir_assets' );

/**
 * Set language + motion flags before first paint to avoid flashes.
 */
function guurti_shir_head_flags() {
	?>
<script>
(function(d){try{var l=localStorage.getItem('guurti-lang');if(!l){l=(navigator.language||'').toLowerCase().indexOf('so')===0?'so':'en';}
d.documentElement.setAttribute('data-lang',l==='so'?'so':'en');d.documentElement.lang=l==='so'?'so':'en';
if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){d.documentElement.classList.add('js-motion');}}catch(e){}})(document);
</script>
	<?php
}
add_action( 'wp_head', 'guurti_shir_head_flags', 1 );

/**
 * Preload the display font used above the fold.
 */
function guurti_shir_preload() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_template_directory_uri() . '/assets/fonts/cormorant-garamond-latin-600-normal.woff2' )
	);
}
add_action( 'wp_head', 'guurti_shir_preload', 2 );

/**
 * Allow root-relative links ("/leadership/") in templates to work on any install,
 * including WordPress in a sub-folder.
 *
 * @param string $content Block HTML.
 * @return string
 */
function guurti_shir_root_relative_links( $content ) {
	$home = untrailingslashit( home_url() );
	$path = (string) wp_parse_url( $home, PHP_URL_PATH );
	if ( '' === $path ) {
		return $content;
	}
	return preg_replace( '#href="/(?!/)#', 'href="' . esc_attr( $path ) . '/', $content );
}
add_filter( 'render_block_core/navigation-link', 'guurti_shir_root_relative_links' );
add_filter( 'render_block_core/navigation-submenu', 'guurti_shir_root_relative_links' );
add_filter( 'render_block_core/button', 'guurti_shir_root_relative_links' );

/**
 * Replace {year} in paragraphs (used in the footer copyright line).
 *
 * @param string $content Block HTML.
 * @return string
 */
function guurti_shir_year_token( $content ) {
	return str_replace( '{year}', esc_html( gmdate( 'Y' ) ), $content );
}
add_filter( 'render_block_core/paragraph', 'guurti_shir_year_token' );

/**
 * Flush permalinks once per theme version, after post types exist.
 */
function guurti_shir_maybe_flush() {
	if ( get_option( 'guurti_shir_rewrite_ver' ) !== GUURTI_SHIR_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'guurti_shir_rewrite_ver', GUURTI_SHIR_VERSION );
	}
}
add_action( 'init', 'guurti_shir_maybe_flush', 99 );
