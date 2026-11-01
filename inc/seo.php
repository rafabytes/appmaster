<?php
/**
 * SEO: meta tags, Open Graph, dados estruturados, robots, sitemap e favicon.
 *
 * Se um plugin de SEO (Yoast, Rank Math, AIOSEO, SEOPress) estiver ativo,
 * as meta tags e o JSON-LD do tema são desativados para evitar duplicidade.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/**
 * Há plugin de SEO cuidando do <head>?
 */
function am_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * ID da página atual (inclui a home estática).
 */
function am_seo_page_id() {
	return is_singular() ? get_queried_object_id() : 0;
}

/**
 * Descrição da página: campo SEO → resumo → descrição padrão.
 */
function am_seo_description() {
	$id          = am_seo_page_id();
	$description = '';

	if ( $id ) {
		$description = am_field( 'seo_description', $id );
		if ( ! $description && has_excerpt( $id ) ) {
			$description = get_the_excerpt( $id );
		}
	}
	if ( ! $description ) {
		$description = am_option( 'meta_description', get_bloginfo( 'description' ) );
	}

	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $description ) ) );
}

/**
 * Imagem de compartilhamento: da página → padrão das opções → imagem do tema.
 *
 * @return array{url:string,width:int,height:int,alt:string}
 */
function am_seo_image() {
	$id    = am_seo_page_id();
	$image = $id ? (int) am_field( 'seo_image', $id ) : 0;
	if ( ! $image ) {
		$image = (int) am_option( 'og_image' );
	}

	if ( $image ) {
		$src = wp_get_attachment_image_src( $image, 'full' );
		if ( $src ) {
			$alt = (string) get_post_meta( $image, '_wp_attachment_image_alt', true );
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => $alt ? $alt : am_brand(),
			);
		}
	}

	return array(
		'url'    => APPMASTER_URI . '/assets/img/og-default.png',
		'width'  => 1200,
		'height' => 630,
		'alt'    => am_brand() . ' — ' . get_bloginfo( 'description' ),
	);
}

/**
 * URL canônica da página atual.
 */
function am_seo_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		$url = wp_get_canonical_url();
		if ( $url ) {
			return $url;
		}
	}
	global $wp;
	return home_url( trailingslashit( $wp->request ?? '' ) );
}

/**
 * URL do logo/ícone (ícone do site ou fallback do tema).
 */
function am_seo_logo() {
	$icon = get_site_icon_url( 512 );
	return $icon ? $icon : APPMASTER_URI . '/assets/img/icon-512.png';
}

/*
 * Título: campo "Título para buscadores" e separador.
 */
add_filter( 'document_title_separator', fn() => '—' );

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( am_seo_plugin_active() ) {
			return $title;
		}
		$id     = am_seo_page_id();
		$custom = $id ? am_field( 'seo_title', $id ) : '';
		return $custom ? $custom : $title;
	}
);

/*
 * Robots: noindex em 404, busca, arquivos (o site não tem blog) e páginas marcadas.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		$id = am_seo_page_id();
		if ( is_404() || is_search() || is_archive() || ( is_home() && ! is_front_page() ) || ( $id && am_field( 'seo_noindex', $id ) ) ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

/*
 * Meta tags + Open Graph + Twitter.
 */
add_action(
	'wp_head',
	function () {
		echo '<meta name="theme-color" content="#0a0c0b">' . "\n";

		if ( am_seo_plugin_active() ) {
			return;
		}

		$description = am_seo_description();
		$image       = am_seo_image();
		$title       = wp_get_document_title();
		$url         = am_seo_url();

		$tags = array(
			array( 'name', 'description', $description ),
			array( 'property', 'og:locale', get_locale() ),
			array( 'property', 'og:type', 'website' ),
			array( 'property', 'og:site_name', am_brand() ),
			array( 'property', 'og:title', $title ),
			array( 'property', 'og:description', $description ),
			array( 'property', 'og:url', $url ),
			array( 'property', 'og:image', $image['url'] ),
			array( 'property', 'og:image:width', $image['width'] ),
			array( 'property', 'og:image:height', $image['height'] ),
			array( 'property', 'og:image:alt', $image['alt'] ),
			array( 'name', 'twitter:card', 'summary_large_image' ),
			array( 'name', 'twitter:title', $title ),
			array( 'name', 'twitter:description', $description ),
			array( 'name', 'twitter:image', $image['url'] ),
			array( 'name', 'twitter:image:alt', $image['alt'] ),
		);

		echo "<!-- appMaster SEO -->\n";
		foreach ( $tags as list( $attr, $key, $value ) ) {
			if ( '' !== (string) $value ) {
				printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $attr ), esc_attr( $key ), esc_attr( $value ) );
			}
		}
	},
	1
);

/*
 * Dados estruturados (JSON-LD / schema.org).
 */
