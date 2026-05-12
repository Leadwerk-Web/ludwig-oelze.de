<?php
/**
 * Ludwig structured data and geo tags.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default business SEO payload when JSON config is missing or invalid.
 *
 * @return array<string,mixed>
 */
function leadwerk_theme_ludwig_business_seo_defaults() {
	return array(
		'aggregateRating'            => array(
			'@type'       => 'AggregateRating',
			'ratingValue' => 4.9,
			'bestRating'  => 5,
			'worstRating' => 1,
			'reviewCount' => 50,
		),
		'googleBusinessProfileUrl' => '',
	);
}

/**
 * Load ludwig-business-seo.json from the theme config directory.
 *
 * @return array<string,mixed>
 */
function leadwerk_theme_ludwig_load_business_seo_config() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$cached = leadwerk_theme_ludwig_business_seo_defaults();
	$path   = LEADWERK_THEME_DIR . '/config/ludwig-business-seo.json';

	if ( ! is_readable( $path ) ) {
		return $cached;
	}

	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return $cached;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return $cached;
	}

	if ( ! empty( $data['aggregateRating'] ) && is_array( $data['aggregateRating'] ) ) {
		$ar                        = $data['aggregateRating'];
		$cached['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => isset( $ar['ratingValue'] ) ? $ar['ratingValue'] : $cached['aggregateRating']['ratingValue'],
			'bestRating'  => isset( $ar['bestRating'] ) ? $ar['bestRating'] : $cached['aggregateRating']['bestRating'],
			'worstRating' => isset( $ar['worstRating'] ) ? $ar['worstRating'] : $cached['aggregateRating']['worstRating'],
			'reviewCount' => isset( $ar['reviewCount'] ) ? (int) $ar['reviewCount'] : (int) $cached['aggregateRating']['reviewCount'],
		);
	}

	if ( isset( $data['googleBusinessProfileUrl'] ) && is_string( $data['googleBusinessProfileUrl'] ) ) {
		$cached['googleBusinessProfileUrl'] = trim( $data['googleBusinessProfileUrl'] );
	}

	return $cached;
}

/**
 * Base sameAs URLs (social); GBP is appended from config when set.
 *
 * @return array<int,string>
 */
function leadwerk_theme_ludwig_schema_base_same_as() {
	return array(
		'https://www.facebook.com/ludwig.finanzmakler/',
		'https://www.linkedin.com/in/ludwig-oelze-6656b8173',
		'https://www.instagram.com/ludwig_finanzmakler',
	);
}

/**
 * Collect FAQ rows from Ludwig flexible sections (layout "faq") for JSON-LD.
 *
 * @param int    $post_id    Post ID.
 * @param string $source_key Leadwerk source key.
 * @return array<int,array{question:string,answer:string}>
 */
function leadwerk_theme_ludwig_collect_faq_schema_items( $post_id, $source_key ) {
	$post_id    = (int) $post_id;
	$source_key = (string) $source_key;

	if ( $post_id < 1 || ! class_exists( 'Leadwerk_Content_Schema' ) ) {
		return array();
	}

	$group = Leadwerk_Content_Schema::get_group_for_source_key( $source_key );
	if ( ! is_array( $group ) || empty( $group['field_name'] ) ) {
		return array();
	}

	$field_name = (string) $group['field_name'];
	if ( 'ludwig_page_document' === $field_name ) {
		return array();
	}

	$sections = function_exists( 'leadwerk_theme_get_managed_field_value' )
		? leadwerk_theme_get_managed_field_value( $field_name, $post_id )
		: null;

	if ( ! is_array( $sections ) ) {
		return array();
	}

	$out = array();
	foreach ( $sections as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$layout = isset( $row['acf_fc_layout'] ) ? (string) $row['acf_fc_layout'] : '';
		if ( 'faq' !== $layout ) {
			continue;
		}
		$items = isset( $row['items'] ) && is_array( $row['items'] ) ? $row['items'] : array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$q = isset( $item['question'] ) ? trim( wp_strip_all_tags( (string) $item['question'] ) ) : '';
			$a = isset( $item['answer'] ) ? trim( wp_strip_all_tags( (string) $item['answer'] ) ) : '';
			if ( '' === $q ) {
				continue;
			}
			$out[] = array(
				'question' => $q,
				'answer'   => $a,
			);
		}
	}

	return $out;
}

