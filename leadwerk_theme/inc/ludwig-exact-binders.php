<?php
/**
 * Ludwig exact-shell binders for structured field groups.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function leadwerk_theme_bind_exact_ludwig_template( $template, $xpath, $section_node, $section ) {
	switch ( (string) $template ) {
		case 'ludwig_hero':
			leadwerk_theme_bind_exact_ludwig_hero( $xpath, $section_node, $section );
			break;
		case 'ludwig_trust_strip':
			leadwerk_theme_bind_exact_ludwig_trust_strip( $xpath, $section_node, $section );
			break;
		case 'ludwig_problem_cards':
			leadwerk_theme_bind_exact_ludwig_problem_cards( $xpath, $section_node, $section );
			break;
		case 'ludwig_split_story':
			leadwerk_theme_bind_exact_ludwig_split_story( $xpath, $section_node, $section );
			break;
		case 'ludwig_audience_tabs':
			leadwerk_theme_bind_exact_ludwig_audience_tabs( $xpath, $section_node, $section );
			break;
		case 'ludwig_pillars_cta':
			leadwerk_theme_bind_exact_ludwig_pillars_cta( $xpath, $section_node, $section );
			break;
		case 'ludwig_credential_grid':
			leadwerk_theme_bind_exact_ludwig_credential_grid( $xpath, $section_node, $section );
			break;
		case 'ludwig_testimonials':
			leadwerk_theme_bind_exact_ludwig_testimonials( $xpath, $section_node, $section );
			break;
		case 'ludwig_center_cta':
			leadwerk_theme_bind_exact_ludwig_center_cta( $xpath, $section_node, $section );
			break;
		case 'ludwig_intro_copy':
			leadwerk_theme_bind_exact_ludwig_intro_copy( $xpath, $section_node, $section );
			break;
		case 'ludwig_timeline':
			leadwerk_theme_bind_exact_ludwig_timeline( $xpath, $section_node, $section );
			break;
		case 'ludwig_comparison_table':
			leadwerk_theme_bind_exact_ludwig_comparison_table( $xpath, $section_node, $section );
			break;
		case 'ludwig_quote_callout':
			leadwerk_theme_bind_exact_ludwig_quote_callout( $xpath, $section_node, $section );
			break;
		case 'ludwig_feature_grid':
			leadwerk_theme_bind_exact_ludwig_feature_grid( $xpath, $section_node, $section );
			break;
		case 'ludwig_checklist_split':
			leadwerk_theme_bind_exact_ludwig_checklist_split( $xpath, $section_node, $section );
			break;
		case 'ludwig_feature_checklist_cta':
			leadwerk_theme_bind_exact_ludwig_feature_checklist_cta( $xpath, $section_node, $section );
			break;
		case 'ludwig_pricing_cards':
			leadwerk_theme_bind_exact_ludwig_pricing_cards( $xpath, $section_node, $section );
			break;
		case 'ludwig_faq':
			leadwerk_theme_bind_exact_ludwig_faq( $xpath, $section_node, $section );
			break;
		case 'ludwig_case_study':
			leadwerk_theme_bind_exact_ludwig_case_study( $xpath, $section_node, $section );
			break;
		case 'ludwig_contact_cards':
			leadwerk_theme_bind_exact_ludwig_contact_cards( $xpath, $section_node, $section );
			break;
		case 'ludwig_article_cards':
			leadwerk_theme_bind_exact_ludwig_article_cards( $xpath, $section_node, $section );
			break;
		case 'ludwig_customer_videos':
			leadwerk_theme_bind_exact_ludwig_customer_videos( $xpath, $section_node, $section );
			break;
		case 'ludwig_contact_form_split':
			leadwerk_theme_bind_exact_ludwig_contact_form_split( $xpath, $section_node, $section );
			break;
		case 'ludwig_location_map':
			leadwerk_theme_bind_exact_ludwig_location_map( $xpath, $section_node, $section );
			break;
		case 'ludwig_legal_document':
			leadwerk_theme_bind_exact_ludwig_legal_document( $xpath, $section_node, $section );
			break;
	}
}

function leadwerk_theme_ludwig_rows( $value ) {
	$rows = array();
	foreach ( (array) $value as $row ) {
		if ( is_array( $row ) ) {
			$rows[] = $row;
		}
	}
	return array_values( $rows );
}

function leadwerk_theme_ludwig_button_data( $section, $prefix ) {
	return array(
		'label'    => (string) ( $section[ $prefix . '_label' ] ?? '' ),
		'page_key' => (string) ( $section[ $prefix . '_page_key' ] ?? '' ),
		'url'      => (string) ( $section[ $prefix . '_url' ] ?? '' ),
	);
}

function leadwerk_theme_ludwig_has_visible_content( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}

	if ( is_numeric( $value ) ) {
		return 0 !== (int) $value;
	}

	if ( is_string( $value ) ) {
		return '' !== trim( wp_strip_all_tags( $value ) );
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $item ) {
			if ( leadwerk_theme_ludwig_has_visible_content( $item ) ) {
				return true;
			}
		}
	}

	return false;
}

function leadwerk_theme_ludwig_split_markup_blocks( $html ) {
	$html = (string) $html;
	if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
		return array();
	}

	$temp = new DOMDocument( '1.0', 'UTF-8' );
	libxml_use_internal_errors( true );
	$temp->loadHTML( '<?xml encoding="utf-8" ?><div id="leadwerk-ludwig-block-root">' . $html . '</div>' );
	libxml_clear_errors();

	$root = ( new DOMXPath( $temp ) )->query( '//*[@id="leadwerk-ludwig-block-root"]' )->item( 0 );
	if ( ! $root instanceof DOMNode ) {
		return array( $html );
	}

	$blocks = array();
	foreach ( $root->childNodes as $child ) {
		if ( $child instanceof DOMText ) {
			$text = trim( preg_replace( '/\s+/u', ' ', (string) $child->nodeValue ) );
			if ( '' !== $text ) {
				$blocks[] = esc_html( $text );
			}
			continue;
		}

		if ( ! $child instanceof DOMElement ) {
			continue;
		}

		$tag = strtolower( $child->tagName );
		if ( in_array( $tag, array( 'p', 'div', 'section', 'article' ), true ) ) {
			$inner = '';
			foreach ( $child->childNodes as $grandchild ) {
				$inner .= $temp->saveHTML( $grandchild );
			}
			$blocks[] = trim( $inner );
			continue;
		}

		$blocks[] = trim( $temp->saveHTML( $child ) );
	}

	$blocks = array_values(
		array_filter(
			$blocks,
			static function ( $block ) {
				return '' !== trim( wp_strip_all_tags( (string) $block ) );
			}
		)
	);

	return ! empty( $blocks ) ? $blocks : array( $html );
}

function leadwerk_theme_ludwig_remove_heading_descendants( $node ) {
	if ( ! $node instanceof DOMNode ) {
		return;
	}

	$targets = array();
	$walker  = static function ( $current ) use ( &$targets, &$walker ) {
		if ( ! $current instanceof DOMNode ) {
			return;
		}

		foreach ( $current->childNodes as $child ) {
			if ( ! $child instanceof DOMNode ) {
				continue;
			}
			if ( $child instanceof DOMElement && preg_match( '/^h[1-6]$/i', $child->tagName ) ) {
				$targets[] = $child;
				continue;
			}
			$walker( $child );
		}
	};

	$walker( $node );
	foreach ( $targets as $target ) {
		if ( $target->parentNode ) {
			$target->parentNode->removeChild( $target );
		}
	}
}

function leadwerk_theme_ludwig_direct_element_children( $node, $allowed_tags = array() ) {
	$children = array();
	if ( ! $node instanceof DOMNode ) {
		return $children;
	}

	$allowed_tags = array_map( 'strtolower', (array) $allowed_tags );
	foreach ( $node->childNodes as $child ) {
		if ( ! $child instanceof DOMElement ) {
			continue;
		}
		if ( ! empty( $allowed_tags ) && ! in_array( strtolower( $child->tagName ), $allowed_tags, true ) ) {
			continue;
		}
		$children[] = $child;
	}

	return $children;
}

function leadwerk_theme_ludwig_find_two_col_parts( $xpath, $section_node ) {
	$two_col = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"two-col")][1]', $section_node );
	if ( ! $two_col instanceof DOMElement ) {
		return array( null, null );
	}

	$text_node  = null;
	$image_node = null;
	foreach ( leadwerk_theme_ludwig_direct_element_children( $two_col ) as $child ) {
		$has_image = leadwerk_theme_dom_first( $xpath, './/img[1]', $child ) instanceof DOMElement;
		if ( $has_image && null === $image_node ) {
			$image_node = $child;
			continue;
		}
		if ( null === $text_node ) {
			$text_node = $child;
		}
	}

	return array( $text_node, $image_node );
}

function leadwerk_theme_ludwig_append_html( $node, $html ) {
	if ( ! $node instanceof DOMNode ) {
		return;
	}

	$html = (string) $html;
	if ( '' === trim( $html ) ) {
		return;
	}

	$temp = new DOMDocument( '1.0', 'UTF-8' );
	libxml_use_internal_errors( true );
	$temp->loadHTML( '<?xml encoding="utf-8" ?><div id="leadwerk-ludwig-fragment">' . $html . '</div>' );
	libxml_clear_errors();

	$fragment_nodes = ( new DOMXPath( $temp ) )->query( '//*[@id="leadwerk-ludwig-fragment"]/* | //*[@id="leadwerk-ludwig-fragment"]/text()' );
	if ( ! $fragment_nodes instanceof DOMNodeList ) {
		return;
	}

	foreach ( $fragment_nodes as $child ) {
		$node->appendChild( $node->ownerDocument->importNode( $child, true ) );
	}
}

function leadwerk_theme_ludwig_clone_or_create_element( $node, $tag, $doc ) {
	if ( $node instanceof DOMElement ) {
		return $node->cloneNode( true );
	}

	return $doc->createElement( (string) $tag );
}

function leadwerk_theme_ludwig_replace_style_property( $style, $property, $value ) {
	$style    = trim( (string) $style );
	$property = trim( (string) $property );
	$value    = trim( (string) $value );

	if ( '' === $property ) {
		return $style;
	}

	if ( preg_match( '/(?:^|;)\s*' . preg_quote( $property, '/' ) . '\s*:\s*[^;]+;?/i', $style ) ) {
		$style = preg_replace(
			'/(?:^|;)\s*' . preg_quote( $property, '/' ) . '\s*:\s*[^;]+;?/i',
			'; ' . $property . ': ' . $value . ';',
			$style,
			1
		);
		return trim( preg_replace( '/\s+/', ' ', (string) $style ), " ;" ) . ';';
	}

	$style = rtrim( $style, '; ' );
	if ( '' !== $style ) {
		$style .= '; ';
	}
	return $style . $property . ': ' . $value . ';';
}

function leadwerk_theme_ludwig_bind_image_element( $image, $value, $alt = '', $position = '' ) {
	if ( ! $image instanceof DOMElement ) {
		return;
	}

	$url = leadwerk_theme_resolve_media_url( $value );
	if ( '' !== trim( $url ) ) {
		leadwerk_theme_dom_set_attr( $image, 'src', $url );
	}
	if ( '' !== trim( (string) $alt ) ) {
		leadwerk_theme_dom_set_attr( $image, 'alt', $alt );
	}
	if ( '' !== trim( (string) $position ) ) {
		$style = leadwerk_theme_ludwig_replace_style_property( (string) $image->getAttribute( 'style' ), 'object-position', (string) $position );
		leadwerk_theme_dom_set_attr( $image, 'style', $style );
	}
}

function leadwerk_theme_ludwig_resolve_media_mapped_url( $value ) {
	$url = trim( (string) leadwerk_theme_resolve_media_url( $value ) );
	if ( '' === $url ) {
		return '';
	}

	return leadwerk_theme_map_template_href_to_url( $url );
}

function leadwerk_theme_ludwig_bind_leading_svg_label( $node, $label ) {
	if ( ! $node instanceof DOMElement ) {
		return;
	}

	$label = trim( wp_strip_all_tags( (string) $label ) );
	$svg   = null;
	foreach ( iterator_to_array( $node->childNodes, false ) as $child ) {
		if ( $child instanceof DOMElement && 'svg' === strtolower( $child->tagName ) ) {
			$svg = $child;
			break;
		}
	}

	$remove = array();
	foreach ( iterator_to_array( $node->childNodes, false ) as $child ) {
		if ( $child === $svg ) {
			continue;
		}
		$remove[] = $child;
	}
	foreach ( $remove as $child ) {
		if ( $child->parentNode === $node ) {
			$node->removeChild( $child );
		}
	}

	if ( '' === $label ) {
		return;
	}

	if ( $svg instanceof DOMElement ) {
		$node->appendChild( $node->ownerDocument->createTextNode( ' ' . $label ) );
		return;
	}

	leadwerk_theme_dom_set_text( $node, $label );
}

function leadwerk_theme_ludwig_bind_anchor_node( $anchor, $label, $page_key = '', $url = '' ) {
	if ( ! $anchor instanceof DOMElement ) {
		return;
	}

	$href = leadwerk_theme_resolve_exact_href( $page_key, $url );
	leadwerk_theme_dom_set_attr( $anchor, 'href', $href );

	if ( preg_match( '#^(?:mailto|tel):#i', $href ) || preg_match( '#^(?:https?:)?//#i', $href ) ) {
		if ( ! $anchor->hasAttribute( 'target' ) ) {
			$anchor->setAttribute( 'target', '_blank' );
		}
		$anchor->setAttribute( 'rel', 'noopener noreferrer' );
	} else {
		$anchor->removeAttribute( 'target' );
		$anchor->removeAttribute( 'rel' );
	}

	leadwerk_theme_dom_set_text( $anchor, (string) $label );
}

function leadwerk_theme_ludwig_rebuild_content_container( $container, $heading_node, $heading_html, $heading_tag, $blocks ) {
	if ( ! $container instanceof DOMElement ) {
		return;
	}

	leadwerk_theme_dom_clear( $container );
	if ( '' !== trim( wp_strip_all_tags( (string) $heading_html ) ) ) {
		$heading = leadwerk_theme_ludwig_clone_or_create_element( $heading_node, $heading_tag, $container->ownerDocument );
		leadwerk_theme_set_placeholder_markup( $heading, (string) $heading_html, 'heading' );
		$container->appendChild( $heading );
	}
	foreach ( (array) $blocks as $block ) {
		if ( $block instanceof DOMNode ) {
			$container->appendChild( $block->cloneNode( true ) );
			continue;
		}
		leadwerk_theme_ludwig_append_html( $container, (string) $block );
	}
}

function leadwerk_theme_ludwig_bind_button_list( $xpath, $context, $query, $buttons ) {
	$buttons = array_values(
		array_filter(
			(array) $buttons,
			static function ( $button ) {
				return is_array( $button ) && '' !== trim( (string) ( $button['label'] ?? '' ) );
			}
		)
	);

	$nodes = leadwerk_theme_dom_query( $xpath, $query, $context );
	if ( empty( $nodes ) ) {
		return;
	}

	$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $buttons ) ) );
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $buttons[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$button = $buttons[ $index ];
		leadwerk_theme_ludwig_bind_anchor_node( $node, $button['label'], $button['page_key'], $button['url'] );
	}
}

function leadwerk_theme_ludwig_build_button_nodes( $xpath, $context, $query, $buttons ) {
	$nodes   = array();
	$buttons = array_values(
		array_filter(
			(array) $buttons,
			static function ( $button ) {
				return is_array( $button ) && '' !== trim( (string) ( $button['label'] ?? '' ) );
			}
		)
	);
	if ( empty( $buttons ) || ! $context instanceof DOMElement ) {
		return $nodes;
	}

	$prototypes = leadwerk_theme_dom_query( $xpath, $query, $context );
	$last_index = count( $prototypes ) - 1;
	foreach ( $buttons as $index => $button ) {
		$prototype = null;
		if ( isset( $prototypes[ $index ] ) && $prototypes[ $index ] instanceof DOMElement ) {
			$prototype = $prototypes[ $index ];
		} elseif ( $last_index >= 0 && isset( $prototypes[ $last_index ] ) && $prototypes[ $last_index ] instanceof DOMElement ) {
			$prototype = $prototypes[ $last_index ];
		}

		$node = leadwerk_theme_ludwig_clone_or_create_element( $prototype, 'a', $context->ownerDocument );
		if ( $node instanceof DOMElement && ! $node->hasAttribute( 'class' ) ) {
			$node->setAttribute( 'class', 'btn btn-primary' );
		}
		leadwerk_theme_ludwig_bind_anchor_node( $node, $button['label'], $button['page_key'], $button['url'] );
		$nodes[] = $node;
	}

	return $nodes;
}

function leadwerk_theme_ludwig_build_intro_copy_card_node( $xpath, $content, $section ) {
	if ( ! $content instanceof DOMElement ) {
		return null;
	}

	$card_title  = trim( (string) ( $section['card_title'] ?? '' ) );
	$card_body   = (string) ( $section['card_body'] ?? '' );
	$card_footer = (string) ( $section['card_footer'] ?? '' );
	$card        = leadwerk_theme_dom_first( $xpath, './/div[contains(@class,"card")][.//h3[1]][1]', $content );

	if ( '' === $card_title && '' === trim( wp_strip_all_tags( $card_body ) ) && '' === trim( wp_strip_all_tags( $card_footer ) ) && ! $card instanceof DOMElement ) {
		return null;
	}

	$card_node = leadwerk_theme_ludwig_clone_or_create_element( $card, 'div', $content->ownerDocument );
	if ( $card_node instanceof DOMElement && ! $card_node->hasAttribute( 'class' ) ) {
		$card_node->setAttribute( 'class', 'card mt-8 mb-8' );
	}

	$heading = $card instanceof DOMElement
		? leadwerk_theme_dom_first( $xpath, './/h3[1] | .//h4[1]', $card )
		: null;
	$heading = leadwerk_theme_ludwig_clone_or_create_element( $heading, 'h3', $content->ownerDocument );

	leadwerk_theme_dom_clear( $card_node );
	if ( '' !== $card_title ) {
		leadwerk_theme_set_placeholder_markup( $heading, $card_title, 'heading' );
		$card_node->appendChild( $heading );
	}
	leadwerk_theme_ludwig_append_html( $card_node, $card_body );
	leadwerk_theme_ludwig_append_html( $card_node, $card_footer );

	return $card_node;
}

function leadwerk_theme_ludwig_build_info_grid_node( $xpath, $content, $items ) {
	$items = leadwerk_theme_ludwig_rows( $items );
	if ( ! $content instanceof DOMElement || empty( $items ) ) {
		return null;
	}

	$grid = leadwerk_theme_dom_first( $xpath, './/div[contains(@class,"grid")][.//h4[1]][1]', $content );
	$doc  = $content->ownerDocument;
	$grid_node = leadwerk_theme_ludwig_clone_or_create_element( $grid, 'div', $doc );
	if ( $grid_node instanceof DOMElement && ! $grid_node->hasAttribute( 'class' ) ) {
		$grid_node->setAttribute( 'class', 'grid grid-3 mt-8' );
	}

	$item_prototypes = $grid instanceof DOMElement ? leadwerk_theme_ludwig_direct_element_children( $grid ) : array();
	$last_index      = count( $item_prototypes ) - 1;

	leadwerk_theme_dom_clear( $grid_node );
	foreach ( $items as $index => $item ) {
		$title = trim( (string) ( $item['title'] ?? '' ) );
		$body  = (string) ( $item['body'] ?? '' );
		if ( '' === $title && '' === trim( wp_strip_all_tags( $body ) ) ) {
			continue;
		}

		$prototype = null;
		if ( isset( $item_prototypes[ $index ] ) && $item_prototypes[ $index ] instanceof DOMElement ) {
			$prototype = $item_prototypes[ $index ];
		} elseif ( $last_index >= 0 && isset( $item_prototypes[ $last_index ] ) && $item_prototypes[ $last_index ] instanceof DOMElement ) {
			$prototype = $item_prototypes[ $last_index ];
		}

		$item_node = leadwerk_theme_ludwig_clone_or_create_element( $prototype, 'div', $doc );
		$heading   = $prototype instanceof DOMElement ? leadwerk_theme_dom_first( $xpath, './/h4[1]', $prototype ) : null;
		$body_node = $prototype instanceof DOMElement ? leadwerk_theme_dom_first( $xpath, './/p[1]', $prototype ) : null;

		leadwerk_theme_dom_clear( $item_node );
		if ( '' !== $title ) {
			$heading = leadwerk_theme_ludwig_clone_or_create_element( $heading, 'h4', $doc );
			leadwerk_theme_dom_set_text( $heading, $title );
			$item_node->appendChild( $heading );
		}
		if ( '' !== $body ) {
			$body_node = leadwerk_theme_ludwig_clone_or_create_element( $body_node, 'p', $doc );
			if ( $body_node instanceof DOMElement && ! $body_node->hasAttribute( 'class' ) ) {
				$body_node->setAttribute( 'class', 'text-muted' );
			}
			leadwerk_theme_set_placeholder_markup( $body_node, $body, 'container' );
			$item_node->appendChild( $body_node );
		}
		$grid_node->appendChild( $item_node );
	}

	return $grid_node->hasChildNodes() ? $grid_node : null;
}

function leadwerk_theme_ludwig_bind_simple_cards( $xpath, $context, $card_query, $items ) {
	$cards = leadwerk_theme_dom_query( $xpath, $card_query, $context );
	if ( empty( $cards ) ) {
		return;
	}

	$items = leadwerk_theme_ludwig_rows( $items );
	$cards = leadwerk_theme_dom_ensure_count( $cards, max( 1, count( $items ) ) );
	foreach ( $cards as $index => $card ) {
		if ( ! $card instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $card );
			continue;
		}

		$item       = $items[ $index ];
		$title_node = leadwerk_theme_dom_first( $xpath, './h3[1] | ./h4[1]', $card );
		$icon_node  = leadwerk_theme_dom_first(
			$xpath,
			'.//*[self::div or self::span][contains(concat(" ", normalize-space(@class), " "), " card-icon ") or contains(concat(" ", normalize-space(@class), " "), " problem-card-icon ") or contains(concat(" ", normalize-space(@class), " "), " contact-card-icon ")][1]',
			$card
		);
		$heading    = leadwerk_theme_ludwig_clone_or_create_element( $title_node, $title_node instanceof DOMElement ? strtolower( $title_node->tagName ) : 'h3', $card->ownerDocument );

		$trusted_icon = trim( (string) ( $item['icon'] ?? '' ) );

		// #region agent log
		if ( $index < 4 && function_exists( 'wp_json_encode' ) ) {
			$agent_log_path = dirname( __DIR__, 2 ) . '/debug-c3ba8b.log';
			$agent_line     = array(
				'sessionId'     => 'c3ba8b',
				'hypothesisId'  => 'H-feature-card-icon',
				'location'      => 'ludwig-exact-binders.php:leadwerk_theme_ludwig_bind_simple_cards',
				'message'       => 'bind simple card',
				'data'          => array(
					'cardIndex'       => (int) $index,
					'hasIconShell'    => $icon_node instanceof DOMElement,
					'trustedIconLen'  => strlen( $trusted_icon ),
					'titlePlainChars' => strlen( trim( wp_strip_all_tags( (string) ( $item['title'] ?? '' ) ) ) ),
				),
				'timestamp'     => (int) round( microtime( true ) * 1000 ),
			);
			@file_put_contents( $agent_log_path, wp_json_encode( $agent_line ) . "\n", FILE_APPEND | LOCK_EX );
		}
		// #endregion

		leadwerk_theme_set_placeholder_markup( $heading, (string) ( $item['title'] ?? '' ), 'heading' );
		leadwerk_theme_dom_clear( $card );

		$icon_clone = null;
		if ( $icon_node instanceof DOMElement ) {
			$icon_clone = $icon_node->cloneNode( true );
			leadwerk_theme_ludwig_remove_heading_descendants( $icon_clone );
		}

		if ( '' !== $trusted_icon ) {
			if ( $icon_clone instanceof DOMElement ) {
				leadwerk_theme_dom_set_trusted_inner_html( $icon_clone, $trusted_icon );
			} else {
				$icon_clone = $card->ownerDocument->createElement( 'div' );
				$icon_clone->setAttribute( 'class', 'card-icon' );
				leadwerk_theme_dom_set_trusted_inner_html( $icon_clone, $trusted_icon );
			}
		}

		if ( $icon_clone instanceof DOMElement ) {
			$card->appendChild( $icon_clone );
		}
		$card->appendChild( $heading );
		leadwerk_theme_ludwig_append_html( $card, (string) ( $item['body'] ?? '' ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_hero( $xpath, $section_node, $section ) {
	$image = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"hero-bg")]//img[1]', $section_node );
	leadwerk_theme_ludwig_bind_image_element( $image, $section['image'] ?? 0, (string) ( $section['image_alt'] ?? '' ), (string) ( $section['image_position'] ?? '' ) );

	$badge = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"hero-badge")][1]', $section_node );
	if ( $badge instanceof DOMElement ) {
		$span = leadwerk_theme_dom_first( $xpath, './/span[last()]', $badge );
		if ( $span instanceof DOMElement ) {
			leadwerk_theme_dom_set_text( $span, (string) ( $section['eyebrow'] ?? '' ) );
		} else {
			leadwerk_theme_ludwig_bind_leading_svg_label( $badge, (string) ( $section['eyebrow'] ?? '' ) );
		}
	}

	$heading_node            = leadwerk_theme_dom_first( $xpath, './/h1[1]', $section_node );
	$existing_highlight_node = leadwerk_theme_dom_first( $xpath, './/h1[1]//*[contains(concat(" ", normalize-space(@class), " "), " highlight ")][1]', $section_node );
	$existing_highlight_style = $existing_highlight_node instanceof DOMElement ? trim( (string) $existing_highlight_node->getAttribute( 'style' ) ) : '';
	leadwerk_theme_set_placeholder_markup( $heading_node, (string) ( $section['title'] ?? '' ), 'heading' );

	if ( '' !== $existing_highlight_style ) {
		$updated_highlight_node = leadwerk_theme_dom_first( $xpath, './/h1[1]//*[contains(concat(" ", normalize-space(@class), " "), " highlight ")][1]', $section_node );
		if ( $updated_highlight_node instanceof DOMElement && '' === trim( (string) $updated_highlight_node->getAttribute( 'style' ) ) ) {
			$updated_highlight_node->setAttribute( 'style', $existing_highlight_style );
		}
	}

	$subtitle_nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"hero-subtitle")]', $section_node );
	$subtitle_parts = leadwerk_theme_ludwig_split_markup_blocks( (string) ( $section['subtitle'] ?? '' ) );
	if ( ! empty( $subtitle_nodes ) && ! empty( $subtitle_parts ) ) {
		if ( 1 === count( $subtitle_nodes ) && count( $subtitle_parts ) > 1 ) {
			leadwerk_theme_set_placeholder_markup( $subtitle_nodes[0], implode( '<br><br>', $subtitle_parts ), 'container' );
		} else {
			foreach ( $subtitle_nodes as $index => $subtitle_node ) {
				if ( ! $subtitle_node instanceof DOMElement ) {
					continue;
				}
				if ( empty( $subtitle_parts[ $index ] ) ) {
					continue;
				}
				leadwerk_theme_set_placeholder_markup( $subtitle_node, (string) $subtitle_parts[ $index ], 'container' );
			}
		}
	}

	leadwerk_theme_ludwig_bind_button_list(
		$xpath,
		$section_node,
		'.//*[contains(@class,"hero-content")]//a[contains(@class,"btn")]',
		array(
			leadwerk_theme_ludwig_button_data( $section, 'primary' ),
			leadwerk_theme_ludwig_button_data( $section, 'secondary' ),
		)
	);
}

function leadwerk_theme_bind_exact_ludwig_trust_strip( $xpath, $section_node, $section ) {
	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"trust-item")]', $section_node );
	if ( empty( $nodes ) ) {
		return;
	}

	$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/span[last()]', $node ), (string) ( $items[ $index ]['label'] ?? '' ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_problem_cards( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"text-center")][contains(@class,"text-muted")][1] | .//p[contains(@class,"text-center")][1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );
	leadwerk_theme_ludwig_bind_simple_cards( $xpath, $section_node, './/*[contains(@class,"problem-card")]', $section['items'] ?? array() );
}

function leadwerk_theme_bind_exact_ludwig_split_story( $xpath, $section_node, $section ) {
	$has_text_content = leadwerk_theme_ludwig_has_visible_content( $section['title'] ?? '' )
		|| leadwerk_theme_ludwig_has_visible_content( $section['intro'] ?? '' )
		|| leadwerk_theme_ludwig_has_visible_content( $section['body'] ?? '' )
		|| leadwerk_theme_ludwig_has_visible_content( $section['highlight'] ?? '' )
		|| leadwerk_theme_ludwig_has_visible_content( $section['cta_label'] ?? '' );

	if ( ! $has_text_content ) {
		return;
	}

	list( $text_node, $image_node ) = leadwerk_theme_ludwig_find_two_col_parts( $xpath, $section_node );
	if ( $image_node instanceof DOMElement ) {
		$image = leadwerk_theme_dom_first( $xpath, './/img[1]', $image_node );
		leadwerk_theme_ludwig_bind_image_element( $image, $section['image'] ?? 0, (string) ( $section['image_alt'] ?? '' ), (string) ( $section['image_position'] ?? '' ) );
	}

	if ( $text_node instanceof DOMElement ) {
		$heading = leadwerk_theme_dom_first( $xpath, './/h2[1]', $text_node );
		$button  = leadwerk_theme_dom_first( $xpath, './/a[contains(@class,"btn")][1]', $text_node );
		$cta     = leadwerk_theme_ludwig_button_data( $section, 'cta' );
		if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
			leadwerk_theme_ludwig_bind_anchor_node( $button, $cta['label'], $cta['page_key'], $cta['url'] );
		}

		$blocks = array(
			(string) ( $section['intro'] ?? '' ),
			(string) ( $section['body'] ?? '' ),
			(string) ( $section['highlight'] ?? '' ),
		);
		if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
			$blocks[] = $button;
		}

		leadwerk_theme_ludwig_rebuild_content_container( $text_node, $heading, (string) ( $section['title'] ?? '' ), 'h2', $blocks );
	}
}

function leadwerk_theme_bind_exact_ludwig_audience_tabs( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );

	$tabs         = leadwerk_theme_ludwig_rows( $section['tabs'] ?? array() );
	$button_nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"tab-button")]', $section_node );
	$panel_nodes  = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"tab-panel")]', $section_node );
	if ( ! empty( $button_nodes ) ) {
		$button_nodes = leadwerk_theme_dom_ensure_count( $button_nodes, max( 1, count( $tabs ) ) );
	}
	if ( ! empty( $panel_nodes ) ) {
		$panel_nodes = leadwerk_theme_dom_ensure_count( $panel_nodes, max( 1, count( $tabs ) ) );
	}

	foreach ( $tabs as $index => $tab ) {
		if ( ! empty( $button_nodes[ $index ] ) && $button_nodes[ $index ] instanceof DOMElement ) {
			leadwerk_theme_dom_set_text( $button_nodes[ $index ], (string) ( $tab['tab_label'] ?? '' ) );
			leadwerk_theme_dom_toggle_class( $button_nodes[ $index ], 'active', 0 === $index );
		}

		if ( empty( $panel_nodes[ $index ] ) || ! $panel_nodes[ $index ] instanceof DOMElement ) {
			continue;
		}

		$panel = $panel_nodes[ $index ];
		leadwerk_theme_dom_toggle_class( $panel, 'active', 0 === $index );
		list( $text_node, $image_node ) = leadwerk_theme_ludwig_find_two_col_parts( $xpath, $panel );
		if ( $image_node instanceof DOMElement ) {
			$image = leadwerk_theme_dom_first( $xpath, './/img[1]', $image_node );
			leadwerk_theme_ludwig_bind_image_element( $image, $tab['image'] ?? 0, (string) ( $tab['image_alt'] ?? '' ), (string) ( $tab['image_position'] ?? '' ) );
		}
		if ( $text_node instanceof DOMElement ) {
			$heading = leadwerk_theme_dom_first( $xpath, './/h3[1]', $text_node );
			$button  = leadwerk_theme_dom_first( $xpath, './/a[contains(@class,"btn")][1]', $text_node );
			$cta     = leadwerk_theme_ludwig_button_data( $tab, 'cta' );
			if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
				leadwerk_theme_ludwig_bind_anchor_node( $button, $cta['label'], $cta['page_key'], $cta['url'] );
			}
			$blocks = array( (string) ( $tab['body'] ?? '' ) );
			if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
				$blocks[] = $button;
			}
			leadwerk_theme_ludwig_rebuild_content_container( $text_node, $heading, (string) ( $tab['title'] ?? '' ), 'h3', $blocks );
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_pillars_cta( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1] | .//p[contains(@class,"text-center")][1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$cards = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"pillar-card")]', $section_node );
	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	if ( ! empty( $cards ) ) {
		$cards = leadwerk_theme_dom_ensure_count( $cards, max( 1, count( $items ) ) );
	}

	foreach ( $cards as $index => $card ) {
		if ( ! $card instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $card );
			continue;
		}
		$item       = $items[ $index ];
		$number     = leadwerk_theme_dom_first( $xpath, './div[contains(@class,"pillar-number")][1]', $card );
		$title_node = leadwerk_theme_dom_first( $xpath, './/h3[1]', $card );
		$heading    = leadwerk_theme_ludwig_clone_or_create_element( $title_node, 'h3', $card->ownerDocument );
		leadwerk_theme_set_placeholder_markup( $heading, (string) ( $item['title'] ?? '' ), 'heading' );
		leadwerk_theme_dom_clear( $card );
		if ( $number instanceof DOMElement ) {
			$card->appendChild( $number->cloneNode( true ) );
		}
		$card->appendChild( $heading );
		leadwerk_theme_ludwig_append_html( $card, (string) ( $item['body'] ?? '' ) );
	}

	$cta = leadwerk_theme_ludwig_button_data( $section, 'cta' );
	$button = leadwerk_theme_dom_first( $xpath, './/a[contains(@class,"btn")][1]', $section_node );
	if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
		leadwerk_theme_ludwig_bind_anchor_node( $button, $cta['label'], $cta['page_key'], $cta['url'] );
	}
}

function leadwerk_theme_bind_exact_ludwig_credential_grid( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );
	leadwerk_theme_ludwig_bind_simple_cards( $xpath, $section_node, './/*[contains(@class,"grid")]//*[contains(@class,"card")][.//h3[1]]', $section['items'] ?? array() );
}

function leadwerk_theme_bind_exact_ludwig_testimonials( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );

	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"testimonial")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}

	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$item = $items[ $index ];
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"testimonial-text")][1]', $node ), '<p>' . esc_html( (string) ( $item['text'] ?? '' ) ) . '</p>', 'container' );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"testimonial-name")][1]', $node ), (string) ( $item['name'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"testimonial-role")][1]', $node ), (string) ( $item['role'] ?? '' ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_center_cta( $xpath, $section_node, $section ) {
	$container = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"text-center")][1] | .//div[contains(@class,"card")][1]', $section_node );
	if ( ! $container instanceof DOMElement ) {
		$container = $section_node;
	}

	$heading = leadwerk_theme_dom_first( $xpath, './/h2[1]', $container );
	$blocks  = array();
	if ( '' !== trim( wp_strip_all_tags( (string) ( $section['body'] ?? '' ) ) ) ) {
		$blocks[] = (string) ( $section['body'] ?? '' );
	}

	foreach (
		leadwerk_theme_ludwig_build_button_nodes(
			$xpath,
			$container,
			'.//a[contains(@class,"btn")]',
			array(
				leadwerk_theme_ludwig_button_data( $section, 'primary' ),
				leadwerk_theme_ludwig_button_data( $section, 'secondary' ),
			)
		) as $button_node
	) {
		$blocks[] = $button_node;
	}

	leadwerk_theme_ludwig_rebuild_content_container( $container, $heading, (string) ( $section['title'] ?? '' ), 'h2', $blocks );
}

function leadwerk_theme_bind_exact_ludwig_intro_copy( $xpath, $section_node, $section ) {
	$content = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"reveal")][.//p[1]][1] | .//div[contains(@class,"card")][.//h2[1]][1]', $section_node );
	if ( ! $content instanceof DOMElement ) {
		return;
	}

	$section_heading = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//h2[1]', $section_node );
	$heading         = leadwerk_theme_dom_first( $xpath, './h2[1]', $content );
	if ( $section_heading instanceof DOMElement && $section_heading !== $heading ) {
		leadwerk_theme_set_placeholder_markup( $section_heading, (string) ( $section['title'] ?? '' ), 'heading' );
		$heading = null;
	}
	$blocks = array(
		(string) ( $section['intro'] ?? '' ),
		(string) ( $section['body'] ?? '' ),
	);

	$card_node = leadwerk_theme_ludwig_build_intro_copy_card_node( $xpath, $content, $section );
	if ( $card_node instanceof DOMElement ) {
		$blocks[] = $card_node;
	}

	$info_grid_node = leadwerk_theme_ludwig_build_info_grid_node( $xpath, $content, $section['info_items'] ?? array() );
	if ( $info_grid_node instanceof DOMElement ) {
		$blocks[] = $info_grid_node;
	}

	if ( '' !== trim( wp_strip_all_tags( (string) ( $section['closing_note'] ?? '' ) ) ) ) {
		$blocks[] = (string) ( $section['closing_note'] ?? '' );
	}

	foreach (
		leadwerk_theme_ludwig_build_button_nodes(
			$xpath,
			$content,
			'.//a[contains(@class,"btn")]',
			array(
				leadwerk_theme_ludwig_button_data( $section, 'cta' ),
			)
		) as $button_node
	) {
		$blocks[] = $button_node;
	}

	leadwerk_theme_ludwig_rebuild_content_container( $content, $heading, $heading instanceof DOMElement ? (string) ( $section['title'] ?? '' ) : '', 'h2', $blocks );
}

function leadwerk_theme_ludwig_render_note_list_html( $tag, $items ) {
	$html = '<' . tag_escape( $tag ) . ' style="padding-left: var(--space-5); margin: var(--space-4) 0;">';
	foreach ( leadwerk_theme_ludwig_rows( $items ) as $item ) {
		$text = trim( wp_strip_all_tags( (string) ( $item['text'] ?? '' ) ) );
		if ( '' === $text ) {
			continue;
		}
		$html .= '<li style="margin-bottom: var(--space-2);">' . esc_html( $text );
		$note = trim( wp_strip_all_tags( (string) ( $item['note'] ?? '' ) ) );
		if ( '' !== $note ) {
			$html .= ' <span class="hint-icon" aria-label="Hinweis"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg><span class="hint-tooltip">' . esc_html( $note ) . '</span></span>';
		}
		$html .= '</li>';
	}
	$html .= '</' . tag_escape( $tag ) . '>';
	return $html;
}

function leadwerk_theme_bind_exact_ludwig_timeline( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1] | .//h2[1]/following-sibling::p[1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$steps = leadwerk_theme_ludwig_rows( $section['steps'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"timeline-item")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $steps ) ) );
	}

	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $steps[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}

		$step         = $steps[ $index ];
		$content_node = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"timeline-content")][1]', $node );
		if ( ! $content_node instanceof DOMElement ) {
			continue;
		}

		$heading = leadwerk_theme_dom_first( $xpath, './/h3[1]', $content_node );
		$full    = trim( trim( (string) ( $step['step_label'] ?? '' ) ) . ' ' . trim( (string) ( $step['title'] ?? '' ) ) );
		leadwerk_theme_ludwig_rebuild_content_container( $content_node, $heading, $full, 'h3', array( (string) ( $step['body'] ?? '' ) ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_comparison_table( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );

	$headers = leadwerk_theme_dom_query( $xpath, './/thead//th', $section_node );
	if ( isset( $headers[0] ) ) {
		leadwerk_theme_dom_set_text( $headers[0], (string) ( $section['left_column_label'] ?? '' ) );
	}
	if ( isset( $headers[1] ) ) {
		leadwerk_theme_dom_set_text( $headers[1], (string) ( $section['right_column_label'] ?? '' ) );
	}

	$rows = leadwerk_theme_ludwig_rows( $section['rows'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/tbody/tr', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $rows ) ) );
	}
	foreach ( $nodes as $index => $row_node ) {
		if ( ! $row_node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $rows[ $index ] ) ) {
			leadwerk_theme_dom_remove( $row_node );
			continue;
		}
		$cells = leadwerk_theme_dom_query( $xpath, './td', $row_node );
		if ( isset( $cells[0] ) ) {
			leadwerk_theme_dom_set_text( $cells[0], (string) ( $rows[ $index ]['left_text'] ?? '' ) );
		}
		if ( isset( $cells[1] ) ) {
			leadwerk_theme_dom_set_text( $cells[1], (string) ( $rows[ $index ]['right_text'] ?? '' ) );
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_quote_callout( $xpath, $section_node, $section ) {
	$copy = leadwerk_theme_dom_first( $xpath, './/div[contains(@class,"reveal")][1]', $section_node );
	if ( $copy instanceof DOMElement ) {
		$heading = leadwerk_theme_dom_first( $xpath, './/h2[1]', $copy );
		leadwerk_theme_ludwig_rebuild_content_container( $copy, $heading, (string) ( $section['title'] ?? '' ), 'h2', array( (string) ( $section['body'] ?? '' ) ) );
	}

	$quote = leadwerk_theme_dom_first( $xpath, './/blockquote[1]//p[1] | .//*[contains(@class,"card-quote")]//p[1]', $section_node );
	if ( $quote instanceof DOMElement ) {
		leadwerk_theme_dom_set_text( $quote, (string) ( $section['quote'] ?? '' ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_feature_grid( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1] | .//p[contains(@class,"text-center")][1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$grid   = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"grid")][1]', $section_node );
	$groups = leadwerk_theme_ludwig_rows( $section['groups'] ?? array() );
	if ( ! $grid instanceof DOMElement ) {
		return;
	}

	if ( ! empty( $groups ) ) {
		$columns = leadwerk_theme_ludwig_direct_element_children( $grid );
		$columns = leadwerk_theme_dom_ensure_count( $columns, max( 1, count( $groups ) ) );
		foreach ( $columns as $index => $column ) {
			if ( ! $column instanceof DOMElement ) {
				continue;
			}
			if ( empty( $groups[ $index ] ) ) {
				leadwerk_theme_dom_remove( $column );
				continue;
			}
			$group = $groups[ $index ];
			leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './h3[1]', $column ), (string) ( $group['title'] ?? '' ) );
			leadwerk_theme_ludwig_bind_simple_cards( $xpath, $column, './div[contains(@class,"card")]', $group['items'] ?? array() );
		}
		return;
	}

	leadwerk_theme_ludwig_bind_simple_cards( $xpath, $grid, './div[contains(@class,"card")] | ./article[contains(@class,"article-card")]', $section['items'] ?? array() );
}

function leadwerk_theme_bind_exact_ludwig_checklist_split( $xpath, $section_node, $section ) {
	$columns = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"two-col")][1]/*', $section_node );
	$left    = isset( $columns[0] ) && $columns[0] instanceof DOMElement ? $columns[0] : null;
	$right   = isset( $columns[1] ) && $columns[1] instanceof DOMElement ? $columns[1] : null;

	foreach ( array(
		array( 'node' => $left, 'title' => (string) ( $section['left_title'] ?? $section['title'] ?? '' ), 'intro' => (string) ( $section['intro'] ?? '' ), 'items' => $section['left_items'] ?? array() ),
		array( 'node' => $right, 'title' => (string) ( $section['right_title'] ?? '' ), 'intro' => '', 'items' => $section['right_items'] ?? array() ),
	) as $column ) {
		if ( ! $column['node'] instanceof DOMElement ) {
			continue;
		}
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $column['node'] ), $column['title'], 'heading' );
		if ( '' !== $column['intro'] ) {
			leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]/following-sibling::p[1]', $column['node'] ), $column['intro'], 'container' );
		}
		$items = leadwerk_theme_ludwig_rows( $column['items'] );
		$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"checklist-item")]', $column['node'] );
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
		foreach ( $nodes as $index => $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}
			if ( empty( $items[ $index ] ) ) {
				leadwerk_theme_dom_remove( $node );
				continue;
			}
			leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/span[1]', $node ), (string) ( $items[ $index ]['text'] ?? '' ) );
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_feature_checklist_cta( $xpath, $section_node, $section ) {
	$copy = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"reveal")][1]', $section_node );
	if ( ! $copy instanceof DOMElement ) {
		return;
	}

	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $copy ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]/following-sibling::p[1]', $copy ), (string) ( $section['intro'] ?? '' ), 'container' );
	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"checklist-item")]', $copy );
	$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/span[1]', $node ), (string) ( $items[ $index ]['text'] ?? '' ) );
	}

	$cta = leadwerk_theme_ludwig_button_data( $section, 'cta' );
	$button = leadwerk_theme_dom_first( $xpath, './/a[contains(@class,"btn")][1]', $copy );
	if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
		leadwerk_theme_ludwig_bind_anchor_node( $button, $cta['label'], $cta['page_key'], $cta['url'] );
	}
}

function leadwerk_theme_bind_exact_ludwig_pricing_cards( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$plans = leadwerk_theme_ludwig_rows( $section['plans'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"pricing-card")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $plans ) ) );
	}

	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $plans[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$plan = $plans[ $index ];
		leadwerk_theme_dom_toggle_class( $node, 'pricing-card-featured', ! empty( $plan['featured'] ) );
		$badge = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"pricing-badge")][1]', $node );
		if ( $badge instanceof DOMElement ) {
			if ( '' !== trim( (string) ( $plan['badge'] ?? '' ) ) ) {
				leadwerk_theme_dom_set_text( $badge, (string) $plan['badge'] );
			} else {
				leadwerk_theme_dom_remove( $badge );
			}
		}
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"pricing-title")][1]', $node ), (string) ( $plan['title'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"pricing-subtitle")][1]', $node ), (string) ( $plan['subtitle'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"pricing-amount")][1]', $node ), (string) ( $plan['amount'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"pricing-period")][1]', $node ), (string) ( $plan['period'] ?? '' ) );

		$feature_nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"pricing-feature")]', $node );
		$features      = leadwerk_theme_ludwig_rows( $plan['features'] ?? array() );
		if ( ! empty( $feature_nodes ) ) {
			$feature_nodes = leadwerk_theme_dom_ensure_count( $feature_nodes, max( 1, count( $features ) ) );
		}
		foreach ( $feature_nodes as $feature_index => $feature_node ) {
			if ( ! $feature_node instanceof DOMElement ) {
				continue;
			}
			if ( empty( $features[ $feature_index ] ) ) {
				leadwerk_theme_dom_remove( $feature_node );
				continue;
			}
			leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/span[1]', $feature_node ), (string) ( $features[ $feature_index ]['text'] ?? '' ) );
		}

		$button = leadwerk_theme_dom_first( $xpath, './/a[contains(@class,"btn")][1]', $node );
		$cta    = leadwerk_theme_ludwig_button_data( $plan, 'cta' );
		if ( $button instanceof DOMElement && '' !== trim( (string) $cta['label'] ) ) {
			leadwerk_theme_ludwig_bind_anchor_node( $button, $cta['label'], $cta['page_key'], $cta['url'] );
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_faq( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1] | .//*[contains(@class,"faq-intro")][1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"accordion-item")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}

	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$item = $items[ $index ];
		$header = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"accordion-header")][1]', $node );
		if ( $header instanceof DOMElement ) {
			if ( function_exists( 'leadwerk_theme_bind_exact_inline_label_keep_svg' ) ) {
				leadwerk_theme_bind_exact_inline_label_keep_svg( $xpath, $node, './/*[contains(@class,"accordion-header")][1]', (string) ( $item['question'] ?? '' ) );
			} else {
				leadwerk_theme_dom_set_text( $header, (string) ( $item['question'] ?? '' ) );
			}
		}
		$body = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"accordion-body")][1] | .//*[contains(@class,"accordion-content")][1]', $node );
		if ( $body instanceof DOMElement ) {
			leadwerk_theme_set_placeholder_markup( $body, (string) ( $item['answer'] ?? '' ), 'container' );
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_case_study( $xpath, $section_node, $section ) {
	$case = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"case-study")][1]', $section_node );
	if ( ! $case instanceof DOMElement ) {
		return;
	}

	$doc = $case->ownerDocument;
	leadwerk_theme_dom_clear( $case );

	$label = $doc->createElement( 'span' );
	$label->setAttribute( 'class', 'case-study-label' );
	$label->appendChild( $doc->createTextNode( (string) ( $section['label'] ?? '' ) ) );
	$case->appendChild( $label );

	$title = $doc->createElement( 'h3' );
	leadwerk_theme_set_placeholder_markup( $title, (string) ( $section['title'] ?? '' ), 'heading' );
	$case->appendChild( $title );

	leadwerk_theme_ludwig_append_html( $case, '<p><strong>' . esc_html( (string) ( $section['situation_title'] ?? '' ) ) . '</strong></p>' );
	leadwerk_theme_ludwig_append_html( $case, (string) ( $section['situation_body'] ?? '' ) );
	leadwerk_theme_ludwig_append_html( $case, '<p><strong>' . esc_html( (string) ( $section['measures_title'] ?? '' ) ) . '</strong></p>' );
	leadwerk_theme_ludwig_append_html( $case, leadwerk_theme_ludwig_render_note_list_html( 'ol', $section['measure_items'] ?? array() ) );

	$results = $doc->createElement( 'div' );
	$results->setAttribute( 'class', 'case-study-results' );
	$heading = $doc->createElement( 'h4' );
	$heading->appendChild( $doc->createTextNode( (string) ( $section['results_title'] ?? '' ) ) );
	$results->appendChild( $heading );
	leadwerk_theme_ludwig_append_html( $results, leadwerk_theme_ludwig_render_note_list_html( 'ul', $section['result_items'] ?? array() ) );
	$case->appendChild( $results );
	leadwerk_theme_ludwig_append_html( $case, (string) ( $section['closing_note'] ?? '' ) );
}

function leadwerk_theme_bind_exact_ludwig_contact_cards( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1] | .//p[contains(@class,"text-center")][1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"contact-card")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$item = $items[ $index ];
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/h3[1]', $node ), (string) ( $item['title'] ?? '' ) );
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/p[1]', $node ), nl2br( esc_html( (string) ( $item['body'] ?? '' ) ) ), 'container' );
		if ( 'a' === strtolower( $node->tagName ) ) {
			$href = leadwerk_theme_resolve_exact_href( (string) ( $item['cta_page_key'] ?? '' ), (string) ( $item['cta_url'] ?? '' ) );
			leadwerk_theme_dom_set_attr( $node, 'href', $href );
			if ( preg_match( '#^(?:mailto|tel):#i', $href ) || preg_match( '#^(?:https?:)?//#i', $href ) ) {
				$node->setAttribute( 'target', '_blank' );
				$node->setAttribute( 'rel', 'noopener noreferrer' );
			} else {
				$node->removeAttribute( 'target' );
				$node->removeAttribute( 'rel' );
			}
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_article_cards( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	$nodes = leadwerk_theme_dom_query( $xpath, './/article[contains(@class,"article-card")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$item = $items[ $index ];
		leadwerk_theme_ludwig_bind_image_element( leadwerk_theme_dom_first( $xpath, './/img[1]', $node ), $item['image'] ?? 0, (string) ( $item['image_alt'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"article-card-category")][1]', $node ), (string) ( $item['category'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/h3[1]', $node ), (string) ( $item['title'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"article-card-meta")][1]', $node ), (string) ( $item['meta'] ?? '' ) );
	}
}

function leadwerk_theme_bind_exact_ludwig_customer_videos( $xpath, $section_node, $section ) {
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"section-header")]//p[1]', $section_node ), (string) ( $section['intro'] ?? '' ), 'container' );

	$items = leadwerk_theme_ludwig_rows( $section['items'] ?? array() );
	if ( empty( $items ) ) {
		return;
	}

	$nodes = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"customer-video-card")]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}

	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}

		$item = $items[ $index ];
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"customer-video-kicker")][1]', $node ), (string) ( $item['kicker'] ?? '' ) );
		leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"customer-video-title")][1]', $node ), (string) ( $item['title'] ?? '' ) );
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"customer-video-description")][1]', $node ), (string) ( $item['body'] ?? '' ), 'container' );

		$video_node = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"customer-video-inline")][1]', $node );
		if ( $video_node instanceof DOMElement ) {
			$video_src = leadwerk_theme_ludwig_resolve_media_mapped_url( $item['video'] ?? '' );
			if ( '' !== $video_src ) {
				$source_node = leadwerk_theme_dom_first( $xpath, './/source[1]', $video_node );
				if ( $source_node instanceof DOMElement ) {
					leadwerk_theme_dom_set_attr( $source_node, 'src', $video_src );
				} else {
					leadwerk_theme_dom_set_attr( $video_node, 'src', $video_src );
				}
				leadwerk_theme_dom_set_attr( $node, 'data-video-src', $video_src );
			}

			$poster_url = leadwerk_theme_ludwig_resolve_media_mapped_url( $item['poster'] ?? 0 );
			if ( '' !== $poster_url ) {
				leadwerk_theme_dom_set_attr( $video_node, 'poster', $poster_url );
				leadwerk_theme_dom_set_attr( $node, 'data-video-poster', $poster_url );
			}
		}
	}
}

function leadwerk_theme_bind_exact_ludwig_contact_form_split( $xpath, $section_node, $section ) {
	$columns = leadwerk_theme_dom_query( $xpath, './/*[contains(@class,"two-col")][1]/*', $section_node );
	$booking = isset( $columns[0] ) && $columns[0] instanceof DOMElement ? $columns[0] : null;
	$form    = isset( $columns[1] ) && $columns[1] instanceof DOMElement ? $columns[1] : null;

	if ( $booking instanceof DOMElement ) {
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $booking ), (string) ( $section['booking_title'] ?? '' ), 'heading' );
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]/following-sibling::p[1]', $booking ), (string) ( $section['booking_intro'] ?? '' ), 'container' );
		$cards = leadwerk_theme_ludwig_rows( $section['booking_cards'] ?? array() );
		$nodes = leadwerk_theme_dom_query( $xpath, './/div[contains(@class,"card")]', $booking );
		if ( ! empty( $nodes ) ) {
			$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $cards ) ) );
		}
		foreach ( $nodes as $index => $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}
			if ( empty( $cards[ $index ] ) ) {
				leadwerk_theme_dom_remove( $node );
				continue;
			}
			$card = $cards[ $index ];
			leadwerk_theme_dom_set_text( leadwerk_theme_dom_first( $xpath, './/h3[1]', $node ), (string) ( $card['title'] ?? '' ) );
			leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/p[1]', $node ), nl2br( esc_html( (string) ( $card['text'] ?? '' ) ) ), 'container' );
			leadwerk_theme_ludwig_bind_button_list(
				$xpath,
				$node,
				'.//a[contains(@class,"btn")]',
				array(
					leadwerk_theme_ludwig_button_data( $card, 'primary' ),
					leadwerk_theme_ludwig_button_data( $card, 'secondary' ),
				)
			);
		}
	}

	if ( $form instanceof DOMElement ) {
		$heading = leadwerk_theme_dom_first( $xpath, './/h2[1]', $form );
		leadwerk_theme_ludwig_rebuild_content_container(
			$form,
			$heading,
			(string) ( $section['form_title'] ?? '' ),
			'h2',
			array(
				(string) ( $section['form_intro'] ?? '' ),
				leadwerk_theme_get_contact_form_markup(),
			)
		);
	}
}

function leadwerk_theme_bind_exact_ludwig_location_map( $xpath, $section_node, $section ) {
	if ( leadwerk_theme_ludwig_has_visible_content( $section['title'] ?? '' ) ) {
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/h2[1]', $section_node ), (string) ( $section['title'] ?? '' ), 'heading' );
	}

	$iframe = leadwerk_theme_dom_first( $xpath, './/iframe[1]', $section_node );
	if ( $iframe instanceof DOMElement && '' !== trim( (string) ( $section['embed_url'] ?? '' ) ) ) {
		leadwerk_theme_dom_set_attr( $iframe, 'src', (string) $section['embed_url'] );
	}

	$items = leadwerk_theme_ludwig_rows( $section['info_items'] ?? array() );
	if ( empty( $items ) ) {
		return;
	}

	$nodes = leadwerk_theme_dom_query( $xpath, './/div[contains(@class,"grid")][contains(@class,"grid-3")]/*[.//p[1]]', $section_node );
	if ( ! empty( $nodes ) ) {
		$nodes = leadwerk_theme_dom_ensure_count( $nodes, max( 1, count( $items ) ) );
	}
	foreach ( $nodes as $index => $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( empty( $items[ $index ] ) ) {
			leadwerk_theme_dom_remove( $node );
			continue;
		}
		$item = $items[ $index ];
		leadwerk_theme_set_placeholder_markup( leadwerk_theme_dom_first( $xpath, './/p[1]', $node ), '<strong>' . esc_html( (string) ( $item['title'] ?? '' ) ) . '</strong><br>' . esc_html( (string) ( $item['body'] ?? '' ) ), 'container' );
	}
}

function leadwerk_theme_bind_exact_ludwig_legal_document( $xpath, $section_node, $section ) {
	$container = leadwerk_theme_dom_first( $xpath, './/*[contains(@class,"container-narrow")][1] | .//*[contains(@class,"container")][1]', $section_node );
	if ( ! $container instanceof DOMElement ) {
		$container = $section_node;
	}

	$doc = $container->ownerDocument;
	leadwerk_theme_dom_clear( $container );

	$headline = $doc->createElement( 'h1' );
	leadwerk_theme_set_placeholder_markup( $headline, (string) ( $section['headline'] ?? '' ), 'heading' );
	$container->appendChild( $headline );
	leadwerk_theme_ludwig_append_html( $container, (string) ( $section['intro'] ?? '' ) );

	foreach ( leadwerk_theme_ludwig_rows( $section['sections'] ?? array() ) as $item ) {
		$title = trim( (string) ( $item['title'] ?? '' ) );
		$body  = (string) ( $item['body'] ?? '' );
		if ( ! empty( $item['is_highlight_card'] ) ) {
			$card = $doc->createElement( 'div' );
			$card->setAttribute( 'class', 'card' );
			if ( '' !== $title ) {
				$h3 = $doc->createElement( 'h3' );
				$h3->appendChild( $doc->createTextNode( $title ) );
				$card->appendChild( $h3 );
			}
			leadwerk_theme_ludwig_append_html( $card, $body );
			$container->appendChild( $card );
			continue;
		}
		if ( '' !== $title ) {
			$h2 = $doc->createElement( 'h2' );
			$h2->setAttribute( 'class', 'mt-8' );
			$h2->appendChild( $doc->createTextNode( $title ) );
			$container->appendChild( $h2 );
		}
		leadwerk_theme_ludwig_append_html( $container, $body );
	}
}
