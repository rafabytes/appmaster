<?php
/**
 * Sincronização de conteúdo via Git (content/site.json).
 *
 * Ambiente local (fonte da verdade): ao salvar qualquer conteúdo no painel, o tema
 * exporta páginas, campos ACF, projetos, menu e opções para content/site.json.
 * Commit + push + deploy no cPanel levam o arquivo para produção.
 *
 * Qualquer ambiente: quando o site.json muda (hash diferente do último importado),
 * o conteúdo é importado automaticamente e o cache do LiteSpeed é limpo.
 * Também serve como conteúdo inicial em instalações novas.
 *
 * Atenção: em produção, edições feitas direto no painel são sobrescritas na próxima
 * vez que um site.json diferente for publicado. Edite no ambiente local.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

define( 'AM_CONTENT_FILE', APPMASTER_DIR . '/content/site.json' );
define( 'AM_CONTENT_URL_TOKEN', '{{site_url}}' );

/**
 * Este ambiente exporta conteúdo? (local/desenvolvimento ou domínio .local)
 */
function am_sync_is_source() {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	return in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) || str_ends_with( $host, '.local' ) || 'localhost' === $host;
}

function am_sync_file_hash() {
	return file_exists( AM_CONTENT_FILE ) ? md5_file( AM_CONTENT_FILE ) : '';
}

/**
 * Troca a URL do site por um token (e vice-versa), recursivamente.
 */
function am_sync_urls( $value, $to_token ) {
	if ( is_array( $value ) ) {
		return array_map( fn( $v ) => am_sync_urls( $v, $to_token ), $value );
	}
	// Normaliza escalares (0 e "0" geram o mesmo arquivo).
	if ( is_bool( $value ) ) {
		return $value ? '1' : '0';
	}
	if ( is_int( $value ) || is_float( $value ) ) {
		return (string) $value;
	}
	if ( ! is_string( $value ) ) {
		return $value;
	}
	$site = untrailingslashit( home_url() );
	return $to_token ? str_replace( $site, AM_CONTENT_URL_TOKEN, $value ) : str_replace( AM_CONTENT_URL_TOKEN, $site, $value );
}

/**
 * Campos ACF (exceto abas) de um post ou das opções, em valor bruto.
 */
function am_sync_fields_export( $post_id ) {
	$groups = 'option' === $post_id
		? array( acf_get_field_group( 'group_am_options' ) )
		: acf_get_field_groups( array( 'post_id' => $post_id ) );

	$out = array();
	foreach ( array_filter( $groups ) as $group ) {
		foreach ( acf_get_fields( $group ) as $field ) {
			if ( in_array( $field['type'], array( 'tab', 'message', 'accordion' ), true ) ) {
				continue;
			}
			$value = get_field( $field['key'], $post_id, false );
			if ( 'image' === $field['type'] || 'file' === $field['type'] ) {
				// Mídia é referenciada pelo caminho em uploads/ (IDs mudam entre ambientes).
				$file  = $value ? get_post_meta( (int) $value, '_wp_attached_file', true ) : '';
				$value = $file ? array( '__attachment' => $file ) : '';
			}
			$out[ $field['key'] ] = am_sync_urls( $value, true );
		}
	}
	return $out;
}

function am_sync_fields_import( array $fields, $post_id ) {
	foreach ( $fields as $key => $value ) {
		if ( is_array( $value ) && isset( $value['__attachment'] ) ) {
			$found = get_posts(
				array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_attached_file', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'  => $value['__attachment'], // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
			if ( ! $found ) {
				continue; // Mídia não existe neste ambiente: mantém o valor atual.
			}
			$value = $found[0];
		}
		update_field( $key, am_sync_urls( $value, false ), $post_id );
	}
}

/**
 * Monta o array de conteúdo do site.
 */
function am_sync_build() {
	$post_data = function ( WP_Post $post ) {
		return array(
			'slug'       => $post->post_name,
			'title'      => am_sync_urls( $post->post_title, true ),
			'status'     => $post->post_status,
			'menu_order' => (int) $post->menu_order,
			'fields'     => am_sync_fields_export( $post->ID ),
		);
	};

	$pages = array();
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'private' ), 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $page ) {
		$pages[] = array_merge(
			$post_data( $page ),
			array(
				'template' => (string) get_page_template_slug( $page ),
				'excerpt'  => am_sync_urls( $page->post_excerpt, true ),
				'content'  => am_sync_urls( $page->post_content, true ),
			)
		);
	}

	$projects = array();
	foreach ( get_posts( array( 'post_type' => 'projeto', 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ) ) ) as $project ) {
		$projects[] = $post_data( $project );
	}

	$menus     = array();
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( array( 'primary', 'footer' ) as $location ) {
		if ( empty( $locations[ $location ] ) ) {
			continue;
		}
		$items = array();
		foreach ( (array) wp_get_nav_menu_items( $locations[ $location ] ) as $item ) {
			$entry = array( 'title' => $item->title );
			if ( 'post_type' === $item->type && 'page' === $item->object ) {
				$entry['page'] = get_post_field( 'post_name', $item->object_id );
			} else {
				$entry['url']    = am_sync_urls( $item->url, true );
				$entry['target'] = $item->target;
			}
			$items[] = $entry;
		}
		$menus[ $location ] = $items;
	}

	$front = (int) get_option( 'page_on_front' );

	return array(
		'version'    => 1,
		'site'       => array(
			'blogname'        => get_option( 'blogname' ),
			'blogdescription' => get_option( 'blogdescription' ),
		),
		'front_page' => $front ? get_post_field( 'post_name', $front ) : '',
		'options'    => am_sync_fields_export( 'option' ),
		'pages'      => $pages,
		'projects'   => $projects,
		'menus'      => $menus,
	);
}