/**
 * Get geo metadata for the current Ludwig page.
 *
 * @param string $source_key Leadwerk source key.
 * @return array<string,mixed>
 */
function leadwerk_theme_ludwig_schema_geo_profile( $source_key ) {
	$source_key = (string) $source_key;

	if ( 'ludwig-expat-beratung-1-v1' === $source_key ) {
		return array(
			'region'    => 'AE-DU',
			'place'     => 'Dubai',
			'latitude'  => 25.2048,
			'longitude' => 55.2708,
			'country'   => 'AE',
			'address'   => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Dubai',
				'addressRegion'   => 'Dubai',
				'addressCountry'  => 'AE',
			),
		);
	}

	return array(
		'region'    => 'DE-BW',
		'place'     => 'Baden-Baden',
		'latitude'  => 48.7606,
		'longitude' => 8.2398,
		'country'   => 'DE',
		'address'   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Bismarckstraße 26',
			'postalCode'      => '76530',
			'addressLocality' => 'Baden-Baden',
			'addressRegion'   => 'Baden-Württemberg',
			'addressCountry'  => 'DE',
		),
	);
}

/**
 * Return page schema type for source key.
 *
 * @param string $source_key Leadwerk source key.
 * @return string
 */
function leadwerk_theme_ludwig_schema_page_type( $source_key ) {
	switch ( (string) $source_key ) {
		case 'ludwig-kontakt-v1':
			return 'ContactPage';
		case 'ludwig-ueber-ludwig-v1':
			return 'AboutPage';
		case 'ludwig-wissen-v1':
			return 'CollectionPage';
		default:
			return 'WebPage';
	}
}

/**
 * Whether the current page should be modeled as a service page.
 *
 * @param string $source_key Leadwerk source key.
 * @return bool
 */
function leadwerk_theme_ludwig_schema_is_service_page( $source_key ) {
	$source_key = (string) $source_key;

	if ( '' === $source_key ) {
		return false;
	}

	if ( in_array( $source_key, array( 'ludwig-impressum-v1', 'ludwig-datenschutz-v1', 'ludwig-erstinformation-v1', 'ludwig-teilnahmebedingungen-v1', 'ludwig-vorgangsabfrage-v1', 'ludwig-404-v1' ), true ) ) {
		return false;
	}

	return true;
}

/**
 * Build structured data graph for a Ludwig page.
 *
 * @param int    $post_id    Post ID.
 * @param string $source_key Leadwerk source key.
 * @return array<string,mixed>
 */
