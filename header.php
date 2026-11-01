<?php
/**
 * Cabeçalho.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

$am_github = am_option( 'github_profile' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#conteudo">pular para o conteúdo</a>
<div class="crt" aria-hidden="true"></div>

<header class="site-header">
	<div class="container flex items-center justify-between gap-6 py-5">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( am_brand() ); ?> — início">
			<span class="text-term-green" aria-hidden="true">&gt;_</span>
			<span><?php echo esc_html( am_brand() ); ?></span>
		</a>

		<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false">
			<span class="nav-toggle__open">[ menu ]</span>
			<span class="nav-toggle__close">[ fechar ]</span>
		</button>

		<nav id="site-nav" class="site-nav" aria-label="Menu principal">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-list',
					'depth'          => 1,
					'fallback_cb'    => 'am_menu_fallback',
				)
			);
			?>
			<?php if ( $am_github ) : ?>
				<a class="nav-github" href="<?php echo esc_url( $am_github ); ?>" target="_blank" rel="noopener">
					<?php echo am_icon( 'git', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span>github</span>
				</a>
			<?php endif; ?>
		</nav>
	</div>
</header>

<main id="swup" class="transition-main">
	<div id="conteudo" tabindex="-1" class="outline-none">
