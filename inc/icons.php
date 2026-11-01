<?php
/**
 * Ícones SVG embutidos (traço, currentColor).
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista de ícones: slug => [rótulo, conteúdo do SVG].
 */
function am_icons() {
	return array(
		'gift'      => array( 'Presente', '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8h14v-8M12 8v12"/><path d="M12 8C10.5 5 7 4.5 7 6.5S10 8 12 8Zm0 0c1.5-3 5-3.5 5-1.5S14 8 12 8Z"/>' ),
		'broadcast' => array( 'Transmissão', '<circle cx="12" cy="12" r="2"/><path d="M8.5 15.5a5 5 0 0 1 0-7M15.5 8.5a5 5 0 0 1 0 7M5.6 18.4a9 9 0 0 1 0-12.8M18.4 5.6a9 9 0 0 1 0 12.8"/>' ),
		'chef'      => array( 'Chapéu de chef', '<path d="M7 14v6h10v-6M7 17h10"/><path d="M7 14a4 4 0 1 1 1.5-7.7 4 4 0 0 1 7 0A4 4 0 1 1 17 14Z"/>' ),
		'terminal'  => array( 'Terminal', '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="m7 9 3 3-3 3M13 15h4"/>' ),
		'code'      => array( 'Código', '<path d="m8 7-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/>' ),
		'globe'     => array( 'Globo', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>' ),
		'git'       => array( 'Git', '<circle cx="6" cy="5" r="2"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="8" r="2"/><path d="M6 7v10M18 10c0 4-6 3-11 7.5"/>' ),
		'external'  => array( 'Link externo', '<path d="M7 17 17 7M9 7h8v8"/>' ),
	);
}

/**
 * Opções para o select do ACF (sem ícones de interface).
 */
function am_icon_choices() {
	$choices = array();
	foreach ( am_icons() as $slug => $icon ) {
		if ( 'external' !== $slug ) {
			$choices[ $slug ] = $icon[0];
		}
	}
	return $choices;
}

/**
 * Retorna o SVG de um ícone.
 */
function am_icon( $name, $class = '' ) {
	$icons = am_icons();
	$svg   = isset( $icons[ $name ] ) ? $icons[ $name ][1] : $icons['terminal'][1];

	return sprintf(
		'<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class ),
		$svg // SVG estático definido acima.
	);
}
