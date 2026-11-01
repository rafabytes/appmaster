<?php
/**
 * Funções utilitárias do tema.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lê um campo ACF (com fallback para post meta se o ACF estiver desativado).
 *
 * @param string     $name    Nome do campo.
 * @param int|string $post_id ID do post ou 'option'.
 * @param mixed      $default Valor quando vazio.
 */
function am_field( $name, $post_id = false, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, $post_id );
	} elseif ( 'option' === $post_id ) {
		$value = get_option( 'options_' . $name );
	} else {
		$value = get_post_meta( $post_id ? $post_id : get_the_ID(), $name, true );
	}

	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $default : $value;
}

/**
 * Lê um campo da página de opções "appMaster".
 */
function am_option( $name, $default = '' ) {
	return am_field( $name, 'option', $default );
}

/**
 * Nome curto exibido no prompt / logo.
 */
function am_brand() {
	return am_option( 'brand_name', get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'appMaster' );
}

/**
 * Linha de prompt estilo shell: usuario@host:~/path$ comando
 */
function am_prompt( $command = '', $path = '~' ) {
	$user = am_option( 'prompt_user', 'visitante' );
	$host = strtolower( am_brand() );
	?>
	<p class="prompt">
		<span class="prompt__user"><?php echo esc_html( $user . '@' . $host ); ?></span><span class="text-term-dim">:</span><span class="prompt__path"><?php echo esc_html( $path ); ?></span><span class="text-term-dim">$</span>
		<?php if ( $command ) : ?>
			<span class="prompt__cmd"><?php echo esc_html( $command ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Abre uma "janela" de terminal.
 */
function am_window_open( $title = '', $tag = 'section', $attrs = '' ) {
	printf( '<%1$s class="term-window" %2$s>', tag_escape( $tag ), $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput -- attrs montados internamente.
	?>
	<div class="term-window__bar" aria-hidden="true">
		<span class="term-window__dots"><i></i><i></i><i></i></span>
		<span class="term-window__title"><?php echo esc_html( $title ); ?></span>
	</div>
	<div class="term-window__body">
	<?php
}

function am_window_close( $tag = 'section' ) {
	printf( '</div></%s>', tag_escape( $tag ) );
}

/**
 * Renderiza um campo "link" do ACF como botão.
 *
 * @param array|string $link  Array do ACF (url, title, target).
 * @param string       $class Classes extras.
 */
function am_link( $link, $class = 'btn' ) {
	if ( empty( $link['url'] ) ) {
		return;
	}
	$external = ! empty( $link['target'] ) && '_blank' === $link['target'];
	printf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s%5$s</a>',
		esc_attr( $class ),
		esc_url( $link['url'] ),
		$external ? ' target="_blank" rel="noopener"' : '',
		esc_html( $link['title'] ? $link['title'] : am_domain( $link['url'] ) ),
		$external ? am_icon( 'external', 'h-4 w-4' ) . '<span class="sr-only">(abre em nova aba)</span>' : '' // phpcs:ignore WordPress.Security.EscapeOutput
	);
}

/**
 * Domínio legível a partir de uma URL.
 */
function am_domain( $url ) {
	$host = wp_parse_url( (string) $url, PHP_URL_HOST );
	return $host ? preg_replace( '/^www\./', '', $host ) : '';
}

/**
 * Caminho "owner/repo" a partir de uma URL do GitHub.
 */
function am_github_path( $url ) {
	$path = trim( (string) wp_parse_url( (string) $url, PHP_URL_PATH ), '/' );
	return $path ? $path : am_domain( $url );
}

/**
 * URL da primeira página publicada que usa determinado template.
 */
function am_page_url( $template ) {
	$pages = get_pages(
		array(
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $template, // phpcs:ignore WordPress.DB.SlowDBQuery
			'number'      => 1,
			'post_status' => 'publish',
		)
	);
	return $pages ? get_permalink( $pages[0] ) : '';
}

/**
 * Projetos publicados, na ordem definida no painel.
 *
 * @return WP_Post[]
 */
function am_get_projects() {
	return get_posts(
		array(
			'post_type'      => 'projeto',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
		)
	);
}

/**
 * Dados normalizados de um projeto.
 */
function am_project( $post ) {
	$post = get_post( $post );
	$tags = array_filter( array_map( 'trim', explode( ',', (string) am_field( 'tags', $post->ID ) ) ) );

	return array(
		'id'      => $post->ID,
		'slug'    => $post->post_name,
		'name'    => get_the_title( $post ),
		'url'     => (string) am_field( 'url', $post->ID ),
		'github'  => (string) am_field( 'github', $post->ID ),
		'summary' => (string) am_field( 'resumo', $post->ID ),
		'tags'    => array_values( $tags ),
		'status'  => (string) am_field( 'status', $post->ID, 'online' ),
		'icon'    => (string) am_field( 'icone', $post->ID, 'terminal' ),
		'image'   => am_field( 'icone_personalizado', $post->ID ),
	);
}

/**
 * Rótulos de status dos projetos.
 */
function am_status_labels() {
	return array(
		'online'  => 'online',
		'beta'    => 'beta',
		'dev'     => 'em desenvolvimento',
		'offline' => 'offline',
	);
}

/**
 * HTML do ícone de um projeto (upload personalizado ou ícone embutido).
 */
function am_project_icon( array $project, $class = '' ) {
	$image = $project['image'];
	if ( $image ) {
		$id = is_array( $image ) ? (int) $image['ID'] : (int) $image;
		if ( $id ) {
			return wp_get_attachment_image( $id, 'thumbnail', false, array( 'class' => $class, 'alt' => '' ) );
		}
	}
	return am_icon( $project['icon'], $class );
}
