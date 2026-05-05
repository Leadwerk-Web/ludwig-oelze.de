<?php
/**
 * Ludwig exact renderer helpers.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a source key belongs to the Ludwig profile.
 *
 * @param string $source_key Source key.
 * @return bool
 */
function leadwerk_theme_is_ludwig_source_key( $source_key ) {
	return 0 === strpos( (string) $source_key, 'ludwig-' );
}

/**
 * Whether a structured group is the generic Ludwig document group.
 *
 * @param array<string,mixed> $group   Group schema.
 * @param int                 $post_id Optional post ID.
 * @return bool
 */
function leadwerk_theme_group_is_ludwig_document( $group, $post_id = 0 ) {
	if ( ! is_array( $group ) ) {
		return false;
	}

	return 'ludwig_page_document' === (string) ( $group['field_name'] ?? '' );
}

/**
 * Look up an attachment URL by imported source path.
 *
 * @param string $source_path Relative source path.
 * @return string
 */
function leadwerk_theme_get_attachment_url_by_source_path( $source_path ) {
	$source_path = trim( str_replace( '\\', '/', (string) $source_path ), '/' );
	if ( '' === $source_path ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'leadwerk_source_path',
					'value' => $source_path,
				),
			),
		)
	);

	$ids = $query->get_posts();
	if ( empty( $ids ) ) {
		return '';
	}

	$url = wp_get_attachment_url( (int) $ids[0] );
	return is_string( $url ) ? $url : '';
}

/**
 * Render the generic Ludwig document from stored section HTML.
 *
 * @param array<string,mixed> $group   Group schema.
 * @param mixed               $value   Stored value.
 * @param int                 $post_id Post ID.
 * @return string
 */
function leadwerk_theme_render_ludwig_page_group( $group, $value, $post_id = 0 ) {
	$document = is_array( $value ) ? $value : array();
	$sections = array_values( (array) ( $document['sections'] ?? array() ) );

	if ( empty( $sections ) && $post_id > 0 ) {
		$snapshot = get_post_meta( $post_id, '_leadwerk_last_good_ludwig_page_document', true );
		if ( is_array( $snapshot ) && ! empty( $snapshot['value']['sections'] ) ) {
			$sections = array_values( (array) $snapshot['value']['sections'] );
		}
	}

	if ( empty( $sections ) ) {
		$post = get_post( $post_id );
		$post_content = $post instanceof WP_Post ? trim( (string) $post->post_content ) : '';
		if ( '' !== $post_content && false === strpos( $post_content, 'wp:acf/' ) ) {
			return $post_content;
		}

		return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
			? leadwerk_theme_render_exact_runtime_notice( 'Ludwig document contains no renderable sections.', $post_id )
			: '';
	}

	$output = '';
	foreach ( $sections as $index => $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}

		$section_html = trim( (string) ( $section['section_html'] ?? '' ) );
		if ( '' === $section_html ) {
			continue;
		}

		$section_html = leadwerk_theme_prepare_section_html( $section_html );
		$section_html = leadwerk_theme_normalize_html_fragment_urls( $section_html );
		$output      .= $section_html;
	}

	if ( '' === trim( $output ) ) {
		return function_exists( 'leadwerk_theme_render_exact_runtime_notice' )
			? leadwerk_theme_render_exact_runtime_notice( 'Ludwig document rendered empty HTML.', $post_id )
			: '';
	}

	return $output;
}

/**
 * Extract and normalize the Ludwig header from the current shell.
 *
 * @param string $source_key Optional source key (e.g. for admin/Yoast when queried object is unset).
 * @return string
 */
function leadwerk_theme_render_ludwig_exact_site_header( $source_key = '' ) {
	$source_key = is_string( $source_key ) ? trim( $source_key ) : '';
	if ( '' === $source_key ) {
		$source_key = leadwerk_theme_get_current_source_key();
	}

	$html = leadwerk_theme_get_source_template_html( $source_key );
	if ( '' === trim( $html ) ) {
		return '';
	}

	list( $dom, $xpath ) = leadwerk_theme_create_document_dom( $html );
	$header = leadwerk_theme_dom_first( $xpath, '//body/header[1]' );
	if ( ! $header instanceof DOMElement ) {
		return '';
	}

	leadwerk_theme_normalize_template_urls( $xpath, $header );
	leadwerk_theme_ludwig_harden_navigation_markup( $xpath, $header );

	return leadwerk_theme_dom_outer_html( $header );
}

/**
 * Extract and normalize the Ludwig footer from the current shell.
 *
 * @param string $source_key Optional source key (e.g. for admin/Yoast when queried object is unset).
 * @return string
 */
function leadwerk_theme_render_ludwig_exact_site_footer( $source_key = '' ) {
	$source_key = is_string( $source_key ) ? trim( $source_key ) : '';
	if ( '' === $source_key ) {
		$source_key = leadwerk_theme_get_current_source_key();
	}

	$html = leadwerk_theme_get_source_template_html( $source_key );
	if ( '' === trim( $html ) ) {
		return '';
	}

	list( $dom, $xpath ) = leadwerk_theme_create_document_dom( $html );
	$footer = leadwerk_theme_dom_first( $xpath, '//body/footer[1]' );
	if ( ! $footer instanceof DOMElement ) {
		return '';
	}

	leadwerk_theme_normalize_template_urls( $xpath, $footer );
	leadwerk_theme_ludwig_harden_navigation_markup( $xpath, $footer );

	return leadwerk_theme_dom_outer_html( $footer );
}

/**
 * Replace non-link dropdown anchors with buttons and tighten target=_blank rel handling.
 *
 * @param DOMXPath   $xpath XPath.
 * @param DOMElement $root  Root element.
 * @return void
 */
function leadwerk_theme_ludwig_harden_navigation_markup( $xpath, $root ) {
	foreach ( leadwerk_theme_dom_query( $xpath, './/a[@href="#"] | .//a[@href=""]', $root ) as $anchor ) {
		if ( ! $anchor instanceof DOMElement || ! $anchor->parentNode instanceof DOMNode ) {
			continue;
		}

		$button = $anchor->ownerDocument->createElement( 'button' );
		$button->setAttribute( 'type', 'button' );

		foreach ( $anchor->attributes as $attribute ) {
			if ( in_array( $attribute->name, array( 'href', 'target', 'rel' ), true ) ) {
				continue;
			}
			$button->setAttribute( $attribute->name, (string) $attribute->value );
		}

		$class_name = trim( (string) $button->getAttribute( 'class' ) );
		if ( false === strpos( ' ' . $class_name . ' ', ' dropdown-toggle ' ) ) {
			$class_name = trim( $class_name . ' dropdown-toggle' );
			$button->setAttribute( 'class', $class_name );
		}

		if ( ! $button->hasAttribute( 'aria-expanded' ) ) {
			$button->setAttribute( 'aria-expanded', 'false' );
		}
		if ( ! $button->hasAttribute( 'aria-haspopup' ) ) {
			$button->setAttribute( 'aria-haspopup', 'true' );
		}

		while ( $anchor->firstChild ) {
			$button->appendChild( $anchor->firstChild );
		}

		$anchor->parentNode->replaceChild( $button, $anchor );
	}
}
