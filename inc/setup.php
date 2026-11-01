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
 * Site institucional: sem comentários, sem pingbacks.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_filter( 'xmlrpc_methods', fn( $methods ) => array_diff_key( $methods, array( 'pingback.ping' => 1 ) ) );

add_action(
	'init',
	function () {
		foreach ( array( 'post', 'page' ) as $type ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	},
	100
);

add_action( 'admin_menu', fn() => remove_menu_page( 'edit-comments.php' ) );
add_action( 'wp_before_admin_bar_render', fn() => $GLOBALS['wp_admin_bar']->remove_menu( 'comments' ) );

/*
 * Cabeçalho mais enxuto.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

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
 * Meta description: resumo da página ou descrição padrão das opções.
 */
add_action(
	'wp_head',
	function () {
		$description = '';
		if ( is_singular() && has_excerpt() ) {
			$description = get_the_excerpt();
		}
		if ( ! $description ) {
			$description = am_option( 'meta_description', get_bloginfo( 'description' ) );
		}
		if ( $description ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
		}
		echo '<meta name="theme-color" content="#0a0c0b">' . "\n";
	},
	1
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