function leadwerk_theme_ludwig_schema_graph( $post_id, $source_key ) {
	$post_id      = (int) $post_id;
	$source_key   = (string) $source_key;
	$canonical    = get_permalink( $post_id );
	$canonical    = is_string( $canonical ) && '' !== $canonical ? $canonical : home_url( '/' );
	$site_url     = home_url( '/' );
	$org_id       = trailingslashit( $site_url ) . '#organization';
	$website_id   = trailingslashit( $site_url ) . '#website';
	$place_id     = $canonical . '#primary-location';
	$service_id   = $canonical . '#service';
	$title        = wp_strip_all_tags( get_the_title( $post_id ) );
	$description  = trim( (string) get_post_meta( $post_id, 'leadwerk_meta_description', true ) );
	$lang         = function_exists( 'leadwerk_theme_get_current_lang' ) ? leadwerk_theme_get_current_lang() : 'de';
	$language     = 'en' === $lang ? 'en-US' : 'de-DE';
	$geo          = leadwerk_theme_ludwig_schema_geo_profile( $source_key );
	$phone        = function_exists( 'leadwerk_theme_get_option_value' ) ? leadwerk_theme_get_option_value( 'company_phone', '+49 176 43689181' ) : '+49 176 43689181';
	$email        = function_exists( 'leadwerk_theme_get_option_value' ) ? leadwerk_theme_get_option_value( 'company_email', 'finanzen@ludwigoelze.com' ) : 'finanzen@ludwigoelze.com';
	$logo_url     = LEADWERK_THEME_URI . '/assets/images/logo.png';
	$business_img = LEADWERK_THEME_URI . '/assets/images/logo.png';

	if ( '' === $description ) {
		$description = get_bloginfo( 'description' );
	}

	$seo        = leadwerk_theme_ludwig_load_business_seo_config();
	$same_as    = leadwerk_theme_ludwig_schema_base_same_as();
	$gbp        = isset( $seo['googleBusinessProfileUrl'] ) ? trim( (string) $seo['googleBusinessProfileUrl'] ) : '';
	if ( '' !== $gbp && filter_var( $gbp, FILTER_VALIDATE_URL ) ) {
		array_unshift( $same_as, $gbp );
		$same_as = array_values( array_unique( $same_as ) );
	}

	$place = array(
		'@type'   => 'Place',
		'@id'     => $place_id,
		'name'    => (string) $geo['place'],
		'address' => $geo['address'],
		'geo'     => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $geo['latitude'],
			'longitude' => (float) $geo['longitude'],
		),
	);

	$business = array(
		'@type'             => 'LocalBusiness',
		'@id'               => $org_id,
		'name'              => 'Ludwig Oelze',
		'legalName'         => 'Ludwig Oelze',
		'url'               => $site_url,
		'description'       => 'Freier Versicherungsmakler und Baufinanzierungsberater in Baden-Baden mit Beratung für Familien, Selbstständige und Expats.',
		'additionalType'    => array(
			'https://schema.org/InsuranceAgency',
			'https://schema.org/FinancialService',
		),
		'image'             => $business_img,
		'logo'              => $logo_url,
		'telephone'         => $phone,
		'email'             => $email,
		'priceRange'        => '€€',
		'address'           => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Bismarckstraße 26',
			'postalCode'      => '76530',
			'addressLocality' => 'Baden-Baden',
			'addressRegion'   => 'Baden-Württemberg',
			'addressCountry'  => 'DE',
		),
		'geo'               => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => 48.7606,
			'longitude' => 8.2398,
		),
		'areaServed'        => array(
			array( '@type' => 'City', 'name' => 'Baden-Baden', 'addressCountry' => 'DE' ),
			array( '@type' => 'Country', 'name' => 'Deutschland' ),
			array( '@type' => 'City', 'name' => 'Dubai', 'addressCountry' => 'AE' ),
			array( '@type' => 'Country', 'name' => 'Spanien' ),
		),
		'openingHoursSpecification' => array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
			'opens'     => '09:00',
			'closes'    => '18:00',
		),
		'aggregateRating'   => isset( $seo['aggregateRating'] ) && is_array( $seo['aggregateRating'] ) ? $seo['aggregateRating'] : leadwerk_theme_ludwig_business_seo_defaults()['aggregateRating'],
		'founder'           => array(
			'@type'    => 'Person',
			'name'     => 'Ludwig Oelze',
			'jobTitle' => 'Versicherungsmakler',
		),
		'sameAs'            => $same_as,
	);

	$web_page = array(
		'@type'           => leadwerk_theme_ludwig_schema_page_type( $source_key ),
		'@id'             => $canonical . '#webpage',
		'url'             => $canonical,
		'name'            => $title,
		'description'     => $description,
		'inLanguage'      => $language,
		'isPartOf'        => array( '@id' => $website_id ),
		'publisher'       => array( '@id' => $org_id ),
		'about'           => array( '@id' => $org_id ),
		'spatialCoverage' => array( '@id' => $place_id ),
	);

	$breadcrumb = array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $canonical . '#breadcrumb',
		'itemListElement' => array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => 'Start',
				'item'     => $site_url,
			),
			array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $title,
				'item'     => $canonical,
			),
		),
	);

	$graph = array(
		array(
			'@type'       => 'WebSite',
			'@id'         => $website_id,
			'url'         => $site_url,
			'name'        => 'Ludwig Oelze',
			'inLanguage'  => $language,
			'publisher'   => array( '@id' => $org_id ),
		),
		$business,
		$place,
		$web_page,
		$breadcrumb,
	);

	if ( leadwerk_theme_ludwig_schema_is_service_page( $source_key ) ) {
		$service = array(
			'@type'       => 'Service',
			'@id'         => $service_id,
			'name'        => $title,
			'url'         => $canonical,
			'description' => $description,
			'provider'    => array( '@id' => $org_id ),
			'areaServed'  => array( '@id' => $place_id ),
			'serviceType' => $title,
		);

		if ( 'ludwig-expat-beratung-1-v1' === $source_key ) {
			$service['name']        = 'Krankenversicherung für deutsche Expats in Dubai';
			$service['serviceType'] = array(
				'Krankenversicherungsberatung',
				'Expat Versicherung Dubai',
				'Internationale Krankenversicherung',
				'UAE Health Insurance Beratung',
			);
			$service['areaServed']  = array(
				array( '@type' => 'City', 'name' => 'Dubai', 'addressCountry' => 'AE' ),
				array( '@type' => 'Country', 'name' => 'United Arab Emirates' ),
			);
		}

		$graph[]                = $service;
		$web_page['mainEntity'] = array( '@id' => $service_id );
		$graph[3]               = $web_page;
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
}

