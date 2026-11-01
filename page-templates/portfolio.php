<?php
/**
 * Template Name: Portfolio
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();

$am_projects = array_map( 'am_project', am_get_projects() );

while ( have_posts() ) :
	the_post();
	$am_lead = am_field( 'lead' );
	?>
	<div class="container py-10 md:py-16">
		<header class="mb-10">
			<?php am_prompt( am_field( 'page_command', false, 'ls -la ~/projetos' ) ); ?>
			<h1 class="page-title"><?php the_title(); ?></h1>
			<?php if ( $am_lead ) : ?>
				<p class="lead"><?php echo wp_kses( $am_lead, array( 'br' => array() ) ); ?></p>
			<?php endif; ?>
			<?php if ( get_the_content() ) : ?>
				<div class="prose-term mt-6 max-w-3xl"><?php the_content(); ?></div>
			<?php endif; ?>
			<p class="mt-6 text-sm text-term-dim">total <?php echo (int) count( $am_projects ); ?></p>
		</header>

		<?php if ( $am_projects ) : ?>
			<div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $am_projects as $am_project ) : ?>
					<?php get_template_part( 'template-parts/project-card', null, array( 'project' => $am_project, 'heading' => 'h2' ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="text-term-dim">nenhum projeto publicado ainda.</p>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
