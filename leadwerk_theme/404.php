<?php
/**
 * 404 template.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$home_url    = home_url( '/' );
$contact_url = function_exists( 'leadwerk_theme_get_page_url' ) ? leadwerk_theme_get_page_url( 'ludwig-kontakt-v1', 'de', home_url( '/kontakt/' ) ) : home_url( '/kontakt/' );
$image_url   = defined( 'LEADWERK_THEME_URI' ) ? LEADWERK_THEME_URI . '/Ludwig_prev_foto/_X8A2938_prev.webp' : '';
?>

<main>
	<section class="error-page">
		<div class="container error-page-inner">
			<div class="error-copy">
				<span class="error-kicker"><?php esc_html_e( 'Fehler 404', 'leadwerk-theme' ); ?></span>
				<h1><?php esc_html_e( 'Diese Seite gibt es nicht.', 'leadwerk-theme' ); ?></h1>
				<p><?php esc_html_e( 'Der Link ist veraltet oder die Adresse wurde falsch eingegeben. Von hier kommst Du direkt zurueck zur Startseite oder in die persoenliche Beratung.', 'leadwerk-theme' ); ?></p>
				<div class="error-actions">
					<a href="<?php echo esc_url( $home_url ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'Zur Startseite', 'leadwerk-theme' ); ?></a>
					<a href="<?php echo esc_url( $contact_url ); ?>" class="btn btn-secondary btn-lg"><?php esc_html_e( 'Kontakt aufnehmen', 'leadwerk-theme' ); ?></a>
				</div>
			</div>

			<?php if ( '' !== $image_url ) : ?>
				<div class="error-visual" aria-hidden="true">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="eager">
					<span class="error-code">404</span>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