add_action(
	'wp_head',
	function () {
		if ( am_seo_plugin_active() || is_404() ) {
			return;
		}

		$home   = home_url( '/' );
		$org_id = $home . '#organization';
		$web_id = $home . '#website';
		$github = am_option( 'github_profile' );

		$graph = array(
			array_filter(
				array(
					'@type'       => 'Organization',
					'@id'         => $org_id,
					'name'        => am_option( 'organization_name', am_brand() ),
					'url'         => $home,
					'logo'        => array(
						'@type' => 'ImageObject',
						'url'   => am_seo_logo(),
					),
					'description' => am_option( 'meta_description' ),
					'sameAs'      => $github ? array( $github ) : null,
				)
			),
			array(
				'@type'       => 'WebSite',
				'@id'         => $web_id,
				'url'         => $home,
				'name'        => am_brand(),
				'description' => get_bloginfo( 'description' ),
				'publisher'   => array( '@id' => $org_id ),
				'inLanguage'  => get_bloginfo( 'language' ),
			),
		);

		$id = am_seo_page_id();
		if ( $id ) {
			$template = get_page_template_slug( $id );
			$type     = 'WebPage';
			if ( 'page-templates/sobre.php' === $template ) {
				$type = 'AboutPage';
			} elseif ( 'page-templates/portfolio.php' === $template ) {
				$type = 'CollectionPage';
			}

			$page = array(
				'@type'              => $type,
				'@id'                => am_seo_url() . '#webpage',
				'url'                => am_seo_url(),
				'name'               => wp_get_document_title(),
				'description'        => am_seo_description(),
				'isPartOf'           => array( '@id' => $web_id ),
				'about'              => array( '@id' => $org_id ),
				'inLanguage'         => get_bloginfo( 'language' ),
				'primaryImageOfPage' => array(
					'@type' => 'ImageObject',
					'url'   => am_seo_image()['url'],
				),
			);

			// Lista de projetos na home e no portfolio.
			if ( is_front_page() || 'page-templates/portfolio.php' === $template ) {
				$items = array();
				foreach ( am_get_projects() as $i => $post ) {
					$p = am_project( $post );
					if ( ! $p['url'] ) {
						continue;
					}
					$items[] = array(
						'@type'    => 'ListItem',
						'position' => $i + 1,
						'item'     => array_filter(
							array(
								'@type'               => 'WebApplication',
								'name'                => $p['name'],
								'url'                 => $p['url'],
								'description'         => $p['summary'],
								'applicationCategory' => 'WebApplication',
								'operatingSystem'     => 'Web',
								'creator'             => array( '@id' => $org_id ),
								'keywords'            => $p['tags'] ? implode( ', ', $p['tags'] ) : null,
								'subjectOf'           => $p['github'] ? array(
									'@type'          => 'SoftwareSourceCode',
									'codeRepository' => $p['github'],
								) : null,
							)
						),
					);
				}
				if ( $items ) {
					$page['mainEntity'] = array(
						'@type'           => 'ItemList',
						'numberOfItems'   => count( $items ),
						'itemListElement' => $items,
					);
				}
			}

			$graph[] = $page;
		}

		printf(
			'<script type="application/ld+json" data-no-optimize="1">%s</script>' . "\n",
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $graph,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
			)
		);
	},
	5
);

/*
 * Favicon de fallback (quando nenhum "Ícone do site" foi definido no painel).
 */
$am_icon_fallback = function () {
	if ( has_site_icon() ) {
		return;
	}
	$base = APPMASTER_URI . '/assets/img/';
	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( $base . 'icon-32.png' ) );
	printf( '<link rel="icon" href="%s" sizes="512x512">' . "\n", esc_url( $base . 'icon-512.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'icon-180.png' ) );
};
add_action( 'wp_head', $am_icon_fallback, 99 );
add_action( 'login_head', $am_icon_fallback, 99 );
add_action( 'admin_head', $am_icon_fallback, 99 );

/*
 * Sitemap (wp-sitemap.xml): sem usuários, sem posts se não houver blog,
 * sem páginas marcadas como "Ocultar dos buscadores".
 */
add_filter(
	'wp_sitemaps_add_provider',
	fn( $provider, $name ) => 'users' === $name ? false : $provider,
	10,
	2
);

add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		if ( isset( $types['post'] ) && ! wp_count_posts( 'post' )->publish ) {
			unset( $types['post'] );
		}
		return $types;
	}
);

add_filter(
	'wp_sitemaps_taxonomies',
	fn( $taxonomies ) => wp_count_posts( 'post' )->publish ? $taxonomies : array()
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array(
					'key'     => 'seo_noindex',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'seo_noindex',
					'value'   => '1',
					'compare' => '!=',
				),
			);
		}
		return $args;
	},
	10,
	2
);

/*
 * Redireciona arquivos de autor (evita expor nomes de usuário) e páginas de anexo.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
		if ( is_attachment() ) {
			$url = wp_get_attachment_url( get_queried_object_id() );
			wp_safe_redirect( $url ? $url : home_url( '/' ), 301 );
			exit;
		}
	},
	1 // Antes do redirect_canonical, que levaria ?author=1 para /author/<usuario>/.
);

/*
 * Aviso no painel se o site estiver bloqueado para buscadores.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! get_option( 'blog_public' ) && current_user_can( 'manage_options' ) ) {
			printf(
				'<div class="notice notice-warning"><p><strong>appMaster:</strong> o site está oculto dos buscadores. Para indexar, desmarque a opção em <a href="%s">Configurações → Leitura</a>.</p></div>',
				esc_url( admin_url( 'options-reading.php' ) )
			);
		}
	}
);
