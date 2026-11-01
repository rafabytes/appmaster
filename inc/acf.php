<?php
/**
 * Página de opções e grupos de campos ACF (registrados em código).
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

/*
 * Aviso caso o ACF PRO não esteja ativo.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p><strong>appMaster:</strong> ative o plugin <em>Advanced Custom Fields PRO</em> para editar os conteúdos do site.</p></div>';
		}
	}
);

/**
 * Atalhos para montar campos.
 */
function am_acf_tab( $key, $label ) {
	return array(
		'key'       => 'field_am_' . $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

function am_acf( $key, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_am_' . $key,
			'name'  => $key,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

add_action(
	'acf/init',
	function () {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				array(
					'page_title' => 'Configurações do appMaster',
					'menu_title' => 'appMaster',
					'menu_slug'  => 'appmaster-opcoes',
					'capability' => 'edit_theme_options',
					'icon_url'   => 'dashicons-editor-code',
					'position'   => 59,
					'redirect'   => false,
				)
			);
		}

		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		/*
		 * Opções globais.
		 */
		acf_add_local_field_group(
			array(
				'key'      => 'group_am_options',
				'title'    => 'Configurações gerais',
				'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'appmaster-opcoes' ) ) ),
				'fields'   => array(
					am_acf_tab( 'opt_tab_geral', 'Geral' ),
					am_acf( 'brand_name', 'Nome no logo / prompt', 'text', array( 'default_value' => 'appMaster', 'instructions' => 'Exibido no cabeçalho e no prompt (ex.: visitante@appmaster).' ) ),
					am_acf( 'prompt_user', 'Usuário do prompt', 'text', array( 'default_value' => 'visitante' ) ),
					am_acf( 'github_profile', 'Perfil no GitHub', 'url', array( 'instructions' => 'Link exibido no cabeçalho e no rodapé.' ) ),
					am_acf( 'contact_email', 'E-mail de contato', 'email', array( 'instructions' => 'Opcional. Se preenchido, aparece no rodapé.' ) ),

					am_acf_tab( 'opt_tab_footer', 'Rodapé' ),
					am_acf( 'footer_text', 'Texto do rodapé', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
					am_acf( 'footer_credit', 'Crédito', 'link', array( 'instructions' => 'Ex.: "desenvolvido por rafabytes".', 'return_format' => 'array' ) ),

					am_acf_tab( 'opt_tab_login', 'Tela de login' ),
					am_acf( 'login_title', 'Título', 'text', array( 'default_value' => 'acesso restrito' ) ),
					am_acf( 'login_message', 'Mensagem', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'default_value' => 'Área administrativa do appMaster.' ) ),

					am_acf_tab( 'opt_tab_seo', 'SEO' ),
					am_acf( 'meta_description', 'Descrição padrão (meta description)', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'maxlength' => 300, 'instructions' => 'Usada quando a página não tem descrição própria (caixa "SEO" na edição da página). Ideal: 120 a 160 caracteres.' ) ),
					am_acf( 'og_image', 'Imagem de compartilhamento padrão', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'mime_types' => 'jpg,jpeg,png,webp', 'instructions' => 'Exibida ao compartilhar links em redes sociais e mensageiros. Tamanho ideal: 1200×630 px. Se vazio, usa a imagem padrão do tema.' ) ),
					am_acf( 'organization_name', 'Nome da organização (dados estruturados)', 'text', array( 'default_value' => 'appMaster', 'instructions' => 'Usado no schema.org Organization. O ícone do site (Aparência → Personalizar → Identidade do site) é usado como logo.' ) ),

					am_acf_tab( 'opt_tab_404', 'Página 404' ),
					am_acf( 'notfound_message', 'Mensagem', 'textarea', array( 'rows' => 2, 'new_lines' => '', 'default_value' => 'O endereço acessado não existe ou foi movido.' ) ),
				),
			)
		);

		/*
		 * SEO por página.
		 */
		acf_add_local_field_group(
			array(
				'key'        => 'group_am_seo',
				'title'      => 'SEO',
				'position'   => 'side',
				'menu_order' => 99,
				'location'   => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) ),
				'fields'     => array(
					am_acf( 'seo_title', 'Título para buscadores', 'text', array( 'maxlength' => 70, 'instructions' => 'Opcional. Substitui o título da página na aba e no Google.' ) ),
					am_acf( 'seo_description', 'Descrição', 'textarea', array( 'rows' => 4, 'new_lines' => '', 'maxlength' => 300, 'instructions' => 'Opcional. Ideal: 120 a 160 caracteres. Se vazio, usa o "Resumo" da página ou a descrição padrão.' ) ),
					am_acf( 'seo_image', 'Imagem de compartilhamento', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'mime_types' => 'jpg,jpeg,png,webp', 'instructions' => 'Opcional. 1200×630 px.' ) ),
					am_acf( 'seo_noindex', 'Ocultar dos buscadores', 'true_false', array( 'ui' => 1, 'instructions' => 'Adiciona noindex e remove a página do sitemap.' ) ),
				),
			)
		);

		/*
		 * Home.
		 */
		acf_add_local_field_group(
			array(
				'key'            => 'group_am_home',
				'title'          => 'Conteúdo da home',
				'location'       => array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ) ),
				'hide_on_screen' => array( 'the_content' ),
				'fields'         => array(
					am_acf_tab( 'home_tab_hero', 'Destaque' ),
					am_acf( 'hero_command', 'Comando do prompt', 'text', array( 'default_value' => 'whoami', 'instructions' => 'Texto que aparece depois do $.' ) ),
					am_acf( 'hero_title', 'Título', 'text' ),
					am_acf( 'hero_subtitle', 'Subtítulo', 'text' ),
					am_acf( 'hero_text', 'Texto', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
					am_acf( 'hero_badges', 'Selos', 'text', array( 'instructions' => 'Separados por vírgula. Ex.: wordpress, 100% web, sem instalação' ) ),
					am_acf( 'cta_primary', 'Botão principal', 'link', array( 'return_format' => 'array', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'cta_secondary', 'Botão secundário', 'link', array( 'return_format' => 'array', 'wrapper' => array( 'width' => 50 ) ) ),

					am_acf_tab( 'home_tab_terminal', 'Terminal interativo' ),
					am_acf( 'terminal_enabled', 'Exibir terminal interativo', 'true_false', array( 'ui' => 1, 'default_value' => 1, 'instructions' => 'Pequeno terminal onde o visitante digita comandos (help, ls, open…) para navegar pelos projetos.' ) ),
					am_acf( 'terminal_intro', 'Mensagem inicial', 'text', array( 'default_value' => 'digite "help" para ver os comandos disponíveis.' ) ),

					am_acf_tab( 'home_tab_projects', 'Projetos' ),
					am_acf( 'projects_command', 'Comando do prompt', 'text', array( 'default_value' => 'ls ~/projetos' ) ),
					am_acf( 'projects_title', 'Título', 'text' ),
					am_acf( 'projects_text', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
					am_acf( 'projects_link', 'Link "ver todos"', 'link', array( 'return_format' => 'array' ) ),

					am_acf_tab( 'home_tab_about', 'Sobre' ),
					am_acf( 'about_command', 'Comando do prompt', 'text', array( 'default_value' => 'cat sobre.txt' ) ),
					am_acf( 'about_title', 'Título', 'text' ),
					am_acf( 'about_text', 'Texto', 'textarea', array( 'rows' => 4, 'new_lines' => 'wpautop' ) ),
					am_acf( 'about_link', 'Link', 'link', array( 'return_format' => 'array' ) ),
				),
			)
		);

		/*
		 * Sobre.
		 */
		acf_add_local_field_group(
			array(
				'key'      => 'group_am_sobre',
				'title'    => 'Página Sobre',
				'position' => 'acf_after_title',
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/sobre.php' ) ) ),
				'fields'   => array(
					am_acf( 'page_command', 'Comando do prompt', 'text', array( 'key' => 'field_am_sobre_command', 'default_value' => 'cat sobre.txt' ) ),
					am_acf( 'lead', 'Introdução', 'textarea', array( 'key' => 'field_am_sobre_lead', 'rows' => 3, 'new_lines' => 'br', 'instructions' => 'Parágrafo de destaque. O texto completo vai no editor abaixo.' ) ),
					am_acf(
						'items',
						'Lista de destaques',
						'repeater',
						array(
							'layout'       => 'block',
							'button_label' => 'Adicionar item',
							'sub_fields'   => array(
								am_acf( 'item_title', 'Título', 'text' ),
								am_acf( 'item_text', 'Texto', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
							),
						)
					),
					am_acf( 'page_cta', 'Botão ao final', 'link', array( 'key' => 'field_am_sobre_cta', 'return_format' => 'array' ) ),
				),
			)
		);

		/*
		 * Portfolio.
		 */
		acf_add_local_field_group(
			array(
				'key'      => 'group_am_portfolio',
				'title'    => 'Página Portfolio',
				'position' => 'acf_after_title',
				'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/portfolio.php' ) ) ),
				'fields'   => array(
					am_acf( 'page_command', 'Comando do prompt', 'text', array( 'key' => 'field_am_portfolio_command', 'default_value' => 'ls -la ~/projetos' ) ),
					am_acf( 'lead', 'Introdução', 'textarea', array( 'key' => 'field_am_portfolio_lead', 'rows' => 3, 'new_lines' => 'br', 'instructions' => 'Os projetos são cadastrados no menu "Projetos".' ) ),
				),
			)
		);

		/*
		 * Projeto.
		 */
		acf_add_local_field_group(
			array(
				'key'            => 'group_am_projeto',
				'title'          => 'Dados do projeto',
				'position'       => 'acf_after_title',
				'location'       => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'projeto' ) ) ),
				'hide_on_screen' => array( 'the_content', 'excerpt', 'featured_image' ),
				'fields'         => array(
					am_acf( 'url', 'Endereço do projeto', 'url', array( 'required' => 1, 'placeholder' => 'https://', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'github', 'Repositório no GitHub', 'url', array( 'placeholder' => 'https://github.com/...', 'instructions' => 'Deixe vazio para ocultar o botão.', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'resumo', 'Resumo', 'textarea', array( 'required' => 1, 'rows' => 3, 'new_lines' => '', 'maxlength' => 320 ) ),
					am_acf( 'tags', 'Tags', 'text', array( 'instructions' => 'Separadas por vírgula. Ex.: PHP, MySQL', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'status', 'Status', 'select', array( 'choices' => am_status_labels(), 'default_value' => 'online', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'icone', 'Ícone', 'select', array( 'choices' => am_icon_choices(), 'default_value' => 'terminal', 'wrapper' => array( 'width' => 50 ) ) ),
					am_acf( 'icone_personalizado', 'Ícone personalizado', 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'library' => 'all', 'mime_types' => 'svg,png,webp', 'instructions' => 'Opcional. Substitui o ícone acima (de preferência SVG monocromático).', 'wrapper' => array( 'width' => 50 ) ) ),
				),
			)
		);
	}
);
