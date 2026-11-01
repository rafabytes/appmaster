<?php
/**
 * Card de projeto.
 *
 * @package appmaster
 *
 * @var array $args { project: array, index: int, heading: string }
 */

defined( 'ABSPATH' ) || exit;

$am_p       = $args['project'];
$am_heading = $args['heading'] ?? 'h3';
$am_labels  = am_status_labels();
$am_status  = $am_p['status'];
?>
<article class="project-card">
	<header class="flex items-start gap-4">
		<div class="project-card__icon">
			<?php echo am_project_icon( $am_p, 'h-7 w-7' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<div class="min-w-0 flex-1">
			<<?php echo tag_escape( $am_heading ); ?> class="project-card__title"><?php echo esc_html( $am_p['name'] ); ?></<?php echo tag_escape( $am_heading ); ?>>
			<span class="status status--<?php echo esc_attr( $am_status ); ?>">
				<?php echo esc_html( $am_labels[ $am_status ] ?? $am_status ); ?>
			</span>
		</div>
	</header>

	<?php if ( $am_p['url'] ) : ?>
		<p class="project-card__domain"><?php echo esc_html( am_domain( $am_p['url'] ) ); ?></p>
	<?php endif; ?>

	<?php if ( $am_p['summary'] ) : ?>
		<p class="project-card__summary"><?php echo esc_html( $am_p['summary'] ); ?></p>
	<?php endif; ?>

	<?php if ( $am_p['tags'] ) : ?>
		<ul class="tags" aria-label="Tecnologias">
			<?php foreach ( $am_p['tags'] as $am_tag ) : ?>
				<li><?php echo esc_html( $am_tag ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="project-card__actions">
		<?php if ( $am_p['url'] ) : ?>
			<a class="btn btn--primary" href="<?php echo esc_url( $am_p['url'] ); ?>" target="_blank" rel="noopener">
				acessar
				<?php echo am_icon( 'external', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="sr-only">(<?php echo esc_html( $am_p['name'] ); ?>, abre em nova aba)</span>
			</a>
		<?php endif; ?>
		<?php if ( $am_p['github'] ) : ?>
			<a class="btn" href="<?php echo esc_url( $am_p['github'] ); ?>" target="_blank" rel="noopener">
				<?php echo am_icon( 'git', 'h-4 w-4' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span><?php echo esc_html( am_github_path( $am_p['github'] ) ); ?></span>
				<span class="sr-only">(código-fonte no GitHub, abre em nova aba)</span>
			</a>
		<?php endif; ?>
	</div>
</article>
