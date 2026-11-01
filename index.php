<?php
/**
 * Fallback genérico (posts, arquivos, busca).
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container py-10 md:py-16">
	<?php if ( is_singular() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php am_window_open( get_post_field( 'post_name' ) . '.txt', 'article', 'aria-labelledby="page-title"' ); ?>
				<p class="text-sm text-term-dim"><?php echo esc_html( get_the_date() ); ?></p>
				<h1 id="page-title" class="page-title"><?php the_title(); ?></h1>
				<div class="prose-term mt-6 max-w-3xl"><?php the_content(); ?></div>
			<?php am_window_close( 'article' ); ?>
		<?php endwhile; ?>
	<?php else : ?>
		<?php am_prompt( 'ls' ); ?>
		<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php if ( have_posts() ) : ?>
			<ul class="mt-8 space-y-4">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li>
						<a class="link-arrow" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<span class="ml-2 text-sm text-term-dim"><?php echo esc_html( get_the_date() ); ?></span>
					</li>
				<?php endwhile; ?>
			</ul>
			<div class="mt-8"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="mt-4 text-term-dim">nada encontrado.</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
get_footer();
