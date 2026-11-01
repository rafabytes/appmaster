<?php
/**
 * Conteúdo inicial: cria páginas, projetos, menu e opções na primeira execução.
 *
 * Roda uma única vez (opção "appmaster_seeded") e só cria o que ainda não existe.
 * Tudo pode ser editado depois pelo painel.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_init',
	function () {
		if ( get_option( 'appmaster_seeded' ) || ! function_exists( 'update_field' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		am_seed();
	}
);

/**
 * Cria (ou retorna) uma página pelo slug.
 */
function am_seed_page( $slug, $title, $template = '', $content = '', $excerpt = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return $page->ID;
	}
	return wp_insert_post(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name'     => $slug,
			'post_title'    => $title,
			'post_content'  => $content,
			'post_excerpt'  => $excerpt,
			'page_template' => $template,
		)
	);
}

/**
 * Link no formato do campo "link" do ACF.
 */
function am_seed_link( $title, $url, $external = false ) {
	return array(
		'title'  => $title,
		'url'    => $url,
		'target' => $external ? '_blank' : '',
	);
}

function am_seed() {
	$github = 'https://github.com/rafabytes';

	/*
	 * Opções gerais.
	 */
	$options = array(
		'field_am_brand_name'       => 'appMaster',
		'field_am_prompt_user'      => 'visitante',
		'field_am_github_profile'   => $github,
		'field_am_footer_text'      => 'Laboratório de IA sem fins lucrativos que publica soluções de código livre.',
		'field_am_footer_credit'    => am_seed_link( 'desenvolvido por rafabytes', 'https://rafabytes.com', true ),
		'field_am_login_title'      => 'acesso restrito',
		'field_am_login_message'    => 'Área administrativa do appMaster.',
		'field_am_meta_description' => 'appMaster: laboratório de IA sem fins lucrativos que publica soluções de código livre feitas com IA, prontas para usar no navegador, sem instalação.',
		'field_am_notfound_message' => 'O endereço acessado não existe ou foi movido.',
	);
	foreach ( $options as $key => $value ) {
		if ( ! get_field( $key, 'option' ) ) {
			update_field( $key, $value, 'option' );
		}
	}

	/*
	 * Projetos.
	 */
	$projects = array(
		array(
			'slug'   => 'amigo-secreto',
			'title'  => 'Amigo Secreto',
			'url'    => 'https://amigosecreto.appmaster.com.br',
			'github' => 'https://github.com/rafabytes/amigosecreto',
			'resumo' => 'Organize o amigo secreto da família, dos amigos ou do trabalho sem planilha: crie o grupo, convide os participantes por e-mail, monte listas de desejos com links e faixa de preço e faça o sorteio com um clique. Cada pessoa vê apenas quem tirou.',
			'tags'   => 'WordPress, PHP',
			'icone'  => 'gift',
		),
		array(
			'slug'   => 'get-a-live',
			'title'  => 'get a live',
			'url'    => 'https://getalive.appmaster.com.br',
			'github' => 'https://github.com/rafabytes/getalive',
			'resumo' => 'Catálogo de templates gratuitos de overlay animado para OBS Studio e Twitch: cenas de início, jogo, conversa, pausa e encerramento, chat, alertas e painéis. Escolha, favorite e baixe os templates direto pelo site.',
			'tags'   => 'WordPress, OBS Studio, Twitch',
			'icone'  => 'broadcast',
		),
		array(
			'slug'   => 'chefmix',
			'title'  => 'ChefMix',
			'url'    => 'https://chefmix.appmaster.com.br',
			'github' => 'https://github.com/rafabytes/chefmix',
			'resumo' => 'Cozinha guiada passo a passo: uma etapa por tela com quantidade, fogo e tempo, mise en place com porções ajustáveis, vários timers ao mesmo tempo e comandos de voz para cozinhar sem tocar na tela.',
			'tags'   => 'WordPress, PHP',
			'icone'  => 'chef',
		),
	);

	foreach ( $projects as $order => $data ) {
		if ( get_posts( array( 'post_type' => 'projeto', 'name' => $data['slug'], 'post_status' => 'any', 'numberposts' => 1 ) ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'projeto',
				'post_status' => 'publish',
				'post_name'   => $data['slug'],
				'post_title'  => $data['title'],
				'menu_order'  => $order + 1,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			foreach ( array( 'url', 'github', 'resumo', 'tags', 'icone' ) as $field ) {
				update_field( 'field_am_' . $field, $data[ $field ], $id );
			}
			update_field( 'field_am_status', 'online', $id );
		}
	}

	/*
	 * Páginas.
	 */
	$portfolio_id = am_seed_page(
		'portfolio',
		'Portfolio',
		'page-templates/portfolio.php',
		'',
		'Soluções de código livre feitas com IA pelo appMaster: Amigo Secreto, get a live e ChefMix. Plataformas WordPress que funcionam no navegador, sem instalação.'
	);
	update_field( 'field_am_portfolio_command', 'ls -la ~/projetos', $portfolio_id );
	update_field( 'field_am_portfolio_lead', 'Soluções de código livre feitas com IA e publicadas pelo appMaster. Todas são plataformas WordPress que funcionam direto no navegador, sem instalar nada: é só acessar o endereço. O código-fonte de cada uma está no GitHub.', $portfolio_id );

	$sobre_id = am_seed_page(
		'sobre',
		'Sobre o projeto',
		'page-templates/sobre.php',
		"O appMaster nasceu para transformar experimentos com inteligência artificial em ferramentas úteis e abertas. Os projetos são desenvolvidos com IA, publicados com o código-fonte aberto e mantidos sem intenção de fins lucrativos.\n\nTodas as plataformas são feitas em WordPress e funcionam direto na web: não é preciso baixar nem instalar nada, basta acessar o endereço pelo navegador, no computador ou no celular.\n\nCada plataforma resolve um problema específico e funciona de forma independente, no seu próprio subdomínio de appmaster.com.br. Este site reúne todas elas em um só lugar, com um resumo do que fazem, o link para acessar e o repositório com o código-fonte.\n\nO código é aberto: qualquer pessoa pode consultar, estudar, reportar problemas ou sugerir melhorias diretamente no GitHub.",
		'O appMaster é um laboratório de IA sem fins lucrativos que publica soluções de código livre feitas com IA.'
	);
	update_field( 'field_am_sobre_command', 'cat sobre.txt', $sobre_id );
	update_field( 'field_am_sobre_lead', 'O appMaster é um laboratório de IA que publica soluções de código livre feitas com inteligência artificial, sem intenção de fins lucrativos.', $sobre_id );
	update_field(
		'field_am_items',
		array(
			array(
				'field_am_item_title' => 'feito com IA',
				'field_am_item_text'  => 'Cada projeto é desenvolvido com apoio de inteligência artificial, do código à interface.',
			),
			array(
				'field_am_item_title' => 'sem fins lucrativos',
				'field_am_item_text'  => 'O objetivo não é comercial: é publicar soluções úteis e abertas para quem quiser usar.',
			),
			array(
				'field_am_item_title' => 'código livre',
				'field_am_item_text'  => 'O código-fonte de cada plataforma está publicado no GitHub para consulta, estudo e contribuição.',
			),
			array(
				'field_am_item_title' => 'sem instalação',
				'field_am_item_text'  => 'Plataformas WordPress que funcionam direto no navegador, no computador ou no celular.',
			),
		),
		$sobre_id
	);
	update_field( 'field_am_sobre_cta', am_seed_link( 'ver portfolio', get_permalink( $portfolio_id ) ), $sobre_id );

	$home_id = am_seed_page( 'home', 'Home' );
	$home    = array(
		'field_am_hero_command'     => 'whoami',
		'field_am_hero_title'       => 'appMaster',
		'field_am_hero_subtitle'    => 'laboratório de IA · código livre',
		'field_am_hero_text'        => 'Publicamos soluções de código livre feitas com inteligência artificial, sem fins lucrativos. Todas são plataformas WordPress que funcionam direto no navegador, sem instalação. Aqui você encontra o que cada uma faz, onde acessar e onde está o código-fonte.',
		'field_am_hero_badges'      => 'feito com IA, código livre, sem fins lucrativos, wordpress, sem instalação',
		'field_am_cta_primary'      => am_seed_link( 'ver portfolio', get_permalink( $portfolio_id ) ),
		'field_am_cta_secondary'    => am_seed_link( 'github', $github, true ),
		'field_am_terminal_enabled' => 1,
		'field_am_terminal_intro'   => 'digite "help" para ver os comandos disponíveis.',
		'field_am_projects_command' => 'ls ~/projetos',
		'field_am_projects_title'   => 'projetos publicados',
		'field_am_projects_text'    => 'Todos rodam em WordPress, direto no navegador: é só acessar e usar. Cada projeto tem endereço próprio e repositório público no GitHub.',
		'field_am_projects_link'    => am_seed_link( 'ver portfolio completo', get_permalink( $portfolio_id ) ),
		'field_am_about_command'    => 'cat sobre.txt',
		'field_am_about_title'      => 'sobre o projeto',
		'field_am_about_text'       => 'O appMaster é um laboratório de IA que publica soluções de código livre feitas com inteligência artificial, sem intenção de fins lucrativos. Cada projeto é uma plataforma WordPress que funciona direto na web, sem instalação, no seu próprio subdomínio, com o código-fonte aberto no GitHub.',
		'field_am_about_link'       => am_seed_link( 'ler mais', get_permalink( $sobre_id ) ),
	);
	foreach ( $home as $key => $value ) {
		update_field( $key, $value, $home_id );
	}

	if ( ! get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'laboratório de IA · soluções de código livre' );
	}
	/*
	 * SEO das páginas (títulos até ~60 caracteres, descrições até ~160).
	 */
	$seo = array(
		$home_id      => array( '', 'Laboratório de IA sem fins lucrativos que publica soluções de código livre feitas com IA: Amigo Secreto, get a live e ChefMix. Use direto no navegador.' ),
		$sobre_id     => array( 'Sobre o appMaster — laboratório de IA de código livre', 'Conheça o appMaster, laboratório de IA sem fins lucrativos que cria plataformas WordPress de código livre com IA, prontas para usar no navegador.' ),
		$portfolio_id => array( 'Portfolio de projetos de código livre — appMaster', 'Projetos do appMaster: Amigo Secreto, get a live e ChefMix. Plataformas de código livre feitas com IA, com link de acesso e repositório no GitHub.' ),
	);
	foreach ( $seo as $page_id => list( $title, $description ) ) {
		update_field( 'field_am_seo_title', $title, $page_id );
		update_field( 'field_am_seo_description', $description, $page_id );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );

	/*
	 * Menu principal.
	 */
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		$menu_id = wp_create_nav_menu( 'Menu principal' );
		if ( ! is_wp_error( $menu_id ) ) {
			foreach ( array( $home_id => 'home', $sobre_id => 'sobre', $portfolio_id => 'portfolio' ) as $page_id => $label ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $label,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page_id,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'appmaster_seeded', APPMASTER_VERSION );
}