/**
 * Output Ludwig geo tags and JSON-LD.
 *
 * @return void
 */
function leadwerk_theme_ludwig_output_structured_data() {
	if ( ! function_exists( 'leadwerk_theme_profile_is_ludwig' ) || ! leadwerk_theme_profile_is_ludwig() ) {
		return;
	}

	if ( ! is_singular( 'page' ) ) {
		return;
	}

	$post_id    = (int) get_queried_object_id();
	$source_key = function_exists( 'leadwerk_theme_get_current_source_key' ) ? leadwerk_theme_get_current_source_key() : '';
	if ( $post_id < 1 || '' === $source_key || 0 !== strpos( $source_key, 'ludwig-' ) ) {
		return;
	}

	$geo = leadwerk_theme_ludwig_schema_geo_profile( $source_key );
	echo '<meta name="geo.region" content="' . esc_attr( (string) $geo['region'] ) . '">' . "\n";
	echo '<meta name="geo.placename" content="' . esc_attr( (string) $geo['place'] ) . '">' . "\n";
	echo '<meta name="geo.position" content="' . esc_attr( (string) $geo['latitude'] . ';' . (string) $geo['longitude'] ) . '">' . "\n";
	echo '<meta name="ICBM" content="' . esc_attr( (string) $geo['latitude'] . ', ' . (string) $geo['longitude'] ) . '">' . "\n";

	$schema = leadwerk_theme_ludwig_schema_graph( $post_id, $source_key );
	$json   = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	if ( is_string( $json ) && '' !== $json ) {
		echo '<script type="application/ld+json" id="ludwig-structured-data">' . "\n" . $json . "\n" . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'leadwerk_theme_ludwig_output_structured_data', 20 );

/**
 * Output FAQPage JSON-LD when the page has Ludwig FAQ flexible rows.
 *
 * @return void
 */
function leadwerk_theme_ludwig_output_faqpage_schema() {
	if ( ! function_exists( 'leadwerk_theme_profile_is_ludwig' ) || ! leadwerk_theme_profile_is_ludwig() ) {
		return;
	}

	if ( ! is_singular( 'page' ) ) {
		return;
	}

	$post_id    = (int) get_queried_object_id();
	$source_key = function_exists( 'leadwerk_theme_get_current_source_key' ) ? leadwerk_theme_get_current_source_key() : '';
	if ( $post_id < 1 || '' === $source_key || 0 !== strpos( $source_key, 'ludwig-' ) ) {
		return;
	}

	$faq_rows = leadwerk_theme_ludwig_collect_faq_schema_items( $post_id, $source_key );
	if ( empty( $faq_rows ) ) {
		return;
	}

	$main_entity = array();
	foreach ( $faq_rows as $faq ) {
		$q = isset( $faq['question'] ) ? trim( (string) $faq['question'] ) : '';
		$a = isset( $faq['answer'] ) ? trim( (string) $faq['answer'] ) : '';
		if ( '' === $q ) {
			continue;
		}
		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $a,
			),
		);
	}

	if ( empty( $main_entity ) ) {
		return;
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $main_entity,
	);

	$json = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	if ( is_string( $json ) && '' !== $json ) {
		echo '<script type="application/ld+json" id="ludwig-faq-structured-data">' . "\n" . $json . "\n" . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'leadwerk_theme_ludwig_output_faqpage_schema', 21 );