/**
 * Grava content/site.json.
 */
function am_sync_export() {
	if ( ! function_exists( 'acf_get_fields' ) ) {
		return false;
	}
	wp_mkdir_p( dirname( AM_CONTENT_FILE ) );
	$json = wp_json_encode( am_sync_build(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
	if ( false === file_put_contents( AM_CONTENT_FILE, $json ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		return false;
	}
	update_option( 'appmaster_content_hash', am_sync_file_hash(), false );
	return true;
}

/**
 * Cria/atualiza um post pelo slug.
 */
function am_sync_upsert( array $data, $post_type, array $extra = array() ) {
	$existing = get_posts(
		array(
			'post_type'   => $post_type,
			'name'        => $data['slug'],
			'post_status' => 'any',
			'numberposts' => 1,
		)
	);

	$postarr = array_merge(
		array(
			'post_type'   => $post_type,
			'post_name'   => $data['slug'],
			'post_title'  => am_sync_urls( $data['title'], false ),
			'post_status' => $data['status'],
			'menu_order'  => $data['menu_order'],
		),
		$extra
	);

	if ( $existing ) {
		$postarr['ID'] = $existing[0]->ID;
		$id            = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$id = wp_insert_post( wp_slash( $postarr ), true );
	}

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	am_sync_fields_import( $data['fields'], $id );
	return (int) $id;
}

/**
 * Importa content/site.json.
 */
function am_sync_import() {
	if ( ! function_exists( 'update_field' ) || ! file_exists( AM_CONTENT_FILE ) ) {
		return false;
	}
	$data = json_decode( (string) file_get_contents( AM_CONTENT_FILE ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $data ) || 1 !== ( $data['version'] ?? 0 ) ) {
		return false;
	}

	// Evita disparar uma nova exportação durante a importação.
	$GLOBALS['am_sync_importing'] = true;

	foreach ( (array) ( $data['site'] ?? array() ) as $option => $value ) {
		if ( in_array( $option, array( 'blogname', 'blogdescription' ), true ) ) {
			update_option( $option, $value );
		}
	}

	am_sync_fields_import( (array) ( $data['options'] ?? array() ), 'option' );

	$page_ids = array();
	foreach ( (array) ( $data['pages'] ?? array() ) as $page ) {
		$id = am_sync_upsert(
			$page,
			'page',
			array(
				'post_content'  => am_sync_urls( $page['content'], false ),
				'post_excerpt'  => am_sync_urls( $page['excerpt'], false ),
				'page_template' => $page['template'] ? $page['template'] : 'default',
			)
		);
		if ( $id ) {
			$page_ids[ $page['slug'] ] = $id;
		}
	}

	// Projetos: espelha a lista (o que não está no arquivo vai para a lixeira).
	$keep = array();
	foreach ( (array) ( $data['projects'] ?? array() ) as $project ) {
		$keep[] = am_sync_upsert( $project, 'projeto' );
	}
	foreach ( get_posts( array( 'post_type' => 'projeto', 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => -1, 'fields' => 'ids' ) ) as $id ) {
		if ( ! in_array( $id, $keep, true ) ) {
			wp_trash_post( $id );
		}
	}

	if ( ! empty( $data['front_page'] ) && isset( $page_ids[ $data['front_page'] ] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids[ $data['front_page'] ] );
	}

	// Menus: recria os itens de cada local.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$names     = array(
		'primary' => 'Menu principal',
		'footer'  => 'Links do rodapé',
	);
	foreach ( (array) ( $data['menus'] ?? array() ) as $location => $items ) {
		if ( ! isset( $names[ $location ] ) ) {
			continue;
		}
		$menu_id = $locations[ $location ] ?? 0;
		if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
			$menu    = wp_get_nav_menu_object( $names[ $location ] );
			$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $names[ $location ] );
		}
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $old ) {
			wp_delete_post( $old->ID, true );
		}
		foreach ( $items as $position => $item ) {
			$args = array(
				'menu-item-title'    => $item['title'],
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position + 1,
			);
			if ( ! empty( $item['page'] ) ) {
				if ( empty( $page_ids[ $item['page'] ] ) ) {
					continue;
				}
				$args += array(
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $item['page'] ],
					'menu-item-type'      => 'post_type',
				);
			} else {
				$args += array(
					'menu-item-url'    => am_sync_urls( $item['url'], false ),
					'menu-item-type'   => 'custom',
					'menu-item-target' => $item['target'] ?? '',
				);
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'appmaster_content_hash', am_sync_file_hash(), false );
	update_option( 'appmaster_content_imported', current_time( 'mysql' ), false );
	unset( $GLOBALS['am_sync_importing'] );

	do_action( 'litespeed_purge_all' );
	return true;
}

