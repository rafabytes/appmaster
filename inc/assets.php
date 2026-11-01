<?php
/**
 * Enfileiramento dos assets compilados pelo Parcel (dist/).
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL + versão (filemtime) de um arquivo em dist/.
 *
 * @return array{0:string,1:string}|null
 */
function am_asset( $file ) {
	$path = APPMASTER_DIR . '/dist/' . $file;
	if ( ! file_exists( $path ) ) {
		return null;
	}
	return array( APPMASTER_URI . '/dist/' . $file, (string) filemtime( $path ) );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = am_asset( 'js/main.css' );
		if ( $css ) {
			wp_enqueue_style( 'appmaster', $css[0], array(), $css[1] );
		}

		$js = am_asset( 'js/main.js' );
		if ( $js ) {
			wp_enqueue_script(
				'appmaster',
				$js[0],
				array(),
				$js[1],
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
		}
	}
);

/*
 * Aviso no painel se os assets ainda não foram compilados.
 */
add_action(
	'admin_notices',
	function () {
		if ( current_user_can( 'edit_theme_options' ) && ! am_asset( 'js/main.css' ) ) {
			echo '<div class="notice notice-warning"><p><strong>appMaster:</strong> assets não compilados. Rode <code>npm install && npm run build</code> na pasta do tema.</p></div>';
		}
	}
);
