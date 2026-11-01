<?php
/**
 * Página padrão.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$am_file = get_post_field( 'post_name' ) . '.txt';
	?>
	<div class="container py-10 md:py-16">
		<?php am_window_open( $am_file, 'article', 'aria-labelledby="page-title"' ); ?>
			<?php am_prompt( 'cat ' . $am_file ); ?>
			<h1 id="page-title" class="page-title"><?php the_title(); ?></h1>
			<div class="prose-term mt-6 max-w-3xl"><?php the_content(); ?></div>
		<?php am_window_close( 'article' ); ?>
	</div>
	<?php
endwhile;

get_footer();