/*
 * Importação automática quando o site.json muda (ex.: depois do deploy).
 */
add_action(
	'init',
	function () {
		$hash = am_sync_file_hash();
		if ( ! $hash || get_option( 'appmaster_content_hash' ) === $hash || ! function_exists( 'update_field' ) ) {
			return;
		}
		// Trava simples para não importar em paralelo.
		if ( ! add_option( 'appmaster_content_lock', time(), '', false ) ) {
			if ( time() - (int) get_option( 'appmaster_content_lock' ) < 120 ) {
				return;
			}
			update_option( 'appmaster_content_lock', time(), false );
		}
		am_sync_import();
		delete_option( 'appmaster_content_lock' );
	},
	99
);

/*
 * Exportação automática no ambiente local: agenda para o fim da requisição.
 */
function am_sync_schedule_export() {
	if ( ! empty( $GLOBALS['am_sync_importing'] ) || ! am_sync_is_source() || has_action( 'shutdown', 'am_sync_export' ) ) {
		return;
	}
	add_action( 'shutdown', 'am_sync_export' );
}

add_action( 'acf/save_post', 'am_sync_schedule_export', 30 );
add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( in_array( $post->post_type, array( 'page', 'projeto' ), true ) && ! wp_is_post_revision( $post_id ) && ! wp_is_post_autosave( $post_id ) ) {
			am_sync_schedule_export();
		}
	},
	30,
	2
);
add_action( 'deleted_post', 'am_sync_schedule_export' );
add_action( 'trashed_post', 'am_sync_schedule_export' );
add_action( 'wp_update_nav_menu', 'am_sync_schedule_export' );
add_action( 'update_option_blogname', 'am_sync_schedule_export' );
add_action( 'update_option_blogdescription', 'am_sync_schedule_export' );
add_action( 'update_option_page_on_front', 'am_sync_schedule_export' );

/*
 * Status e ação manual na página de opções do appMaster.
 */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'appmaster-opcoes',
			'Sincronizar conteúdo',
			'Sincronizar conteúdo',
			'manage_options',
			'appmaster-sync',
			function () {
				$source = am_sync_is_source();
				$file   = file_exists( AM_CONTENT_FILE );
				$synced = $file && get_option( 'appmaster_content_hash' ) === am_sync_file_hash();
				?>
				<div class="wrap">
					<h1>Sincronizar conteúdo</h1>
					<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
						<div class="notice notice-success"><p>Pronto.</p></div>
					<?php endif; ?>
					<p>O conteúdo do site (páginas, campos, projetos, menus e opções) fica em <code>wp-content/themes/<?php echo esc_html( get_template() ); ?>/content/site.json</code> e vai para produção junto com o tema pelo Git.</p>
					<ul style="list-style:disc;padding-left:20px">
						<li>Este ambiente: <strong><?php echo $source ? 'local — exporta o conteúdo a cada alteração' : 'produção — importa o conteúdo quando o site.json muda'; ?></strong></li>
						<li>Arquivo: <strong><?php echo $file ? esc_html( wp_date( 'd/m/Y H:i', filemtime( AM_CONTENT_FILE ) ) ) : 'não encontrado'; ?></strong></li>
						<li>Banco e arquivo: <strong><?php echo $synced ? 'sincronizados' : 'diferentes'; ?></strong></li>
						<?php if ( get_option( 'appmaster_content_imported' ) ) : ?>
							<li>Última importação: <strong><?php echo esc_html( get_option( 'appmaster_content_imported' ) ); ?></strong></li>
						<?php endif; ?>
					</ul>
					<?php if ( ! $source ) : ?>
						<p><strong>Atenção:</strong> alterações feitas aqui no painel serão sobrescritas no próximo deploy com um site.json diferente. Edite no ambiente local.</p>
					<?php endif; ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'appmaster_sync' ); ?>
						<input type="hidden" name="action" value="appmaster_sync">
						<?php if ( $source ) : ?>
							<button class="button button-primary" name="do" value="export">Exportar agora para o site.json</button>
						<?php endif; ?>
						<?php if ( $file ) : ?>
							<button class="button" name="do" value="import" onclick="return confirm('Substituir o conteúdo do banco pelo site.json?')">Importar do site.json</button>
						<?php endif; ?>
					</form>
				</div>
				<?php
			}
		);
	},
	20
);

add_action(
	'admin_post_appmaster_sync',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sem permissão.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'appmaster_sync' );
		$do = sanitize_key( $_POST['do'] ?? '' );
		if ( 'export' === $do && am_sync_is_source() ) {
			am_sync_export();
		} elseif ( 'import' === $do ) {
			am_sync_import();
		}
		wp_safe_redirect( admin_url( 'admin.php?page=appmaster-sync&done=1' ) );
		exit;
	}
);
