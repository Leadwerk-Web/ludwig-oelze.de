<?php
/**
 * CLI/HTTP diagnostic: Yoast analysis HTML length for Leadwerk/Ludwig pages.
 * Usage: php debug-yoast.php   (from WordPress root with this file copied, or adjust path below)
 */
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "--- Leadwerk Yoast diagnostics ---\n";
echo 'Leadwerk_Content_Schema: ' . ( class_exists( 'Leadwerk_Content_Schema' ) ? 'yes' : 'no' ) . "\n";
echo 'WPSEO_Options (Yoast): ' . ( class_exists( 'WPSEO_Options' ) ? 'yes' : 'no' ) . "\n";
echo 'leadwerk_theme_get_yoast_analysis_content: ' . ( function_exists( 'leadwerk_theme_get_yoast_analysis_content' ) ? 'yes' : 'no' ) . "\n";

$post_id = 0;
if ( function_exists( 'leadwerk_theme_get_page_id' ) ) {
	$post_id = (int) leadwerk_theme_get_page_id( 'acm-home-v1', 'de' );
	if ( ! $post_id ) {
		$post_id = (int) leadwerk_theme_get_page_id( 'ludwig-home-v1', 'de' );
	}
}
if ( ! $post_id ) {
	$pages = get_pages(
		array(
			'meta_key'   => 'leadwerk_source_key',
			'number'     => 1,
			'post_status'=> 'any',
		)
	);
	if ( ! empty( $pages ) && $pages[0] instanceof WP_Post ) {
		$post_id = (int) $pages[0]->ID;
	}
}
if ( ! $post_id ) {
	echo "No post ID found (no Leadwerk pages?).\n";
	exit( 1 );
}

$source_key = (string) get_post_meta( $post_id, 'leadwerk_source_key', true );
$lang       = (string) get_post_meta( $post_id, 'leadwerk_lang', true );

echo "Post ID: {$post_id}\n";
echo "leadwerk_source_key: {$source_key}\n";
echo "leadwerk_lang: {$lang}\n";

$group = class_exists( 'Leadwerk_Content_Schema' ) ? Leadwerk_Content_Schema::get_group_for_post( $post_id ) : null;
echo 'Schema group: ' . ( is_array( $group ) && ! empty( $group['field_name'] ) ? $group['field_name'] : '(none)' ) . "\n";

if ( ! function_exists( 'leadwerk_theme_get_yoast_analysis_content' ) ) {
	echo "Theme function missing — active theme may not be Leadwerk.\n";
	exit( 1 );
}

$content = leadwerk_theme_get_yoast_analysis_content( $post_id );
$plain     = wp_strip_all_tags( $content );
$word_tokens = preg_split( '/\s+/u', trim( $plain ), -1, PREG_SPLIT_NO_EMPTY );
$words       = is_array( $word_tokens ) ? count( $word_tokens ) : 0;
$img_count = substr_count( strtolower( $content ), '<img' );
$home      = home_url();
$a_internal = preg_match_all( '#href=["\']' . preg_quote( $home, '#' ) . '#i', $content );

echo 'Analysis HTML length (bytes): ' . strlen( $content ) . "\n";
echo 'Approx. word count (strip_tags): ' . (int) $words . "\n";
echo '<img> count: ' . $img_count . "\n";
echo 'Internal hrefs (home_url prefix): ' . ( false !== $a_internal ? (int) $a_internal : 0 ) . "\n";
echo "Snippet: " . substr( $content, 0, 240 ) . "\n";
