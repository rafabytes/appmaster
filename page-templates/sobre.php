<?php
/**
 * Template Name: Sobre o projeto
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$am_lead  = am_field( 'lead' );
	$am_items = am_field( 'items', false, array() );
	$am_cta   = am_field( 'page_cta' );
	?>
	<div class="container py-10 md:py-16">
		<?php am_window_open( get_post_field( 'post_name' ) . '.txt', 'article', 'aria-labelledby="page-title"' ); ?>
			<?php am_prompt( am_field( 'page_command', false, 'cat sobre.txt' ) ); ?>
			<h1 id="page-title" class="page-title"><?php the_title(); ?></h1>

			<?php if ( $am_lead ) : ?>
				<p class="lead"><?php echo wp_kses( $am_lead, array( 'br' => array() ) ); ?></p>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="prose-term mt-6 max-w-3xl"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $am_items ) : ?>
				<ol class="feature-list">
					<?php foreach ( $am_items as $am_i => $am_item ) : ?>
						<li>
							<span class="feature-list__index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $am_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div>
								<h2 class="feature-list__title"><?php echo esc_html( $am_item['item_title'] ); ?></h2>
								<?php if ( $am_item['item_text'] ) : ?>
									<p class="text-term-dim"><?php echo wp_kses( $am_item['item_text'], array( 'br' => array() ) ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>

			<?php if ( $am_cta ) : ?>
				<p class="mt-8"><?php am_link( $am_cta, 'btn btn--primary' ); ?></p>
			<?php endif; ?>
		<?php am_window_close( 'article' ); ?>
	</div>
	<?php
endwhile;

get_footer();
