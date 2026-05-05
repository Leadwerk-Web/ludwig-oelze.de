<?php
/**
 * Leadwerk theme integration for static-profile imports.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEADWERK_THEME_VERSION', '1.0.11' );
define( 'LEADWERK_THEME_DIR', get_template_directory() );
define( 'LEADWERK_THEME_URI', get_template_directory_uri() );
define( 'LEADWERK_THEME_ACM_NEWS_FILTER_SLUG_META', 'acm_news_filter_slug' );

$leadwerk_structured_render_file = LEADWERK_THEME_DIR . '/inc/structured-acm-render.php';
$leadwerk_structured_render_alt  = LEADWERK_THEME_DIR . '/inc/structured-finora-render.php';
if ( is_file( $leadwerk_structured_render_file ) ) {
	require_once $leadwerk_structured_render_file;
} elseif ( is_file( $leadwerk_structured_render_alt ) ) {
	require_once $leadwerk_structured_render_alt;
}

$leadwerk_secure_pdf = LEADWERK_THEME_DIR . '/inc/leadwerk-secure-pdf-download.php';
if ( is_file( $leadwerk_secure_pdf ) ) {
	require_once $leadwerk_secure_pdf;
}

$leadwerk_exact_render_file = LEADWERK_THEME_DIR . '/inc/exact-acm-render.php';
$leadwerk_exact_render_alt  = LEADWERK_THEME_DIR . '/inc/exact-finora-render.php';
if ( is_file( $leadwerk_exact_render_file ) ) {
	require_once $leadwerk_exact_render_file;
} elseif ( is_file( $leadwerk_exact_render_alt ) ) {
	require_once $leadwerk_exact_render_alt;
}

$leadwerk_ludwig_render_file = LEADWERK_THEME_DIR . '/inc/ludwig-render.php';
if ( is_file( $leadwerk_ludwig_render_file ) ) {
	require_once $leadwerk_ludwig_render_file;
}

$leadwerk_ludwig_exact_binders_file = LEADWERK_THEME_DIR . '/inc/ludwig-exact-binders.php';
if ( is_file( $leadwerk_ludwig_exact_binders_file ) ) {
	require_once $leadwerk_ludwig_exact_binders_file;
}

/**
 * Whether the active static profile is Ludwig.
 *
 * @return bool
 */
