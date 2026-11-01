<?php
/**
 * 404.
 *
 * @package appmaster
 */

defined( 'ABSPATH' ) || exit;

get_header();

$am_path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/', PHP_URL_PATH );
?>
<div class="container py-10 md:py-16">
	<?php am_window_open( 'erro 404', 'section', 'aria-labelledby="page-title"' ); ?>
		<?php am_prompt( 'cd ' . $am_path ); ?>
		<p class="text-term-red">bash: cd: <?php echo esc_html( $am_path ); ?>: arquivo ou diretório inexistente</p>
		<h1 id="page-title" class="page-title mt-6">404</h1>
		<p class="lead"><?php echo esc_html( am_option( 'notfound_message', 'O endereço acessado não existe ou foi movido.' ) ); ?></p>
		<p class="mt-8"><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">cd ~</a></p>
	<?php am_window_close(); ?>
</div>
<?php
get_footer();
