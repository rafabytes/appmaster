<?php
/**
 * Home.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();

$am_id       = get_queried_object_id();
$am_projects = array_map( 'am_project', am_get_projects() );
$am_host     = strtolower( am_brand() );
?>

<div class="container space-y-12 py-10 md:space-y-16 md:py-16">

	<?php am_window_open( am_option( 'prompt_user', 'visitante' ) . '@' . $am_host . ': ~', 'section', 'aria-labelledby="hero-title"' ); ?>
		<?php am_prompt( am_field( 'hero_command', $am_id, 'whoami' ) ); ?>

		<h1 id="hero-title" class="hero-title">
			<?php echo esc_html( am_field( 'hero_title', $am_id, am_brand() ) ); ?><span class="cursor" aria-hidden="true"></span>
		</h1>

		<?php $am_subtitle = am_field( 'hero_subtitle', $am_id ); ?>
		<?php if ( $am_subtitle ) : ?>
			<p class="hero-subtitle"><?php echo esc_html( $am_subtitle ); ?></p>
		<?php endif; ?>

		<?php $am_text = am_field( 'hero_text', $am_id ); ?>
		<?php if ( $am_text ) : ?>
			<p class="mt-4 max-w-2xl text-term-text"><?php echo wp_kses( $am_text, array( 'br' => array() ) ); ?></p>
		<?php endif; ?>

		<?php $am_badges = array_filter( array_map( 'trim', explode( ',', (string) am_field( 'hero_badges', $am_id ) ) ) ); ?>
		<?php if ( $am_badges ) : ?>
			<ul class="badges">
				<?php foreach ( $am_badges as $am_badge ) : ?>
					<li><?php echo esc_html( $am_badge ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="mt-8 flex flex-wrap gap-3">
			<?php am_link( am_field( 'cta_primary', $am_id ), 'btn btn--primary' ); ?>
			<?php am_link( am_field( 'cta_secondary', $am_id ) ); ?>
		</div>

		<?php if ( am_field( 'terminal_enabled', $am_id, function_exists( 'get_field' ) ? 0 : 1 ) && $am_projects ) : ?>
			<?php
			$am_terminal = array(
				'user'     => am_option( 'prompt_user', 'visitante' ),
				'host'     => $am_host,
				'intro'    => am_field( 'terminal_intro', $am_id, 'digite "help" para ver os comandos disponíveis.' ),
				'github'   => am_option( 'github_profile' ),
				'pages'    => array_filter(
					array(
						'home'      => home_url( '/' ),
						'sobre'     => am_page_url( 'page-templates/sobre.php' ),
						'portfolio' => am_page_url( 'page-templates/portfolio.php' ),
					)
				),
				'projects' => array_map(
					fn( $p ) => array(
						'slug'    => $p['slug'],
						'name'    => $p['name'],
						'url'     => $p['url'],
						'github'  => $p['github'],
						'summary' => $p['summary'],
					),
					$am_projects
				),
			);
			?>
			<div class="mini-terminal" data-react="terminal">
				<script type="application/json" data-no-optimize="1"><?php echo wp_json_encode( $am_terminal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
			</div>
		<?php endif; ?>
	<?php am_window_close(); ?>

	<?php if ( $am_projects ) : ?>
		<section aria-labelledby="projetos-title">
			<?php am_prompt( am_field( 'projects_command', $am_id, 'ls ~/projetos' ) ); ?>
			<h2 id="projetos-title" class="section-title"><?php echo esc_html( am_field( 'projects_title', $am_id, 'projetos' ) ); ?></h2>
			<?php $am_ptext = am_field( 'projects_text', $am_id ); ?>
			<?php if ( $am_ptext ) : ?>
				<p class="mt-2 max-w-2xl text-term-dim"><?php echo wp_kses( $am_ptext, array( 'br' => array() ) ); ?></p>
			<?php endif; ?>

			<div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $am_projects as $am_project ) : ?>
					<?php get_template_part( 'template-parts/project-card', null, array( 'project' => $am_project ) ); ?>
				<?php endforeach; ?>
			</div>

			<?php $am_plink = am_field( 'projects_link', $am_id ); ?>
			<?php if ( $am_plink ) : ?>
				<p class="mt-6"><?php am_link( $am_plink, 'link-arrow' ); ?></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php $am_about = am_field( 'about_text', $am_id ); ?>
	<?php if ( $am_about ) : ?>
		<?php
		$am_about_cmd = am_field( 'about_command', $am_id, 'cat sobre.txt' );
		$am_about_win = preg_match( '/(\S+)\s*$/', $am_about_cmd, $am_m ) ? $am_m[1] : 'sobre.txt'; // Arquivo do comando (ex.: cat LEIAME.md).
		?>
		<?php am_window_open( $am_about_win, 'section', 'aria-labelledby="sobre-title"' ); ?>
			<?php am_prompt( $am_about_cmd ); ?>
			<h2 id="sobre-title" class="section-title"><?php echo esc_html( am_field( 'about_title', $am_id, 'sobre' ) ); ?></h2>
			<div class="prose-term mt-4 max-w-3xl"><?php echo wp_kses_post( $am_about ); ?></div>

			<?php $am_about_items = am_field( 'about_items', $am_id, array() ); ?>
			<?php if ( $am_about_items ) : ?>
				<ul class="contrib-list">
					<?php foreach ( $am_about_items as $am_item ) : ?>
						<li>
							<h3 class="contrib-list__title"><?php echo esc_html( $am_item['about_item_title'] ); ?></h3>
							<?php if ( $am_item['about_item_text'] ) : ?>
								<p class="text-term-text"><?php echo wp_kses( $am_item['about_item_text'], array( 'br' => array() ) ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $am_item['about_item_link']['url'] ) ) : ?>
								<p class="mt-4"><?php am_link( $am_item['about_item_link'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php $am_alink = am_field( 'about_link', $am_id ); ?>
			<?php if ( $am_alink ) : ?>
				<p class="mt-6"><?php am_link( $am_alink, 'link-arrow' ); ?></p>
			<?php endif; ?>
		<?php am_window_close(); ?>
	<?php endif; ?>

</div>

<?php
get_footer();
