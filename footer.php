<?php
/**
 * Rodapé.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

$am_github = am_option( 'github_profile' );
$am_email  = am_option( 'contact_email' );
$am_credit = am_option( 'footer_credit' );
$am_text   = am_option( 'footer_text' );
?>
	</div>
</main>

<footer class="site-footer">
	<div class="container grid gap-8 py-10 md:grid-cols-[1fr_auto] md:items-end">
		<div class="space-y-2">
			<p class="logo text-base">
				<span class="text-term-green" aria-hidden="true">&gt;_</span>
				<span><?php echo esc_html( am_brand() ); ?></span>
			</p>
			<?php if ( $am_text ) : ?>
				<p class="text-sm text-term-dim"><?php echo wp_kses( $am_text, array( 'br' => array() ) ); ?></p>
			<?php endif; ?>
		</div>

		<ul class="footer-links">
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
					)
				);
				?>
			<?php endif; ?>
			<?php if ( $am_github ) : ?>
				<li><a href="<?php echo esc_url( $am_github ); ?>" target="_blank" rel="noopener">github</a></li>
			<?php endif; ?>
			<?php if ( $am_email ) : ?>
				<li><a href="<?php echo esc_url( 'mailto:' . antispambot( $am_email ) ); ?>"><?php echo esc_html( antispambot( $am_email ) ); ?></a></li>
			<?php endif; ?>
			<?php if ( ! empty( $am_credit['url'] ) ) : ?>
				<li>
					<a href="<?php echo esc_url( $am_credit['url'] ); ?>"<?php echo $am_credit['target'] ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php echo esc_html( $am_credit['title'] ? $am_credit['title'] : am_domain( $am_credit['url'] ) ); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="container border-t border-term-line py-4 text-xs text-term-dim">
		<span aria-hidden="true">$ echo</span> &copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . am_brand() ); ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
