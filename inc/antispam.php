<?php
/**
 * Antispam: o site é institucional e não recebe nenhum conteúdo de visitantes.
 *
 * - Comentários, pingbacks e trackbacks desativados em todos os tipos de post
 * - wp-comments-post.php, endpoint REST de comentários e feeds de comentários bloqueados
 * - XML-RPC desativado (pingback e ataques de força bruta via system.multicall)
 * - Cadastro de usuários desativado e listagem de usuários fechada para visitantes
 * - Busca desativada (evita spam de URLs de busca)
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/*
 * Comentários e pings fechados em qualquer conteúdo, inclusive os antigos.
 */
add_filter( 'comments_open', '__return_false', 99 );
add_filter( 'pings_open', '__return_false', 99 );
add_filter( 'comments_array', '__return_empty_array', 99 );
add_filter( 'get_comments_number', '__return_zero', 99 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

// Padrões de "Configurações → Discussão" forçados, mesmo que alguém altere no banco.
add_filter( 'pre_option_default_comment_status', fn() => 'closed' );
add_filter( 'pre_option_default_ping_status', fn() => 'closed' );
add_filter( 'pre_option_default_pingback_flag', fn() => '0' );
add_filter( 'pre_option_comment_registration', fn() => '1' );

add_action(
	'init',
	function () {
		foreach ( get_post_types() as $type ) {
			if ( post_type_supports( $type, 'comments' ) ) {
				remove_post_type_support( $type, 'comments' );
			}
			if ( post_type_supports( $type, 'trackbacks' ) ) {
				remove_post_type_support( $type, 'trackbacks' );
			}
		}
	},
	100
);

// Envio direto para wp-comments-post.php.
add_action(
	'pre_comment_on_post',
	function () {
		wp_die( 'Comentários desativados.', 'Comentários desativados', array( 'response' => 403 ) );
	}
);

// Qualquer comentário que chegue por outra via é descartado.
add_filter( 'pre_comment_approved', fn() => 'spam', 99 );

// Feeds de comentários.
add_action(
	'template_redirect',
	function () {
		if ( is_comment_feed() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

/*
 * XML-RPC: bloqueado por completo (os métodos system.* continuariam respondendo
 * só com o filtro xmlrpc_enabled).
 */
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	add_action(
		'init',
		function () {
			status_header( 403 );
			header( 'Content-Type: text/plain; charset=utf-8' );
			exit( 'XML-RPC desativado.' );
		},
		0
	);
}
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);
add_filter( 'bloginfo_url', fn( $output, $show ) => 'pingback_url' === $show ? '' : $output, 10, 2 );

/*
 * REST API: sem endpoint de comentários; usuários apenas para quem está logado.
 */
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( str_starts_with( $route, '/wp/v2/comments' ) ) {
				unset( $endpoints[ $route ] );
			}
			if ( ! is_user_logged_in() && str_starts_with( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

// oEmbed não expõe o autor.
add_filter(
	'oembed_response_data',
	function ( $data ) {
		unset( $data['author_name'], $data['author_url'] );
		return $data;
	}
);

/*
 * Cadastro de usuários desativado.
 */
add_filter( 'pre_option_users_can_register', fn() => '0' );

/*
 * Busca desativada no front-end (o site não tem campo de busca).
 */
add_action(
	'template_redirect',
	function () {
		if ( is_search() || isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);
add_filter( 'get_search_form', '__return_empty_string' );

/*
 * Painel: remove tudo que diz respeito a comentários.
 */
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
);

add_action(
	'admin_init',
	function () {
		global $pagenow;
		if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php', 'options-discussion.php' ), true ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}
);

add_action(
	'wp_before_admin_bar_render',
	function () {
		$GLOBALS['wp_admin_bar']->remove_menu( 'comments' );
	}
);

// Esconde o resumo de comentários no widget "Agora" e "Atividade".
add_action(
	'admin_head-index.php',
	function () {
		echo '<style>#dashboard_right_now .comment-count,#dashboard_right_now .comment-mod-count,#latest-comments{display:none!important}</style>';
	}
);