function leadwerk_theme_profile_is_ludwig() {
	$map = function_exists( 'leadwerk_theme_get_source_template_map' )
		? (array) leadwerk_theme_get_source_template_map()
		: array();

	foreach ( array_keys( $map ) as $source_key ) {
		if ( function_exists( 'leadwerk_theme_is_ludwig_source_key' ) && leadwerk_theme_is_ludwig_source_key( (string) $source_key ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether one field belongs to Leadwerk-managed structured storage.
 *
 * @param string $field_name Field name.
 * @return bool
 */
function leadwerk_theme_is_managed_structured_field( $field_name ) {
	$field_name = (string) $field_name;
	if ( '' === $field_name ) {
		return false;
	}

	if ( 'ludwig_page_document' === $field_name ) {
		return true;
	}

	if ( class_exists( 'Leadwerk_Content_Schema' ) ) {
		$group = Leadwerk_Content_Schema::get_group( $field_name );
		if ( is_array( $group ) && ! empty( $group ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Read one field value from the correct storage provider.
 *
 * @param string     $field_name Field name.
 * @param int|string $post_id    Post ID or option scope.
 * @return mixed
 */
function leadwerk_theme_get_managed_field_value( $field_name, $post_id = null ) {
	if ( class_exists( 'Leadwerk_Fields_API' ) && leadwerk_theme_is_managed_structured_field( $field_name ) ) {
		return Leadwerk_Fields_API::get_field( $field_name, $post_id );
	}

	if ( function_exists( 'get_field' ) ) {
		return get_field( $field_name, $post_id );
	}

	if ( class_exists( 'Leadwerk_Fields_API' ) ) {
		return Leadwerk_Fields_API::get_field( $field_name, $post_id );
	}

	return null;
}

/**
 * Theme setup.
 *
 * @return void
 */
function leadwerk_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'editor-styles' );
	$thumbnail_types = leadwerk_theme_profile_is_ludwig()
		? array( 'post', 'page' )
		: array( 'post', 'page', 'acm_news' );
	add_theme_support( 'post-thumbnails', $thumbnail_types );
	remove_theme_support( 'block-templates' );
	remove_theme_support( 'block-template-parts' );
}
add_action( 'after_setup_theme', 'leadwerk_theme_setup' );
add_filter( 'leadwerk_render_floating_switcher', '__return_false' );

/**
 * Register the ACM News custom post type.
 *
 * @return void
 */
function leadwerk_theme_register_acm_news_cpt() {
	if ( leadwerk_theme_profile_is_ludwig() ) {
		return;
	}

	if ( post_type_exists( 'acm_news' ) ) {
		return;
	}

	$labels = array(
		'name'               => 'ACM News',
		'singular_name'      => 'News-Beitrag',
		'menu_name'          => 'ACM News',
		'add_new'            => 'Neuen Beitrag',
		'add_new_item'       => 'Neuen News-Beitrag erstellen',
		'edit_item'          => 'News-Beitrag bearbeiten',
		'new_item'           => 'Neuer News-Beitrag',
		'view_item'          => 'News-Beitrag ansehen',
		'search_items'       => 'News durchsuchen',
		'not_found'          => 'Keine News gefunden',
		'not_found_in_trash' => 'Keine News im Papierkorb',
		'all_items'          => 'Alle News',
	);

	// has_archive false: vermeidet Rewrite-Konflikt mit der WordPress-Seite slug "news" (statische Uebersicht).
	register_post_type( 'acm_news', array(
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-megaphone',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'rewrite'       => array( 'slug' => 'news', 'with_front' => false ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'leadwerk_theme_register_acm_news_cpt' );

/**
 * Classic editor for ACM News: sichtbares Beitragsbild, Auszug, vertrautes UI.
 *
 * @param bool   $use       Whether to use block editor.
 * @param string $post_type Post type.
 * @return bool
 */
function leadwerk_theme_use_block_editor_for_acm_news( $use, $post_type ) {
	return ( 'acm_news' === $post_type ) ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'leadwerk_theme_use_block_editor_for_acm_news', 10, 2 );

/**
 * Labels for news filter slugs (news page filter bar + card pills).
 *
 * @return array<string,string>
 */
function leadwerk_theme_get_acm_news_filter_slug_choices() {
	return array(
		'unternehmen' => 'Unternehmen',
		'operations'  => 'Operations',
		'flotte'      => 'Flotte',
		'maintenance' => 'Maintenance',
		'partner'     => 'Partner',
		'handling'    => 'Handling',
		'sicherheit'  => 'Sicherheit',
	);
}

/**
 * Meta box: Kategorie fuer News-Filter (data-category).
 *
 * @param WP_Post $post Post.
 * @return void
 */
function leadwerk_theme_render_acm_news_filter_metabox( $post ) {
	if ( ! $post instanceof WP_Post || 'acm_news' !== $post->post_type ) {
		return;
	}
	wp_nonce_field( 'leadwerk_theme_acm_news_filter_save', 'leadwerk_theme_acm_news_filter_nonce' );
	$key     = LEADWERK_THEME_ACM_NEWS_FILTER_SLUG_META;
	$current = sanitize_title( (string) get_post_meta( $post->ID, $key, true ) );
	$choices = leadwerk_theme_get_acm_news_filter_slug_choices();
	if ( '' === $current || ! isset( $choices[ $current ] ) ) {
		$current = 'unternehmen';
	}
	echo '<p class="description">' . esc_html__( 'Steuert die Zuordnung zu den Filter-Buttons auf der News-Uebersicht.', 'leadwerk-theme' ) . '</p>';
	echo '<label for="leadwerk_acm_news_filter_slug" class="screen-reader-text">' . esc_html__( 'News-Kategorie', 'leadwerk-theme' ) . '</label>';
	echo '<select name="leadwerk_acm_news_filter_slug" id="leadwerk_acm_news_filter_slug" style="max-width:100%;">';
	foreach ( $choices as $slug => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $slug ),
			selected( $current, $slug, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
}

/**
 * Register ACM News meta boxes.
 *
 * @return void
 */
function leadwerk_theme_register_acm_news_metaboxes() {
	add_meta_box(
		'leadwerk_acm_news_filter',
		__( 'News-Kategorie (Filter)', 'leadwerk-theme' ),
		'leadwerk_theme_render_acm_news_filter_metabox',
		'acm_news',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'leadwerk_theme_register_acm_news_metaboxes' );

/**
 * Save news filter slug meta.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function leadwerk_theme_save_acm_news_filter_meta( $post_id ) {
	if ( ! isset( $_POST['leadwerk_theme_acm_news_filter_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['leadwerk_theme_acm_news_filter_nonce'] ) ), 'leadwerk_theme_acm_news_filter_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$pt = get_post_type( $post_id );
	if ( 'acm_news' !== $pt ) {
		return;
	}
	$key     = LEADWERK_THEME_ACM_NEWS_FILTER_SLUG_META;
	$choices = leadwerk_theme_get_acm_news_filter_slug_choices();
	$raw     = isset( $_POST['leadwerk_acm_news_filter_slug'] )
		? sanitize_title( wp_unslash( $_POST['leadwerk_acm_news_filter_slug'] ) )
		: '';
	if ( isset( $choices[ $raw ] ) ) {
		update_post_meta( $post_id, $key, $raw );
	} else {
		update_post_meta( $post_id, $key, 'unternehmen' );
	}
}
add_action( 'save_post_acm_news', 'leadwerk_theme_save_acm_news_filter_meta' );

/**
 * Permalink fuer acm_news immer /news/{slug}/ (Admin „URL“ + konsistente Links).
 *
 * @param string  $post_link Permalink.
 * @param WP_Post $post     Beitrag.
 * @return string
 */
function leadwerk_theme_acm_news_post_type_link( $post_link, $post ) {
	if ( class_exists( 'Leadwerk_Translation_API' ) ) {
		return $post_link;
	}
	if ( ! $post instanceof WP_Post || 'acm_news' !== $post->post_type ) {
		return $post_link;
	}
	if ( '' === (string) $post->post_name ) {
		return $post_link;
	}
	return home_url( user_trailingslashit( 'news/' . $post->post_name ) );
}
add_filter( 'post_type_link', 'leadwerk_theme_acm_news_post_type_link', 10, 2 );

/**
 * Einmalig Rewrite-Regeln flushen (nach CPT-Aenderung / fehlendem /news/-Prefix).
 *
 * @return void
 */
function leadwerk_theme_maybe_flush_acm_news_rewrites() {
	if ( ! post_type_exists( 'acm_news' ) ) {
		return;
	}
	$flag = 'leadwerk_flush_acm_news_rw_v3';
	if ( get_option( $flag ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( $flag, '1' );
}
add_action( 'init', 'leadwerk_theme_maybe_flush_acm_news_rewrites', 999 );

/**
 * Enqueue static ACM assets.
 *
 * @return void
 */
function leadwerk_theme_enqueue_assets() {
	$current_lang = leadwerk_theme_get_current_lang();
	$default_lang = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_default_language() : 'de';
	$is_default   = $current_lang === $default_lang;
	$source_key   = leadwerk_theme_get_current_source_key();

	// Core stylesheet.
	wp_enqueue_style( 'leadwerk-theme-core', get_stylesheet_uri(), array(), LEADWERK_THEME_VERSION );

	if ( leadwerk_theme_profile_is_ludwig() || ( function_exists( 'leadwerk_theme_is_ludwig_source_key' ) && leadwerk_theme_is_ludwig_source_key( $source_key ) ) ) {
		$ludwig_font_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap';
		wp_enqueue_style( 'leadwerk-theme-fonts', $ludwig_font_url, array(), null );

		$ludwig_css = LEADWERK_THEME_DIR . '/css/styles.css';
		if ( file_exists( $ludwig_css ) ) {
			wp_enqueue_style(
				'leadwerk-theme-ludwig',
				LEADWERK_THEME_URI . '/css/styles.css',
				array( 'leadwerk-theme-core' ),
				(string) filemtime( $ludwig_css )
			);
		}

		$ludwig_js = LEADWERK_THEME_DIR . '/js/main.js';
		if ( file_exists( $ludwig_js ) ) {
			wp_enqueue_script(
				'leadwerk-theme-ludwig',
				LEADWERK_THEME_URI . '/js/main.js',
				array(),
				(string) filemtime( $ludwig_js ),
				true
			);
		}

		return;
	}

	// Google Fonts: Cormorant Garamond (headings) + Inter (body) — ACM design system.
	wp_enqueue_style( 'leadwerk-theme-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Inter:wght@300;400;500;600&display=swap', array(), null );

	// Tailwind CSS CDN — used by all ACM source shells.
	wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', array(), null, false );
	wp_enqueue_script( 'leadwerk-tailwind-config', LEADWERK_THEME_URI . '/js/tailwind-config.js', array( 'tailwindcss' ), LEADWERK_THEME_VERSION, false );

	// Base CSS — shared styles across all pages.
	wp_enqueue_style( 'leadwerk-theme-base', LEADWERK_THEME_URI . '/css/base.css', array( 'leadwerk-theme-core' ), LEADWERK_THEME_VERSION );

	if ( function_exists( 'leadwerk_theme_get_structured_inline_styles' ) ) {
		wp_add_inline_style( 'leadwerk-theme-base', leadwerk_theme_get_structured_inline_styles() );
	}

	// Page-specific CSS/JS based on source_key.
	$page_assets = array(
		'acm-index-v1'          => 'index',
		'acm-thats-acm-v1'      => 'thats-acm',
		'acm-charter-v1'        => 'charter',
		'acm-global-7500-v1'    => 'fleet',
		'acm-global-6000-v1'    => 'fleet',
		'acm-global-xrs-v1'     => 'fleet',
		'acm-aircraft-v1'       => 'aircraft',
		'acm-maintenance-v1'    => 'maintenance',
		'acm-careers-v1'        => 'karriere',
		'acm-contact-v1'        => 'kontakt',
		'acm-news-v1'           => 'news',
	);

	if ( isset( $page_assets[ $source_key ] ) ) {
		$slug     = $page_assets[ $source_key ];
		$css_file = '/css/page-' . $slug . '.css';
		$js_file  = '/js/page-' . $slug . '.js';

		if ( file_exists( get_stylesheet_directory() . $css_file ) ) {
			wp_enqueue_style( 'leadwerk-page-' . $slug, LEADWERK_THEME_URI . $css_file, array( 'leadwerk-theme-base' ), LEADWERK_THEME_VERSION );
		}
		if ( file_exists( get_stylesheet_directory() . $js_file ) ) {
			wp_enqueue_script( 'leadwerk-page-' . $slug, LEADWERK_THEME_URI . $js_file, array(), LEADWERK_THEME_VERSION, true );
		}
	}

	// News article detail pages — dedicated CSS for single acm_news posts.
	if ( is_singular( 'acm_news' ) ) {
		wp_enqueue_style( 'leadwerk-page-news-article', LEADWERK_THEME_URI . '/css/page-news-article.css', array( 'leadwerk-theme-base' ), LEADWERK_THEME_VERSION );
	}

	// Global mobile QA overrides â€” load after page styles so mobile fixes can win.
	$mobile_qa_file = LEADWERK_THEME_DIR . '/css/mobile-qa.css';
	if ( file_exists( $mobile_qa_file ) ) {
		$mobile_qa_deps = array( 'leadwerk-theme-base' );
		if ( isset( $slug ) && wp_style_is( 'leadwerk-page-' . $slug, 'enqueued' ) ) {
			$mobile_qa_deps[] = 'leadwerk-page-' . $slug;
		}
		if ( wp_style_is( 'leadwerk-page-news-article', 'enqueued' ) ) {
			$mobile_qa_deps[] = 'leadwerk-page-news-article';
		}

		wp_enqueue_style(
			'leadwerk-theme-mobile-qa',
			LEADWERK_THEME_URI . '/css/mobile-qa.css',
			$mobile_qa_deps,
			(string) filemtime( $mobile_qa_file )
		);
	}

	// Main JS.
	wp_enqueue_script( 'leadwerk-theme-main', LEADWERK_THEME_URI . '/js/main.js', array(), LEADWERK_THEME_VERSION, true );
	wp_localize_script(
		'leadwerk-theme-main',
		'leadwerkThemeData',
		array(
			'locale'              => $current_lang,
			'defaultLang'         => $default_lang,
			'wpformsTranslations' => $is_default ? array() : leadwerk_theme_get_wpforms_translations( $current_lang ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'leadwerk_theme_enqueue_assets' );

/**
 * Print late Complianz accept-button overrides so plugin styles do not win.
 *
 * @return void
 */
function leadwerk_theme_print_complianz_button_override() {
if ( is_admin() ) {
return;
}
?>
<style id="leadwerk-theme-cmplz-accept-override">
:root,
#cmplz-cookiebanner-container,
.cmplz-cookiebanner {
--cmplz_button_accept_background_color: #266063 !important;
--cmplz_button_accept_border_color: #266063 !important;
--cmplz_button_accept_text_color: #ffffff !important;
}

.cmplz-cookiebanner .cmplz-buttons .cmplz-btn.cmplz-accept,
#cmplz-cookiebanner-container .cmplz-buttons .cmplz-btn.cmplz-accept,
#cmplz-cookiebanner-container button.cmplz-btn.cmplz-accept {
background: #266063 !important;
background-color: #266063 !important;
background-image: none !important;
border: 1px solid #266063 !important;
border-color: #266063 !important;
color: #ffffff !important;
box-shadow: none !important;
}

.cmplz-cookiebanner .cmplz-buttons .cmplz-btn.cmplz-accept:hover,
.cmplz-cookiebanner .cmplz-buttons .cmplz-btn.cmplz-accept:focus,
.cmplz-cookiebanner .cmplz-buttons .cmplz-btn.cmplz-accept:focus-visible,
#cmplz-cookiebanner-container .cmplz-buttons .cmplz-btn.cmplz-accept:hover,
#cmplz-cookiebanner-container .cmplz-buttons .cmplz-btn.cmplz-accept:focus,
#cmplz-cookiebanner-container .cmplz-buttons .cmplz-btn.cmplz-accept:focus-visible,
#cmplz-cookiebanner-container button.cmplz-btn.cmplz-accept:hover,
#cmplz-cookiebanner-container button.cmplz-btn.cmplz-accept:focus,
#cmplz-cookiebanner-container button.cmplz-btn.cmplz-accept:focus-visible {
background: #043c43 !important;
background-color: #043c43 !important;
background-image: none !important;
border: 1px solid #043c43 !important;
border-color: #043c43 !important;
color: #ffffff !important;
box-shadow: 0 0 0 3px rgba(144, 180, 178, 0.38) !important;
outline: none !important;
}

.cmplz-cookiebanner .cmplz-buttons .cmplz-btn.cmplz-accept:active,
#cmplz-cookiebanner-container .cmplz-buttons .cmplz-btn.cmplz-accept:active,
#cmplz-cookiebanner-container button.cmplz-btn.cmplz-accept:active {
background: #043c43 !important;
background-color: #043c43 !important;
background-image: none !important;
border: 1px solid #043c43 !important;
border-color: #043c43 !important;
color: #ffffff !important;
box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.18) !important;
}
</style>
<?php
}
add_action( 'wp_footer', 'leadwerk_theme_print_complianz_button_override', 100 );

/**
 * Start output buffering early so we can replace Complianz banner strings.
 *
 * Complianz stores its banner text as wp_options (not gettext .mo strings),
 * so switch_to_locale / load_plugin_textdomain has no effect on the banner.
 * We capture the full page HTML and do a targeted string replacement for
 * the cmplz-cookiebanner-container block.
 *
 * @return void
 */
function leadwerk_theme_cmplz_ob_start() {
if ( is_admin() ) {
return;
}

$lang         = leadwerk_theme_get_current_lang();
$default_lang = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_default_language() : 'de';

if ( $lang === $default_lang ) {
return;
}

ob_start( 'leadwerk_theme_cmplz_replace_banner_strings' );
}

/**
 * Output buffer callback: replace German Complianz banner strings with
 * the current language translations.
 *
 * @param string $html Full page HTML.
 * @return string
 */
function leadwerk_theme_cmplz_replace_banner_strings( $html ) {
/* Only process if the banner container is present. */
if ( false === strpos( $html, 'cmplz-cookiebanner-container' ) ) {
return $html;
}

$lang       = leadwerk_theme_get_current_lang();
$de_strings = leadwerk_theme_get_theme_strings( 'de' );
$tr_strings = leadwerk_theme_get_theme_strings( $lang );

$search  = array();
$replace = array();
foreach ( $de_strings as $key => $de_value ) {
if ( 0 !== strpos( $key, 'cmplz_' ) ) {
continue;
}

$tr_value = isset( $tr_strings[ $key ] ) ? (string) $tr_strings[ $key ] : '';
if ( '' === $tr_value || $tr_value === $de_value ) {
continue;
}

$search[]  = $de_value;
$replace[] = $tr_value;
}

if ( empty( $search ) ) {
return $html;
}

return str_replace( $search, $replace, $html );
}
add_action( 'template_redirect', 'leadwerk_theme_cmplz_ob_start', 1 );

/**
 * Also hook cmplz_cookie_banner_text if the filter exists in the installed
 * Complianz version.
 *
 * @param string $html Banner HTML.
 * @return string
 */
function leadwerk_theme_cmplz_filter_banner_text( $html ) {
$lang         = leadwerk_theme_get_current_lang();
$default_lang = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_default_language() : 'de';

if ( $lang === $default_lang ) {
return $html;
}

return leadwerk_theme_cmplz_replace_banner_strings( $html );
}
add_filter( 'cmplz_cookie_banner_text', 'leadwerk_theme_cmplz_filter_banner_text', 10, 1 );

/**
 * Inject a small JS patch in the footer that overrides the German strings
 * inside the global complianz config object (placeholdertext, aria_label)
 * for non-default-language pages.
 *
 * @return void
 */
function leadwerk_theme_cmplz_js_locale_override() {
if ( is_admin() ) {
return;
}

$lang         = leadwerk_theme_get_current_lang();
$default_lang = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_default_language() : 'de';

if ( $lang === $default_lang ) {
return;
}

$js_overrides = array(
'placeholdertext' => 'en' === $lang
? leadwerk_theme_get_string( 'cmplz_placeholder_accept', 'Click here to accept {category} cookies and enable this content', $lang )
: '',
'aria_label'      => 'en' === $lang
? leadwerk_theme_get_string( 'cmplz_placeholder_accept', 'Click here to accept {category} cookies and enable this content', $lang )
: '',
);

$js_overrides = array_filter( $js_overrides );
if ( empty( $js_overrides ) ) {
return;
}
?>
<script id="leadwerk-cmplz-locale-override">
(function(){
if(typeof complianz==='undefined')return;
var t=<?php echo wp_json_encode( $js_overrides ); ?>;
for(var k in t){if(t.hasOwnProperty(k))complianz[k]=t[k];}
})();
</script>
<?php
}
add_action( 'wp_footer', 'leadwerk_theme_cmplz_js_locale_override', 101 );

/**
 * Register dynamic theme blocks.
 *
 * @return void
 */
function leadwerk_theme_register_blocks() {
$blocks = array(
'leadwerk-acm-page'   => 'leadwerk_theme_render_page_block',
'leadwerk-acm-header' => 'leadwerk_theme_render_header_block',
'leadwerk-acm-footer' => 'leadwerk_theme_render_footer_block',
);

foreach ( $blocks as $name => $callback ) {
register_block_type(
'acf/' . $name,
array(
'render_callback' => $callback,
)
);
}
}
add_action( 'init', 'leadwerk_theme_register_blocks' );

/**
 * Add static body classes and language marker.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function leadwerk_theme_body_classes( $classes ) {
if ( is_singular( 'page' ) ) {
$post_id    = get_queried_object_id();
$body_class = trim( (string) get_post_meta( $post_id, 'leadwerk_body_class', true ) );
$source_key = trim( (string) get_post_meta( $post_id, 'leadwerk_source_key', true ) );
$lang       = leadwerk_theme_get_current_lang();

if ( '' === $body_class && '' !== $source_key && function_exists( 'leadwerk_theme_get_source_template_body_class' ) ) {
$body_class = (string) leadwerk_theme_get_source_template_body_class( $source_key );
}

if ( '' !== $body_class ) {
$classes = array_merge( $classes, preg_split( '/\s+/', $body_class ) );
}

if ( 'acm-index-v1' === $source_key || 'acm-home-v1' === $source_key || 'ludwig-home-v1' === $source_key ) {
$classes[] = 'home';
}

$classes[] = 'lang-' . $lang;
}

if ( is_404() && leadwerk_theme_profile_is_ludwig() ) {
$classes[] = 'page-404';
$classes[] = 'header-scrolled';
$classes[] = 'lang-' . leadwerk_theme_get_current_lang();
}

$classes = array_values(
array_filter(
array_unique( array_filter( $classes ) ),
static function ( $class ) {
return false === strpos( (string) $class, 'leadwerk' );
}
)
);

return $classes;
}
add_filter( 'body_class', 'leadwerk_theme_body_classes' );
/**
 * Output fallback favicon if site icon is not configured.
 *
 * @return void
 */
function leadwerk_theme_favicon() {
	if ( get_option( 'site_icon' ) ) {
		return;
	}

	if ( leadwerk_theme_profile_is_ludwig() ) {
		echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( LEADWERK_THEME_URI . '/assets/images/favicon.svg' ) . '">' . "\n";
		echo '<link rel="icon" type="image/png" href="' . esc_url( LEADWERK_THEME_URI . '/assets/images/favicon.png' ) . '">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( LEADWERK_THEME_URI . '/assets/images/favicon.png' ) . '">' . "\n";
		return;
	}

	echo '<link rel="icon" type="image/png" href="' . esc_url( LEADWERK_THEME_URI . '/favicon-32x32.png' ) . '" sizes="32x32">' . "\n";
	echo '<link rel="icon" type="image/png" href="' . esc_url( LEADWERK_THEME_URI . '/favicon-192x192.png' ) . '" sizes="192x192">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( LEADWERK_THEME_URI . '/apple-touch-icon.png' ) . '">' . "\n";
}
add_action( 'wp_head', 'leadwerk_theme_favicon', 1 );

/**
 * Output canonical, hreflang and meta description tags.
 *
 * @return void
 */
function leadwerk_theme_head_meta() {
	if ( ! is_singular( 'page' ) ) {
		return;
	}

	$post_id          = get_queried_object_id();
	$meta_description = trim( (string) get_post_meta( $post_id, 'leadwerk_meta_description', true ) );
	$robots           = trim( (string) get_post_meta( $post_id, 'leadwerk_meta_robots', true ) );

	if ( '' !== $meta_description ) {
		echo '<meta name="description" content="' . esc_attr( $meta_description ) . '">' . "\n";
	}

	if ( '' !== $robots ) {
		echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
	}

	echo '<link rel="canonical" href="' . esc_url( get_permalink( $post_id ) ) . '">' . "\n";

	if ( class_exists( 'Leadwerk_Translation_Router' ) && method_exists( 'Leadwerk_Translation_Router', 'get_current_page_alternate_urls' ) ) {
		$alternates = Leadwerk_Translation_Router::get_current_page_alternate_urls();
		if ( ! empty( $alternates['de'] ) ) {
			echo '<link rel="alternate" hreflang="de" href="' . esc_url( $alternates['de'] ) . '">' . "\n";
			echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $alternates['de'] ) . '">' . "\n";
		}
		if ( ! empty( $alternates['en'] ) ) {
			echo '<link rel="alternate" hreflang="en" href="' . esc_url( $alternates['en'] ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'leadwerk_theme_head_meta', 5 );

/**
 * Render the dynamic page block.
 *
 * @return string
 */
function leadwerk_theme_render_page_block() {
	$post_id = get_the_ID();
	if ( ! $post_id || ! class_exists( 'Leadwerk_Content_Schema' ) ) {
		return '';
	}

	$group = Leadwerk_Content_Schema::get_group_for_post( $post_id );
	if ( ! $group || empty( $group['field_name'] ) ) {
		return '';
	}

	$field_name = $group['field_name'];
	$value      = leadwerk_theme_get_managed_field_value( $field_name, $post_id );
	if ( function_exists( 'leadwerk_theme_render_exact_page_group' ) ) {
		$exact_html = leadwerk_theme_render_exact_page_group( $group, $value, $post_id );
		if ( false !== strpos( $exact_html, 'leadwerk-structured-' ) ) {
			return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
				? leadwerk_theme_render_exact_runtime_notice(
					'Structured fallback markers were detected for post #' . $post_id . '. Exact shell rendering must be fixed before this page is used publicly.',
					$post_id
				)
				: '';
		}
		if ( '' !== trim( wp_strip_all_tags( $exact_html ) ) || false !== strpos( $exact_html, '<section' ) || false !== strpos( $exact_html, 'runtime-notice' ) ) {
			return $exact_html;
		}
	}

	return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
		? leadwerk_theme_render_exact_runtime_notice(
			'Exact shell rendering is required for post #' . $post_id . ', but no mapped shell output was produced.',
			$post_id
		)
		: '';
}

/**
 * Render current page content in classic theme templates.
 *
 * @param int $post_id Optional post ID.
 * @return string
 */
function leadwerk_theme_render_current_page_content( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return '';
	}

	$group = class_exists( 'Leadwerk_Content_Schema' )
		? Leadwerk_Content_Schema::get_group_for_post( $post_id )
		: null;

	if ( $group && ! empty( $group['field_name'] ) ) {
		$field_name = $group['field_name'];
		$value      = leadwerk_theme_get_managed_field_value( $field_name, $post_id );

		if ( function_exists( 'leadwerk_theme_render_exact_page_group' ) ) {
			$exact_html = leadwerk_theme_render_exact_page_group( $group, $value, $post_id );
			if ( false !== strpos( $exact_html, 'leadwerk-structured-' ) ) {
				return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
					? leadwerk_theme_render_exact_runtime_notice(
						'Structured fallback markers were detected for post #' . $post_id . '. Exact shell rendering is not clean yet.',
						$post_id
					)
					: '';
			}
			if ( false !== strpos( $exact_html, '<section' ) || false !== strpos( $exact_html, '<div class="runtime-notice"' ) ) {
				return $exact_html;
			}

			return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
				? leadwerk_theme_render_exact_runtime_notice(
					'Exact shell missing or source key is unmapped for post #' . $post_id . '.',
					$post_id
				)
				: '';
		}

		return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
			? leadwerk_theme_render_exact_runtime_notice(
				'Exact renderer is unavailable for post #' . $post_id . '.',
				$post_id
			)
			: '';
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	return apply_filters( 'the_content', $post->post_content );
}

/**
 * Force the Ludwig transparent header into its scrolled visual state.
 *
 * @param string $html Header HTML.
 * @return string
 */
function leadwerk_theme_ludwig_force_scrolled_header( $html ) {
	if ( '' === trim( (string) $html ) ) {
		return (string) $html;
	}

	$updated = preg_replace_callback(
		'/<header\b([^>]*)>/i',
		static function ( $matches ) {
			$attrs = (string) ( $matches[1] ?? '' );

			if ( preg_match( '/\sclass=(["\'])(.*?)\1/i', $attrs, $class_match ) ) {
				$classes = preg_split( '/\s+/', trim( (string) $class_match[2] ) );
				$classes = is_array( $classes ) ? $classes : array();
				if ( ! in_array( 'scrolled', $classes, true ) ) {
					$classes[] = 'scrolled';
				}
				$new_class = ' class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '"';
				$attrs = preg_replace( '/\sclass=(["\'])(.*?)\1/i', $new_class, $attrs, 1 );
			} else {
				$attrs .= ' class="header scrolled"';
			}

			if ( false === stripos( $attrs, 'data-header-state=' ) ) {
				$attrs .= ' data-header-state="scrolled"';
			}

			return '<header' . $attrs . '>';
		},
		(string) $html,
		1
	);

	return is_string( $updated ) ? $updated : (string) $html;
}

/**
 * Render the dynamic header block.
 *
 * @return string
 */
function leadwerk_theme_render_header_block() {
	if ( is_404() && leadwerk_theme_profile_is_ludwig() && function_exists( 'leadwerk_theme_render_ludwig_exact_site_header' ) ) {
		$ludwig_header = leadwerk_theme_render_ludwig_exact_site_header( 'ludwig-home-v1' );
		if ( '' !== trim( $ludwig_header ) ) {
			return leadwerk_theme_ludwig_force_scrolled_header( $ludwig_header );
		}
	}

	if ( function_exists( 'leadwerk_theme_render_exact_site_header' ) ) {
		$exact_header = leadwerk_theme_render_exact_site_header();
		if ( '' !== trim( $exact_header ) ) {
			return $exact_header;
		}
	}

	$lang            = leadwerk_theme_get_current_lang();
	$strings         = leadwerk_theme_get_theme_strings( $lang );
	$home_url        = leadwerk_theme_get_page_url( 'acm-index-v1', $lang, home_url( '/' ) );
	$language_url    = leadwerk_theme_get_alternate_language_url();
	$lang_pair       = leadwerk_theme_get_header_footer_lang_pair_urls();
	$de_url          = $lang_pair['de'];
	$en_url          = $lang_pair['en'];
	$is_service_page = leadwerk_theme_is_service_page();
	$service_label   = $strings['services_menu_label'] ?? 'Leistungen';
	$lang_group_label = $strings['header_language_group_label'] ?? ( 'en' === $lang ? 'Choose language' : 'Sprache wählen' );
	$lang_button_label = $strings['header_language_button_label'] ?? ( 'en' === $lang ? 'Change language' : 'Sprache wechseln' );
	$open_menu_label = $strings['header_open_menu_label'] ?? ( 'en' === $lang ? 'Open menu' : 'Menü öffnen' );
	$lang_option_de  = $strings['header_language_option_de'] ?? 'Deutsch';
	$lang_option_en  = $strings['header_language_option_en'] ?? 'English';
	$language_label  = 'en' === $lang ? 'EN' : 'DE';
	$other_short     = 'en' === $lang ? 'DE' : 'EN';
	$header_logo_alt = leadwerk_theme_get_string( 'header_logo_alt', 'ACM AIR CHARTER', $lang );
	$header_logo_aria = leadwerk_theme_get_string( 'header_logo_link_aria_label', '', $lang );
	$header_logo_fallback = leadwerk_theme_profile_is_ludwig() ? 'assets/images/logo.svg' : 'assets/images/Logo-final-weiss-rz_svg.svg';

	ob_start();
	?>
	<header class="site-header">
		<div class="header-row">
			<div class="header-logo">
				<a href="<?php echo esc_url( $home_url ); ?>"<?php echo '' !== $header_logo_aria ? ' aria-label="' . esc_attr( $header_logo_aria ) . '"' : ''; ?>>
					<img src="<?php echo esc_url( leadwerk_theme_get_option_image_url( 'header_logo', $header_logo_fallback ) ); ?>" alt="<?php echo esc_attr( $header_logo_alt ); ?>" width="920" height="210">
				</a>
			</div>
			<nav class="header-nav">
				<ul class="nav-menu">
					<li><a class="<?php echo leadwerk_theme_is_source_key( 'acm-thats-acm-v1' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-thats-acm-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-thats-acm-v1', $lang, "That's ACM" ) ); ?></a></li>
					<li class="has-submenu">
						<a class="<?php echo in_array( leadwerk_theme_get_current_source_key(), array( 'acm-charter-v1', 'acm-global-7500-v1', 'acm-global-6000-v1', 'acm-global-xrs-v1' ), true ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-charter-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-charter-v1', $lang, 'Charter' ) ); ?></a>
						<ul class="sub-menu">
							<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-charter-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-charter-v1', $lang, 'Charter' ) ); ?></a></li>
							<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-7500-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-7500-v1', $lang, 'Bombardier Global 7500' ) ); ?></a></li>
							<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-6000-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-6000-v1', $lang, 'Bombardier Global 6000' ) ); ?></a></li>
							<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-xrs-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-xrs-v1', $lang, 'Bombardier Global XRS' ) ); ?></a></li>
						</ul>
					</li>
					<li><a class="<?php echo leadwerk_theme_is_source_key( 'acm-aircraft-v1' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-aircraft-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-aircraft-v1', $lang, 'Aircraft Management' ) ); ?></a></li>
					<li><a class="<?php echo leadwerk_theme_is_source_key( 'acm-maintenance-v1' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-maintenance-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-maintenance-v1', $lang, 'Maintenance' ) ); ?></a></li>
					<li><a class="<?php echo leadwerk_theme_is_source_key( 'acm-careers-v1' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-careers-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-careers-v1', $lang, 'Karriere' ) ); ?></a></li>
					<li><a class="<?php echo leadwerk_theme_is_source_key( 'acm-contact-v1' ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-contact-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-contact-v1', $lang, 'Kontakt' ) ); ?></a></li>
				</ul>
			</nav>
			<div class="header-cta">
				<div class="header-lang" role="group" aria-label="<?php echo esc_attr( $lang_group_label ); ?>">
					<button class="header-lang-btn" type="button" aria-haspopup="true" aria-expanded="false" aria-label="<?php echo esc_attr( $lang_button_label ); ?>" title="<?php echo esc_attr( $lang_button_label ); ?>">
						<span class="header-lang-icon" aria-hidden="true">
							<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
						</span>
						<span class="header-lang-label"><?php echo esc_html( $language_label ); ?></span>
					</button>
					<div class="header-lang-dropdown" hidden>
						<a class="header-lang-option<?php echo 'de' === $lang ? ' is-active' : ''; ?>" href="<?php echo esc_url( $de_url ); ?>" hreflang="de" lang="de"><?php echo esc_html( $lang_option_de ); ?></a>
						<a class="header-lang-option<?php echo 'en' === $lang ? ' is-active' : ''; ?>" href="<?php echo esc_url( $en_url ); ?>" hreflang="en" lang="en"><?php echo esc_html( $lang_option_en ); ?></a>
					</div>
				</div>
			</div>
			<button class="mobile-menu-toggle" type="button" aria-label="<?php echo esc_attr( $open_menu_label ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
		<div class="mobile-menu">
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-thats-acm-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-thats-acm-v1', $lang, "That's ACM" ) ); ?></a>
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-charter-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-charter-v1', $lang, 'Charter' ) ); ?></a>
			<div class="sub-menu">
				<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-7500-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-7500-v1', $lang, 'Bombardier Global 7500' ) ); ?></a>
				<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-6000-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-6000-v1', $lang, 'Bombardier Global 6000' ) ); ?></a>
				<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-global-xrs-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-global-xrs-v1', $lang, 'Bombardier Global XRS' ) ); ?></a>
			</div>
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-aircraft-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-aircraft-v1', $lang, 'Aircraft Management' ) ); ?></a>
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-maintenance-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-maintenance-v1', $lang, 'Maintenance' ) ); ?></a>
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-careers-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-careers-v1', $lang, 'Karriere' ) ); ?></a>
			<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-contact-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-contact-v1', $lang, 'Kontakt' ) ); ?></a>
			<a class="mobile-menu-locale" href="<?php echo esc_url( $language_url ); ?>"><?php echo esc_html( $other_short ); ?></a>
		</div>
	</header>
	<?php

	return ob_get_clean();
}

/**
 * Render the dynamic footer block.
 *
 * @return string
 */
function leadwerk_theme_render_footer_block() {
	if ( is_404() && leadwerk_theme_profile_is_ludwig() && function_exists( 'leadwerk_theme_render_ludwig_exact_site_footer' ) ) {
		$ludwig_footer = leadwerk_theme_render_ludwig_exact_site_footer( 'ludwig-home-v1' );
		if ( '' !== trim( $ludwig_footer ) ) {
			return $ludwig_footer;
		}
	}

	if ( function_exists( 'leadwerk_theme_render_exact_site_footer' ) ) {
		$exact_footer = leadwerk_theme_render_exact_site_footer();
		if ( '' !== trim( $exact_footer ) ) {
			return $exact_footer;
		}
	}

	$lang             = leadwerk_theme_get_current_lang();
	$strings          = leadwerk_theme_get_theme_strings( $lang );
	$source_key       = leadwerk_theme_get_current_source_key();
	$footer_desc_key  = leadwerk_theme_is_legal_source_key( $source_key ) ? 'footer_desc_legal' : ( in_array( $source_key, array( 'acm-index-v1', 'acm-home-v1' ), true ) ? 'footer_desc_home' : 'footer_desc_general' );
	$footer_tagline_s = leadwerk_theme_get_string( 'footer_tagline', '', $lang );
	$footer_desc_text = '' !== trim( $footer_tagline_s ) ? $footer_tagline_s : (string) ( $strings[ $footer_desc_key ] ?? '' );
	$address          = nl2br( esc_html( leadwerk_theme_get_option_value( 'company_address', "Am Flughafen 12\n77836 Rheinmünster" ) ) );
	$phone            = leadwerk_theme_get_option_value( 'company_phone', '+49 7229 30405-0' );
	$email            = leadwerk_theme_get_option_value( 'company_email', 'info@acm.aero' );
	$maps_url         = leadwerk_theme_get_option_value( 'google_maps_url', '#' );
	$phone_prefix     = $strings['footer_phone_prefix'] ?? ( 'en' === $lang ? 'Phone:' : 'Tel.:' );
	$footer_camo_url  = trim( leadwerk_theme_get_option_value( 'footer_camo_url', '' ) );
	$footer_agb_url   = trim( leadwerk_theme_get_option_value( 'footer_agb_url', '' ) );
	$footer_logo_alt  = leadwerk_theme_get_string( 'footer_logo_alt', 'ACM Logo', $lang );
	$footer_logo_fallback = leadwerk_theme_profile_is_ludwig() ? 'assets/images/logo-inverted.svg' : 'assets/images/Logo-final-weiss-rz.png';
	$footer_wordmark_fallback = leadwerk_theme_profile_is_ludwig() ? 'assets/images/logo-inverted.svg' : 'assets/images/Schriftzug.svg';

	ob_start();
	?>
	<footer class="site-footer">
		<div class="footer-main">
			<div class="footer-col">
				<img src="<?php echo esc_url( leadwerk_theme_get_option_image_url( 'footer_logo', $footer_logo_fallback ) ); ?>" alt="<?php echo esc_attr( $footer_logo_alt ); ?>" class="footer-logo" width="922" height="212" loading="lazy">
				<p class="footer-desc"><?php echo esc_html( $footer_desc_text ); ?></p>
			</div>
			<div class="footer-col">
				<h4><?php echo esc_html( $strings['footer_services_heading'] ?? 'Leistungen' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-charter-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-charter-v1', $lang, 'Charter' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-aircraft-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-aircraft-v1', $lang, 'Aircraft Management' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-maintenance-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-maintenance-v1', $lang, 'Maintenance' ) ); ?></a></li>
					<?php if ( '' !== $footer_camo_url ) : ?>
					<li><a href="<?php echo esc_url( $footer_camo_url ); ?>"><?php echo esc_html( leadwerk_theme_get_string( 'footer_camo_link_label', 'CAMO', $lang ) ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="footer-col">
				<h4><?php echo esc_html( $strings['footer_company_heading'] ?? 'Unternehmen' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-thats-acm-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-thats-acm-v1', $lang, "That's ACM" ) ); ?></a></li>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-careers-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-careers-v1', $lang, 'Karriere' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-news-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-news-v1', $lang, 'News' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-contact-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-contact-v1', $lang, 'Kontakt' ) ); ?></a></li>
				</ul>
			</div>
			<div class="footer-col footer-contact">
				<h4><?php echo esc_html( $strings['footer_contact_heading'] ?? 'Kontakt' ); ?></h4>
				<div class="contact-item">
					<span class="contact-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
					<p><a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $address; ?></a></p>
				</div>
				<div class="contact-item">
					<span class="contact-icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
					<p><?php echo esc_html( $phone_prefix ); ?> <a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
				</div>
				<div class="contact-item">
					<span class="contact-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
					<p><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
				</div>
			</div>
		</div>
		<div class="footer-logo-full">
			<img src="<?php echo esc_url( leadwerk_theme_get_option_image_url( 'footer_wordmark', $footer_wordmark_fallback ) ); ?>" alt="<?php echo esc_attr( leadwerk_theme_get_string( 'footer_wordmark_alt', 'ACM AIR CHARTER', $lang ) ); ?>" class="footer-logo-full__img" width="972" height="176" loading="lazy">
			<div class="footer-logo-full__gradient" aria-hidden="true"></div>
		</div>
		<div class="footer-bottom">
			<p>
				<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-impressum-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-impressum-v1', $lang, 'Impressum' ) ); ?></a>&nbsp; &nbsp; &nbsp;|&nbsp; &nbsp; &nbsp;<a href="<?php echo esc_url( leadwerk_theme_get_page_url( 'acm-datenschutz-v1', $lang ) ); ?>"><?php echo esc_html( leadwerk_theme_get_page_title( 'acm-datenschutz-v1', $lang, 'Datenschutz' ) ); ?></a>
				<?php if ( '' !== $footer_agb_url ) : ?>
					&nbsp; &nbsp; &nbsp;|&nbsp; &nbsp; &nbsp;<a href="<?php echo esc_url( $footer_agb_url ); ?>"><?php echo esc_html( leadwerk_theme_get_string( 'footer_agb_link_label', 'AGB', $lang ) ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</footer>
	<?php

	return ob_get_clean();
}

/**
 * Prepare a stored HTML section before output.
 *
 * @param string $html HTML.
 * @return string
 */
function leadwerk_theme_prepare_section_html( $html ) {
	if ( preg_match( '#<form\b(?=[^>]*data-form-purpose=["\']schadenfall["\'])[^>]*>.*?</form>#si', (string) $html, $schadenfall_match ) ) {
		$form_markup = leadwerk_theme_get_schadenfall_form_markup( (string) ( $schadenfall_match[0] ?? '' ) );
		$html        = preg_replace( '#<form\b(?=[^>]*data-form-purpose=["\']schadenfall["\'])[^>]*>.*?</form>#si', $form_markup, (string) $html, 1 );
	} elseif ( preg_match( '#<form\b[^>]*data-validate[^>]*>.*?</form>#si', (string) $html ) ) {
		$form_markup = leadwerk_theme_get_contact_form_markup();
		$html        = preg_replace( '#<form\b[^>]*data-validate[^>]*>.*?</form>#si', $form_markup, (string) $html, 1 );
	} elseif ( false !== strpos( (string) $html, 'class="contact-form"' ) ) {
		$form_markup = leadwerk_theme_get_contact_form_markup();
		$html        = preg_replace( '#<form class="contact-form".*?</form>#si', $form_markup, (string) $html, 1 );
	}

	return leadwerk_theme_normalize_ludwig_cta_actions_markup( (string) $html );
}

/**
 * Return the dedicated Schadenfall form markup when configured.
 *
 * If no dedicated WPForms form is configured yet, keep the imported HTML form
 * instead of replacing it with the generic contact form.
 *
 * @param string $fallback_html Imported static form fallback.
 * @return string
 */
function leadwerk_theme_get_schadenfall_form_markup( $fallback_html = '' ) {
	$lang        = leadwerk_theme_get_current_lang();
	$form_config = trim(
		(string) (
			class_exists( 'Leadwerk_Translation_API' )
				? Leadwerk_Translation_API::get_localized_option( 'wpforms_schadenfall_form_id', $lang, '' )
				: leadwerk_theme_get_option_value( 'en' === $lang ? 'wpforms_schadenfall_form_id_en' : 'wpforms_schadenfall_form_id_de', '' )
		)
	);

	if ( '' === $form_config || ! shortcode_exists( 'wpforms' ) ) {
		return (string) $fallback_html;
	}

	$shortcode = leadwerk_theme_normalize_wpforms_shortcode( $form_config );
	if ( '' === $shortcode ) {
		return (string) $fallback_html;
	}

	$markup = (string) do_shortcode( $shortcode );
	if ( '' === trim( wp_strip_all_tags( $markup ) ) && false === strpos( $markup, '<form' ) && false === strpos( $markup, 'wpforms' ) ) {
		return (string) $fallback_html;
	}

	return '<div class="leadwerk-contact-form-slot leadwerk-contact-form-slot--schadenfall">' . $markup . '</div>';
}

/**
 * Ensure stored Ludwig CTA sections keep their button wrapper after import.
 *
 * Older imports may have saved the final homepage CTA with direct buttons under
 * .cta-section. The stylesheet expects .cta-actions for spacing and mobile stack.
 *
 * @param string $html Section HTML.
 * @return string
 */
function leadwerk_theme_normalize_ludwig_cta_actions_markup( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html || false === strpos( $html, 'cta-section' ) || false !== strpos( $html, 'cta-actions' ) ) {
		return $html;
	}

	$wrapped = '<?xml encoding="utf-8" ?><html><body><div id="leadwerk-cta-normalize-root">' . $html . '</div></body></html>';
	$dom     = new DOMDocument( '1.0', 'UTF-8' );
	libxml_use_internal_errors( true );
	$loaded = $dom->loadHTML( $wrapped );
	libxml_clear_errors();
	if ( ! $loaded ) {
		return $html;
	}

	$xpath = new DOMXPath( $dom );
	foreach ( $xpath->query( '//*[@id="leadwerk-cta-normalize-root"]//*[contains(concat(" ", normalize-space(@class), " "), " cta-section ")]' ) as $section ) {
		if ( ! $section instanceof DOMElement ) {
			continue;
		}
		if ( $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " cta-actions ")]', $section )->length > 0 ) {
			continue;
		}

		$links  = $xpath->query( './a[contains(concat(" ", normalize-space(@class), " "), " btn ")]', $section );
		$parent = $section;
		if ( 0 === $links->length ) {
			$container = $xpath->query( './*[contains(concat(" ", normalize-space(@class), " "), " container ")][1]', $section )->item( 0 );
			if ( $container instanceof DOMElement ) {
				$links  = $xpath->query( './a[contains(concat(" ", normalize-space(@class), " "), " btn ")]', $container );
				$parent = $container;
			}
		}

		if ( $links->length < 2 || ! $parent instanceof DOMElement ) {
			continue;
		}

		$first_link = $links->item( 0 );
		if ( ! $first_link instanceof DOMElement ) {
			continue;
		}

		$actions = $dom->createElement( 'div' );
		$actions->setAttribute( 'class', 'cta-actions reveal' );
		$parent->insertBefore( $actions, $first_link );

		$move_links = array();
		foreach ( $links as $link ) {
			if ( $link instanceof DOMElement ) {
				$move_links[] = $link;
			}
		}
		foreach ( $move_links as $link ) {
			$actions->appendChild( $link );
		}
	}

	$root = $xpath->query( '//*[@id="leadwerk-cta-normalize-root"]' )->item( 0 );
	if ( ! $root instanceof DOMElement ) {
		return $html;
	}

	$out = '';
	foreach ( $root->childNodes as $child ) {
		$out .= $dom->saveHTML( $child );
	}

	return '' !== trim( $out ) ? trim( $out ) : $html;
}

add_action( 'template_redirect', 'leadwerk_theme_render_missing_passportcard_en_shell' );
/**
 * Render the English PassportCard shell if the imported page is missing.
 *
 * This keeps /passportcard-en/ available with English content while the
 * standalone page import/registry catches up on an environment.
 *
 * @return void
 */
function leadwerk_theme_render_missing_passportcard_en_shell() {
	if ( ! is_404() ) {
		return;
	}

	$request_path = trim( (string) wp_parse_url( (string) $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( 'passportcard-en' !== $request_path || ! function_exists( 'leadwerk_theme_get_source_template_html' ) ) {
		return;
	}

	$html = leadwerk_theme_get_source_template_html( 'ludwig-passportcard-en-v1' );
	if ( '' === trim( $html ) ) {
		return;
	}

	if ( function_exists( 'leadwerk_theme_create_document_dom' ) && function_exists( 'leadwerk_theme_normalize_template_urls' ) ) {
		list( $dom, $xpath ) = leadwerk_theme_create_document_dom( $html );
		$root = $dom->documentElement;
		if ( $root instanceof DOMElement ) {
			leadwerk_theme_normalize_template_urls( $xpath, $root );
			$html = (string) $dom->saveHTML();
		}
	}

	$theme_uri = trailingslashit( LEADWERK_THEME_URI );
	$html      = str_replace(
		array(
			'href="css/styles.css"',
			"href='css/styles.css'",
			'src="js/main.js"',
			"src='js/main.js'",
			'href="assets/images/favicon.svg"',
			"href='assets/images/favicon.svg'",
		),
		array(
			'href="' . esc_url( $theme_uri . 'css/styles.css' ) . '"',
			"href='" . esc_url( $theme_uri . 'css/styles.css' ) . "'",
			'src="' . esc_url( $theme_uri . 'js/main.js' ) . '"',
			"src='" . esc_url( $theme_uri . 'js/main.js' ) . "'",
			'href="' . esc_url( $theme_uri . 'assets/images/favicon.svg' ) . '"',
			"href='" . esc_url( $theme_uri . 'assets/images/favicon.svg' ) . "'",
		),
		$html
	);

	status_header( 200 );
	header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local source shell, URLs normalized above.
	exit;
}

/**
 * Return contact form markup or fallback.
 *
 * @return string
 */
function leadwerk_theme_get_contact_form_markup() {
	$lang        = leadwerk_theme_get_current_lang();
	$form_config = trim(
		(string) (
			class_exists( 'Leadwerk_Translation_API' )
				? Leadwerk_Translation_API::get_localized_option( 'wpforms_form_id', $lang, '' )
				: leadwerk_theme_get_option_value( 'en' === $lang ? 'wpforms_form_id_en' : 'wpforms_form_id_de', '' )
		)
	);
	$strings     = leadwerk_theme_get_theme_strings( $lang );
	$fallback    = '<div class="contact-form-placeholder">' . esc_html( $strings['wpforms_missing'] ?? 'WPForms configuration missing.' ) . '</div>';

	if ( '' === $form_config ) {
		return $fallback;
	}

	if ( ! shortcode_exists( 'wpforms' ) ) {
		return $fallback;
	}

	$shortcode = leadwerk_theme_normalize_wpforms_shortcode( $form_config );
	if ( '' === $shortcode ) {
		return $fallback;
	}

	$markup = (string) do_shortcode( $shortcode );
	if ( '' === trim( wp_strip_all_tags( $markup ) ) && false === strpos( $markup, '<form' ) && false === strpos( $markup, 'wpforms' ) ) {
		return $fallback;
	}

	return '<div class="leadwerk-contact-form-slot">' . $markup . '</div>';
}

/**
 * Normalize a stored WPForms value into a valid shortcode.
 *
 * @param string $value Stored option value.
 * @return string
 */
function leadwerk_theme_normalize_wpforms_shortcode( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	if ( 0 === stripos( $value, '[wpforms' ) ) {
		return $value;
	}

	if ( preg_match( '/^\d+$/', $value ) ) {
		return '[wpforms id="' . absint( $value ) . '" title="false" description="false"]';
	}

	return '';
}

/**
 * Get current language.
 *
 * @return string
 */
function leadwerk_theme_get_current_lang() {
	$post_id = get_queried_object_id();
	if ( $post_id && class_exists( 'Leadwerk_Translation_API' ) ) {
		return Leadwerk_Translation_API::get_post_language( $post_id );
	}

	if ( class_exists( 'Leadwerk_Translation_API' ) && method_exists( 'Leadwerk_Translation_API', 'get_current_request_language' ) ) {
		return Leadwerk_Translation_API::get_current_request_language();
	}

	return 'de';
}

/**
 * Get current source key.
 *
 * @return string
 */
function leadwerk_theme_get_current_source_key() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$candidates = array();
	$queried_id = (int) get_queried_object_id();
	if ( $queried_id > 0 ) {
		$candidates[] = $queried_id;
	}

	$queried_object = get_queried_object();
	if ( $queried_object instanceof WP_Post ) {
		$candidates[] = (int) $queried_object->ID;
	}

	global $post;
	if ( $post instanceof WP_Post ) {
		$candidates[] = (int) $post->ID;
	}

	$front_page_id = (int) get_option( 'page_on_front' );
	if ( function_exists( 'is_front_page' ) && is_front_page() && $front_page_id > 0 ) {
		$candidates[] = $front_page_id;
	}

	foreach ( array_values( array_unique( array_filter( $candidates ) ) ) as $post_id ) {
		$source_key = trim( (string) get_post_meta( $post_id, 'leadwerk_source_key', true ) );
		if ( '' !== $source_key ) {
			$cached = $source_key;
			return $cached;
		}
	}

	$cached = '';
	return $cached;
}

/**
 * Whether current page matches a source key.
 *
 * @param string $source_key Source key.
 * @return bool
 */
function leadwerk_theme_is_source_key( $source_key ) {
	return leadwerk_theme_get_current_source_key() === $source_key;
}

/**
 * Whether the current page is one of the service pages.
 *
 * @return bool
 */
function leadwerk_theme_is_service_page() {
	return in_array(
		leadwerk_theme_get_current_source_key(),
		array( 'acm-retirement-v1', 'acm-investment-v1', 'acm-real-estate-v1', 'acm-inheritance-v1' ),
		true
	);
}

/**
 * Whether a source key belongs to a legal page.
 *
 * @param string $source_key Source key.
 * @return bool
 */
function leadwerk_theme_is_legal_source_key( $source_key ) {
	return in_array( $source_key, array( 'acm-impressum-v1', 'acm-datenschutz-v1', 'ludwig-impressum-v1', 'ludwig-datenschutz-v1', 'ludwig-erstinformation-v1' ), true );
}

/**
 * Get alternate language URL for current page.
 *
 * @return string
 */
function leadwerk_theme_get_alternate_language_url() {
	$post_id = get_queried_object_id();
	if ( ! $post_id || ! class_exists( 'Leadwerk_Translation_API' ) ) {
		return home_url( '/' );
	}

	$target_lang = 'en' === leadwerk_theme_get_current_lang() ? 'de' : 'en';
	$current_url = get_permalink( $post_id );
	$fallback    = is_string( $current_url ) && '' !== $current_url ? $current_url : home_url( '/' );
	$alternate   = Leadwerk_Translation_API::get_translation_url( $post_id, $target_lang, '' );

	return '' !== $alternate ? $alternate : $fallback;
}

/**
 * Get a page ID by source key and language.
 *
 * @param string $source_key Source key.
 * @param string $lang       Language code.
 * @return int
 */
function leadwerk_theme_get_page_id( $source_key, $lang ) {
	if ( class_exists( 'Leadwerk_Translation_API' ) ) {
		return Leadwerk_Translation_API::get_post_by_source_key( $source_key, $lang );
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'leadwerk_source_key',
					'value' => $source_key,
				),
				array(
					'key'   => 'leadwerk_lang',
					'value' => $lang,
				),
			),
		)
	);

	$ids = $query->get_posts();
	return ! empty( $ids ) ? (int) $ids[0] : 0;
}

/**
 * Get a page URL by source key.
 *
 * @param string $source_key Source key.
 * @param string $lang       Language code.
 * @param string $fallback   Fallback URL.
 * @return string
 */
function leadwerk_theme_get_page_url( $source_key, $lang, $fallback = '#' ) {
	if ( class_exists( 'Leadwerk_Translation_API' ) ) {
		return Leadwerk_Translation_API::get_post_url_by_source_key( $source_key, $lang, $fallback );
	}

	$page_id = leadwerk_theme_get_page_id( $source_key, $lang );
	return $page_id ? get_permalink( $page_id ) : $fallback;
}

/**
 * DE/EN URLs for shell header/footer language links (aligned with Leadwerk translation URLs).
 * On single acm_news, uses the translated post pair; otherwise the current page source_key.
 *
 * @return array{de: string, en: string}
 */
function leadwerk_theme_get_header_footer_lang_pair_urls() {
	if ( leadwerk_theme_profile_is_ludwig() ) {
		$post_id = get_queried_object_id();
		$key     = leadwerk_theme_get_current_source_key();
		$de_url  = leadwerk_theme_get_page_url( $key, 'de', home_url( '/' ) );
		$en_url  = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_translation_url( $post_id, 'en', '' ) : '';

		if ( '' === $en_url ) {
			$en_url = $de_url;
		}

		return array(
			'de' => $de_url,
			'en' => $en_url,
		);
	}

	$de_fb = leadwerk_theme_get_page_url( 'acm-news-v1', 'de', home_url( '/' ) );
	$en_fb = leadwerk_theme_get_page_url( 'acm-news-v1', 'en', home_url( '/en/' ) );

	if ( class_exists( 'Leadwerk_Translation_API' ) ) {
		$post_id = get_queried_object_id();
		if ( $post_id && is_singular( 'acm_news' ) ) {
			return array(
				'de' => Leadwerk_Translation_API::get_translation_url( $post_id, 'de', $de_fb ),
				'en' => Leadwerk_Translation_API::get_translation_url( $post_id, 'en', $en_fb ),
			);
		}
	}

	$key = leadwerk_theme_get_current_source_key();

	return array(
		'de' => leadwerk_theme_get_page_url( $key, 'de', home_url( '/' ) ),
		'en' => leadwerk_theme_get_page_url( $key, 'en', home_url( '/en/' ) ),
	);
}

/**
 * Get a page title by source key.
 *
 * @param string $source_key Source key.
 * @param string $lang       Language code.
 * @param string $fallback   Fallback label.
 * @return string
 */
function leadwerk_theme_get_page_title( $source_key, $lang, $fallback = '' ) {
	$page_id = leadwerk_theme_get_page_id( $source_key, $lang );
	if ( ! $page_id ) {
		return $fallback;
	}

	$title = trim( (string) get_the_title( $page_id ) );
	if ( '' === $title ) {
		return $fallback;
	}
	$decoded = trim( html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

	return '' !== $decoded ? $decoded : $fallback;
}

/**
 * Get an option value through Leadwerk Fields.
 *
 * @param string $field_name Field name.
 * @param string $default    Default.
 * @return string
 */
function leadwerk_theme_get_option_value( $field_name, $default = '' ) {
	$value = null;
	if ( class_exists( 'Leadwerk_Fields_API' ) ) {
		$value = Leadwerk_Fields_API::get_field( $field_name, 'option' );
	} elseif ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, 'option' );
	}
	if ( null !== $value && '' !== trim( (string) $value ) ) {
		return (string) $value;
	}

	return $default;
}

/**
 * Get an option image URL.
 *
 * @param string $field_name   Field name.
 * @param string $default_path Theme-relative fallback path.
 * @return string
 */
function leadwerk_theme_get_option_image_url( $field_name, $default_path ) {
	$value = null;
	if ( class_exists( 'Leadwerk_Fields_API' ) ) {
		$value = Leadwerk_Fields_API::get_field( $field_name, 'option' );
	} elseif ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, 'option' );
	}
	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_url( (int) $value );
		if ( $url ) {
			return $url;
		}
	}

	return LEADWERK_THEME_URI . '/' . ltrim( $default_path, '/' );
}

/**
 * Attachment ID for an image option (Leadwerk Fields / ACF option).
 *
 * @param string $field_name Field name.
 * @return int
 */
function leadwerk_theme_get_option_image_id( $field_name ) {
	$value = null;
	if ( class_exists( 'Leadwerk_Fields_API' ) ) {
		$value = Leadwerk_Fields_API::get_field( $field_name, 'option' );
	} elseif ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, 'option' );
	}
	return is_numeric( $value ) ? (int) $value : 0;
}

/**
 * Format multiline address for safe HTML (line breaks only).
 *
 * @param string $raw Raw textarea.
 * @return string
 */
function leadwerk_theme_format_address_lines_html( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$parts = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$parts[] = esc_html( $line );
		}
	}

	return implode( '<br />', $parts );
}

/**
 * Merge only non-empty translated string values into defaults.
 *
 * @param array<string,string> $defaults Base strings.
 * @param array<string,mixed>  $translations Candidate translations.
 * @return array<string,string>
 */
function leadwerk_theme_merge_non_empty_strings( $defaults, $translations ) {
	$merged = is_array( $defaults ) ? $defaults : array();

	foreach ( (array) $translations as $key => $value ) {
		$key = sanitize_key( (string) $key );
		if ( '' === $key ) {
			continue;
		}

		$value = (string) $value;
		if ( '' === trim( $value ) ) {
			continue;
		}

		$merged[ $key ] = $value;
	}

	return $merged;
}

/**
 * Get language-aware theme strings.
 *
 * @param string|null $lang Optional language code.
 * @return array<string,string>
 */
function leadwerk_theme_get_theme_strings( $lang = null ) {
	$lang     = $lang ?: leadwerk_theme_get_current_lang();
	$defaults = 'en' === $lang
		? array(
			'services_menu_label' => 'Services',
			'header_language_group_label' => 'Choose language',
			'header_language_button_label' => 'Change language',
			'header_open_menu_label' => 'Open menu',
			'header_language_option_de' => 'Deutsch',
			'header_language_option_en' => 'English',
			'header_contact_cta_label' => 'Contact us',
			'header_logo_alt' => 'ACM AIR CHARTER Logo',
			'header_logo_link_aria_label' => 'ACM AIR CHARTER Home',
			'footer_tagline' => 'Premium Business Aviation from a single source — Charter, Management, Maintenance.',
			'footer_copyright' => '© 2026 ACM AIR CHARTER GmbH. All rights reserved.',
			'footer_legal_heading' => 'Legal',
			'footer_social_heading' => 'Follow us',
			'footer_agb_link_label' => 'Terms and conditions',
			'footer_camo_link_label' => 'CAMO',
			'footer_social_linkedin_aria' => 'LinkedIn',
			'footer_social_instagram_aria' => 'Instagram',
			'footer_logo_alt' => 'ACM Logo',
			'footer_wordmark_alt' => 'ACM AIR CHARTER',
			'footer_services_heading' => 'Services',
			'footer_company_heading' => 'Company',
			'footer_contact_heading' => 'Contact',
			'footer_menu_home' => 'Home',
			'footer_phone_prefix' => 'Phone:',
			'footer_desc_home' => 'ACM AIR CHARTER — Premium Business Aviation. Charter flights with Bombardier Global jets, professional Aircraft Management, certified Maintenance (Part-145) and CAMO from a single source.',
			'footer_desc_general' => 'ACM AIR CHARTER offers premium business aviation services from Rheinmünster, Germany. Charter, Aircraft Management, Maintenance and CAMO — everything from one trusted partner.',
			'footer_desc_legal' => 'ACM AIR CHARTER GmbH — Premium Business Aviation. Charter, Aircraft Management, Maintenance (Part-145) and CAMO services from a single source.',
			'contact_privacy_link_label' => 'Privacy policy',
			'structured_open_link_label' => 'Open link',
			'ui_learn_more_label' => 'Learn more',
			'ui_step_label' => 'Step',
			'ui_steps_nav_label' => 'Steps',
			'ui_prev_step_label' => 'Previous step',
			'ui_next_step_label' => 'Next step',
			'ui_swipe_or_click_hint' => 'Swipe or click',
			'ui_close_label' => 'Close',
			'news_read_more_label' => 'Read more',
			'store_badge_apple_aria_label' => 'Download on the App Store',
			'store_badge_apple_alt' => 'Download on the App Store',
			'store_badge_google_aria_label' => 'Get it on Google Play',
			'store_badge_google_alt' => 'Get it on Google Play',
			'legacy_home_hero_title_gradient' => 'Save your city center.',
			'legacy_home_hero_typewriter_words' => 'Shop local.|Find deals.|Discover fashion.|Strengthen your city.',
			'legacy_home_hero_cta_text' => 'Download app',
			'legacy_home_why_label' => 'Why U-like-it?',
			'legacy_home_why_title' => 'Because local is simply better.',
			'legacy_home_app_steps_title' => 'How the app works',
			'legacy_home_packages_label' => 'These are',
			'legacy_home_packages_title' => 'U like it packages',
			'legacy_home_solutions_label' => 'For retailers and gastronomy',
			'legacy_home_solutions_title' => 'Our solutions for businesses',
			'legacy_home_solutions_register_text' => 'Register business',
			'legacy_home_faq_label' => 'FAQ',
			'legacy_home_faq_title' => 'Everything you need to know',
			'legacy_home_cta_title' => "Join now\nand experience local deals.",
			'legacy_home_cta_primary_text' => 'Register business',
			'legacy_home_cta_secondary_text' => 'Download app',
			'legacy_home_ticker_items' => 'TROUSERS|SWEATERS|JACKETS|HATS|ACCESSORIES|DRESSES|SHOES|BAGS|JEANS|COATS|SHIRTS|FABRICS',
			'legacy_user_ticker_items' => 'FASHION|FOOD|CAFES|BOUTIQUES|RESTAURANTS|BARS|CONCEPT STORES|LOCAL DEALS',
			'legacy_merchant_benefits_ticker_items' => 'BOUTIQUES|FASHION|CONCEPT STORES|ACCESSORIES|LOCAL RETAILERS|SHOES|JEWELRY|INTERIOR',
			'legacy_merchant_dashboard_ticker_items' => 'MORE FOOTFALL|LOCAL DEALS|MEASURABLE|NO WASTAGE|QR REDEMPTION|PLANNABLE|SIMPLE|LOCAL',
			'wpforms_missing' => 'Please connect an English WPForms form ID or shortcode in Leadwerk options.',
			'wpforms_name_label' => 'Name',
			'wpforms_first_name_placeholder' => 'First name',
			'wpforms_last_name_placeholder' => 'Last name',
			'wpforms_email_label' => 'Email address',
			'wpforms_email_placeholder' => 'your@email.com',
			'wpforms_message_label' => 'Your message',
			'wpforms_message_placeholder' => 'What is it about? What is on your mind right now?',
			'wpforms_submit_label' => 'Send message',
			'wpforms_consent_prefix' => 'I have read the ',
			'wpforms_consent_link_label' => 'privacy policy',
			'wpforms_consent_suffix' => ' and agree.',
			'cmplz_title' => 'Manage consent',
			'cmplz_message' => 'To provide the best experience, we use technologies like cookies to store and/or access device information. If you consent to these technologies, we may process data such as browsing behavior or unique IDs on this site. If you do not consent or withdraw your consent, certain features and functions may be affected.',
			'cmplz_category_functional_title' => 'Functional',
			'cmplz_category_functional_desc' => 'The technical storage or access is strictly necessary for the legitimate purpose of enabling the use of a specific service explicitly requested by the subscriber or user, or for the sole purpose of carrying out the transmission of a communication over an electronic communications network.',
			'cmplz_category_preferences_title' => 'Preferences',
			'cmplz_category_preferences_desc' => 'The technical storage or access is necessary for the legitimate purpose of storing preferences that are not requested by the subscriber or user.',
			'cmplz_category_statistics_title' => 'Statistics',
			'cmplz_category_statistics_desc' => 'The technical storage or access that is used exclusively for statistical purposes.',
			'cmplz_category_statistics_anonymous_desc' => 'Without a subpoena, the voluntary consent of your Internet service provider, or additional records from third parties, the information stored or accessed for this purpose alone usually cannot be used to identify you.',
			'cmplz_category_marketing_title' => 'Marketing',
			'cmplz_category_marketing_desc' => 'The technical storage or access is required to create user profiles, send advertising, or track the user across one website or across several websites for similar marketing purposes.',
			'cmplz_always_active' => 'Always active',
			'cmplz_manage_options' => 'Manage options',
			'cmplz_manage_services' => 'Manage services',
			'cmplz_manage_vendors' => 'Manage {vendor_count} vendors',
			'cmplz_read_more_purposes' => 'Read more about these purposes',
			'cmplz_accept' => 'Accept',
			'cmplz_deny' => 'Deny',
			'cmplz_view_preferences' => 'View preferences',
			'cmplz_save_preferences' => 'Save preferences',
			'cmplz_placeholder_accept' => 'Click here to accept {category} cookies and enable this content',
		)
		: array(
			'services_menu_label' => 'Leistungen',
			'header_language_group_label' => 'Sprache wählen',
			'header_language_button_label' => 'Sprache wechseln',
			'header_open_menu_label' => 'Menü öffnen',
			'header_language_option_de' => 'Deutsch',
			'header_language_option_en' => 'English',
			'header_contact_cta_label' => 'Kontakt aufnehmen',
			'header_logo_alt' => 'ACM AIR CHARTER Logo',
			'header_logo_link_aria_label' => 'ACM AIR CHARTER Startseite',
			'footer_tagline' => 'Premium Business Aviation aus einer Hand – Charter, Management, Maintenance.',
			'footer_copyright' => '© 2026 ACM AIR CHARTER GmbH. Alle Rechte vorbehalten.',
			'footer_legal_heading' => 'Rechtliches',
			'footer_social_heading' => 'Folgen Sie uns',
			'footer_agb_link_label' => 'AGB',
			'footer_camo_link_label' => 'CAMO',
			'footer_social_linkedin_aria' => 'LinkedIn',
			'footer_social_instagram_aria' => 'Instagram',
			'footer_logo_alt' => 'ACM Logo',
			'footer_wordmark_alt' => 'ACM AIR CHARTER',
			'footer_services_heading' => 'Leistungen',
			'footer_company_heading' => 'Unternehmen',
			'footer_contact_heading' => 'Kontakt',
			'footer_menu_home' => 'Startseite',
			'footer_phone_prefix' => 'Tel.:',
			'footer_desc_home' => 'ACM AIR CHARTER — Premium Business Aviation. Charterflüge mit Bombardier Global Jets, professionelles Aircraft Management, zertifizierte Maintenance (Part-145) und CAMO aus einer Hand.',
			'footer_desc_general' => 'ACM AIR CHARTER bietet Premium Business Aviation Services aus Rheinmünster. Charter, Aircraft Management, Maintenance und CAMO — alles aus einer Hand.',
			'footer_desc_legal' => 'ACM AIR CHARTER GmbH — Premium Business Aviation. Charter, Aircraft Management, Maintenance (Part-145) und CAMO aus einer Hand.',
			'contact_privacy_link_label' => 'Datenschutz',
			'structured_open_link_label' => 'Link öffnen',
			'ui_learn_more_label' => 'Mehr erfahren',
			'ui_step_label' => 'Schritt',
			'ui_steps_nav_label' => 'Schritte',
			'ui_prev_step_label' => 'Vorheriger Schritt',
			'ui_next_step_label' => 'Nächster Schritt',
			'ui_swipe_or_click_hint' => 'Wischen oder klicken',
			'ui_close_label' => 'Schließen',
			'news_read_more_label' => 'Weiterlesen',
			'store_badge_apple_aria_label' => 'Im App Store herunterladen',
			'store_badge_apple_alt' => 'Im App Store herunterladen',
			'store_badge_google_aria_label' => 'Bei Google Play herunterladen',
			'store_badge_google_alt' => 'Bei Google Play herunterladen',
			'legacy_home_hero_title_gradient' => 'Rette&nbsp;deine Innenstadt.',
			'legacy_home_hero_typewriter_words' => 'Shoppe lokal.|Finde Deals.|Entdecke Mode.|Stärke deine Stadt.',
			'legacy_home_hero_cta_text' => 'App herunterladen',
			'legacy_home_why_label' => 'Warum U-like-it?',
			'legacy_home_why_title' => 'Weil lokal einfach besser ist.',
			'legacy_home_app_steps_title' => 'So funktioniert die App',
			'legacy_home_packages_label' => 'Das sind',
			'legacy_home_packages_title' => 'U like it Pakete',
			'legacy_home_solutions_label' => 'Für Händler & Gastronomie',
			'legacy_home_solutions_title' => 'Unsere Lösungen für Unternehmen',
			'legacy_home_solutions_register_text' => 'Unternehmen registrieren',
			'legacy_home_faq_label' => 'FAQ',
			'legacy_home_faq_title' => 'Alles was du wissen musst',
			'legacy_home_cta_title' => "Jetzt mitmachen\nund lokale Deals erleben.",
			'legacy_home_cta_primary_text' => 'Unternehmen registrieren',
			'legacy_home_cta_secondary_text' => 'App herunterladen',
			'legacy_home_ticker_items' => 'HOSEN|PULLOVER|JACKEN|HÜTE|ACCESSOIRES|KLEIDER|SCHUHE|TASCHEN|JEANS|MÄNTEL|SHIRTS|STOFFE',
			'legacy_user_ticker_items' => 'FASHION|GASTRO|CAFES|BOUTIQUEN|RESTAURANTS|BARS|CONCEPT STORES|LOKALE DEALS',
			'legacy_merchant_benefits_ticker_items' => 'BOUTIQUEN|FASHION|CONCEPT STORES|ACCESSOIRES|LOKALE HÄNDLER|SCHUHE|SCHMUCK|INTERIOR',
			'legacy_merchant_dashboard_ticker_items' => 'MEHR FREQUENZ|LOKALE DEALS|MESSBAR|KEIN STREUVERLUST|QR-EINLÖSUNG|PLANBAR|EINFACH|LOKAL',
			'wpforms_missing' => 'Bitte hinterlege eine WPForms Formular-ID oder einen Shortcode fuer Deutsch in den Leadwerk Optionen.',
			'wpforms_name_label' => 'Name',
			'wpforms_first_name_placeholder' => 'Vorname',
			'wpforms_last_name_placeholder' => 'Nachname',
			'wpforms_email_label' => 'E-Mail-Adresse',
			'wpforms_email_placeholder' => 'deine@email.de',
			'wpforms_message_label' => 'Deine Nachricht',
			'wpforms_message_placeholder' => 'Worum geht es? Was beschaeftigt dich gerade?',
			'wpforms_submit_label' => 'Nachricht senden',
			'wpforms_consent_prefix' => 'Ich habe die ',
			'wpforms_consent_link_label' => 'Datenschutzbestimmungen',
			'wpforms_consent_suffix' => ' gelesen und bin einverstanden.',
			'cmplz_title' => 'Zustimmung verwalten',
			'cmplz_message' => 'Um dir ein optimales Erlebnis zu bieten, verwenden wir Technologien wie Cookies, um Geräteinformationen zu speichern und/oder darauf zuzugreifen. Wenn du diesen Technologien zustimmst, können wir Daten wie das Surfverhalten oder eindeutige IDs auf dieser Website verarbeiten. Wenn du deine Zustimmung nicht erteilst oder zurückziehst, können bestimmte Merkmale und Funktionen beeinträchtigt werden.',
			'cmplz_category_functional_title' => 'Funktional',
			'cmplz_category_functional_desc' => 'Die technische Speicherung oder der Zugang ist unbedingt erforderlich für den rechtmäßigen Zweck, die Nutzung eines bestimmten Dienstes zu ermöglichen, der vom Teilnehmer oder Nutzer ausdrücklich gewünscht wird, oder für den alleinigen Zweck, die Übertragung einer Nachricht über ein elektronisches Kommunikationsnetz durchzuführen.',
			'cmplz_category_preferences_title' => 'Preferences',
			'cmplz_category_preferences_desc' => 'The technical storage or access is necessary for the legitimate purpose of storing preferences that are not requested by the subscriber or user.',
			'cmplz_category_statistics_title' => 'Statistiken',
			'cmplz_category_statistics_desc' => 'The technical storage or access that is used exclusively for statistical purposes.',
			'cmplz_category_statistics_anonymous_desc' => 'Die technische Speicherung oder der Zugriff, der ausschließlich zu anonymen statistischen Zwecken verwendet wird. Ohne eine Vorladung, die freiwillige Zustimmung deines Internetdienstanbieters oder zusätzliche Aufzeichnungen von Dritten können die zu diesem Zweck gespeicherten oder abgerufenen Informationen allein in der Regel nicht dazu verwendet werden, dich zu identifizieren.',
			'cmplz_category_marketing_title' => 'Marketing',
			'cmplz_category_marketing_desc' => 'Die technische Speicherung oder der Zugriff ist erforderlich, um Nutzerprofile zu erstellen, um Werbung zu versenden oder um den Nutzer auf einer Website oder über mehrere Websites hinweg zu ähnlichen Marketingzwecken zu verfolgen.',
			'cmplz_always_active' => 'Immer aktiv',
			'cmplz_manage_options' => 'Optionen verwalten',
			'cmplz_manage_services' => 'Dienste verwalten',
			'cmplz_manage_vendors' => 'Verwalten von {vendor_count}-Lieferanten',
			'cmplz_read_more_purposes' => 'Lese mehr über diese Zwecke',
			'cmplz_accept' => 'Akzeptieren',
			'cmplz_deny' => 'Ablehnen',
			'cmplz_view_preferences' => 'Einstellungen ansehen',
			'cmplz_save_preferences' => 'Einstellungen speichern',
			'cmplz_placeholder_accept' => 'Klicke hier, um {category}-Cookies zu akzeptieren und diesen Inhalt zu aktivieren',
		);

	if ( leadwerk_theme_profile_is_ludwig() ) {
		$defaults = array_merge(
			$defaults,
			'en' === $lang
				? array(
					'header_contact_cta_label' => 'Book appointment',
					'header_logo_alt' => 'Ludwig Oelze',
					'header_logo_link_aria_label' => 'Ludwig Oelze Home',
					'footer_tagline' => 'More than financial advice. Your loyal partner for insurance, protection and finances.',
					'footer_copyright' => '© 2026 Ludwig Oelze. All rights reserved.',
					'footer_services_heading' => 'Navigation',
					'footer_company_heading' => 'More',
					'footer_contact_heading' => 'Contact',
					'footer_menu_home' => 'Home',
					'footer_desc_home' => 'Insurance, retirement and financing advice in Baden-Baden.',
					'footer_desc_general' => 'Personal, long-term financial guidance with Ludwig Oelze.',
					'footer_desc_legal' => 'Legal information for Ludwig Oelze.',
					'footer_logo_alt' => 'Ludwig Oelze',
					'footer_wordmark_alt' => 'Ludwig Oelze',
					'contact_privacy_link_label' => 'Privacy policy',
					'wpforms_missing' => 'Please connect an English WPForms form ID or shortcode in Leadwerk options.',
					'wpforms_consent_prefix' => 'I have read the ',
					'wpforms_consent_link_label' => 'privacy policy',
					'wpforms_consent_suffix' => '.',
				)
				: array(
					'header_contact_cta_label' => 'Termin buchen',
					'header_logo_alt' => 'Ludwig Oelze',
					'header_logo_link_aria_label' => 'Ludwig Oelze Startseite',
					'footer_tagline' => 'Mehr als nur Finanzberatung. Dein loyaler Partner für Versicherungen, Vorsorge und Finanzen.',
					'footer_copyright' => '© 2026 Ludwig Oelze. Alle Rechte vorbehalten.',
					'footer_services_heading' => 'Navigation',
					'footer_company_heading' => 'Mehr',
					'footer_contact_heading' => 'Kontakt',
					'footer_menu_home' => 'Start',
					'footer_desc_home' => 'Versicherungs-, Vorsorge- und Finanzberatung in Baden-Baden.',
					'footer_desc_general' => 'Persönliche, langfristige Finanzberatung mit Ludwig Oelze.',
					'footer_desc_legal' => 'Rechtliche Informationen zu Ludwig Oelze.',
					'footer_logo_alt' => 'Ludwig Oelze',
					'footer_wordmark_alt' => 'Ludwig Oelze',
					'contact_privacy_link_label' => 'Datenschutzerklärung',
					'wpforms_missing' => 'Bitte hinterlege eine WPForms Formular-ID oder einen Shortcode für Deutsch in den Leadwerk Optionen.',
					'wpforms_consent_prefix' => 'Ich habe die ',
					'wpforms_consent_link_label' => 'Datenschutzerklärung',
					'wpforms_consent_suffix' => ' gelesen.',
				)
		);
	}

	$package_strings = class_exists( 'Leadwerk_Translation_API' )
		? Leadwerk_Translation_API::get_package_strings( 'theme_strings', $lang, array() )
		: array();

	if ( ! empty( $package_strings ) ) {
		return leadwerk_theme_merge_non_empty_strings( $defaults, $package_strings );
	}

	$raw = class_exists( 'Leadwerk_Translation_API' )
		? Leadwerk_Translation_API::get_localized_option( 'theme_strings', $lang, '' )
		: leadwerk_theme_get_option_value( 'en' === $lang ? 'theme_strings_en' : 'theme_strings_de', '' );

	if ( is_array( $raw ) ) {
		return leadwerk_theme_merge_non_empty_strings( $defaults, $raw );
	}

	if ( '' === trim( $raw ) ) {
		return $defaults;
	}

	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? leadwerk_theme_merge_non_empty_strings( $defaults, $decoded ) : $defaults;
}

/**
 * Get one translated theme string with a fallback.
 *
 * @param string      $key      String key.
 * @param string      $fallback Fallback label.
 * @param string|null $lang     Optional language code.
 * @return string
 */
function leadwerk_theme_get_string( $key, $fallback = '', $lang = null ) {
	$strings = leadwerk_theme_get_theme_strings( $lang );
	$value   = isset( $strings[ $key ] ) ? trim( (string) $strings[ $key ] ) : '';

	return '' !== $value ? $value : (string) $fallback;
}

/**
 * Get one translated string list split by pipes or line breaks.
 *
 * @param string        $key      String key.
 * @param array<string> $fallback Fallback items.
 * @param string|null   $lang     Optional language code.
 * @return array<string>
 */
function leadwerk_theme_get_string_list( $key, $fallback = array(), $lang = null ) {
	$raw = leadwerk_theme_get_string( $key, '', $lang );
	if ( '' === trim( $raw ) ) {
		return array_values( array_filter( array_map( 'trim', (array) $fallback ) ) );
	}

	$items = preg_split( '/\r\n|\r|\n|\|/', $raw );
	return array_values( array_filter( array_map( 'trim', (array) $items ) ) );
}

/**
 * Get WPForms contact-form translations for the active language.
 *
 * @param string|null $lang Optional language code.
 * @return array<string,string>
 */
function leadwerk_theme_get_wpforms_translations( $lang = null ) {
	$strings = leadwerk_theme_get_theme_strings( $lang );

	return array(
		'nameLabel'            => (string) ( $strings['wpforms_name_label'] ?? '' ),
		'firstNamePlaceholder' => (string) ( $strings['wpforms_first_name_placeholder'] ?? '' ),
		'lastNamePlaceholder'  => (string) ( $strings['wpforms_last_name_placeholder'] ?? '' ),
		'emailLabel'           => (string) ( $strings['wpforms_email_label'] ?? '' ),
		'emailPlaceholder'     => (string) ( $strings['wpforms_email_placeholder'] ?? '' ),
		'messageLabel'         => (string) ( $strings['wpforms_message_label'] ?? '' ),
		'messagePlaceholder'   => (string) ( $strings['wpforms_message_placeholder'] ?? '' ),
		'submitLabel'          => (string) ( $strings['wpforms_submit_label'] ?? '' ),
		'consentPrefix'        => (string) ( $strings['wpforms_consent_prefix'] ?? '' ),
		'consentLinkLabel'     => (string) ( $strings['wpforms_consent_link_label'] ?? '' ),
		'consentSuffix'        => (string) ( $strings['wpforms_consent_suffix'] ?? '' ),
	);
}

/**
 * Get visible Complianz banner translations for the active language.
 *
 * @param string|null $lang Optional language code.
 * @return array<string,string>
 */
function leadwerk_theme_get_complianz_banner_translations( $lang = null ) {
	$lang         = $lang ?: leadwerk_theme_get_current_lang();
	$default_lang = class_exists( 'Leadwerk_Translation_API' ) ? Leadwerk_Translation_API::get_default_language() : 'de';
	if ( $lang === $default_lang ) {
		return array();
	}

	$strings = leadwerk_theme_get_theme_strings( $lang );

	return array(
		'title'                          => (string) ( $strings['cmplz_title'] ?? '' ),
		'message'                        => (string) ( $strings['cmplz_message'] ?? '' ),
		'functionalTitle'                => (string) ( $strings['cmplz_category_functional_title'] ?? '' ),
		'functionalDescription'          => (string) ( $strings['cmplz_category_functional_desc'] ?? '' ),
		'preferencesTitle'               => (string) ( $strings['cmplz_category_preferences_title'] ?? '' ),
		'preferencesDescription'         => (string) ( $strings['cmplz_category_preferences_desc'] ?? '' ),
		'statisticsTitle'                => (string) ( $strings['cmplz_category_statistics_title'] ?? '' ),
		'statisticsDescription'          => (string) ( $strings['cmplz_category_statistics_desc'] ?? '' ),
		'statisticsAnonymousDescription' => (string) ( $strings['cmplz_category_statistics_anonymous_desc'] ?? '' ),
		'marketingTitle'                 => (string) ( $strings['cmplz_category_marketing_title'] ?? '' ),
		'marketingDescription'           => (string) ( $strings['cmplz_category_marketing_desc'] ?? '' ),
		'alwaysActive'                   => (string) ( $strings['cmplz_always_active'] ?? '' ),
		'manageOptions'                  => (string) ( $strings['cmplz_manage_options'] ?? '' ),
		'manageServices'                 => (string) ( $strings['cmplz_manage_services'] ?? '' ),
		'manageVendors'                  => (string) ( $strings['cmplz_manage_vendors'] ?? '' ),
		'readMorePurposes'               => (string) ( $strings['cmplz_read_more_purposes'] ?? '' ),
		'accept'                         => (string) ( $strings['cmplz_accept'] ?? '' ),
		'deny'                           => (string) ( $strings['cmplz_deny'] ?? '' ),
		'viewPreferences'                => (string) ( $strings['cmplz_view_preferences'] ?? '' ),
		'savePreferences'                => (string) ( $strings['cmplz_save_preferences'] ?? '' ),
		'placeholderAccept'             => (string) ( $strings['cmplz_placeholder_accept'] ?? '' ),
	);
}

/**
 * Truncate a human-readable SEO title for Yoast pixel/width hints (character-based heuristic).
 *
 * @param string $title      Raw title.
 * @param int    $max_chars  Maximum characters before ellipsis.
 * @return string
 */
function leadwerk_theme_truncate_seo_title_for_yoast( $title, $max_chars = 58 ) {
	$title = trim( (string) $title );
	if ( '' === $title ) {
		return '';
	}
	if ( $max_chars < 8 ) {
		$max_chars = 8;
	}
	if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $title ) > $max_chars ) {
		return rtrim( mb_substr( $title, 0, $max_chars - 1 ) ) . '…';
	}
	if ( strlen( $title ) > $max_chars ) {
		return rtrim( substr( $title, 0, $max_chars - 1 ) ) . '…';
	}

	return $title;
}

/**
 * Build rendered page HTML for Yoast analysis on field-driven pages.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function leadwerk_theme_get_yoast_analysis_content( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || ! class_exists( 'Leadwerk_Content_Schema' ) ) {
		return '';
	}

	$group = Leadwerk_Content_Schema::get_group_for_post( $post_id );
	if ( ! $group || empty( $group['field_name'] ) ) {
		return '';
	}

	$field_name = $group['field_name'];
	$source_key = (string) get_post_meta( $post_id, 'leadwerk_source_key', true );

	// Prioritize ludwig_page_document if this is a Ludwig page, as the importer stores content there.
	if ( function_exists( 'leadwerk_theme_is_ludwig_source_key' ) && leadwerk_theme_is_ludwig_source_key( $source_key ) ) {
		$ludwig_doc = leadwerk_theme_get_managed_field_value( 'ludwig_page_document', $post_id );
		if ( ! empty( $ludwig_doc ) ) {
			$field_name = 'ludwig_page_document';
			$group = Leadwerk_Content_Schema::get_group( 'ludwig_page_document' );
			$group['field_name'] = 'ludwig_page_document';
		}
	}

	$value = leadwerk_theme_get_managed_field_value( $field_name, $post_id );
	
	$content = '';
	if ( function_exists( 'leadwerk_theme_render_exact_page_group' ) ) {
		$content = leadwerk_theme_render_exact_page_group( $group, $value, $post_id );
	} else {
		$content = leadwerk_theme_render_current_page_content( $post_id );
	}

	// Ludwig Yoast analysis only receives section HTML; append real header/footer from the same shell
	// so internal links and footer copy match the public page.
	if ( function_exists( 'leadwerk_theme_is_ludwig_source_key' ) && leadwerk_theme_is_ludwig_source_key( $source_key ) ) {
		$shell = '';
		if ( function_exists( 'leadwerk_theme_render_ludwig_exact_site_header' ) ) {
			$shell .= leadwerk_theme_render_ludwig_exact_site_header( $source_key );
		}
		if ( function_exists( 'leadwerk_theme_render_ludwig_exact_site_footer' ) ) {
			$shell .= ' ' . leadwerk_theme_render_ludwig_exact_site_footer( $source_key );
		}
		$shell = trim( $shell );
		if ( '' !== $shell ) {
			$content .= ' ' . $shell;
		}
	}

	if ( '' === trim( wp_strip_all_tags( $content ) ) && false === strpos( $content, '<img' ) ) {
		return '';
	}

	$clean_content = wp_kses_post( $content );
	
	// Pre-safe fallback for JSON encoding.
	$clean_content = (string) str_replace( array( "\r", "\n", "\t" ), ' ', $clean_content );
	$clean_content = (string) preg_replace( '/\s+/', ' ', $clean_content );

	return trim( $clean_content );
}

/**
 * Rebuild Yoast SEO Indexable for one post (admin list dots, admin bar) after meta-only changes.
 *
 * Importer writes Yoast meta via update_post_meta without a full save; Yoast normally
 * rebuilds indexables on wp_insert_post. This calls the same watcher Yoast registers.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function leadwerk_theme_rebuild_yoast_post_indexable( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || ! function_exists( 'YoastSEO' ) ) {
		return;
	}
	if ( ! class_exists( '\Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher', false ) ) {
		return;
	}

	try {
		$yoast = YoastSEO();
		if ( ! is_object( $yoast ) || ! isset( $yoast->classes ) || ! is_object( $yoast->classes ) || ! method_exists( $yoast->classes, 'get' ) ) {
			return;
		}

		$watcher = $yoast->classes->get( \Yoast\WP\SEO\Integrations\Watchers\Indexable_Post_Watcher::class );
		if ( is_object( $watcher ) && method_exists( $watcher, 'build_indexable' ) ) {
			$watcher->build_indexable( $post_id );
		}
	} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		return;
	}
}

/**
 * After saving a Leadwerk-managed page, refresh Yoast indexables (ACF-only saves may skip wp_insert_post).
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post.
 * @return void
 */
function leadwerk_theme_leadwerk_page_yoast_indexable_touch( $post_id, $post, $update ) {
	unset( $update );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( '' === (string) get_post_meta( $post_id, 'leadwerk_source_key', true ) ) {
		return;
	}

	leadwerk_theme_rebuild_yoast_post_indexable( $post_id );
}

add_action( 'save_post', 'leadwerk_theme_leadwerk_page_yoast_indexable_touch', 99, 3 );

/**
 * Feed rendered Leadwerk page content into Yoast's content analysis.
 *
 * Yoast analyses the editor content by default. Our ACF/Ludwig pages render from
 * fields and exact source shells, so the editor can appear empty even when the
 * public page contains headings, links, images and copy.
 *
 * @param string $hook_suffix Current admin hook.
 * @return void
 */
function leadwerk_theme_enqueue_admin_yoast_analysis( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || ! class_exists( 'WPSEO_Options' ) || ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = (int) $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = (int) $_POST['post_ID'];
	}

	if ( $post_id <= 0 ) {
		return;
	}

	$analysis_content = leadwerk_theme_get_yoast_analysis_content( $post_id );
	if ( '' === $analysis_content ) {
		return;
	}

	// Very large inline JSON can break the browser parser and leave the Yoast React metabox blank.
	$max_bytes = (int) apply_filters( 'leadwerk_yoast_analysis_inline_max_bytes', 350000 );
	if ( $max_bytes > 0 && strlen( $analysis_content ) > $max_bytes ) {
		$analysis_content = substr( $analysis_content, 0, $max_bytes );
	}

	$payload = array(
		'postId'          => $post_id,
		'renderedContent' => $analysis_content,
	);
	$json    = wp_json_encode(
		$payload,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);
	if ( false === $json ) {
		$payload['renderedContent'] = substr( wp_strip_all_tags( $analysis_content ), 0, 60000 );
		$json                       = wp_json_encode(
			$payload,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);
	}
	if ( false === $json ) {
		return;
	}

	// Do not add script dependencies on Yoast handles: that can reorder bundles and prevent the metabox from mounting.
	wp_enqueue_script(
		'leadwerk-admin-yoast-analysis',
		LEADWERK_THEME_URI . '/js/admin-yoast-analysis.js',
		array(),
		LEADWERK_THEME_VERSION,
		true
	);

	wp_add_inline_script(
		'leadwerk-admin-yoast-analysis',
		'window.leadwerkYoastAnalysis = ' . $json . ';',
		'before'
	);
}
add_action( 'admin_enqueue_scripts', 'leadwerk_theme_enqueue_admin_yoast_analysis', 100 );
