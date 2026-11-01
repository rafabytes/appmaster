<?php
/**
 * Suporte do tema, menus e limpeza do front-end.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'appmaster', APPMASTER_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			array(
				'primary' => __( 'Menu principal', 'appmaster' ),
				'footer'  => __( 'Links do rodapé', 'appmaster' ),
			)
		);

		add_post_type_support( 'page', 'excerpt' );
	}
);

/*
 * Comentários, pingbacks e demais bloqueios de spam: inc/antispam.php.
 */

/*
 * Cabeçalho mais enxuto.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'feed_links', 2 ); // Site sem blog: sem feeds no <head>.
remove_action( 'wp_head', 'feed_links_extra', 3 );

/*
 * Remove estilos de blocos no front-end: o tema usa o editor clássico e Tailwind.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	},
	100
);

/*
 * Menu: marca o item atual com aria-current (usado também após trocas do Swup).
 */
add_filter(
	'nav_menu_link_attributes',
	function ( $atts, $item ) {
		if ( ! empty( $item->current ) ) {
			$atts['aria-current'] = 'page';
		}
		return $atts;
	},
	10,
	2
);

/**
 * Fallback do menu principal quando nenhum menu foi atribuído.
 */
function am_menu_fallback() {
	$items = array( home_url( '/' ) => 'home' );
	foreach ( array( 'page-templates/sobre.php' => 'sobre', 'page-templates/portfolio.php' => 'portfolio' ) as $template => $label ) {
		$url = am_page_url( $template );
		if ( $url ) {
			$items[ $url ] = $label;
		}
	}

	echo '<ul class="nav-list">';
	foreach ( $items as $url => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
