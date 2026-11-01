<?php
/**
 * Tela de login personalizada.
 *
 * O front-end não exibe nenhum link de acesso: o login fica apenas em /wp-admin.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'login_enqueue_scripts',
	function () {
		$css = am_asset( 'css/login.css' );
		if ( $css ) {
			wp_enqueue_style( 'appmaster-login', $css[0], array( 'login' ), $css[1] );
		}
	}
);

add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => am_brand() );
add_filter( 'login_display_language_dropdown', '__return_false' );
add_filter( 'login_body_class', fn( $classes ) => array_merge( $classes, array( 'am-login' ) ) );
add_filter( 'login_title', fn() => am_option( 'login_title', 'acesso restrito' ) . ' — ' . am_brand() );

/*
 * Bloco estilo terminal acima do formulário.
 */
add_filter(
	'login_message',
	function ( $message ) {
		$title = am_option( 'login_title', 'acesso restrito' );
		$text  = am_option( 'login_message', 'Área administrativa do appMaster.' );

		$block  = '<div class="am-login-intro">';
		$block .= '<p class="am-login-intro__cmd"><span>$</span> sudo login</p>';
		$block .= '<p class="am-login-intro__title">' . esc_html( $title ) . '</p>';
		if ( $text ) {
			$block .= '<p class="am-login-intro__text">' . esc_html( $text ) . '</p>';
		}
		$block .= '</div>';

		return $block . $message;
	}
);
