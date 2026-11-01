<?php
/**
 * Compatibilidade com LiteSpeed Cache (predefinição "Avançado").
 *
 * - O JS do tema já sai minificado e com defer do Parcel e carrega chunks sob demanda
 *   (React). Combinar/atrasar esse JS quebraria o carregamento dos chunks, então ele
 *   fica fora das otimizações de JS do plugin.
 * - Conteúdo que aparece em várias páginas (opções, projetos, menus) limpa todo o cache.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trecho que identifica os scripts do tema nas listas de exclusão do LiteSpeed.
 */
function am_litespeed_js_marker() {
	return 'themes/' . get_template() . '/dist/';
}

$am_ls_exclude = function ( $list ) {
	$list   = is_array( $list ) ? $list : array_filter( array_map( 'trim', explode( "\n", (string) $list ) ) );
	$list[] = am_litespeed_js_marker();
	return array_values( array_unique( $list ) );
};

add_filter( 'litespeed_optimize_js_excludes', $am_ls_exclude ); // Minificar/combinar JS.
add_filter( 'litespeed_optm_js_defer_exc', $am_ls_exclude );    // Carregar JS adiado/atrasado.
add_filter( 'litespeed_optm_gm_js_exc', $am_ls_exclude );       // Modo convidado.

/*
 * Marca o <script> do tema e os JSON inline para o LiteSpeed não tocar
 * (atributos reconhecidos pelo plugin).
 */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle ) {
		if ( 'appmaster' === $handle && ! str_contains( $tag, 'data-no-optimize' ) ) {
			$tag = str_replace( '<script ', '<script data-no-optimize="1" data-no-defer="1" ', $tag );
		}
		return $tag;
	},
	10,
	2
);

/**
 * Limpa todo o cache do LiteSpeed (sem efeito se o plugin não estiver ativo).
 */
function am_litespeed_purge_all() {
	do_action( 'litespeed_purge_all' );
}

// Opções do tema (appMaster) aparecem em todas as páginas.
add_action(
	'acf/save_post',
	function ( $post_id ) {
		if ( 'options' === $post_id || 'option' === $post_id ) {
			am_litespeed_purge_all();
		}
	},
	20
);

// Projetos aparecem na home e no portfolio (o post type não é público, então o
// LiteSpeed não sabe quais páginas limpar).
add_action(
	'save_post_projeto',
	function ( $post_id ) {
		if ( ! wp_is_post_revision( $post_id ) && ! wp_is_post_autosave( $post_id ) ) {
			am_litespeed_purge_all();
		}
	}
);
add_action(
	'before_delete_post',
	function ( $post_id ) {
		if ( 'projeto' === get_post_type( $post_id ) ) {
			am_litespeed_purge_all();
		}
	}
);
add_action( 'trashed_post', fn( $post_id ) => 'projeto' === get_post_type( $post_id ) && am_litespeed_purge_all() );

// Menus, ícone do site e título/descrição do site.
add_action( 'wp_update_nav_menu', 'am_litespeed_purge_all' );
add_action( 'update_option_site_icon', 'am_litespeed_purge_all' );
add_action( 'update_option_blogname', 'am_litespeed_purge_all' );
add_action( 'update_option_blogdescription', 'am_litespeed_purge_all' );
add_action( 'switch_theme', 'am_litespeed_purge_all' );
