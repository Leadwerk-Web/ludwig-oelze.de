<?php
/**
 * Leadwerk Fields metabox UI.
 *
 * @package Leadwerk_Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Leadwerk_Fields_Metabox {

	/**
	 * Options page sections (title + fields).
	 *
	 * @var array<int,array{title:string,description?:string,fields:array<string,array<string,mixed>>}>
	 */
	private static $options_sections = array(
		array(
			'title' => 'Header',
			'description' => 'Logo fuer die Kopfzeile (Exact-Shells und Fallback). Texte wie CTA-Button, Logo-Alt und ARIA stehen in den Theme Strings (JSON) unten.',
			'fields'  => array(
				'header_logo' => array( 'label' => 'Header-Logo', 'type' => 'image' ),
			),
		),
		array(
			'title' => 'Footer',
			'description' => 'Logos, Social-Links, optional IS-BAO-Badge und CAMO-Link. Ueberschriften, Tagline, Copyright und AGB-Bezeichnung ueber Theme Strings.',
			'fields'  => array(
				'footer_logo'                => array( 'label' => 'Footer-Logo', 'type' => 'image' ),
				'footer_wordmark'            => array( 'label' => 'Footer-Schriftzug (Fallback-Theme)', 'type' => 'image' ),
				'footer_agb_url'             => array( 'label' => 'AGB-Link (optional, extern)', 'type' => 'url' ),
				'footer_social_linkedin_url' => array( 'label' => 'Social: LinkedIn URL', 'type' => 'url' ),
				'footer_social_instagram_url' => array( 'label' => 'Social: Instagram URL', 'type' => 'url' ),
				'footer_is_bao_badge'        => array( 'label' => 'IS-BAO-Badge Bild (optional)', 'type' => 'image' ),
				'footer_camo_url'            => array( 'label' => 'Services-Spalte: CAMO-Link (optional)', 'type' => 'url' ),
			),
		),
		array(
			'title' => 'Globale Kontaktdaten',
			'fields'  => array(
				'company_address' => array( 'label' => 'Adresse', 'type' => 'textarea' ),
				'company_phone'   => array( 'label' => 'Telefon', 'type' => 'text' ),
				'company_email'   => array( 'label' => 'E-Mail', 'type' => 'text' ),
				'google_maps_url' => array( 'label' => 'Google Maps URL', 'type' => 'url' ),
			),
		),
		array(
			'title' => 'Formulare',
			'fields'  => array(
				'wpforms_form_id_de' => array( 'label' => 'WPForms Form ID / Shortcode (DE)', 'type' => 'text' ),
				'wpforms_form_id_en' => array( 'label' => 'WPForms Form ID / Shortcode (EN)', 'type' => 'text' ),
				'wpforms_schadenfall_form_id_de' => array( 'label' => 'WPForms Schadenfall Form ID / Shortcode (DE)', 'type' => 'text' ),
				'wpforms_schadenfall_form_id_en' => array( 'label' => 'WPForms Schadenfall Form ID / Shortcode (EN)', 'type' => 'text' ),
			),
		),
		array(
			'title'       => 'Uebersetzungen (Theme Strings)',
			'description' => 'JSON mit Schluessel/Wert. Header/Footer u.a.: header_contact_cta_label, header_logo_alt, header_logo_link_aria_label, footer_tagline, footer_copyright, footer_legal_heading, footer_social_heading, footer_agb_link_label, footer_camo_link_label, footer_services_heading, footer_company_heading, footer_contact_heading, footer_phone_prefix, footer_desc_home, footer_desc_general, footer_desc_legal, header_language_* , services_menu_label.',
			'fields'      => array(
				'theme_strings_de' => array( 'label' => 'Theme Strings JSON (DE)', 'type' => 'textarea' ),
				'theme_strings_en' => array( 'label' => 'Theme Strings JSON (EN)', 'type' => 'textarea' ),
			),
		),
	);

	/**
	 * Flat map of all option field definitions.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function get_options_fields_flat() {
		$out = array();
		foreach ( self::$options_sections as $section ) {
			foreach ( $section['fields'] as $key => $def ) {
				$out[ $key ] = $def;
			}
		}
		return $out;
	}

	/**
	 * Load field value via global get_field() when present (ACF or Leadwerk shim), else JSON meta API.
	 *
	 * @param string          $name    Field name.
	 * @param int|string|null $post_id Post ID or "option".
	 * @return mixed
	 */
	private static function storage_get_field( $name, $post_id = null ) {
		// Leadwerk-Optionen immer ueber die API (leadwerk_opt_*), nie ACF — sonst zerstoert ACF/keses
		// Tailwind-Arbitrary-Klassen wie z-[100] und Roh-HTML (Modals) beim Speichern.
		if ( 'option' === $post_id || 'options' === $post_id ) {
			return Leadwerk_Fields_API::get_field( $name, $post_id );
		}
		if ( self::should_use_leadwerk_field_api( $name ) ) {
			return Leadwerk_Fields_API::get_field( $name, $post_id );
		}
		if ( function_exists( 'get_field' ) ) {
			return get_field( $name, $post_id );
		}
		return Leadwerk_Fields_API::get_field( $name, $post_id );
	}

	/**
	 * Persist field via global update_field() when present, else Leadwerk_Fields_API.
	 *
	 * @param string          $name    Field name.
	 * @param mixed           $value   Value.
	 * @param int|string|null $post_id Post ID or "option".
	 */
	private static function storage_update_field( $name, $value, $post_id = null ) {
		if ( 'option' === $post_id || 'options' === $post_id ) {
			Leadwerk_Fields_API::update_field( $name, $value, $post_id );
			return;
		}
		if ( self::should_use_leadwerk_field_api( $name ) ) {
			Leadwerk_Fields_API::update_field( $name, $value, $post_id );
			return;
		}
		if ( function_exists( 'update_field' ) ) {
			update_field( $name, $value, $post_id );
			return;
		}
		Leadwerk_Fields_API::update_field( $name, $value, $post_id );
	}

	/**
	 * Leadwerk-managed groups must bypass foreign get_field()/update_field() providers.
	 *
	 * @param string $name Field name.
	 * @return bool
	 */
	private static function should_use_leadwerk_field_api( $name ) {
		$name = sanitize_key( (string) $name );
		if ( '' === $name ) {
			return false;
		}

		if ( 'ludwig_page_document' === $name ) {
			return true;
		}

		return is_array( Leadwerk_Content_Schema::get_group( $name ) );
	}

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ), 10, 2 );
		add_action( 'save_post_page', array( __CLASS__, 'save_sections' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'register_options_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_filter( 'use_block_editor_for_post', array( __CLASS__, 'maybe_disable_block_editor' ), 10, 2 );
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'filter_tiny_mce_for_leadwerk_editors' ), 10, 2 );
	}

	/**
	 * Preserve layout HTML (div/p/section with class/style) in Leadwerk metabox TinyMCE instances.
	 *
	 * Editor IDs come from sanitize_title( $field_name ) or explicit leadwerk_opt_* — all start with "leadwerk".
	 *
	 * @param array<string,mixed> $init      TinyMCE init array.
	 * @param string              $editor_id Editor instance ID.
	 * @return array<string,mixed>
	 */
	public static function filter_tiny_mce_for_leadwerk_editors( $init, $editor_id ) {
		if ( 0 !== strpos( (string) $editor_id, 'leadwerk' ) ) {
			return $init;
		}

		$append = 'div[id|class|style|align|role|dir|lang],section[id|class|style|align|role|dir|lang],article[id|class|style|align|role|dir|lang],header[id|class|style|align|role|dir|lang],footer[id|class|style|align|role|dir|lang],main[id|class|style|align|role|dir|lang],aside[id|class|style|align|role|dir|lang],p[id|class|style|align|dir|lang],span[id|class|style|align|dir|lang],h1[id|class|style|align|dir|lang],h2[id|class|style|align|dir|lang],h3[id|class|style|align|dir|lang],h4[id|class|style|align|dir|lang],h5[id|class|style|align|dir|lang],h6[id|class|style|align|dir|lang]';

		if ( ! empty( $init['extended_valid_elements'] ) ) {
			$init['extended_valid_elements'] .= ',' . $append;
		} else {
			$init['extended_valid_elements'] = $append;
		}

		return $init;
	}

	public static function maybe_disable_block_editor( $use_block_editor, $post ) {
		if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
			return $use_block_editor;
		}

		$group = Leadwerk_Content_Schema::get_group_for_post( $post );
		if ( $group ) {
			return false;
		}

		return $use_block_editor;
	}

	public static function enqueue_admin_assets( $hook ) {
		$screens = array( 'post.php', 'post-new.php', 'toplevel_page_leadwerk-options' );
		$found   = false;

		foreach ( $screens as $screen ) {
			if ( false !== strpos( $hook, $screen ) || $hook === $screen ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_media();
		wp_add_inline_script( 'media-editor', self::get_inline_js() );
		wp_add_inline_style( 'wp-admin', self::get_inline_css() );
	}

	public static function register_metaboxes( $post_type, $post ) {
		if ( 'page' !== $post_type || ! $post ) {
			return;
		}

		$group = Leadwerk_Content_Schema::get_group_for_post( $post );
		if ( ! $group ) {
			return;
		}

		remove_post_type_support( 'page', 'editor' );

		add_meta_box(
			'leadwerk_page_sections',
			esc_html( $group['label'] ),
			array( __CLASS__, 'render_sections_metabox' ),
			'page',
			'normal',
			'high'
		);
	}

	public static function render_sections_metabox( $post ) {
		$group = Leadwerk_Content_Schema::get_group_for_post( $post );
		if ( ! $group ) {
			return;
		}

		$field_name   = $group['field_name'];
		$stored_value = self::storage_get_field( $field_name, $post->ID );
		$stored_value = is_array( $stored_value ) ? $stored_value : array();
		$legacy_value = self::get_legacy_ludwig_document_value( $field_name, $post->ID );
		$display      = self::get_display_group_context( $group, $field_name, $post->ID, $stored_value, $legacy_value );
		$display_value = is_array( $display['value'] ?? null ) ? $display['value'] : $stored_value;

		wp_nonce_field( 'leadwerk_save_sections', 'leadwerk_sections_nonce' );

		echo '<input type="hidden" name="leadwerk_sections_field_name" value="' . esc_attr( $field_name ) . '">';
		echo '<div class="leadwerk-metabox">';
		echo '<p class="description">' . esc_html( $group['description'] ) . '</p>';
		echo '<p class="description"><strong>Hinweis:</strong> Diese Seite wird ueber Leadwerk Fields gepflegt. Der normale Seiteninhalt ist kein Bearbeitungsbereich.</p>';
		if ( ! empty( $display['hydrated'] ) ) {
			$sources = array_values( array_unique( array_filter( array_map( 'strval', (array) ( $display['sources'] ?? array() ) ) ) ) );
			echo '<div class="notice notice-info inline"><p>';
			echo esc_html__( 'Die Bearbeitungsansicht wurde mit Fallback-Daten vervollstaendigt, damit fehlende strukturierte Felder sichtbar bleiben.', 'leadwerk-fields' );
			if ( ! empty( $sources ) ) {
				echo ' ';
				echo esc_html__( 'Quelle:', 'leadwerk-fields' ) . ' ' . esc_html( implode( ', ', $sources ) ) . '.';
			}
			echo ' ' . esc_html__( 'Beim naechsten Speichern werden die angezeigten Werte uebernommen.', 'leadwerk-fields' );
			echo '</p></div>';
		} elseif ( self::should_show_ludwig_migration_notice( $field_name, $stored_value, $legacy_value ) ) {
			echo '<div class="notice notice-warning inline"><p>';
			echo esc_html__( 'Structured migration pending: Dieses Ludwig-Dokument enthaelt noch Legacy-HTML. Bitte einen Leadwerk-Reparaturlauf oder einen frischen Ludwig-Re-Import ausfuehren, damit die semantischen Felder befuellt werden.', 'leadwerk-fields' );
			echo '</p></div>';
		}

		if ( empty( $group['layouts'] ) ) {
			echo '<div class="leadwerk-section-box">';
			echo '<div class="leadwerk-section-fields" style="display:block;">';

			foreach ( $group['fields'] as $field_key => $definition ) {
				$value = $display_value[ $field_key ] ?? Leadwerk_Content_Schema::get_default_value( $definition );
				self::render_field( "leadwerk_group[{$field_key}]", $definition, $value );
			}

			echo '</div>';
			echo '</div>';
			echo '</div>';
			return;
		}

		$group_values = Leadwerk_Content_Schema::get_group_root_values( $group, $display_value );
		$sections     = Leadwerk_Content_Schema::get_group_sections( $group, $display_value );

		if ( ! empty( $group['fields'] ) ) {
			echo '<div class="leadwerk-section-box leadwerk-group-box">';
			echo '<h3 class="leadwerk-section-title">Seiteneinstellungen</h3>';
			echo '<div class="leadwerk-section-fields" style="display:block;">';

			foreach ( $group['fields'] as $field_key => $definition ) {
				$value = $group_values[ $field_key ] ?? Leadwerk_Content_Schema::get_default_value( $definition );
				self::render_field( "leadwerk_group[{$field_key}]", $definition, $value );
			}

			echo '</div>';
			echo '</div>';
		}

		$index = 0;
		foreach ( (array) ( $group['layouts'] ?? array() ) as $layout => $schema ) {
			$section = isset( $sections[ $index ] ) && is_array( $sections[ $index ] ) ? $sections[ $index ] : array();
			$layout  = sanitize_key( (string) $layout );
			$label   = $schema['label'] ?? ucfirst( $layout );

			echo '<div class="leadwerk-section-box">';
			echo '<h3 class="leadwerk-section-title">';
			echo '<span class="leadwerk-section-number">' . (int) ( $index + 1 ) . '</span> ';
			echo esc_html( $label ) . ' <code>[' . esc_html( $layout ) . ']</code>';
			echo '</h3>';
			echo '<div class="leadwerk-section-fields">';
			echo '<input type="hidden" name="leadwerk_sections[' . esc_attr( (string) $index ) . '][acf_fc_layout]" value="' . esc_attr( $layout ) . '">';

			if ( $schema ) {
				foreach ( $schema['fields'] as $field_key => $definition ) {
					$value = $section[ $field_key ] ?? Leadwerk_Content_Schema::get_default_value( $definition );
					self::render_field( "leadwerk_sections[{$index}][{$field_key}]", $definition, $value );
				}
			}

			echo '</div>';
			echo '</div>';
			++$index;
		}

		echo '</div>';
	}

	/**
	 * Whether one field name belongs to the structured Ludwig groups.
	 *
	 * @param string $field_name Field group name.
	 * @return bool
	 */
	protected static function is_ludwig_structured_field_group( $field_name ) {
		$field_name = (string) $field_name;
		return '' !== $field_name && 0 === strpos( $field_name, 'ludwig_' ) && 'ludwig_page_document' !== $field_name;
	}

	/**
	 * Read the legacy Ludwig document payload if present.
	 *
	 * @param string $field_name Field group name.
	 * @param int    $post_id    Post ID.
	 * @return array<string,mixed>
	 */
	protected static function get_legacy_ludwig_document_value( $field_name, $post_id ) {
		if ( ! self::is_ludwig_structured_field_group( $field_name ) ) {
			return array();
		}

		$legacy_value = self::storage_get_field( 'ludwig_page_document', $post_id );
		if ( is_array( $legacy_value ) && ! empty( $legacy_value['sections'] ) && is_array( $legacy_value['sections'] ) ) {
			return $legacy_value;
		}

		$snapshot = get_post_meta( (int) $post_id, '_leadwerk_legacy_ludwig_page_document', true );
		if ( is_array( $snapshot ) && is_array( $snapshot['value'] ?? null ) ) {
			$legacy_value = $snapshot['value'];
			if ( ! empty( $legacy_value['sections'] ) && is_array( $legacy_value['sections'] ) ) {
				return $legacy_value;
			}
		}

		return array();
	}

	/**
	 * Determine whether the admin should warn about pending Ludwig migration.
	 *
	 * @param string              $field_name   Field group name.
	 * @param array<string,mixed> $stored_value Structured value.
	 * @param array<string,mixed> $legacy_value Legacy value.
	 * @return bool
	 */
	protected static function should_show_ludwig_migration_notice( $field_name, $stored_value, $legacy_value ) {
		if ( ! self::is_ludwig_structured_field_group( $field_name ) ) {
			return false;
		}

		$group = Leadwerk_Content_Schema::get_group( $field_name );
		if ( ! is_array( $group ) ) {
			return false;
		}

		if ( ! empty( Leadwerk_Content_Schema::get_group_sections( $group, $stored_value ) ) ) {
			return false;
		}

		return ! empty( $legacy_value['sections'] ) && is_array( $legacy_value['sections'] );
	}

	/**
	 * Resolve the admin-facing display value for one structured group.
	 *
	 * Stored structured values stay authoritative; missing fields are filled from
	 * last-good snapshots, legacy Ludwig HTML and finally the canonical shell.
	 *
	 * @param array<string,mixed> $group       Group schema.
	 * @param string              $field_name  Field group name.
	 * @param int                 $post_id     Post ID.
	 * @param array<string,mixed> $stored_value Stored structured value.
	 * @param array<string,mixed> $legacy_value Legacy Ludwig value.
	 * @return array<string,mixed>
	 */
	protected static function get_display_group_context( $group, $field_name, $post_id, $stored_value, $legacy_value ) {
		$stored_value = is_array( $stored_value ) ? $stored_value : array();
		$display_value = $stored_value;
		$sources       = array();
		$candidates    = array();

		$snapshot = self::get_last_good_snapshot_value( $field_name, $post_id );
		if ( is_array( $snapshot['value'] ?? null ) ) {
			$candidates[] = array(
				'label' => self::format_display_fallback_source_label( (string) ( $snapshot['source'] ?? 'last_good_snapshot' ) ),
				'value' => $snapshot['value'],
			);
		}

		if ( self::is_ludwig_structured_field_group( $field_name ) ) {
			$page_config = self::get_display_page_config( $group, $field_name, $post_id );

			$legacy_candidate = self::build_structured_candidate_from_legacy_value( $field_name, $page_config, $legacy_value );
			if ( ! empty( $legacy_candidate ) ) {
				$candidates[] = array(
					'label' => __( 'Legacy Ludwig HTML', 'leadwerk-fields' ),
					'value' => $legacy_candidate,
				);
			}

			$shell_candidate = self::build_structured_candidate_from_source_shell( $field_name, $page_config );
			if ( ! empty( $shell_candidate ) ) {
				$candidates[] = array(
					'label' => __( 'Canonical Shell', 'leadwerk-fields' ),
					'value' => $shell_candidate,
				);
			}
		}

		foreach ( $candidates as $candidate ) {
			$candidate_value = is_array( $candidate['value'] ?? null ) ? $candidate['value'] : array();
			if ( empty( $candidate_value ) ) {
				continue;
			}

			$before        = self::normalize_value_for_compare( $display_value );
			$display_value = self::merge_group_value_with_fallback( $group, $display_value, $candidate_value );
			$after         = self::normalize_value_for_compare( $display_value );

			if ( $before !== $after ) {
				$sources[] = (string) ( $candidate['label'] ?? '' );
			}
		}

		return array(
			'value'    => $display_value,
			'hydrated' => ! empty( $sources ),
			'sources'  => array_values( array_unique( array_filter( $sources ) ) ),
		);
	}

	/**
	 * Read the last-good structured snapshot for one field if present.
	 *
	 * @param string $field_name Field group name.
	 * @param int    $post_id    Post ID.
	 * @return array<string,mixed>
	 */
	protected static function get_last_good_snapshot_value( $field_name, $post_id ) {
		$snapshot = get_post_meta( (int) $post_id, '_leadwerk_last_good_' . sanitize_key( (string) $field_name ), true );
		if ( is_array( $snapshot ) && is_array( $snapshot['value'] ?? null ) ) {
			return $snapshot;
		}

		return array();
	}

	/**
	 * Friendly label for the fallback source.
	 *
	 * @param string $source Snapshot source.
	 * @return string
	 */
	protected static function format_display_fallback_source_label( $source ) {
		$map = array(
			'readback'          => __( 'Last-good Snapshot', 'leadwerk-fields' ),
			'restored_previous' => __( 'Last-good Snapshot', 'leadwerk-fields' ),
			'payload_fallback'  => __( 'Last-good Snapshot', 'leadwerk-fields' ),
			'force_canonical_sync' => __( 'Last-good Snapshot', 'leadwerk-fields' ),
		);

		$source = sanitize_key( (string) $source );
		return $map[ $source ] ?? __( 'Last-good Snapshot', 'leadwerk-fields' );
	}

	/**
	 * Build the minimal page config needed to parse canonical shell HTML.
	 *
	 * @param array<string,mixed> $group      Group schema.
	 * @param string              $field_name Field group name.
	 * @param int                 $post_id    Post ID.
	 * @return array<string,mixed>
	 */
	protected static function get_display_page_config( $group, $field_name, $post_id ) {
		$source_key = sanitize_key( (string) get_post_meta( (int) $post_id, 'leadwerk_source_key', true ) );
		if ( '' === $source_key ) {
			$source_key = sanitize_key( (string) ( $group['source_keys'][0] ?? '' ) );
		}

		return array(
			'field_name'  => (string) $field_name,
			'source_key'  => $source_key,
			'source_file' => self::get_source_file_for_source_key( $source_key ),
			'target_type' => 'page',
		);
	}

	/**
	 * Resolve the bundled source file for one source key.
	 *
	 * @param string $source_key Source key.
	 * @return string
	 */
	protected static function get_source_file_for_source_key( $source_key ) {
		$source_key = sanitize_key( (string) $source_key );
		if ( '' === $source_key ) {
			return '';
		}

		if ( function_exists( 'leadwerk_theme_get_source_template_map' ) ) {
			$map = (array) leadwerk_theme_get_source_template_map();
			$file = basename( (string) ( $map[ $source_key ] ?? '' ) );
			if ( '' !== $file ) {
				return $file;
			}
		}

		foreach ( self::get_mapping_manifest_pages() as $page ) {
			if ( ! is_array( $page ) ) {
				continue;
			}

			if ( $source_key !== sanitize_key( (string) ( $page['source_key'] ?? '' ) ) ) {
				continue;
			}

			$file = basename( (string) ( $page['source_file'] ?? '' ) );
			if ( '' !== $file ) {
				return $file;
			}
		}

		return '';
	}

	/**
	 * Load mapping manifest pages once.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	protected static function get_mapping_manifest_pages() {
		static $pages = null;

		if ( null !== $pages ) {
			return $pages;
		}

		$pages      = array();
		$candidates = array();

		if ( defined( 'LEADWERK_IMPORTER_PATH' ) ) {
			$candidates[] = LEADWERK_IMPORTER_PATH . 'manifest/mapping.json';
			$candidates[] = dirname( LEADWERK_IMPORTER_PATH ) . '/leadwerk_importer/manifest/mapping.json';
		}

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$candidates[] = trailingslashit( WP_CONTENT_DIR ) . 'plugins/leadwerk_importer/manifest/mapping.json';
		}

		foreach ( array_unique( array_filter( $candidates ) ) as $candidate ) {
			if ( ! is_file( $candidate ) ) {
				continue;
			}

			$json = file_get_contents( $candidate );
			$data = json_decode( (string) $json, true );
			if ( is_array( $data ) && ! empty( $data['pages'] ) && is_array( $data['pages'] ) ) {
				$pages = array_values( $data['pages'] );
				break;
			}
		}

		return $pages;
	}

	/**
	 * Shared filler instance for admin fallback parsing.
	 *
	 * @return Leadwerk_ACF_Filler|null
	 */
	protected static function get_display_filler() {
		static $filler = false;

		if ( false !== $filler ) {
			return $filler instanceof Leadwerk_ACF_Filler ? $filler : null;
		}

		if ( ! class_exists( 'Leadwerk_ACF_Filler' ) ) {
			$filler = null;
			return null;
		}

		$source_root = defined( 'LEADWERK_IMPORTER_PATH' ) ? LEADWERK_IMPORTER_PATH . 'source_assets' : '';
		$filler      = new Leadwerk_ACF_Filler( $source_root );
		return $filler;
	}

	/**
	 * Convert one legacy Ludwig document into the current structured group.
	 *
	 * @param string              $field_name   Field group name.
	 * @param array<string,mixed> $page_config  Page config.
	 * @param array<string,mixed> $legacy_value Legacy Ludwig document.
	 * @return array<string,mixed>
	 */
	protected static function build_structured_candidate_from_legacy_value( $field_name, $page_config, $legacy_value ) {
		if ( empty( $legacy_value['sections'] ) || ! is_array( $legacy_value['sections'] ) ) {
			return array();
		}

		$filler = self::get_display_filler();
		if ( ! $filler ) {
			return array();
		}

		$sections = array();
		foreach ( $legacy_value['sections'] as $section ) {
			$html = trim( (string) ( is_array( $section ) ? ( $section['section_html'] ?? '' ) : '' ) );
			if ( '' !== $html ) {
				$sections[] = $html;
			}
		}

		if ( empty( $sections ) ) {
			return array();
		}

		$payload = $filler->build_page_payload_from_sections(
			$page_config,
			$sections,
			array(
				'body_class'       => (string) ( $legacy_value['body_class'] ?? '' ),
				'document_title'   => (string) ( $legacy_value['document_title'] ?? '' ),
				'meta_description' => (string) ( $legacy_value['meta_description'] ?? '' ),
			),
			'de'
		);

		return self::extract_display_candidate_value( $filler, $field_name, $payload );
	}

	/**
	 * Parse the canonical shell HTML into a structured candidate.
	 *
	 * @param string              $field_name  Field group name.
	 * @param array<string,mixed> $page_config Page config.
	 * @return array<string,mixed>
	 */
	protected static function build_structured_candidate_from_source_shell( $field_name, $page_config ) {
		$html = self::get_source_shell_html_for_display( $page_config );
		if ( '' === trim( $html ) ) {
			return array();
		}

		$filler = self::get_display_filler();
		if ( ! $filler ) {
			return array();
		}

		$payload = $filler->build_page_payload_from_html( $page_config, $html, 'de' );
		$value   = self::extract_display_candidate_value( $filler, $field_name, $payload );
		if ( ! empty( $value ) ) {
			return $value;
		}

		$sections = self::extract_body_sections_from_html( $html );
		if ( empty( $sections ) ) {
			return array();
		}

		$payload = $filler->build_page_payload_from_sections( $page_config, $sections, array(), 'de' );
		return self::extract_display_candidate_value( $filler, $field_name, $payload );
	}

	/**
	 * Extract a validated structured value from a filler payload.
	 *
	 * @param Leadwerk_ACF_Filler $filler     Filler instance.
	 * @param string              $field_name Field group name.
	 * @param array<string,mixed> $payload    Filler payload.
	 * @return array<string,mixed>
	 */
	protected static function extract_display_candidate_value( $filler, $field_name, $payload ) {
		$value = is_array( $payload['value'] ?? null ) ? $payload['value'] : array();
		if ( empty( $value ) ) {
			return array();
		}

		$validation = $filler->validate_group_value( $field_name, $value );
		if ( empty( $validation['has_visible_content'] ) ) {
			return array();
		}

		return $value;
	}

	/**
	 * Load the canonical shell HTML for admin fallback display.
	 *
	 * @param array<string,mixed> $page_config Page config.
	 * @return string
	 */
	protected static function get_source_shell_html_for_display( $page_config ) {
		$source_key = sanitize_key( (string) ( $page_config['source_key'] ?? '' ) );
		if ( '' !== $source_key && function_exists( 'leadwerk_theme_get_source_template_html' ) ) {
			$html = (string) leadwerk_theme_get_source_template_html( $source_key );
			if ( '' !== trim( $html ) ) {
				return $html;
			}
		}

		$file_name = basename( (string) ( $page_config['source_file'] ?? '' ) );
		if ( '' === $file_name ) {
			return '';
		}

		$candidates = array();
		if ( defined( 'LEADWERK_THEME_DIR' ) ) {
			$candidates[] = trailingslashit( LEADWERK_THEME_DIR ) . 'source_shells/' . $file_name;
		}
		if ( defined( 'LEADWERK_IMPORTER_PATH' ) ) {
			$candidates[] = dirname( LEADWERK_IMPORTER_PATH ) . '/leadwerk_theme/source_shells/' . $file_name;
			$candidates[] = LEADWERK_IMPORTER_PATH . 'source_assets/' . $file_name;
		}
		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$candidates[] = trailingslashit( WP_CONTENT_DIR ) . 'themes/leadwerk_theme/source_shells/' . $file_name;
			$candidates[] = trailingslashit( WP_CONTENT_DIR ) . 'plugins/leadwerk_importer/source_assets/' . $file_name;
		}

		foreach ( array_unique( array_filter( $candidates ) ) as $candidate ) {
			if ( ! is_file( $candidate ) ) {
				continue;
			}

			$html = file_get_contents( $candidate );
			if ( false !== $html && '' !== trim( (string) $html ) ) {
				return (string) $html;
			}
		}

		return '';
	}

	/**
	 * Extract body <section> fragments from one HTML document.
	 *
	 * @param string $html HTML document.
	 * @return array<int,string>
	 */
	protected static function extract_body_sections_from_html( $html ) {
		$html = (string) $html;
		if ( '' === trim( $html ) ) {
			return array();
		}

		$sections = array();
		$dom      = new DOMDocument( '1.0', 'UTF-8' );

		libxml_use_internal_errors( true );
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
		libxml_clear_errors();

		if ( false === $loaded ) {
			return array();
		}

		$xpath = new DOMXPath( $dom );
		foreach ( $xpath->query( '//body//section' ) as $section_node ) {
			if ( ! $section_node instanceof DOMElement ) {
				continue;
			}

			$section_html = $dom->saveHTML( $section_node );
			if ( false === $section_html ) {
				continue;
			}

			$section_html = trim( (string) $section_html );
			if ( '' !== $section_html ) {
				$sections[] = $section_html;
			}
		}

		return $sections;
	}

	/**
	 * Merge one group value with a fallback value, filling only missing fields.
	 *
	 * @param array<string,mixed> $group    Group schema.
	 * @param mixed               $primary  Stored value.
	 * @param mixed               $fallback Fallback value.
	 * @return array<string,mixed>
	 */
	protected static function merge_group_value_with_fallback( $group, $primary, $fallback ) {
		$primary  = is_array( $primary ) ? $primary : array();
		$fallback = is_array( $fallback ) ? $fallback : array();

		if ( empty( $fallback ) ) {
			return $primary;
		}

		if ( empty( $group['layouts'] ) ) {
			return self::merge_definition_field_map( (array) ( $group['fields'] ?? array() ), $primary, $fallback );
		}

		$root_values = self::merge_definition_field_map(
			(array) ( $group['fields'] ?? array() ),
			Leadwerk_Content_Schema::get_group_root_values( $group, $primary ),
			Leadwerk_Content_Schema::get_group_root_values( $group, $fallback )
		);

		$primary_sections  = Leadwerk_Content_Schema::get_group_sections( $group, $primary );
		$fallback_sections = Leadwerk_Content_Schema::get_group_sections( $group, $fallback );
		$sections          = array();
		$index             = 0;

		foreach ( (array) ( $group['layouts'] ?? array() ) as $layout_key => $layout_schema ) {
			$primary_section  = isset( $primary_sections[ $index ] ) && is_array( $primary_sections[ $index ] ) ? $primary_sections[ $index ] : array();
			$fallback_section = isset( $fallback_sections[ $index ] ) && is_array( $fallback_sections[ $index ] ) ? $fallback_sections[ $index ] : array();
			$section          = self::merge_definition_field_map(
				(array) ( $layout_schema['fields'] ?? array() ),
				$primary_section,
				$fallback_section
			);
			$section['acf_fc_layout'] = sanitize_key( (string) ( $primary_section['acf_fc_layout'] ?? $fallback_section['acf_fc_layout'] ?? $layout_key ) );
			$sections[]               = $section;
			++$index;
		}

		return Leadwerk_Content_Schema::compose_group_value( $group, $root_values, $sections );
	}

	/**
	 * Merge a field map recursively, filling empty values from fallback.
	 *
	 * @param array<string,array<string,mixed>> $definitions Field definitions.
	 * @param array<string,mixed>               $primary     Stored values.
	 * @param array<string,mixed>               $fallback    Fallback values.
	 * @return array<string,mixed>
	 */
	protected static function merge_definition_field_map( $definitions, $primary, $fallback ) {
		$primary  = is_array( $primary ) ? $primary : array();
		$fallback = is_array( $fallback ) ? $fallback : array();
		$merged   = $primary;

		foreach ( $definitions as $field_key => $definition ) {
			$merged[ $field_key ] = self::merge_field_value(
				$definition,
				$primary[ $field_key ] ?? null,
				array_key_exists( $field_key, $primary ),
				$fallback[ $field_key ] ?? null,
				array_key_exists( $field_key, $fallback )
			);
		}

		return $merged;
	}

	/**
	 * Merge one field value with its fallback counterpart.
	 *
	 * @param array<string,mixed> $definition       Field definition.
	 * @param mixed               $primary_value    Stored value.
	 * @param bool                $primary_present  Whether the stored key exists.
	 * @param mixed               $fallback_value   Fallback value.
	 * @param bool                $fallback_present Whether the fallback key exists.
	 * @return mixed
	 */
	protected static function merge_field_value( $definition, $primary_value, $primary_present, $fallback_value, $fallback_present ) {
		$type = (string) ( $definition['type'] ?? 'text' );

		if ( 'repeater' === $type ) {
			$primary_rows  = is_array( $primary_value ) ? array_values( array_filter( $primary_value, 'is_array' ) ) : array();
			$fallback_rows = is_array( $fallback_value ) ? array_values( array_filter( $fallback_value, 'is_array' ) ) : array();
			$row_count     = max( count( $primary_rows ), count( $fallback_rows ) );
			$rows          = array();

			for ( $index = 0; $index < $row_count; ++$index ) {
				$primary_row  = isset( $primary_rows[ $index ] ) && is_array( $primary_rows[ $index ] ) ? $primary_rows[ $index ] : array();
				$fallback_row = isset( $fallback_rows[ $index ] ) && is_array( $fallback_rows[ $index ] ) ? $fallback_rows[ $index ] : array();
				$row          = self::merge_definition_field_map( (array) ( $definition['fields'] ?? array() ), $primary_row, $fallback_row );

				if ( self::field_has_meaningful_value( $definition, $row ) || isset( $primary_rows[ $index ] ) || isset( $fallback_rows[ $index ] ) ) {
					$rows[] = $row;
				}
			}

			return $rows;
		}

		if ( 'checkbox' === $type ) {
			if ( $primary_present ) {
				return ! empty( $primary_value );
			}

			if ( $fallback_present ) {
				return ! empty( $fallback_value );
			}

			return Leadwerk_Content_Schema::get_default_value( $definition );
		}

		if ( self::field_has_meaningful_value( $definition, $primary_value ) ) {
			return $primary_value;
		}

		if ( self::field_has_meaningful_value( $definition, $fallback_value ) ) {
			return $fallback_value;
		}

		return $primary_present ? $primary_value : Leadwerk_Content_Schema::get_default_value( $definition );
	}

	/**
	 * Whether a field value contains meaningful visible content.
	 *
	 * @param array<string,mixed> $definition Field definition.
	 * @param mixed               $value      Field value.
	 * @return bool
	 */
	protected static function field_has_meaningful_value( $definition, $value ) {
		$type = (string) ( $definition['type'] ?? 'text' );

		if ( 'checkbox' === $type ) {
			return ! empty( $value );
		}

		if ( in_array( $type, array( 'image', 'video', 'file' ), true ) ) {
			if ( is_numeric( $value ) ) {
				return (int) $value > 0;
			}

			return is_string( $value ) && '' !== trim( $value ) && '0' !== trim( $value );
		}

		if ( 'repeater' === $type ) {
			if ( ! is_array( $value ) ) {
				return false;
			}

			foreach ( $value as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				foreach ( (array) ( $definition['fields'] ?? array() ) as $sub_key => $sub_definition ) {
					if ( self::field_has_meaningful_value( $sub_definition, $row[ $sub_key ] ?? null ) ) {
						return true;
					}
				}
			}

			return false;
		}

		return self::string_has_visible_content( $value );
	}

	/**
	 * Whether one scalar/editor value contains visible content.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool
	 */
	protected static function string_has_visible_content( $value ) {
		if ( ! is_scalar( $value ) ) {
			return false;
		}

		$raw = trim( (string) $value );
		if ( '' === $raw ) {
			return false;
		}

		if ( '' !== trim( wp_strip_all_tags( $raw ) ) ) {
			return true;
		}

		foreach ( array( '<img', '<svg', '<video', '<iframe' ) as $needle ) {
			if ( false !== stripos( $raw, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize a value into a stable comparison string.
	 *
	 * @param mixed $value Value to compare.
	 * @return string
	 */
	protected static function normalize_value_for_compare( $value ) {
		$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $encoded ) ? $encoded : '';
	}

	public static function save_sections( $post_id, $post ) {
		if ( ! isset( $_POST['leadwerk_sections_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['leadwerk_sections_nonce'] ), 'leadwerk_save_sections' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$field_name = isset( $_POST['leadwerk_sections_field_name'] ) ? sanitize_key( wp_unslash( $_POST['leadwerk_sections_field_name'] ) ) : '';
		$group      = Leadwerk_Content_Schema::get_group( $field_name );
		if ( ! $group ) {
			return;
		}

		if ( empty( $group['layouts'] ) ) {
			$raw = $_POST['leadwerk_group'] ?? null;
			if ( ! is_array( $raw ) ) {
				return;
			}

			$values = array();
			foreach ( $group['fields'] as $field_key => $definition ) {
				$values[ $field_key ] = self::sanitize_field_value( $raw[ $field_key ] ?? null, $definition );
			}

			self::storage_update_field( $field_name, $values, $post_id );
			self::sync_post_content_if_needed( $post_id, $group, $values );
			self::sync_page_meta_if_needed( $post_id, $group, $values );
			return;
		}

		$group_raw = $_POST['leadwerk_group'] ?? array();
		$group_raw = is_array( $group_raw ) ? $group_raw : array();
		$group_values = array();
		foreach ( (array) ( $group['fields'] ?? array() ) as $field_key => $definition ) {
			$group_values[ $field_key ] = self::sanitize_field_value( $group_raw[ $field_key ] ?? null, $definition );
		}

		$raw = $_POST['leadwerk_sections'] ?? null;

		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$sections = array();

		foreach ( $raw as $section_raw ) {
			if ( ! is_array( $section_raw ) ) {
				continue;
			}

			$layout = isset( $section_raw['acf_fc_layout'] ) ? sanitize_key( wp_unslash( $section_raw['acf_fc_layout'] ) ) : '';
			$schema = Leadwerk_Content_Schema::get_layout( $field_name, $layout );

			if ( ! $schema ) {
				continue;
			}

			$section                  = array();
			$section['acf_fc_layout'] = $layout;

			foreach ( $schema['fields'] as $field_key => $definition ) {
				$section[ $field_key ] = self::sanitize_field_value( $section_raw[ $field_key ] ?? null, $definition );
			}

			$sections[] = $section;
		}

		$stored_value = Leadwerk_Content_Schema::compose_group_value( $group, $group_values, $sections );
		self::storage_update_field( $field_name, $stored_value, $post_id );
		self::sync_post_content_if_needed( $post_id, $group, $stored_value );
		self::sync_page_meta_if_needed( $post_id, $group, $stored_value );
	}

	public static function register_options_page() {
		add_menu_page(
			__( 'Leadwerk Optionen', 'leadwerk-fields' ),
			__( 'Leadwerk Optionen', 'leadwerk-fields' ),
			'manage_options',
			'leadwerk-options',
			array( __CLASS__, 'render_options_page' ),
			'dashicons-store',
			80
		);
	}

	public static function render_options_page() {
		if ( isset( $_POST['leadwerk_options_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['leadwerk_options_nonce'] ), 'leadwerk_save_options' ) ) {
			self::save_options();
			echo '<div class="notice notice-success"><p>Optionen gespeichert.</p></div>';
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Leadwerk Optionen', 'leadwerk-fields' ); ?></h1>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'leadwerk_save_options', 'leadwerk_options_nonce' ); ?>
				<?php foreach ( self::$options_sections as $section ) : ?>
				<h2 class="leadwerk-options-section-title"><?php echo esc_html( $section['title'] ); ?></h2>
				<?php if ( ! empty( $section['description'] ) ) : ?>
				<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<table class="form-table leadwerk-options-table">
					<?php foreach ( $section['fields'] as $key => $definition ) : ?>
					<tr>
						<th scope="row"><label for="leadwerk_opt_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $definition['label'] ); ?></label></th>
						<td><?php
						$value = self::storage_get_field( $key, 'option' );
						self::render_field( 'leadwerk_opt_' . $key, $definition, $value, 'leadwerk_opt_' . $key );
						if ( ! empty( $definition['help'] ) ) {
							echo '<p class="description">' . esc_html( (string) $definition['help'] ) . '</p>';
						}
						?></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php endforeach; ?>
				<?php submit_button( __( 'Optionen speichern', 'leadwerk-fields' ) ); ?>
			</form>
		</div>
		<?php
	}

	private static function save_options() {
		foreach ( self::get_options_fields_flat() as $key => $definition ) {
			$form_key = 'leadwerk_opt_' . $key;
			if ( array_key_exists( $form_key, $_POST ) ) {
				self::storage_update_field( $key, self::sanitize_field_value( $_POST[ $form_key ], $definition ), 'option' );
			}
		}
	}

	private static function render_field( $name, $definition, $value, $id = '' ) {
		$type  = $definition['type'] ?? 'text';
		$label = $definition['label'] ?? $name;
		$id    = $id ?: sanitize_title( $name );

		echo '<div class="leadwerk-field leadwerk-field-' . esc_attr( $type ) . '">';

		if ( 'checkbox' !== $type ) {
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		}

		switch ( $type ) {
			case 'text':
				echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text">';
				break;

			case 'url':
				// type="text": Browser-Validierung verbietet #anker, tel:, relative Pfade — WP sanitized dennoch mit esc_url_raw / Anker-Regel.
				echo '<input type="text" inputmode="url" autocomplete="url" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text" placeholder="https://… oder #bereich">';
				break;

			case 'textarea':
			case 'wysiwyg':
			case 'svg_code':
				$rows = 'svg_code' === $type ? 8 : 4;
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( (string) $rows ) . '" class="large-text' . ( 'svg_code' === $type ? ' code' : '' ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
				break;

			case 'html':
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="18" class="large-text code">' . esc_textarea( (string) $value ) . '</textarea>';
				echo '<p class="description">Raw HTML der statischen Sektion.</p>';
				break;

			case 'classic_editor':
				wp_editor(
					(string) $value,
					$id,
					array(
						'textarea_name' => $name,
						'textarea_rows' => 18,
						'media_buttons' => false,
						'teeny'         => false,
						'quicktags'     => true,
						'wpautop'       => false,
						// Avoid wrapping root <div> shells in <p> and reduce block splits that drop class/style on <p>.
						'tinymce'       => array(
							'wpautop'           => false,
							'forced_root_block' => false,
						),
					)
				);
				break;

			case 'heading_html':
				wp_editor(
					(string) $value,
					$id,
					array(
						'textarea_name' => $name,
						'textarea_rows' => 10,
						'media_buttons' => false,
						'teeny'         => false,
						'quicktags'     => true,
						'tinymce'       => true,
					)
				);
				echo '<p class="description">Nur Inline-Markup verwenden. Aussenliegende Absatz-Wrapper werden beim Speichern entfernt.</p>';
				break;

			case 'select_options':
				$text_value = '';
				if ( is_array( $value ) ) {
					$text_value = implode( "\n", array_map( 'strval', $value ) );
				}
				echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="5" class="large-text code">' . esc_textarea( $text_value ) . '</textarea>';
				echo '<p class="description">Eine Option pro Zeile.</p>';
				break;

			case 'checkbox':
				echo '<label class="leadwerk-checkbox">';
				echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0">';
				echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( ! empty( $value ), true, false ) . '>';
				echo '<span>' . esc_html( $label ) . '</span>';
				echo '</label>';
				break;

			case 'image':
				$img_id  = is_numeric( $value ) ? (int) $value : 0;
				$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				echo '<div class="leadwerk-image-field" data-target="' . esc_attr( $id ) . '">';
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $img_id ) . '">';
				echo '<div class="leadwerk-image-preview">';
				if ( $img_url ) {
					echo '<img src="' . esc_url( $img_url ) . '" alt="" style="max-width:150px;height:auto;">';
				}
				echo '</div>';
				echo '<button type="button" class="button leadwerk-image-select">Bild waehlen</button> ';
				echo '<button type="button" class="button leadwerk-image-remove">Entfernen</button>';
				echo '</div>';
				break;

			case 'video':
				$vid_id   = is_numeric( $value ) ? (int) $value : 0;
				$legacy   = ( ! $vid_id && is_string( $value ) ) ? trim( $value ) : '';
				$scalar   = is_scalar( $value ) ? (string) $value : '';
				$vid_url  = $vid_id ? wp_get_attachment_url( $vid_id ) : '';
				echo '<div class="leadwerk-video-field" data-target="' . esc_attr( $id ) . '">';
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $scalar ) . '" class="leadwerk-video-input">';
				echo '<div class="leadwerk-video-preview">';
				if ( $vid_url ) {
					echo '<p class="description"><a href="' . esc_url( $vid_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( wp_basename( $vid_url ) ) . '</a> <span class="description">(Anhang-ID ' . (int) $vid_id . ')</span></p>';
				} elseif ( '' !== $legacy ) {
					echo '<p class="description">' . esc_html__( 'Hinweis: Alter Pfad aus Import — bitte Video aus der Mediathek waehlen, um eine Anhang-ID zu setzen.', 'leadwerk-fields' ) . '</p>';
					echo '<p><code>' . esc_html( $legacy ) . '</code></p>';
				}
				echo '</div>';
				echo '<button type="button" class="button leadwerk-video-select">' . esc_html__( 'Video waehlen', 'leadwerk-fields' ) . '</button> ';
				echo '<button type="button" class="button leadwerk-video-remove">' . esc_html__( 'Entfernen', 'leadwerk-fields' ) . '</button>';
				echo '</div>';
				break;

			case 'file':
				$mime     = isset( $definition['mime'] ) ? (string) $definition['mime'] : 'application/pdf';
				$file_id  = is_numeric( $value ) ? (int) $value : 0;
				$legacy   = ( ! $file_id && is_string( $value ) ) ? trim( $value ) : '';
				$scalar   = is_scalar( $value ) ? (string) $value : '';
				$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
				echo '<div class="leadwerk-file-field" data-target="' . esc_attr( $id ) . '" data-mime="' . esc_attr( $mime ) . '">';
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $scalar ) . '" class="leadwerk-file-input">';
				echo '<div class="leadwerk-file-preview">';
				if ( $file_url ) {
					echo '<p class="description"><a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( wp_basename( $file_url ) ) . '</a> <span class="description">(Anhang-ID ' . (int) $file_id . ')</span></p>';
				} elseif ( '' !== $legacy ) {
					echo '<p class="description">' . esc_html__( 'Hinweis: Alter Wert aus Import — bitte Datei aus der Mediathek waehlen, um eine Anhang-ID zu setzen.', 'leadwerk-fields' ) . '</p>';
					echo '<p><code>' . esc_html( $legacy ) . '</code></p>';
				}
				echo '</div>';
				echo '<button type="button" class="button leadwerk-file-select">' . esc_html__( 'PDF waehlen', 'leadwerk-fields' ) . '</button> ';
				echo '<button type="button" class="button leadwerk-file-remove">' . esc_html__( 'Entfernen', 'leadwerk-fields' ) . '</button>';
				echo '</div>';
				break;

			case 'repeater':
				self::render_repeater_field( $name, $definition, $value, $id );
				break;
		}

		echo '</div>';
	}

	private static function render_repeater_field( $name, $definition, $value, $id ) {
		$items     = is_array( $value ) ? array_values( $value ) : array();
		$add_label = $definition['add_button_label'] ?? 'Eintrag hinzufuegen';

		echo '<div class="leadwerk-repeater" id="' . esc_attr( $id ) . '" data-next-index="' . esc_attr( (string) count( $items ) ) . '">';
		if ( ! empty( $definition['top_add_bar'] ) ) {
			echo '<div class="leadwerk-repeater-add-bar">';
			echo '<button type="button" class="button button-primary button-large leadwerk-repeater-add leadwerk-repeater-add-plus" title="' . esc_attr( $add_label ) . '" aria-label="' . esc_attr( $add_label ) . '">';
			echo '<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ';
			echo '<span class="leadwerk-repeater-add-plus-label">' . esc_html( $add_label ) . '</span>';
			echo '</button>';
			echo '</div>';
		}
		echo '<div class="leadwerk-repeater-items">';

		foreach ( $items as $index => $item ) {
			echo self::get_repeater_item_markup( $name, $definition, (int) $index, is_array( $item ) ? $item : array() );
		}

		echo '</div>';
		echo '<button type="button" class="button leadwerk-repeater-add leadwerk-repeater-add-bottom">' . esc_html( $add_label ) . '</button>';
		echo '<template class="leadwerk-repeater-template">' . self::get_repeater_item_markup( $name, $definition, '__INDEX__', array() ) . '</template>';
		echo '</div>';
	}

	private static function get_repeater_item_markup( $name, $definition, $index, $item ) {
		ob_start();
		?>
		<div class="leadwerk-repeater-item">
			<div class="leadwerk-repeater-item-header">
				<strong class="leadwerk-repeater-item-title">Eintrag</strong>
				<div class="leadwerk-repeater-item-actions">
					<button type="button" class="button button-small leadwerk-repeater-move-up">Nach oben</button>
					<button type="button" class="button button-small leadwerk-repeater-move-down">Nach unten</button>
					<button type="button" class="button button-small leadwerk-repeater-remove">Entfernen</button>
				</div>
			</div>
			<div class="leadwerk-repeater-item-fields">
				<?php
				foreach ( $definition['fields'] as $sub_key => $sub_definition ) {
					$sub_value = $item[ $sub_key ] ?? Leadwerk_Content_Schema::get_default_value( $sub_definition );
					self::render_field( $name . '[' . $index . '][' . $sub_key . ']', $sub_definition, $sub_value );
				}
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	private static function sanitize_field_value( $value, $definition ) {
		$type = $definition['type'] ?? 'text';

		switch ( $type ) {
			case 'text':
				return sanitize_text_field( is_null( $value ) ? '' : wp_unslash( $value ) );

			case 'url':
				$raw = trim( (string) wp_unslash( is_null( $value ) ? '' : $value ) );
				if ( '' === $raw ) {
					return '';
				}
				// Gleiche Seite / Anker (kein gueltiger URL-Typ fuer esc_url_raw).
				if ( preg_match( '/^#[^\s#]*$/u', $raw ) ) {
					return sanitize_text_field( $raw );
				}
				return esc_url_raw( $raw );

			case 'textarea':
				return sanitize_textarea_field( is_null( $value ) ? '' : wp_unslash( $value ) );

			case 'wysiwyg':
			case 'classic_editor':
				return wp_kses_post( is_null( $value ) ? '' : wp_unslash( $value ) );

			case 'heading_html':
				return Leadwerk_Content_Schema::sanitize_heading_html( is_null( $value ) ? '' : wp_unslash( $value ) );

			case 'html':
				return is_null( $value ) ? '' : (string) wp_unslash( $value );

			case 'svg_code':
				return trim( (string) wp_unslash( is_null( $value ) ? '' : $value ) );

			case 'image':
				return absint( $value );

			case 'video':
				$raw = wp_unslash( is_null( $value ) ? '' : $value );
				if ( is_numeric( $raw ) ) {
					return absint( $raw );
				}
				return sanitize_text_field( is_scalar( $raw ) ? (string) $raw : '' );

			case 'file':
				$raw = wp_unslash( is_null( $value ) ? '' : $value );
				if ( is_numeric( $raw ) ) {
					return absint( $raw );
				}
				return sanitize_text_field( is_scalar( $raw ) ? (string) $raw : '' );

			case 'checkbox':
				return ! empty( $value );

			case 'select_options':
				$raw = is_null( $value ) ? '' : wp_unslash( $value );
				$raw = is_array( $raw ) ? $raw : preg_split( '/\r\n|\r|\n/', (string) $raw );
				$raw = is_array( $raw ) ? $raw : array();
				$out = array();

				foreach ( $raw as $line ) {
					$line = sanitize_text_field( (string) $line );
					if ( '' !== $line ) {
						$out[] = $line;
					}
				}

				return $out;

			case 'repeater':
				$rows = is_array( $value ) ? $value : array();
				$out  = array();

				foreach ( $rows as $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}

					$item = array();
					foreach ( $definition['fields'] as $sub_key => $sub_definition ) {
						$item[ $sub_key ] = self::sanitize_field_value( $row[ $sub_key ] ?? null, $sub_definition );
					}
					$out[] = $item;
				}

				return $out;
		}

		return sanitize_text_field( is_null( $value ) ? '' : wp_unslash( $value ) );
	}

	private static function sync_post_content_if_needed( $post_id, $group, $value ) {
		if ( empty( $group['sync_post_content'] ) || ! is_array( $value ) ) {
			return;
		}

		$post_content = self::build_legal_page_content( $value );
		remove_action( 'save_post_page', array( __CLASS__, 'save_sections' ), 10 );
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $post_content,
			)
		);
		add_action( 'save_post_page', array( __CLASS__, 'save_sections' ), 10, 2 );
	}

	private static function build_legal_page_content( $value ) {
		$headline = trim( (string) ( $value['headline'] ?? '' ) );
		$content  = (string) ( $value['content'] ?? '' );

		return sprintf(
			'<section class="content-section content-section--white legal-content"><div class="container"><h1>%1$s</h1><div class="legal-copy">%2$s</div></div></section>',
			esc_html( $headline ),
			$content
		);
	}

	/**
	 * Truncate document title for Yoast SEO title meta (pixel-width heuristic).
	 *
	 * @param string $title      Title.
	 * @param int    $max_chars  Max characters.
	 * @return string
	 */
	private static function truncate_seo_title_for_yoast( $title, $max_chars = 58 ) {
		if ( function_exists( 'leadwerk_theme_truncate_seo_title_for_yoast' ) ) {
			return leadwerk_theme_truncate_seo_title_for_yoast( $title, $max_chars );
		}

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

	private static function sync_page_meta_if_needed( $post_id, $group, $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		$root_values = Leadwerk_Content_Schema::get_group_root_values( $group, $value );
		if ( empty( $root_values ) && empty( $group['layouts'] ) ) {
			$root_values = $value;
		}

		if ( array_key_exists( 'document_title', (array) $root_values ) ) {
			$document_title = sanitize_text_field( (string) $root_values['document_title'] );
			if ( '' !== $document_title ) {
				update_post_meta( $post_id, 'leadwerk_document_title', $document_title );
				update_post_meta( $post_id, '_yoast_wpseo_title', self::truncate_seo_title_for_yoast( $document_title ) );
			} else {
				delete_post_meta( $post_id, 'leadwerk_document_title' );
				delete_post_meta( $post_id, '_yoast_wpseo_title' );
			}
		}

		if ( array_key_exists( 'meta_description', (array) $root_values ) ) {
			$meta_description = sanitize_textarea_field( (string) $root_values['meta_description'] );
			if ( '' !== $meta_description ) {
				update_post_meta( $post_id, 'leadwerk_meta_description', $meta_description );
				update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_description );
			} else {
				delete_post_meta( $post_id, 'leadwerk_meta_description' );
				delete_post_meta( $post_id, '_yoast_wpseo_metadesc' );
			}
		}
	}

	private static function get_inline_css() {
		return '
.leadwerk-metabox { max-width: 100%; }
.leadwerk-section-box { background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; margin: 12px 0; overflow: hidden; }
.leadwerk-group-box { border-color: #c8d7e1; background: #f6fbff; }
.leadwerk-section-title { margin: 0; padding: 12px 16px; background: #e9e9e9; border-bottom: 1px solid #ddd; font-size: 14px; font-weight: 600; cursor: pointer; }
.leadwerk-section-title:hover { background: #ddd; }
.leadwerk-section-number { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: #0073aa; color: #fff; border-radius: 50%; font-size: 12px; margin-right: 6px; }
.leadwerk-section-fields { padding: 12px 16px; }
.leadwerk-field { margin-bottom: 14px; }
.leadwerk-field > label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
.leadwerk-checkbox { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
.leadwerk-image-preview { margin: 6px 0; }
.leadwerk-image-preview img { border: 1px solid #ddd; border-radius: 4px; }
.leadwerk-video-preview { margin: 6px 0; }
.leadwerk-file-preview { margin: 6px 0; }
.leadwerk-repeater { border: 1px solid #d0d7de; background: #fff; border-radius: 6px; padding: 10px; }
.leadwerk-repeater-add-bar { margin-bottom: 12px; }
.leadwerk-repeater-add-plus .dashicons { vertical-align: middle; margin-top: -3px; width: 20px; height: 20px; font-size: 20px; line-height: 1; }
.leadwerk-repeater-add-plus .leadwerk-repeater-add-plus-label { vertical-align: middle; }
.leadwerk-repeater-add-bottom { margin-top: 4px; }
.leadwerk-repeater-items { display: grid; gap: 12px; margin-bottom: 10px; }
.leadwerk-repeater-item { border: 1px solid #d0d7de; border-radius: 6px; background: #fafafa; }
.leadwerk-repeater-item-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; background: #f3f4f6; }
.leadwerk-repeater-item.is-collapsed .leadwerk-repeater-item-fields { display: none; }
.leadwerk-repeater-item-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.leadwerk-repeater-item-fields { padding: 12px; }
.leadwerk-field-classic_editor .wp-editor-wrap,
.leadwerk-field-heading_html .wp-editor-wrap { max-width: 100%; }
.leadwerk-field-classic_editor .wp-editor-area { min-height: 320px; }
.leadwerk-field-heading_html .wp-editor-area { min-height: 180px; }
.leadwerk-options-table .leadwerk-field { margin: 0; }
.leadwerk-options-table .leadwerk-field > label { display: none; }
h2.leadwerk-options-section-title { margin: 1.75em 0 0.35em; padding: 0; font-size: 1.3em; }
.wrap h2.leadwerk-options-section-title:first-of-type { margin-top: 0.5em; }
';
	}

	private static function get_inline_js() {
		return "
jQuery(function($){
	function findRepeaterRowTitle(item){
		var selectors = [
			'input[name$=\"[title]\"], textarea[name$=\"[title]\"], input[name$=\"[question]\"], textarea[name$=\"[question]\"], input[name$=\"[label]\"], textarea[name$=\"[label]\"], input[name$=\"[name]\"], textarea[name$=\"[name]\"], input[name$=\"[headline]\"], textarea[name$=\"[headline]\"], input[name$=\"[tab_label]\"], textarea[name$=\"[tab_label]\"]'
		];
		for (var i = 0; i < selectors.length; i++) {
			var field = item.find(selectors[i]).filter(function(){
				return $.trim($(this).val()).length > 0;
			}).first();
			if (field.length) {
				return $.trim(field.val());
			}
		}
		return '';
	}

	function updateRepeaterTitles(container){
		container.find('.leadwerk-repeater-item').each(function(index){
			var title = findRepeaterRowTitle($(this));
			$(this).find('.leadwerk-repeater-item-title').text(title || ('Eintrag ' + (index + 1)));
		});
	}

	$(document).on('click','.leadwerk-image-select',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-image-field');
		var frame = wp.media({title:'Bild waehlen',button:{text:'Auswaehlen'},multiple:false});
		frame.on('select',function(){
			var att = frame.state().get('selection').first().toJSON();
			wrap.find('input[type=hidden]').val(att.id);
			var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
			wrap.find('.leadwerk-image-preview').html('<img src=\"' + thumb + '\" alt=\"\" style=\"max-width:150px;height:auto;\">');
		});
		frame.open();
	});

	$(document).on('click','.leadwerk-image-remove',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-image-field');
		wrap.find('input[type=hidden]').val('0');
		wrap.find('.leadwerk-image-preview').html('');
	});

	$(document).on('click','.leadwerk-video-select',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-video-field');
		var frame = wp.media({title:'Video waehlen',button:{text:'Auswaehlen'},library:{type:'video'},multiple:false});
		frame.on('select',function(){
			var att = frame.state().get('selection').first().toJSON();
			wrap.find('input.leadwerk-video-input').val(String(att.id));
			var name = att.filename || (att.url ? att.url.split('/').pop() : '');
			var link = att.url ? '<a href=\"'+att.url+'\" target=\"_blank\" rel=\"noopener noreferrer\">'+name+'</a>' : name;
			wrap.find('.leadwerk-video-preview').html('<p class=\"description\">'+link+' <span class=\"description\">(Anhang-ID '+att.id+')</span></p>');
		});
		frame.open();
	});

	$(document).on('click','.leadwerk-video-remove',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-video-field');
		wrap.find('input.leadwerk-video-input').val('');
		wrap.find('.leadwerk-video-preview').html('');
	});

	$(document).on('click','.leadwerk-file-select',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-file-field');
		var mime = wrap.attr('data-mime') || 'application/pdf';
		var frame = wp.media({title:'PDF waehlen',button:{text:'Auswaehlen'},library:{type:mime},multiple:false});
		frame.on('select',function(){
			var att = frame.state().get('selection').first().toJSON();
			if (att.mime && att.mime !== 'application/pdf' && mime === 'application/pdf') {
				window.alert('Bitte eine PDF-Datei waehlen.');
				return;
			}
			wrap.find('input.leadwerk-file-input').val(String(att.id));
			var name = att.filename || (att.url ? att.url.split('/').pop() : '');
			var link = att.url ? '<a href=\"'+att.url+'\" target=\"_blank\" rel=\"noopener noreferrer\">'+name+'</a>' : name;
			wrap.find('.leadwerk-file-preview').html('<p class=\"description\">'+link+' <span class=\"description\">(Anhang-ID '+att.id+')</span></p>');
		});
		frame.open();
	});

	$(document).on('click','.leadwerk-file-remove',function(e){
		e.preventDefault();
		var wrap = $(this).closest('.leadwerk-file-field');
		wrap.find('input.leadwerk-file-input').val('');
		wrap.find('.leadwerk-file-preview').html('');
	});

	$(document).on('click','.leadwerk-section-title',function(){
		$(this).next('.leadwerk-section-fields').slideToggle(200);
	});

	$(document).on('click','.leadwerk-repeater-add',function(e){
		e.preventDefault();
		var repeater = $(this).closest('.leadwerk-repeater');
		var nextIndex = parseInt(repeater.attr('data-next-index'), 10) || 0;
		var template = repeater.find('.leadwerk-repeater-template').html() || '';
		repeater.attr('data-next-index', nextIndex + 1);
		template = template.replace(/__INDEX__/g, nextIndex);
		repeater.find('.leadwerk-repeater-items').append(template);
		updateRepeaterTitles(repeater);
		var last = repeater.find('.leadwerk-repeater-item').last();
		last.addClass('is-collapsed');
		var lastItem = last.get(0);
		if (lastItem && lastItem.scrollIntoView) {
			lastItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	});

	$(document).on('click','.leadwerk-repeater-remove',function(e){
		e.preventDefault();
		var repeater = $(this).closest('.leadwerk-repeater');
		$(this).closest('.leadwerk-repeater-item').remove();
		updateRepeaterTitles(repeater);
	});

	$(document).on('click','.leadwerk-repeater-move-up',function(e){
		e.preventDefault();
		var item = $(this).closest('.leadwerk-repeater-item');
		var prev = item.prev('.leadwerk-repeater-item');
		if (prev.length) {
			item.insertBefore(prev);
			updateRepeaterTitles(item.closest('.leadwerk-repeater'));
		}
	});

	$(document).on('click','.leadwerk-repeater-move-down',function(e){
		e.preventDefault();
		var item = $(this).closest('.leadwerk-repeater-item');
		var next = item.next('.leadwerk-repeater-item');
		if (next.length) {
			item.insertAfter(next);
			updateRepeaterTitles(item.closest('.leadwerk-repeater'));
		}
	});

	$(document).on('click','.leadwerk-repeater-item-header',function(e){
		if ($(e.target).closest('.leadwerk-repeater-item-actions').length) {
			return;
		}
		$(this).closest('.leadwerk-repeater-item').toggleClass('is-collapsed');
	});

	$(document).on('input change','.leadwerk-repeater-item input,.leadwerk-repeater-item textarea, .leadwerk-repeater-item select',function(){
		updateRepeaterTitles($(this).closest('.leadwerk-repeater'));
	});

	$('.leadwerk-repeater').each(function(){
		updateRepeaterTitles($(this));
	});
});
";
	}
}
