<?php
/**
 * Post type "projeto" (portfolio).
 *
 * Os projetos não têm página própria: o card leva direto ao app e ao GitHub.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'projeto',
			array(
				'labels'              => array(
					'name'               => 'Projetos',
					'singular_name'      => 'Projeto',
					'menu_name'          => 'Projetos',
					'add_new'            => 'Adicionar projeto',
					'add_new_item'       => 'Adicionar novo projeto',
					'edit_item'          => 'Editar projeto',
					'new_item'           => 'Novo projeto',
					'view_item'          => 'Ver projeto',
					'search_items'       => 'Buscar projetos',
					'not_found'          => 'Nenhum projeto encontrado',
					'not_found_in_trash' => 'Nenhum projeto na lixeira',
					'all_items'          => 'Todos os projetos',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'menu_position'       => 21,
				'menu_icon'           => 'dashicons-editor-code',
				'supports'            => array( 'title', 'page-attributes' ),
			)
		);
	}
);

/*
 * Lista no admin: ordem, domínio e status.
 */
add_filter(
	'manage_projeto_posts_columns',
	function ( $columns ) {
		return array(
			'cb'        => $columns['cb'],
			'am_icon'   => '',
			'title'     => 'Projeto',
			'am_url'    => 'Endereço',
			'am_status' => 'Status',
			'am_order'  => 'Ordem',
		);
	}
);

add_action(
	'manage_projeto_posts_custom_column',
	function ( $column, $post_id ) {
		$project = am_project( $post_id );
		switch ( $column ) {
			case 'am_icon':
				echo am_project_icon( $project, 'am-admin-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput
				break;
			case 'am_url':
				echo $project['url'] ? '<a href="' . esc_url( $project['url'] ) . '" target="_blank" rel="noopener">' . esc_html( am_domain( $project['url'] ) ) . '</a>' : '—';
				break;
			case 'am_status':
				$labels = am_status_labels();
				echo esc_html( $labels[ $project['status'] ] ?? $project['status'] );
				break;
			case 'am_order':
				echo (int) get_post_field( 'menu_order', $post_id );
				break;
		}
	},
	10,
	2
);

add_action(
	'admin_head-edit.php',
	function () {
		if ( 'projeto' === get_current_screen()->post_type ) {
			echo '<style>.column-am_icon{width:36px}.am-admin-icon{width:24px;height:24px;color:#1d2327}.column-am_order{width:70px}</style>';
		}
	}
);

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() && $query->is_main_query() && 'projeto' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
	}
);
