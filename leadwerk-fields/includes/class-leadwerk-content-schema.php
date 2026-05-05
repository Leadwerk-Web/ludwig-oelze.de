<?php
/**
 * Shared structured schema for Leadwerk importer, fields metaboxes and theme renderers.
 *
 * @package Leadwerk_Fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Leadwerk_Content_Schema {

	/**
	 * Return all section field groups.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_groups() {
		static $groups = null;

		if ( null !== $groups ) {
			return $groups;
		}

		$groups = array(
			'ludwig_home_sections'       => array(
				'label'       => 'Ludwig Startseite',
				'description' => 'Strukturierte Sektionen der Ludwig-Startseite.',
				'source_keys' => array( 'ludwig-home-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'            => self::layout_ludwig_hero( 'Hero' ),
					'trust_strip'     => self::layout_ludwig_trust_strip(),
					'problem_cards'   => self::layout_ludwig_problem_cards(),
					'split_story'     => self::layout_ludwig_split_story( 'Story' ),
					'audience_tabs'   => self::layout_ludwig_audience_tabs(),
					'pillars_cta'     => self::layout_ludwig_pillars_cta(),
					'credential_grid' => self::layout_ludwig_credential_grid( 'Kompetenzen' ),
					'testimonials'    => self::layout_ludwig_testimonials(),
					'center_cta'      => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_zusammenarbeit_sections' => array(
				'label'       => 'Ludwig Zusammenarbeit',
				'description' => 'Strukturierte Sektionen der Seite Zusammenarbeit.',
				'source_keys' => array( 'ludwig-zusammenarbeit-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'             => self::layout_ludwig_hero( 'Hero' ),
					'intro_copy'       => self::layout_ludwig_intro_copy( 'Einleitung' ),
					'timeline'         => self::layout_ludwig_timeline(),
					'comparison_table' => self::layout_ludwig_comparison_table(),
					'intro_copy_costs' => self::layout_ludwig_intro_copy( 'Kosten & Transparenz' ),
					'faq'              => self::layout_ludwig_faq(),
					'center_cta'       => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_gold_service_sections' => array(
				'label'       => 'Ludwig Gold-Service',
				'description' => 'Strukturierte Sektionen der Gold-Service-Seite.',
				'source_keys' => array( 'ludwig-gold-service-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'            => self::layout_ludwig_hero( 'Hero' ),
					'quote_callout'   => self::layout_ludwig_quote_callout(),
					'pillars_cta'     => self::layout_ludwig_pillars_cta(),
					'feature_grid'    => self::layout_ludwig_feature_grid(),
					'checklist_split' => self::layout_ludwig_checklist_split(),
					'pricing_cards'   => self::layout_ludwig_pricing_cards(),
					'faq'             => self::layout_ludwig_faq(),
					'center_cta'      => self::layout_ludwig_center_cta(),
					'customer_videos' => self::layout_ludwig_customer_videos(),
				),
			),
			'ludwig_familien_sections' => array(
				'label'       => 'Ludwig Fuer Familien',
				'description' => 'Strukturierte Sektionen der Familien-Seite.',
				'source_keys' => array( 'ludwig-familien-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'          => self::layout_ludwig_hero( 'Hero' ),
					'problem_cards' => self::layout_ludwig_problem_cards(),
					'split_story'   => self::layout_ludwig_split_story( 'Story' ),
					'faq'           => self::layout_ludwig_faq(),
					'case_study'    => self::layout_ludwig_case_study(),
					'testimonials'  => self::layout_ludwig_testimonials(),
					'center_cta'    => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_selbststaendige_sections' => array(
				'label'       => 'Ludwig Fuer Selbststaendige',
				'description' => 'Strukturierte Sektionen der Selbststaendige-Seite.',
				'source_keys' => array( 'ludwig-selbststaendige-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'                  => self::layout_ludwig_hero( 'Hero' ),
					'problem_cards'         => self::layout_ludwig_problem_cards(),
					'split_story'           => self::layout_ludwig_split_story( 'Story' ),
					'feature_grid'          => self::layout_ludwig_feature_grid(),
					'feature_checklist_cta' => self::layout_ludwig_feature_checklist_cta(),
					'case_study'            => self::layout_ludwig_case_study(),
					'center_cta'            => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_expats_sections' => array(
				'label'       => 'Ludwig Expats',
				'description' => 'Strukturierte Sektionen der Expats-Seite.',
				'source_keys' => array( 'ludwig-expats-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'          => self::layout_ludwig_hero( 'Hero' ),
					'problem_cards' => self::layout_ludwig_problem_cards(),
					'split_story'   => self::layout_ludwig_split_story( 'Story' ),
					'feature_grid'  => self::layout_ludwig_feature_grid(),
					'contact_cards' => self::layout_ludwig_contact_cards(),
					'center_cta'    => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_ueber_ludwig_sections' => array(
				'label'       => 'Ludwig Ueber Ludwig',
				'description' => 'Strukturierte Sektionen der Ueber-Ludwig-Seite.',
				'source_keys' => array( 'ludwig-ueber-ludwig-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'                      => self::layout_ludwig_hero( 'Hero' ),
					'split_story_journey'       => self::layout_ludwig_split_story( 'Story' ),
					'credential_grid_values'    => self::layout_ludwig_credential_grid( 'Werte' ),
					'credential_grid_qualities' => self::layout_ludwig_credential_grid( 'Qualifikationen' ),
					'split_story_personal'      => self::layout_ludwig_split_story( 'Persoenlich' ),
					'intro_copy_contact'        => self::layout_ludwig_intro_copy( 'Kontaktkarte' ),
				),
			),
			'ludwig_wissen_sections' => array(
				'label'       => 'Ludwig Wissen',
				'description' => 'Strukturierte Sektionen der Wissens-Seite.',
				'source_keys' => array( 'ludwig-wissen-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'          => self::layout_ludwig_hero( 'Hero' ),
					'feature_grid'  => self::layout_ludwig_feature_grid(),
					'article_cards' => self::layout_ludwig_article_cards(),
					'center_cta'    => self::layout_ludwig_center_cta(),
				),
			),
			'ludwig_kontakt_sections' => array(
				'label'       => 'Ludwig Kontakt',
				'description' => 'Strukturierte Sektionen der Kontakt-Seite.',
				'source_keys' => array( 'ludwig-kontakt-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'hero'               => self::layout_ludwig_hero( 'Hero' ),
					'contact_form_split' => self::layout_ludwig_contact_form_split(),
					'contact_cards'      => self::layout_ludwig_contact_cards(),
					'timeline'           => self::layout_ludwig_timeline(),
					'faq'                => self::layout_ludwig_faq(),
					'location_map'       => self::layout_ludwig_location_map(),
				),
			),
			'ludwig_impressum_page' => array(
				'label'       => 'Ludwig Impressum',
				'description' => 'Strukturiertes Impressum fuer Ludwig.',
				'source_keys' => array( 'ludwig-impressum-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'legal_document' => self::layout_ludwig_legal_document(),
				),
			),
			'ludwig_datenschutz_page' => array(
				'label'       => 'Ludwig Datenschutz',
				'description' => 'Strukturierte Datenschutzerklaerung fuer Ludwig.',
				'source_keys' => array( 'ludwig-datenschutz-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'legal_document' => self::layout_ludwig_legal_document(),
				),
			),
			'ludwig_erstinformation_page' => array(
				'label'       => 'Ludwig Erstinformation',
				'description' => 'Strukturierte Erstinformation fuer Ludwig.',
				'source_keys' => array( 'ludwig-erstinformation-v1' ),
				'fields'      => self::ludwig_root_fields(),
				'layouts'     => array(
					'legal_document' => self::layout_ludwig_legal_document( true ),
				),
			),
			'ludwig_page_document'       => array(
				'label'       => 'Ludwig Seiten-Dokument',
				'description' => 'Statisches Ludwig-Dokument mit Body-Klasse, Meta-Daten und frei editierbaren HTML-Sektionen.',
				'source_keys' => array(),
				'fields'      => array(
					'body_class'       => self::text( 'Body-Klasse' ),
					'document_title'   => self::text( 'Document Title' ),
					'meta_description' => self::textarea( 'Meta Description' ),
					'sections'         => self::repeater(
						'Sektionen',
						array(
							'section_key'  => self::text( 'Section Key' ),
							'section_html' => self::html( 'Section HTML' ),
						),
						'Sektion hinzufügen',
						array(
							'top_add_bar' => true,
						)
					),
				),
			),
			'finora_home_sections'        => array(
				'label'       => 'ACM Startseite (Legacy)',
				'description' => 'Legacy-Sektionen der Startseite in fester Reihenfolge bearbeiten.',
				'source_keys' => array( 'acm-home-v1' ),
				'layouts'     => array(
					'hero_slider'  => self::layout_home_hero_slider(),
					'pillars'      => self::layout_pillars(),
					'audience'     => self::layout_home_audience_switcher(),
					'why_acm'      => self::layout_why_acm(),
					'how_it_works' => self::layout_how_it_works(),
					'testimonials' => self::layout_testimonials(),
					'faq'          => self::layout_faq(),
				),
			),
			'finora_about_sections'       => array(
				'label'       => 'ACM Ueber uns (Legacy)',
				'description' => 'Legacy-Sektionen der Ueber-uns-Seite.',
				'source_keys' => array( 'acm-about-v1' ),
				'layouts'     => array(
					'hero'         => self::layout_hero(),
					'why_acm'      => self::layout_why_acm(),
					'finanzwelt'   => self::layout_banner_cta(),
					'bedeutet'     => self::layout_about_bedeutet(),
					'how_it_works' => self::layout_how_it_works(),
					'testimonials' => self::layout_testimonials(),
					'faq'          => self::layout_faq(),
				),
			),
			'finora_philosophy_sections'  => array(
				'label'       => 'ACM Philosophie (Legacy)',
				'description' => 'Legacy-Sektionen der Philosophie-Seite.',
				'source_keys' => array( 'acm-philosophy-v1' ),
				'layouts'     => array(
					'hero'            => self::layout_hero(),
					'pillars'         => self::layout_pillars(),
					'basis_detail'    => self::layout_media_blurbs( 'basis_detail' ),
					'basis_audience'  => self::layout_audience_cards( 'basis_audience' ),
					'break_one'       => self::layout_center_cta( 'break_one' ),
					'invest_detail'   => self::layout_invest_detail(),
					'invest_audience' => self::layout_audience_cards( 'invest_audience' ),
					'break_two'       => self::layout_center_cta( 'break_two' ),
					'tax_detail'      => self::layout_tax_detail(),
					'tax_audience'    => self::layout_audience_cards( 'tax_audience' ),
					'break_three'     => self::layout_center_cta( 'break_three' ),
					'testimonials'    => self::layout_testimonials(),
					'faq'             => self::layout_faq(),
				),
			),
			'finora_contact_sections'     => array(
				'label'       => 'ACM Kontakt (Legacy)',
				'description' => 'Legacy Finora-Kontakt (hero + contact_main). Kein source_key: acm-contact-v1 gehoert zu acm_contact_sections (11 Shell-Sektionen).',
				'source_keys' => array(),
				'layouts'     => array(
					'hero'         => self::layout_hero( false ),
					'contact_main' => self::layout_contact_main(),
				),
			),
			'finora_retirement_sections'  => array(
				'label'       => 'Legacy Altersvorsorge',
				'description' => 'Legacy-Sektionen (nicht ACM).',
				'source_keys' => array( 'acm-retirement-v1' ),
				'layouts'     => array(
					'hero'             => self::layout_hero( false ),
					'meaning'          => self::layout_media_text(),
					'workflow'         => self::layout_workflow_blurbs(),
					'private_vorsorge' => self::layout_media_text(),
					'gap_cta'          => self::layout_center_cta( 'gap_cta' ),
					'favorites'        => self::layout_tabs_section(),
					'audience'         => self::layout_retirement_audience(),
					'concepts'         => self::layout_concepts_section(),
					'final_cta'        => self::layout_center_cta( 'final_cta' ),
				),
			),
			'finora_investment_sections'  => array(
				'label'       => 'Legacy Investment',
				'description' => 'Legacy-Sektionen (nicht ACM).',
				'source_keys' => array( 'acm-investment-v1' ),
				'layouts'     => array(
					'hero'          => self::layout_hero( false ),
					'strategy'      => self::layout_media_text(),
					'challenge'     => self::layout_blurb_image_section(),
					'approach'      => self::layout_approach_tiles(),
					'timeline'      => self::layout_timeline(),
					'target_groups' => self::layout_target_groups(),
					'results'       => self::layout_results_section(),
					'final_cta'     => self::layout_center_cta( 'final_cta' ),
				),
			),
			'finora_real_estate_sections' => array(
				'label'       => 'Legacy Immobilien',
				'description' => 'Legacy-Sektionen (nicht ACM).',
				'source_keys' => array( 'acm-real-estate-v1' ),
				'layouts'     => array(
					'hero'      => self::layout_hero( false ),
					'intro'     => self::layout_real_estate_intro(),
					'timeline'  => self::layout_timeline(),
					'calculator'=> self::layout_calculator(),
					'case'      => self::layout_case_highlight(),
					'final_cta' => self::layout_dark_cta(),
				),
			),
			'finora_inheritance_sections' => array(
				'label'       => 'Legacy Erbanlage',
				'description' => 'Legacy-Sektionen (nicht ACM).',
				'source_keys' => array( 'acm-inheritance-v1' ),
				'layouts'     => array(
					'hero'           => self::layout_hero( false ),
					'responsibility' => self::layout_responsibility(),
					'timeline'       => self::layout_timeline(),
					'new_phase'      => self::layout_new_phase(),
					'outcomes'       => self::layout_outcomes(),
					'target_group'   => self::layout_target_groups_image(),
					'final_cta'      => self::layout_center_cta( 'final_cta' ),
				),
			),
			'impressum_page'              => array(
				'label'             => 'ACM Impressum',
				'description'       => 'Impressum ueber Leadwerk Fields bearbeiten.',
				'source_keys'       => array( 'acm-impressum-v1' ),
				'sync_post_content' => true,
				'fields'            => array(
					'headline' => self::text( 'Seitenueberschrift' ),
					'content'  => self::editor( 'Inhalt' ),
				),
			),
			'datenschutz_page'            => array(
				'label'             => 'ACM Datenschutz',
				'description'       => 'Datenschutzerklaerung ueber Leadwerk Fields bearbeiten.',
				'source_keys'       => array( 'acm-datenschutz-v1' ),
				'sync_post_content' => true,
				'fields'            => array(
					'headline' => self::text( 'Seitenueberschrift' ),
					'content'  => self::editor( 'Inhalt' ),
				),
			),

			/* ───────────────────────────────────────────────
			 * ACM AIR CHARTER — Neue Seitengruppen
			 * ─────────────────────────────────────────────── */

			'acm_index_sections'          => array(
				'label'       => 'ACM Startseite',
				'description' => 'Alle Sektionen der ACM-Startseite bearbeiten.',
				'source_keys' => array( 'acm-index-v1' ),
				'layouts'     => array(
					'hero'              => self::layout_acm_hero_video(),
					'hero_statement'    => self::layout_acm_hero_statement(),
					'services'          => self::layout_acm_services_grid(),
					'services_promo'    => self::layout_acm_fullwidth_promo(),
					'trust_kpis'        => self::layout_acm_kpi_strip(),
					'about'             => self::layout_acm_about_teaser(),
					'charter_hero'      => self::layout_acm_charter_hero(),
					'maintenance'       => self::layout_acm_content_block(),
					'management'        => self::layout_acm_content_block(),
					'handling'          => self::layout_acm_content_block(),
					'careers'           => self::layout_acm_content_block(),
					'news'              => self::layout_acm_news_teaser(),
					'final_cta'         => self::layout_acm_home_final_cta(),
				),
			),
			'acm_thats_acm_sections'      => array(
				'label'       => 'ACM That\'s ACM',
				'description' => 'Sektionen der That\'s ACM-Seite bearbeiten.',
				'source_keys' => array( 'acm-thats-acm-v1' ),
				'layouts'     => array(
					'hero'                => self::layout_acm_hero(),
					'company_intro'       => self::layout_acm_intro_centered(),
					'banner_experience'   => self::layout_acm_fullwidth_promo(),
					'horizontal_timeline' => self::layout_acm_horizontal_timeline(),
					'certifications'      => self::layout_acm_certifications_grid(),
					'banner_contact'      => self::layout_acm_fullwidth_promo(),
					'capabilities'        => self::layout_acm_split_rows(),
					'homebase'            => self::layout_acm_content_block(),
					'contact_cta'         => self::layout_acm_contact_cta(),
				),
			),
			'acm_charter_sections'        => array(
				'label'       => 'ACM Charter',
				'description' => 'Sektionen der Charter-Seite bearbeiten.',
				'source_keys' => array( 'acm-charter-v1' ),
				'layouts'     => array(
					'hero'              => self::layout_acm_hero(),
					'value_prop'        => self::layout_acm_about_teaser(),
					'operative_split'   => self::layout_acm_split_icon_features(),
					'use_cases'         => self::layout_acm_certifications_grid(),
					'fleet_overview'    => self::layout_acm_fleet_overview(),
					'fleet_banner'      => self::layout_acm_fullwidth_promo(),
					'safety_certs'      => self::layout_acm_certifications_grid(),
					'differentiation'   => self::layout_acm_about_teaser(),
					'contact_cta'       => self::layout_acm_contact_cta(),
				),
			),
			'acm_global7500_sections'     => array(
				'label'       => 'ACM Global 7500',
				'description' => 'Sektionen der Bombardier Global 7500-Seite bearbeiten.',
				'source_keys' => array( 'acm-global-7500-v1' ),
				'layouts'     => array(
					'hero'             => self::layout_acm_hero(),
					'highlights'       => self::layout_acm_certifications_grid(),
					'aircraft_intro'   => self::layout_acm_about_teaser(),
					'cabin_story'      => self::layout_acm_about_teaser(),
					'cabin_zones'      => self::layout_acm_zone_cards(),
					'promo_banner'     => self::layout_acm_fullwidth_promo(),
					'gallery'          => self::layout_acm_image_carousel(),
					'floorplan'        => self::layout_acm_featured_figure(),
					'technical_specs'  => self::layout_acm_aircraft_specs(),
					'fleet_teaser'     => self::layout_acm_fleet_teaser(),
					'contact_cta'      => self::layout_acm_contact_cta(),
				),
			),
			'acm_global6000_sections'     => array(
				'label'       => 'ACM Global 6000',
				'description' => 'Sektionen der Bombardier Global 6000-Seite bearbeiten.',
				'source_keys' => array( 'acm-global-6000-v1' ),
				'layouts'     => array(
					'hero'             => self::layout_acm_hero(),
					'highlights'       => self::layout_acm_certifications_grid(),
					'aircraft_intro'   => self::layout_acm_about_teaser(),
					'cabin_story'      => self::layout_acm_about_teaser(),
					'cabin_zones'      => self::layout_acm_zone_cards(),
					'promo_banner'     => self::layout_acm_fullwidth_promo(),
					'gallery'          => self::layout_acm_image_carousel(),
					'floorplan'        => self::layout_acm_featured_figure(),
					'technical_specs'  => self::layout_acm_aircraft_specs(),
					'fleet_teaser'     => self::layout_acm_fleet_teaser(),
					'contact_cta'      => self::layout_acm_contact_cta(),
				),
			),
			'acm_globalxrs_sections'      => array(
				'label'       => 'ACM Global XRS',
				'description' => 'Sektionen der Bombardier Global XRS-Seite bearbeiten.',
				'source_keys' => array( 'acm-global-xrs-v1' ),
				'layouts'     => array(
					'hero'             => self::layout_acm_hero(),
					'highlights'       => self::layout_acm_certifications_grid(),
					'aircraft_intro'   => self::layout_acm_about_teaser(),
					'cabin_story'      => self::layout_acm_about_teaser(),
					'cabin_zones'      => self::layout_acm_zone_cards(),
					'promo_banner'     => self::layout_acm_fullwidth_promo(),
					'gallery'          => self::layout_acm_image_carousel(),
					'floorplan'        => self::layout_acm_featured_figure(),
					'technical_specs'  => self::layout_acm_aircraft_specs(),
					'fleet_teaser'     => self::layout_acm_fleet_teaser(),
					'contact_cta'      => self::layout_acm_contact_cta(),
				),
			),
			'acm_aircraft_sections'       => array(
				'label'       => 'ACM Aircraft Management',
				'description' => 'Sektionen der Aircraft-Management-Seite bearbeiten.',
				'source_keys' => array( 'acm-aircraft-v1' ),
				'layouts'     => array(
					'hero'              => self::layout_acm_hero(),
					'owner_intro'       => self::layout_acm_about_teaser(),
					'betriebsmodelle'   => self::layout_acm_betriebsmodelle(),
					'promo_banner'      => self::layout_acm_fullwidth_promo(),
					'process'           => self::layout_acm_process_split(),
					'integrated_split'  => self::layout_acm_split_icon_features(),
					'transparency'      => self::layout_acm_split_icon_features(),
					'contact_cta'       => self::layout_acm_contact_cta(),
				),
			),
			'acm_maintenance_sections'    => array(
				'label'       => 'ACM Maintenance',
				'description' => 'Sektionen der Maintenance-Seite bearbeiten.',
				'source_keys' => array( 'acm-maintenance-v1' ),
				'layouts'     => array(
					'hero'          => self::layout_acm_hero(),
					'owner_intro'   => self::layout_acm_about_teaser(),
					'services'      => self::layout_acm_betriebsmodelle(),
					'promo_banner'  => self::layout_acm_fullwidth_promo(),
					'process'       => self::layout_acm_process_split(),
					'aog'           => self::layout_acm_aog_callout(),
					'facility'      => self::layout_acm_split_icon_features(),
					'contact_cta'   => self::layout_acm_contact_cta(),
				),
			),
			'acm_careers_sections'        => array(
				'label'       => 'ACM Karriere',
				'description' => 'Sektionen der Karriere-Seite bearbeiten.',
				'source_keys' => array( 'acm-careers-v1' ),
				'layouts'     => array(
					'hero'           => self::layout_acm_hero(),
					'employer_intro' => self::layout_acm_about_teaser(),
					'benefits'       => self::layout_acm_certifications_grid(),
					'work_areas'     => self::layout_acm_area_cards(),
					'promo_top'      => self::layout_acm_fullwidth_promo(),
					'stellen'        => self::layout_acm_stellen(),
					'promo_bottom'   => self::layout_acm_fullwidth_promo(),
					'culture_split'  => self::layout_acm_split_highlights(),
					'contact_cta'    => self::layout_acm_contact_cta(),
				),
			),
			'acm_contact_sections'        => array(
				'label'       => 'ACM Kontakt',
				'description' => 'Sektionen der Kontakt-Seite bearbeiten.',
				'source_keys' => array( 'acm-contact-v1' ),
				'layouts'     => array(
					'hero'                    => self::layout_acm_hero( false ),
					'quick_links'             => self::layout_acm_contact_command_grid(),
					'dept_zentrale'           => self::layout_acm_contact_dept_section_layout( 'Zentrale' ),
					'dept_geschaeftsfuehrung' => self::layout_acm_contact_dept_section_layout( 'Geschaeftsfuehrung' ),
					'dept_sales_operations'   => self::layout_acm_contact_dept_section_layout( 'Sales Operations' ),
					'dept_camo'               => self::layout_acm_contact_dept_section_layout( 'CAMO' ),
					'dept_ground_operations'  => self::layout_acm_contact_dept_section_layout( 'Ground Operations' ),
					'dept_maintenance'        => self::layout_acm_contact_dept_section_layout( 'Maintenance' ),
					'dept_safety_compliance'  => self::layout_acm_contact_dept_section_layout( 'Safety Compliance' ),
					'dept_stores'             => self::layout_acm_contact_dept_section_layout( 'Stores' ),
					'general_inquiry'         => self::layout_acm_contact_inquiry_band(),
				),
			),
			'acm_news_sections'           => array(
				'label'       => 'ACM News',
				'description' => 'Sektionen der News-Archivseite bearbeiten.',
				'source_keys' => array( 'acm-news-v1' ),
				'layouts'     => array(
					'hero'          => self::layout_acm_hero( false ),
					'news_filters'  => self::layout_acm_news_filter_bar(),
					'news_grid'     => self::layout_acm_news_grid(),
					'news_archive'  => self::layout_acm_news_archive(),
					'contact_cta'   => self::layout_acm_contact_cta(),
				),
			),
		);

		return $groups;
	}

	/**
	 * Return one field group schema.
	 *
	 * @param string $field_name Field name.
	 * @return array<string,mixed>|null
	 */
	public static function get_group( $field_name ) {
		$groups = self::get_groups();
		return $groups[ $field_name ] ?? null;
	}

	/**
	 * Resolve a field group by source key.
	 *
	 * @param string $source_key Source key.
	 * @return array<string,mixed>|null
	 */
	public static function get_group_for_source_key( $source_key ) {
		foreach ( self::get_groups() as $field_name => $group ) {
			if ( in_array( $source_key, $group['source_keys'], true ) ) {
				$group['field_name'] = $field_name;
				return $group;
			}
		}

		if ( 0 === strpos( (string) $source_key, 'ludwig-' ) ) {
			$group = self::get_group( 'ludwig_page_document' );
			if ( is_array( $group ) ) {
				$group['field_name'] = 'ludwig_page_document';
				return $group;
			}
		}

		return null;
	}

	/**
	 * Resolve a field group by post.
	 *
	 * @param int|WP_Post $post Post object or ID.
	 * @return array<string,mixed>|null
	 */
	public static function get_group_for_post( $post ) {
		$post_id = is_object( $post ) ? (int) $post->ID : (int) $post;
		if ( ! $post_id ) {
			return null;
		}

		$source_key = (string) get_post_meta( $post_id, 'leadwerk_source_key', true );
		return self::get_group_for_source_key( $source_key );
	}

	/**
	 * Resolve a layout schema.
	 *
	 * @param string $field_name Field group name.
	 * @param string $layout     Layout name.
	 * @return array<string,mixed>|null
	 */
	public static function get_layout( $field_name, $layout ) {
		$group = self::get_group( $field_name );
		if ( ! $group ) {
			return null;
		}

		return $group['layouts'][ $layout ] ?? null;
	}

	/**
	 * Extract top-level field values for a group.
	 *
	 * @param array<string,mixed> $group Group schema.
	 * @param mixed               $value Stored value.
	 * @return array<string,mixed>
	 */
	public static function get_group_root_values( $group, $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		if ( empty( $group['layouts'] ) ) {
			return $value;
		}

		if ( array_key_exists( 'sections', $value ) && is_array( $value['sections'] ) ) {
			$root = $value;
			unset( $root['sections'] );
			return $root;
		}

		return array();
	}

	/**
	 * Extract ordered layout rows for a group.
	 *
	 * @param array<string,mixed> $group Group schema.
	 * @param mixed               $value Stored value.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_group_sections( $group, $value ) {
		if ( empty( $group['layouts'] ) || ! is_array( $value ) ) {
			return array();
		}

		if ( array_key_exists( 'sections', $value ) && is_array( $value['sections'] ) ) {
			return array_values( array_filter( $value['sections'], 'is_array' ) );
		}

		if ( self::is_list_array( $value ) ) {
			return array_values( array_filter( $value, 'is_array' ) );
		}

		$sections = array();
		foreach ( $value as $item ) {
			if ( is_array( $item ) && array_key_exists( 'acf_fc_layout', $item ) ) {
				$sections[] = $item;
			}
		}

		return $sections;
	}

	/**
	 * Compose one normalized group payload.
	 *
	 * @param array<string,mixed>    $group        Group schema.
	 * @param array<string,mixed>    $root_values  Root values.
	 * @param array<int,array<mixed>> $sections    Layout rows.
	 * @return array<string,mixed>|array<int,array<mixed>>
	 */
	public static function compose_group_value( $group, $root_values, $sections ) {
		$root_values = is_array( $root_values ) ? $root_values : array();
		$sections    = is_array( $sections ) ? array_values( array_filter( $sections, 'is_array' ) ) : array();

		if ( empty( $group['layouts'] ) ) {
			return $root_values;
		}

		if ( empty( $group['fields'] ) ) {
			return $sections;
		}

		$root_values['sections'] = $sections;
		return $root_values;
	}

	/**
	 * Whether an array uses sequential numeric keys.
	 *
	 * @param array<mixed> $value Candidate array.
	 * @return bool
	 */
	protected static function is_list_array( $value ) {
		if ( ! is_array( $value ) ) {
			return false;
		}

		if ( array() === $value ) {
			return true;
		}

		return array_keys( $value ) === range( 0, count( $value ) - 1 );
	}

	/**
	 * Default value for a field definition.
	 *
	 * @param array<string,mixed> $definition Field definition.
	 * @return mixed
	 */
	public static function get_default_value( $definition ) {
		if ( is_array( $definition ) && array_key_exists( 'default', $definition ) ) {
			return $definition['default'];
		}

		$type = $definition['type'] ?? 'text';

		switch ( $type ) {
			case 'checkbox':
				return false;
			case 'image':
			case 'video':
			case 'file':
				return 0;
			case 'repeater':
			case 'select_options':
				return array();
			default:
				return '';
		}
	}

	/**
	 * Basic text field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function text( $label ) {
		return array(
			'label' => $label,
			'type'  => 'text',
		);
	}

	/**
	 * Basic textarea field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function textarea( $label ) {
		return array(
			'label' => $label,
			'type'  => 'textarea',
		);
	}

	/**
	 * Raw HTML field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function html( $label ) {
		return array(
			'label' => $label,
			'type'  => 'html',
		);
	}

	/**
	 * Raw SVG markup (admin-trusted). Uses svg_code field type so save does not strip tags.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function svg_code( $label ) {
		return array(
			'label' => $label,
			'type'  => 'svg_code',
		);
	}

	/**
	 * Rich text field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function editor( $label ) {
		return array(
			'label' => $label,
			'type'  => 'classic_editor',
		);
	}

	/**
	 * Inline-safe rich text field definition for headings.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function heading_html( $label ) {
		return array(
			'label' => $label,
			'type'  => 'heading_html',
		);
	}

	/**
	 * Normalize heading markup so it can be injected into existing h* nodes.
	 *
	 * @param string $html Raw heading markup.
	 * @return string
	 */
	public static function sanitize_heading_html( $html ) {
		$html = wp_kses_post( (string) $html );
		if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
			return '';
		}

		if ( ! class_exists( 'DOMDocument' ) ) {
			$fallback = preg_replace( '#</?(p|div|section|article)\b[^>]*>#i', '', $html );
			$fallback = is_string( $fallback ) ? trim( $fallback ) : '';
			return '' === trim( wp_strip_all_tags( $fallback ) ) ? '' : $fallback;
		}

		$temp = new DOMDocument( '1.0', 'UTF-8' );
		libxml_use_internal_errors( true );
		$temp->loadHTML( '<?xml encoding="utf-8" ?><div id="leadwerk-heading-root">' . $html . '</div>' );
		libxml_clear_errors();

		$root = ( new DOMXPath( $temp ) )->query( '//*[@id="leadwerk-heading-root"]' )->item( 0 );
		if ( ! $root instanceof DOMNode ) {
			return '';
		}

		$normalized = self::serialize_inline_heading_children(
			$root,
			array(
				'a'      => true,
				'abbr'   => true,
				'b'      => true,
				'br'     => true,
				'cite'   => true,
				'code'   => true,
				'em'     => true,
				'i'      => true,
				'mark'   => true,
				'small'  => true,
				'span'   => true,
				'strong' => true,
				'sub'    => true,
				'sup'    => true,
				'u'      => true,
				'wbr'    => true,
			),
			array(
				'article',
				'aside',
				'blockquote',
				'div',
				'footer',
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'header',
				'li',
				'main',
				'ol',
				'p',
				'section',
				'ul',
			)
		);

		$normalized = trim( preg_replace( '/(?:<br>\s*){3,}/i', '<br><br>', (string) $normalized ) );
		return '' === trim( wp_strip_all_tags( $normalized ) ) ? '' : $normalized;
	}

	/**
	 * Serialize child nodes into inline-only heading HTML.
	 *
	 * @param DOMNode              $node                Root node.
	 * @param array<string,bool>   $allowed_inline_tags Allowed inline tags.
	 * @param string[]             $block_tags          Tags that should be flattened.
	 * @return string
	 */
	protected static function serialize_inline_heading_children( $node, $allowed_inline_tags, $block_tags ) {
		$chunks      = array();
		$last_was_br = false;

		foreach ( $node->childNodes as $child ) {
			$is_block = $child instanceof DOMElement && in_array( strtolower( $child->tagName ), $block_tags, true );
			$chunk    = self::serialize_inline_heading_node( $child, $allowed_inline_tags, $block_tags );
			if ( '' === $chunk ) {
				continue;
			}

			if ( $is_block && ! empty( $chunks ) && ! $last_was_br ) {
				$chunks[] = '<br>';
			}

			$chunks[]    = $chunk;
			$last_was_br = '<br>' === $chunk;
		}

		return implode( '', $chunks );
	}

	/**
	 * Serialize one node into inline-safe heading HTML.
	 *
	 * @param DOMNode            $node                Node.
	 * @param array<string,bool> $allowed_inline_tags Allowed inline tags.
	 * @param string[]           $block_tags          Tags that should be flattened.
	 * @return string
	 */
	protected static function serialize_inline_heading_node( $node, $allowed_inline_tags, $block_tags ) {
		if ( $node instanceof DOMText ) {
			$text = preg_replace( '/\s+/u', ' ', (string) $node->nodeValue );
			return '' === trim( (string) $text ) ? '' : esc_html( (string) $text );
		}

		if ( ! $node instanceof DOMElement ) {
			return '';
		}

		$tag = strtolower( $node->tagName );
		if ( 'br' === $tag ) {
			return '<br>';
		}

		if ( 'wbr' === $tag ) {
			return '<wbr>';
		}

		$children_html = self::serialize_inline_heading_children( $node, $allowed_inline_tags, $block_tags );
		if ( in_array( $tag, $block_tags, true ) || ! isset( $allowed_inline_tags[ $tag ] ) ) {
			return $children_html;
		}

		$attrs = '';
		if ( $node->hasAttributes() ) {
			foreach ( $node->attributes as $attribute ) {
				if ( ! $attribute instanceof DOMAttr ) {
					continue;
				}

				$attrs .= sprintf(
					' %1$s="%2$s"',
					esc_attr( $attribute->nodeName ),
					esc_attr( $attribute->nodeValue )
				);
			}
		}

		return sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, $attrs, $children_html );
	}

	/**
	 * URL field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function url( $label ) {
		return array(
			'label' => $label,
			'type'  => 'url',
		);
	}

	/**
	 * Image field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function image( $label ) {
		return array(
			'label' => $label,
			'type'  => 'image',
		);
	}

	/**
	 * Video (Mediathek-Anhang-ID, wie Bild).
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function video( $label ) {
		return array(
			'label' => $label,
			'type'  => 'video',
		);
	}

	/**
	 * Datei aus der Mediathek (Anhang-ID), optional MIME fuer wp.media-Filter.
	 *
	 * @param string $label Label.
	 * @param string $mime  z.B. application/pdf.
	 * @return array<string,mixed>
	 */
	protected static function file( $label, $mime = 'application/pdf' ) {
		return array(
			'label' => $label,
			'type'  => 'file',
			'mime'  => (string) $mime,
		);
	}

	/**
	 * Checkbox field definition.
	 *
	 * @param string $label Label.
	 * @return array<string,mixed>
	 */
	protected static function checkbox( $label, $default = false ) {
		return array(
			'label'   => $label,
			'type'    => 'checkbox',
			'default' => ! empty( $default ),
		);
	}

	/**
	 * Repeater field definition.
	 *
	 * @param string              $label    Label.
	 * @param array<string,mixed> $fields   Sub-fields.
	 * @param string|null         $add_text Optional button label.
	 * @param array<string,mixed> $options  Optional: top_add_bar (bool) — prominente +-Leiste oben.
	 * @return array<string,mixed>
	 */
	protected static function repeater( $label, $fields, $add_text = null, $options = null ) {
		$definition = array(
			'label'  => $label,
			'type'   => 'repeater',
			'fields' => $fields,
		);

		if ( null !== $add_text ) {
			$definition['add_button_label'] = $add_text;
		}

		if ( is_array( $options ) ) {
			foreach ( $options as $opt_key => $opt_val ) {
				$definition[ $opt_key ] = $opt_val;
			}
		}

		return $definition;
	}

	/**
	 * Internal/external button field set.
	 *
	 * @param string $prefix Field prefix.
	 * @return array<string,array<string,mixed>>
	 */
	protected static function button_fields( $prefix ) {
		return array(
			$prefix . '_label'    => self::text( 'Button Text' ),
			$prefix . '_page_key' => self::text( 'Button Zielseite (source_key)' ),
			$prefix . '_url'      => self::url( 'Button URL (Fallback/extern)' ),
		);
	}

	/**
	 * Standard hero layout.
	 *
	 * @param bool $with_button Whether CTA fields should be present.
	 * @return array<string,mixed>
	 */
	protected static function layout_hero( $with_button = true ) {
		$fields = array(
			'title'     => self::heading_html( 'Titel' ),
			'subtitle'  => self::textarea( 'Untertitel' ),
			'image'     => self::image( 'Bild' ),
			'image_alt' => self::text( 'Bild Alt-Text' ),
		);

		if ( $with_button ) {
			$fields = array_merge( $fields, self::button_fields( 'cta' ) );
		}

		return array(
			'label'    => 'Hero',
			'template' => 'hero',
			'fields'   => $fields,
		);
	}

	/**
	 * Home hero slider layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_home_hero_slider() {
		return array(
			'label'    => 'Hero Slider',
			'template' => 'hero_slider',
			'fields'   => array(
				'slides'     => self::repeater(
					'Slides',
					array(
						'title'          => self::heading_html( 'Titel' ),
						'subtitle'       => self::textarea( 'Untertitel' ),
						'background'     => self::image( 'Hintergrundbild' ),
						'background_alt' => self::text( 'Bild Alt-Text' ),
						'cta_label'      => self::text( 'Button Text' ),
						'cta_page_key'   => self::text( 'Button Zielseite (source_key)' ),
						'cta_url'        => self::url( 'Button URL (Fallback/extern)' ),
					),
					'Slide hinzufuegen'
				),
				'services'   => self::repeater(
					'Service Links',
					array(
						'title'       => self::heading_html( 'Titel' ),
						'description' => self::textarea( 'Kurztext' ),
						'icon'        => self::image( 'Icon' ),
						'icon_alt'    => self::text( 'Icon Alt-Text' ),
						'page_key'    => self::text( 'Zielseite (source_key)' ),
						'url'         => self::url( 'URL (Fallback/extern)' ),
					),
					'Service-Link hinzufuegen'
				),
				'prev_label' => self::text( 'Label Vorherige Folie' ),
				'next_label' => self::text( 'Label Naechste Folie' ),
			),
		);
	}

	/**
	 * Shared why ACM layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_why_acm() {
		return array(
			'label'    => 'Why ACM',
			'template' => 'why_acm',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'subtitle'  => self::textarea( 'Untertitel' ),
					'body'      => self::editor( 'Einleitung' ),
					'blurbs'    => self::repeater(
						'Vorteile',
						array(
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Vorteil hinzufuegen'
					),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared pillars layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_pillars() {
		return array(
			'label'    => 'Drei Saeulen',
			'template' => 'pillars',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Karten',
					array(
						'icon'            => self::image( 'Icon' ),
						'icon_alt'        => self::text( 'Icon Alt-Text' ),
						'title'           => self::text( 'Titel' ),
						'description'     => self::editor( 'Beschreibung' ),
						'button_label'    => self::text( 'Button Text' ),
						'button_page_key' => self::text( 'Button Zielseite (source_key)' ),
						'button_url'      => self::url( 'Button URL (Fallback/extern)' ),
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Home audience switcher layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_home_audience_switcher() {
		return array(
			'label'    => 'Fuer Menschen wie dich',
			'template' => 'audience_switcher',
			'fields'   => array(
				'title'      => self::heading_html( 'Titel' ),
				'prev_label' => self::text( 'Label Zurueck' ),
				'next_label' => self::text( 'Label Weiter' ),
				'items'      => self::repeater(
					'Profile',
					array(
						'label'      => self::text( 'Label' ),
						'card_title' => self::text( 'Kartentitel' ),
						'body'       => self::editor( 'Text' ),
						'image'      => self::image( 'Bild' ),
						'image_alt'  => self::text( 'Bild Alt-Text' ),
					),
					'Profil hinzufuegen'
				),
			),
		);
	}

	/**
	 * Shared why Finora layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_why_finora() {
		return array(
			'label'    => 'Why Finora',
			'template' => 'why_finora',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'subtitle'  => self::textarea( 'Untertitel' ),
					'body'      => self::editor( 'Einleitung' ),
					'blurbs'    => self::repeater(
						'Vorteile',
						array(
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Vorteil hinzufuegen'
					),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared how-it-works layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_how_it_works() {
		return array(
			'label'    => 'How It Works',
			'template' => 'how_it_works',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'steps' => self::repeater(
						'Schritte',
						array(
							'icon_text' => self::text( 'Icon/Text' ),
							'title'     => self::text( 'Titel' ),
							'content'   => self::editor( 'Text' ),
						),
						'Schritt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared testimonials layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_testimonials() {
		return array(
			'label'    => 'Testimonials',
			'template' => 'testimonials',
			'fields'   => array(
				'title'    => self::heading_html( 'Titel' ),
				'subtitle' => self::textarea( 'Untertitel' ),
				'items'    => self::repeater(
					'Testimonials',
					array(
						'quote'          => self::editor( 'Zitat' ),
						'toggle_enabled' => self::checkbox( 'Mehr/Weniger aktiv', true ),
						'initials'       => self::text( 'Initialen' ),
						'name'           => self::text( 'Name' ),
						'role'           => self::text( 'Rolle' ),
					),
					'Testimonial hinzufuegen'
				),
			),
		);
	}

	/**
	 * Shared FAQ layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_faq() {
		return array(
			'label'    => 'FAQ',
			'template' => 'faq',
			'fields'   => array(
				'title'            => self::heading_html( 'Titel' ),
				'intro'            => self::textarea( 'Einleitung' ),
				'background_image' => self::image( 'Hintergrundbild' ),
				'items'            => self::repeater(
					'Fragen',
					array(
						'question' => self::text( 'Frage' ),
						'answer'   => self::editor( 'Antwort' ),
					),
					'Frage hinzufuegen'
				),
			),
		);
	}

	/**
	 * Shared banner CTA layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_banner_cta() {
		return array(
			'label'    => 'Banner CTA',
			'template' => 'banner_cta',
			'fields'   => array_merge(
				array(
					'title'            => self::heading_html( 'Titel' ),
					'body'             => self::editor( 'Text' ),
					'background_image' => self::image( 'Hintergrundbild' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * About "Bedeutet" layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_about_bedeutet() {
		return array(
			'label'    => 'Was bedeutet Beratung',
			'template' => 'about_bedeutet',
			'fields'   => array_merge(
				array(
					'title'       => self::heading_html( 'Titel' ),
					'left_body'   => self::editor( 'Linke Spalte' ),
					'right_title' => self::text( 'Rechte Spalte Titel' ),
					'right_items' => self::repeater(
						'Rechte Spalte Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared media + text section.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_media_text() {
		return array(
			'label'    => 'Bild/Text',
			'template' => 'media_text',
			'fields'   => array_merge(
				array(
					'title'          => self::heading_html( 'Titel' ),
					'body'           => self::editor( 'Text' ),
					'image'          => self::image( 'Bild' ),
					'image_alt'      => self::text( 'Bild Alt-Text' ),
					'image_position' => self::text( 'Bild Position (left/right)' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared workflow/blurb layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_workflow_blurbs() {
		return array(
			'label'    => 'Workflow',
			'template' => 'workflow_blurbs',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
					'highlight' => self::editor( 'Highlight' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared centered CTA layout.
	 *
	 * @param string $variant Variant key.
	 * @return array<string,mixed>
	 */
	protected static function layout_center_cta( $variant ) {
		return array(
			'label'    => 'CTA',
			'template' => 'center_cta',
			'variant'  => $variant,
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'body'  => self::editor( 'Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared tabs section layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_tabs_section() {
		return array(
			'label'    => 'Tabs',
			'template' => 'tabs_section',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'tabs'      => self::repeater(
						'Tabs',
						array(
							'title'   => self::text( 'Titel' ),
							'intro'   => self::heading_html( 'Einleitung' ),
							'bullets' => self::repeater(
								'Bullet Points',
								array(
									'text' => self::text( 'Text' ),
								),
								'Bullet hinzufuegen'
							),
							'outro'   => self::editor( 'Abschluss' ),
						),
						'Tab hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Retirement audience cards layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_retirement_audience() {
		return array(
			'label'    => 'Zielgruppen',
			'template' => 'retirement_audience',
			'fields'   => array(
				'title'      => self::heading_html( 'Titel' ),
				'jump_links' => self::repeater(
					'Jump Links',
					array(
						'label'     => self::text( 'Label' ),
						'anchor_id' => self::text( 'Anchor-ID' ),
					),
					'Jump-Link hinzufuegen'
				),
				'cards'      => self::repeater(
					'Karten',
					array(
						'variant'         => self::text( 'Variante (highlight/large/small/default)' ),
						'anchor_id'       => self::text( 'Anchor-ID' ),
						'title'           => self::text( 'Titel' ),
						'intro'           => self::editor( 'Einleitung' ),
						'blurbs'          => self::repeater(
							'Blurbs',
							array(
								'title'   => self::text( 'Titel' ),
								'content' => self::editor( 'Text' ),
							),
							'Blurb hinzufuegen'
						),
						'button_label'    => self::text( 'Button Text' ),
						'button_page_key' => self::text( 'Button Zielseite (source_key)' ),
						'button_url'      => self::url( 'Button URL (Fallback/extern)' ),
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Concepts section layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_concepts_section() {
		return array(
			'label'    => 'Konzepte',
			'template' => 'concepts_section',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared blurb section with optional image.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_blurb_image_section() {
		return array(
			'label'    => 'Bild + Blurbs',
			'template' => 'blurb_image_section',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared approach tiles layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_approach_tiles() {
		return array(
			'label'    => 'Approach Tiles',
			'template' => 'approach_tiles',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'body'  => self::editor( 'Text' ),
					'tiles' => self::repeater(
						'Tiles',
						array(
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Rueckseite' ),
						),
						'Tile hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared timeline layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_timeline() {
		return array(
			'label'    => 'Timeline',
			'template' => 'timeline',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Einleitung' ),
				'items' => self::repeater(
					'Timeline Punkte',
					array(
						'number'  => self::text( 'Nummer' ),
						'icon'    => self::text( 'Icon CSS Klasse' ),
						'title'   => self::text( 'Titel' ),
						'body'    => self::editor( 'Text' ),
						'bullets' => self::repeater(
							'Bullet Points',
							array(
								'text' => self::text( 'Text' ),
							),
							'Bullet hinzufuegen'
						),
					),
					'Timeline Punkt hinzufuegen'
				),
			),
		);
	}

	/**
	 * Shared target groups layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_target_groups() {
		return array(
			'label'    => 'Zielgruppen',
			'template' => 'target_groups',
			'fields'   => array_merge(
				array(
					'title'    => self::heading_html( 'Titel' ),
					'subtitle' => self::text( 'Untertitel' ),
					'items'    => self::repeater(
						'Zielgruppen',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Zielgruppe hinzufuegen'
					),
					'summary'  => self::editor( 'Zusammenfassung' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Shared results section.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_results_section() {
		return array(
			'label'    => 'Ergebnisse',
			'template' => 'results_section',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Real estate intro layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_real_estate_intro() {
		return array(
			'label'    => 'Immobilien Intro',
			'template' => 'real_estate_intro',
			'fields'   => array(
				'image'           => self::image( 'Bild' ),
				'image_alt'       => self::text( 'Bild Alt-Text' ),
				'stats'           => self::repeater(
					'Profilwerte',
					array(
						'value' => self::text( 'Wert' ),
						'label' => self::text( 'Label' ),
					),
					'Wert hinzufuegen'
				),
				'goals_title'     => self::heading_html( 'Ziele Titel' ),
				'goals_body'      => self::editor( 'Ziele Text' ),
				'challenge_title' => self::heading_html( 'Herausforderung Titel' ),
				'challenge_body'  => self::editor( 'Herausforderung Text' ),
				'blurbs'          => self::repeater(
					'Probleme',
					array(
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
					),
					'Problem hinzufuegen'
				),
			),
		);
	}

	/**
	 * Calculator layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_calculator() {
		return array(
			'label'    => 'Berechnung',
			'template' => 'calculator',
			'fields'   => array(
				'title'    => self::heading_html( 'Titel' ),
				'subtitle' => self::editor( 'Untertitel' ),
				'cards'    => self::repeater(
					'Karten',
					array(
						'title'    => self::text( 'Titel' ),
						'icon'     => self::text( 'Icon CSS Klasse' ),
						'featured' => self::checkbox( 'Featured' ),
						'rows'     => self::repeater(
							'Zeilen',
							array(
								'label'    => self::text( 'Label' ),
								'value'    => self::text( 'Wert' ),
								'modifier' => self::text( 'Modifier (plus/minus/subtotal/highlight/hero/accent)' ),
							),
							'Zeile hinzufuegen'
						),
					),
					'Karte hinzufuegen'
				),
				'kpis'     => self::repeater(
					'KPIs',
					array(
						'value'  => self::text( 'Wert' ),
						'label'  => self::text( 'Label' ),
						'accent' => self::checkbox( 'Accent' ),
					),
					'KPI hinzufuegen'
				),
			),
		);
	}

	/**
	 * Case highlight layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_case_highlight() {
		return array(
			'label'    => 'Fallbeispiel',
			'template' => 'case_highlight',
			'fields'   => array(
				'image'     => self::image( 'Bild' ),
				'image_alt' => self::text( 'Bild Alt-Text' ),
				'title'     => self::heading_html( 'Titel' ),
				'body'      => self::editor( 'Text' ),
				'quote'     => self::editor( 'Zitat' ),
			),
		);
	}

	/**
	 * Dark CTA layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_dark_cta() {
		return array(
			'label'    => 'Dark CTA',
			'template' => 'dark_cta',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'body'  => self::editor( 'Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Responsibility section layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_responsibility() {
		return array(
			'label'    => 'Verantwortung',
			'template' => 'responsibility',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'lead'      => self::editor( 'Lead' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
					'note'      => self::editor( 'Hinweis' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * New phase section layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_new_phase() {
		return array(
			'label'    => 'Neue Vermoegensphase',
			'template' => 'new_phase',
			'fields'   => array_merge(
				array(
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'title'     => self::heading_html( 'Titel' ),
					'body'      => self::editor( 'Text' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Outcomes flipbox layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_outcomes() {
		return array(
			'label'    => 'Outcomes',
			'template' => 'outcomes',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Einleitung' ),
				'body'  => self::editor( 'Text' ),
				'items' => self::repeater(
					'Flipboxen',
					array(
						'icon'    => self::text( 'Icon CSS Klasse' ),
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
					),
					'Flipbox hinzufuegen'
				),
			),
		);
	}

	/**
	 * Target groups with image layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_target_groups_image() {
		return array(
			'label'    => 'Zielgruppe mit Bild',
			'template' => 'target_groups_image',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'intro'     => self::editor( 'Einleitung' ),
					'items'     => self::repeater(
						'Punkte',
						array(
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Audience cards layout.
	 *
	 * @param string $variant Variant.
	 * @return array<string,mixed>
	 */
	protected static function layout_audience_cards( $variant ) {
		return array(
			'label'    => 'Audience Cards',
			'template' => 'audience_cards',
			'variant'  => $variant,
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Karten',
					array(
						'title'           => self::text( 'Titel' ),
						'content'         => self::editor( 'Text' ),
						'button_label'    => self::text( 'Button Text' ),
						'button_page_key' => self::text( 'Button Zielseite (source_key)' ),
						'button_url'      => self::url( 'Button URL (Fallback/extern)' ),
						'is_empty'        => self::checkbox( 'Leere Platzhalter-Karte' ),
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Media detail section with blurbs.
	 *
	 * @param string $variant Variant.
	 * @return array<string,mixed>
	 */
	protected static function layout_media_blurbs( $variant ) {
		return array(
			'label'    => 'Media Blurbs',
			'template' => 'media_blurbs',
			'variant'  => $variant,
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'subtitle'  => self::textarea( 'Untertitel' ),
					'body'      => self::editor( 'Text' ),
					'blurbs'    => self::repeater(
						'Blurbs',
						array(
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Blurb hinzufuegen'
					),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Philosophy invest detail layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_invest_detail() {
		return array(
			'label'    => 'Investment Detail',
			'template' => 'invest_detail',
			'fields'   => array_merge(
				array(
					'title'           => self::heading_html( 'Titel' ),
					'subtitle'        => self::textarea( 'Untertitel' ),
					'body'            => self::editor( 'Text' ),
					'image'           => self::image( 'Bild' ),
					'image_alt'       => self::text( 'Bild Alt-Text' ),
					'explainer_title' => self::heading_html( 'Megatrends Titel' ),
					'explainer_body'  => self::editor( 'Megatrends Text' ),
					'explainer_sub'   => self::text( 'Megatrends Subline' ),
					'trends'          => self::repeater(
						'Megatrends',
						array(
							'icon'    => self::text( 'Icon CSS Klasse' ),
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Megatrend hinzufuegen'
					),
					'outro'           => self::editor( 'Outro' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Philosophy tax detail layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_tax_detail() {
		return array(
			'label'    => 'Tax Detail',
			'template' => 'tax_detail',
			'fields'   => array_merge(
				array(
					'title'       => self::heading_html( 'Titel' ),
					'subtitle'    => self::textarea( 'Untertitel' ),
					'body'        => self::editor( 'Text' ),
					'how_title'   => self::text( 'Wie das funktioniert Titel' ),
					'how_body'    => self::editor( 'Wie das funktioniert Text' ),
					'image'       => self::image( 'Bild' ),
					'image_alt'   => self::text( 'Bild Alt-Text' ),
					'steps_title' => self::heading_html( 'Schritte Titel' ),
					'steps'       => self::repeater(
						'Schritte',
						array(
							'icon'    => self::text( 'Icon/Text' ),
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Schritt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Contact main layout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_contact_main() {
		return array(
			'label'    => 'Kontakt Hauptbereich',
			'template' => 'contact_main',
			'fields'   => array(
				'title'         => self::heading_html( 'Titel' ),
				'intro'         => self::editor( 'Einleitung' ),
				'submit_label'  => self::text( 'Fallback Button Text' ),
				'privacy_label' => self::editor( 'Datenschutz Hinweis' ),
				'privacy_page'  => self::text( 'Datenschutz Zielseite (source_key)' ),
				'info_cards'    => self::repeater(
					'Info Karten',
					array(
						'title'   => self::text( 'Titel' ),
						'type'    => self::text( 'Typ (address/phone/email/link)' ),
						'value'   => self::editor( 'Inhalt' ),
						'href'    => self::url( 'Link URL' ),
					),
					'Info Karte hinzufuegen'
				),
			),
		);
	}

	/* ═══════════════════════════════════════════════════════════════
	 * ACM AIR CHARTER — Layout Helpers
	 * ═══════════════════════════════════════════════════════════════ */

	/**
	 * ACM hero with video background.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_hero_video() {
		return array(
			'label'    => 'Hero Video',
			'template' => 'acm_hero_video',
			'fields'   => array(
				'title'          => self::heading_html( 'Titel' ),
				'subtitle'       => self::textarea( 'Untertitel' ),
				'video'          => self::video( 'Hintergrund-Video (Mediathek)' ),
				'poster'         => self::image( 'Poster-Bild (Fallback)' ),
				'poster_alt'     => self::text( 'Poster Alt-Text' ),
			),
		);
	}

	/**
	 * ACM home hero statement (quote under video).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_hero_statement() {
		return array(
			'label'    => 'Hero Statement',
			'template' => 'acm_hero_statement',
			'fields'   => array(
				'quote' => self::editor( 'Zitat' ),
			),
		);
	}

	/**
	 * ACM full-width image promo with overlay CTA (home).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_fullwidth_promo() {
		return array(
			'label'    => 'Fullwidth Promo',
			'template' => 'acm_fullwidth_promo',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'image'     => self::image( 'Hintergrundbild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM KPI / trust figures strip (home).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_kpi_strip() {
		return array(
			'label'    => 'KPI Streifen',
			'template' => 'acm_kpi_strip',
			'fields'   => array(
				'items' => self::repeater(
					'Kennzahlen',
					array(
						'figure'  => self::text( 'Zahl / Kopfzeile' ),
						'caption' => self::text( 'Beschreibung' ),
					),
					'KPI hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM home final CTA band with multiple buttons.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_home_final_cta() {
		return array(
			'label'    => 'Final CTA Band',
			'template' => 'acm_home_final_cta',
			'fields'   => array(
				'title'   => self::heading_html( 'Titel' ),
				'body'    => self::editor( 'Text' ),
				'actions' => self::repeater(
					'Buttons',
					array(
						'label'    => self::text( 'Button Text' ),
						'page_key' => self::text( 'Zielseite (source_key)' ),
						'url'      => self::url( 'URL (Fallback/extern)' ),
					),
					'Button hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM centered intro (label, title, rich body) — e.g. That's ACM company block.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_intro_centered() {
		return array(
			'label'    => 'Intro zentriert',
			'template' => 'acm_intro_centered',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile' ),
				'title' => self::heading_html( 'Titel' ),
				'body'  => self::editor( 'Text' ),
			),
		);
	}

	/**
	 * ACM certifications / license grid.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_certifications_grid() {
		return array(
			'label'    => 'Zertifizierungen Grid',
			'template' => 'acm_certifications_grid',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile' ),
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Einleitung (optional, unter Titel)' ),
				'items' => self::repeater(
					'Eintraege',
					array(
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
						'icon'    => self::svg_code( 'Icon (SVG Markup)' ),
					),
					'Eintrag hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM split: image column + text with icon feature list (Charter operative block).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_split_icon_features() {
		return array(
			'label'    => 'Split Icon-Features',
			'template' => 'acm_split_icon_features',
			'fields'   => array(
				'image_left'  => self::checkbox( 'Bild links (Desktop)', true ),
				'image_right' => self::checkbox( 'Bild rechts (Desktop, prioritaet vor Bild links)' ),
				'image'       => self::image( 'Bild' ),
				'image_alt'   => self::text( 'Bild Alt-Text' ),
				'label'       => self::text( 'Ueberzeile' ),
				'title'       => self::heading_html( 'Titel' ),
				'intro'       => self::editor( 'Einleitung' ),
				'items'       => self::repeater(
					'Merkmale',
					array(
						'icon'    => self::svg_code( 'Icon (SVG Markup)' ),
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
					),
					'Merkmal hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM multi-row image/text splits (capabilities-style).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_split_rows() {
		return array(
			'label'    => 'Split-Zeilen (Bild/Text)',
			'template' => 'acm_split_rows',
			'fields'   => array(
				'intro_label' => self::text( 'Kopf-Ueberzeile' ),
				'intro_title' => self::heading_html( 'Kopftitel' ),
				'rows'        => self::repeater(
					'Zeilen',
					array(
						'label'       => self::text( 'Zeilen-Ueberzeile' ),
						'title'       => self::heading_html( 'Zeilentitel' ),
						'body'        => self::editor( 'Text' ),
						'image'       => self::image( 'Bild' ),
						'image_alt'   => self::text( 'Bild Alt-Text' ),
						'image_right' => self::checkbox( 'Bild rechts (Desktop)' ),
					),
					'Zeile hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM standard hero (image background).
	 *
	 * @param bool $with_button Include CTA fields.
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_hero( $with_button = true ) {
		$fields = array(
			'eyebrow'      => self::text( 'Ueberzeile (ueber Titel)' ),
			'title'        => self::heading_html( 'Titel' ),
			'subtitle'     => self::textarea( 'Untertitel' ),
			'image'        => self::image( 'Hintergrundbild' ),
			'image_alt'    => self::text( 'Bild Alt-Text' ),
			'proof_badges' => self::repeater(
				'Proof-Leiste (optional)',
				array(
					'icon' => self::svg_code( 'Icon (SVG)' ),
					'text' => self::text( 'Text' ),
				),
				'Badge hinzufuegen'
			),
		);

		if ( $with_button ) {
			$fields = array_merge( $fields, self::button_fields( 'cta' ) );
		}

		return array(
			'label'    => 'Hero',
			'template' => 'acm_hero',
			'fields'   => $fields,
		);
	}

	/**
	 * ACM services card grid (Charter, Mgmt, Maintenance).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_services_grid() {
		return array(
			'label'    => 'Leistungen Grid',
			'template' => 'acm_services_grid',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Service-Karten',
					array(
						'title'       => self::text( 'Titel' ),
						'description' => self::editor( 'Beschreibung' ),
						'image'       => self::image( 'Bild' ),
						'image_alt'   => self::text( 'Bild Alt-Text' ),
						'cta_label'   => self::text( 'CTA-Label' ),
						'page_key'    => self::text( 'Zielseite (source_key)' ),
						'url'         => self::url( 'URL (Fallback/extern)' ),
					),
					'Service hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM about teaser section on home page.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_about_teaser() {
		return array(
			'label'    => 'About Teaser',
			'template' => 'acm_about_teaser',
			'fields'   => array_merge(
				array(
					'label'       => self::text( 'Ueberzeile' ),
					'title'       => self::heading_html( 'Titel' ),
					'body'        => self::editor( 'Text' ),
					'image'       => self::image( 'Bild' ),
					'image_alt'   => self::text( 'Bild Alt-Text' ),
					'image_right' => self::checkbox( 'Bild rechts (Desktop)' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM home fleet promo banner.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_charter_hero() {
		return array(
			'label'    => 'Home Flotten-Banner',
			'template' => 'acm_charter_hero',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM generic content block (image + text + optional CTA).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_content_block() {
		return array(
			'label'    => 'Content Block',
			'template' => 'acm_content_block',
			'fields'   => array_merge(
				array(
					'label'     => self::text( 'Section Label' ),
					'title'     => self::heading_html( 'Titel' ),
					'body'      => self::editor( 'Text' ),
					'image'     => self::image( 'Bild' ),
					'image_alt' => self::text( 'Bild Alt-Text' ),
					'items'     => self::repeater(
						'Punkte / Features',
						array(
							'icon'    => self::text( 'Icon (SVG/class)' ),
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Punkt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM news teaser section (home page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_news_teaser() {
		return array(
			'label'    => 'News Teaser',
			'template' => 'acm_news_teaser',
			'fields'   => array_merge(
				array(
					'title'     => self::heading_html( 'Titel' ),
					'max_posts' => self::text( 'Max. angezeigte Beitraege' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM horizontal timeline (That's ACM page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_horizontal_timeline() {
		return array(
			'label'    => 'Horizontale Timeline',
			'template' => 'acm_horizontal_timeline',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile' ),
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Einleitung' ),
				'items' => self::repeater(
					'Timeline Eintraege',
					array(
						'year'    => self::text( 'Jahr' ),
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
						'image'   => self::image( 'Bild' ),
					),
					'Eintrag hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM contact CTA (shared across pages).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_contact_cta() {
		return array(
			'label'    => 'Kontakt CTA',
			'template' => 'acm_contact_cta',
			'fields'   => array_merge(
				array(
					'title'              => self::heading_html( 'Titel' ),
					'body'               => self::editor( 'Text' ),
					'secondary_label'    => self::text( 'Sekundaerer Button Text' ),
					'secondary_page_key' => self::text( 'Sekundaere Zielseite (source_key)' ),
					'secondary_url'      => self::url( 'Sekundaere URL (Fallback/extern)' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM fleet overview (charter page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_fleet_overview() {
		return array(
			'label'    => 'Flottenuebersicht',
			'template' => 'acm_fleet_overview',
			'fields'   => array(
				'label'    => self::text( 'Ueberzeile' ),
				'title'    => self::heading_html( 'Titel' ),
				'subtitle' => self::textarea( 'Untertitel' ),
				'aircraft' => self::repeater(
					'Flugzeuge',
					array(
						'title'     => self::text( 'Flugzeugname' ),
						'subtitle'  => self::text( 'Kategorie' ),
						'description' => self::editor( 'Beschreibung' ),
						'specs'     => self::editor( 'Technische Daten' ),
						'image'     => self::image( 'Bild' ),
						'image_alt' => self::text( 'Bild Alt-Text' ),
						'cta_label' => self::text( 'CTA-Label' ),
						'page_key'  => self::text( 'Detail-Seite (source_key)' ),
						'url'       => self::url( 'URL (Fallback/extern)' ),
					),
					'Flugzeug hinzufuegen'
				),
				'compare_label'  => self::text( 'Vergleich Ueberzeile' ),
				'compare_title'  => self::heading_html( 'Vergleichstitel' ),
				'compare_tabs'   => self::repeater(
					'Vergleich Tabs',
					array(
						'variant' => self::text( 'Key / Variant' ),
						'label'   => self::text( 'Label' ),
					),
					'Tab hinzufuegen'
				),
				'compare_panels' => self::repeater(
					'Vergleich Panels',
					array(
						'variant'      => self::text( 'Key / Variant' ),
						'rows'         => self::repeater(
							'Zeilen',
							array(
								'aircraft' => self::text( 'Flugzeug' ),
								'value'    => self::text( 'Wert' ),
							),
							'Zeile hinzufuegen'
						),
						'scale_labels' => self::repeater(
							'Skalenlabels',
							array(
								'label' => self::text( 'Label' ),
							),
							'Skalenlabel hinzufuegen'
						),
					),
					'Panel hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM aircraft specs page (global-7500, -6000, -xrs).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_aircraft_specs() {
		return array(
			'label'    => 'Aircraft Specs',
			'template' => 'acm_aircraft_specs',
			'fields'   => array(
				'title'          => self::heading_html( 'Flugzeugname' ),
				'subtitle'       => self::text( 'Kategorie / Untertitel' ),
				'description'    => self::editor( 'Beschreibung' ),
				'spec_groups'    => self::repeater(
					'Technische Daten (Gruppen)',
					array(
						'group_title' => self::text( 'Gruppentitel' ),
						'rows'        => self::repeater(
							'Zeilen',
							array(
								'label' => self::text( 'Label' ),
								'value' => self::text( 'Wert' ),
							),
							'Zeile hinzufuegen'
						),
					),
					'Gruppe hinzufuegen'
				),
				'specs'          => self::repeater(
					'Technische Daten (flach, Legacy)',
					array(
						'label' => self::text( 'Label' ),
						'value' => self::text( 'Wert' ),
					),
					'Datenzeile hinzufuegen'
				),
				'footnote'       => self::textarea( 'Fussnote' ),
				'gallery'        => self::repeater(
					'Bildergalerie',
					array(
						'image'     => self::image( 'Bild' ),
						'image_alt' => self::text( 'Alt-Text' ),
						'caption'   => self::text( 'Bildunterschrift' ),
					),
					'Bild hinzufuegen'
				),
				'fleet_links'    => self::repeater(
					'Weitere Flugzeuge',
					array(
						'title'     => self::text( 'Flugzeugname' ),
						'image'     => self::image( 'Bild' ),
						'image_alt' => self::text( 'Bild Alt-Text' ),
						'page_key'  => self::text( 'Zielseite (source_key)' ),
						'url'       => self::url( 'URL (Fallback/extern)' ),
					),
					'Flugzeug hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM numbered zone cards (Global aircraft cabin zones).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_zone_cards() {
		return array(
			'label'    => 'Zonen-Karten',
			'template' => 'acm_zone_cards',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile' ),
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Zonen',
					array(
						'number'  => self::text( 'Nummer' ),
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
					),
					'Zone hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM full-width image carousel (aircraft gallery).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_image_carousel() {
		return array(
			'label'    => 'Bild-Karussell',
			'template' => 'acm_image_carousel',
			'fields'   => array(
				'label'  => self::text( 'Ueberzeile' ),
				'title'  => self::heading_html( 'Titel' ),
				'slides' => self::repeater(
					'Slides',
					array(
						'image'     => self::image( 'Bild' ),
						'image_alt' => self::text( 'Alt-Text' ),
					),
					'Slide hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM single large figure with optional footnote (floorplan).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_featured_figure() {
		return array(
			'label'    => 'Grossbild mit Fussnote',
			'template' => 'acm_featured_figure',
			'fields'   => array(
				'label'     => self::text( 'Ueberzeile' ),
				'title'     => self::heading_html( 'Titel' ),
				'image'     => self::image( 'Bild' ),
				'image_alt' => self::text( 'Alt-Text' ),
				'footnote'  => self::textarea( 'Hinweis unter dem Bild' ),
			),
		);
	}

	/**
	 * ACM fleet teaser row (two link cards).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_fleet_teaser() {
		return array(
			'label'    => 'Flotten-Teaser',
			'template' => 'acm_fleet_teaser',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile' ),
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Flugzeuge',
					array(
						'subtitle'    => self::text( 'Kategorie / Ueberzeile' ),
						'title'       => self::text( 'Flugzeugname' ),
						'description' => self::editor( 'Beschreibung' ),
						'image'       => self::image( 'Bild' ),
						'image_alt'   => self::text( 'Bild Alt-Text' ),
						'cta_label'   => self::text( 'CTA-Label' ),
						'page_key'    => self::text( 'Zielseite (source_key)' ),
						'url'         => self::url( 'URL (Fallback/extern)' ),
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM Betriebsmodelle (Aircraft Management page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_betriebsmodelle() {
		return array(
			'label'    => 'Betriebsmodelle',
			'template' => 'acm_betriebsmodelle',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile (Kopf)' ),
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Einleitung (optional, unter dem Kopf)' ),
				'items' => self::repeater(
					'Modelle',
					array_merge(
						array(
							'kicker'      => self::text( 'Kurzlabel (z.B. AOC / NCC)' ),
							'title'       => self::text( 'Modell-Name' ),
							'description' => self::editor( 'Beschreibung' ),
							'features'    => self::repeater(
								'Features',
								array(
									'text' => self::text( 'Feature' ),
								),
								'Feature hinzufuegen'
							),
						),
						self::button_fields( 'cta' )
					),
					'Modell hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM process split (left intro + CTA, right numbered steps).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_process_split() {
		return array(
			'label'    => 'Prozess Split',
			'template' => 'acm_process_split',
			'fields'   => array_merge(
				array(
					'label' => self::text( 'Ueberzeile' ),
					'title' => self::heading_html( 'Titel' ),
					'intro' => self::editor( 'Einleitung' ),
					'steps' => self::repeater(
						'Schritte',
						array(
							'step'    => self::text( 'Nummer / Code' ),
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Schritt hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM AOG callout section.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_aog_callout() {
		return array(
			'label'    => 'AOG Callout',
			'template' => 'acm_aog_callout',
			'fields'   => array_merge(
				array(
					'label'      => self::text( 'Ueberzeile' ),
					'title'      => self::heading_html( 'Titel' ),
					'body'       => self::editor( 'Einleitung (Fliesstext)' ),
					'highlights' => self::repeater(
						'Highlights',
						array(
							'title'   => self::text( 'Titel' ),
							'content' => self::editor( 'Text' ),
						),
						'Highlight hinzufuegen'
					),
					'phone'      => self::text( 'AOG Telefonnummer' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM Stellenangebote (Karriere page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_stellen() {
		return array(
			'label'    => 'Stellenangebote',
			'template' => 'acm_stellen',
			'fields'   => array_merge(
				array(
					'label' => self::text( 'Ueberzeile' ),
					'title' => self::heading_html( 'Titel' ),
					'intro' => self::editor( 'Einleitung (optional unter Kopf)' ),
					'items' => self::repeater(
						'Stellen',
						array_merge(
							array(
								'title'        => self::text( 'Stellentitel' ),
								'department'   => self::text( 'Abteilung' ),
								'location'     => self::text( 'Standort' ),
								'type'         => self::text( 'Beschaeftigungsart' ),
								'tasks'        => self::editor( 'Aufgaben (Spalte)' ),
								'requirements' => self::editor( 'Anforderungen (Spalte)' ),
								'description'  => self::editor( 'Beschreibung (Legacy)' ),
								'pdf_label'    => self::text( 'PDF-Link Text' ),
								'pdf_url'      => self::file( 'PDF-Datei', 'application/pdf' ),
							),
							self::button_fields( 'apply' )
						),
						'Stelle hinzufuegen',
						array( 'top_add_bar' => true )
					),
					'footer_text' => self::editor( 'Text Initiativbewerbung' ),
				),
				self::button_fields( 'footer' )
			),
		);
	}

	/**
	 * ACM work area cards (Karriere Arbeitsbereiche).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_area_cards() {
		return array(
			'label'    => 'Arbeitsbereiche Karten',
			'template' => 'acm_area_cards',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile (Kopf)' ),
				'title' => self::heading_html( 'Titel' ),
				'items' => self::repeater(
					'Bereiche',
					array_merge(
						array(
							'icon'        => self::svg_code( 'Icon (SVG)' ),
							'title'       => self::text( 'Titel' ),
							'description' => self::editor( 'Text' ),
						),
						self::button_fields( 'link' )
					),
					'Bereich hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM split: image + text with h4 highlight blocks (Karriere Haltung).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_split_highlights() {
		return array(
			'label'    => 'Split Highlights',
			'template' => 'acm_split_highlights',
			'fields'   => array(
				'image'      => self::image( 'Bild' ),
				'image_alt'  => self::text( 'Bild Alt-Text' ),
				'label'      => self::text( 'Ueberzeile' ),
				'title'      => self::heading_html( 'Titel' ),
				'intro'      => self::editor( 'Einleitung' ),
				'highlights' => self::repeater(
					'Highlights',
					array(
						'title'   => self::text( 'Titel' ),
						'content' => self::editor( 'Text' ),
					),
					'Highlight hinzufuegen'
				),
			),
		);
	}

	/**
	 * Kontakt: Command-Panel (Schnellzugriff-Karten).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_contact_command_grid() {
		return array(
			'label'    => 'Bereiche Schnellzugriff',
			'template' => 'acm_contact_command_grid',
			'fields'   => array(
				'label' => self::text( 'Ueberzeile (Kopf)' ),
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::textarea( 'Kurztext unter Titel' ),
				'items' => self::repeater(
					'Karten',
					array_merge(
						array(
							'icon'        => self::svg_code( 'Icon (SVG)' ),
							'title'       => self::text( 'Titel' ),
							'subtitle'    => self::text( 'Untertitel' ),
						),
						self::button_fields( 'link' )
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Kontakt: eine Abteilungs-Sektion (Profilkarten oder Zentrale mit Karte).
	 *
	 * @param string $admin_label Admin-Tab-Label.
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_contact_dept_section_layout( $admin_label ) {
		return array(
			'label'    => $admin_label,
			'template' => 'acm_contact_dept_section',
			'fields'   => array(
				'section_id'    => self::text( 'HTML-ID (Anker)' ),
				'section_label' => self::text( 'Ueberzeile (optional)' ),
				'title'         => self::heading_html( 'Titel' ),
				'intro'         => self::editor( 'Einleitung (unter Kopf)' ),
				'left_column'   => self::editor( 'Linke Spalte (nur Zentrale: Adresse, Kontaktzeilen, Hinweise)' ),
				'map_embed_url' => self::url( 'Karten-Embed-URL (iframe src)' ),
				'profiles'      => self::repeater(
					'Kontaktpersonen',
					array(
						'name'      => self::text( 'Name' ),
						'role'      => self::text( 'Position' ),
						'phone'     => self::text( 'Telefon (Anzeige)' ),
						'phone_url' => self::url( 'Telefon-Link (tel:)' ),
						'email'     => self::text( 'E-Mail (Anzeige)' ),
						'email_url' => self::url( 'E-Mail-Link (mailto:)' ),
						'image'     => self::image( 'Foto' ),
					),
					'Person hinzufuegen'
				),
				'footer_note'   => self::editor( 'Fusstext (optional, z.B. Mailto-Zeile)' ),
			),
		);
	}

	/**
	 * Kontakt: olivfarbenes Abschlussband „Allgemeine Anfrage“.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_contact_inquiry_band() {
		return array(
			'label'    => 'Allgemeine Anfrage',
			'template' => 'acm_contact_inquiry_band',
			'fields'   => array_merge(
				array(
					'section_label' => self::text( 'Ueberzeile' ),
					'title'         => self::heading_html( 'Titel' ),
					'body'          => self::editor( 'Fliesstext' ),
					'phone'         => self::text( 'Telefon (Anzeige)' ),
					'phone_url'     => self::url( 'Telefon-Link (tel:)' ),
					'email'         => self::text( 'E-Mail (Anzeige)' ),
					'email_url'     => self::url( 'E-Mail-Link (mailto:)' ),
					'address'       => self::textarea( 'Adressblock (plain)' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * ACM News: Kategorie-Filterleiste (eigene Sektion vor dem Grid).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_news_filter_bar() {
		return array(
			'label'    => 'News Filter',
			'template' => 'acm_news_filter_bar',
			'fields'   => array(
				'filters' => self::repeater(
					'Filter-Buttons',
					array(
						'label'      => self::text( 'Button-Text' ),
						'slug'       => self::text( 'data-filter (Slug)' ),
						'is_default' => self::checkbox( 'Standard aktiv (Klasse active)' ),
					),
					'Filter hinzufuegen'
				),
			),
		);
	}

	/**
	 * ACM news grid (news archive page).
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_news_grid() {
		return array(
			'label'    => 'News Grid',
			'template' => 'acm_news_grid',
			'fields'   => array(
				'title' => self::heading_html( 'Titel (optional, aktuell ohne H2 im Shell)' ),
			),
		);
	}

	/**
	 * ACM news archive section.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_acm_news_archive() {
		return array(
			'label'    => 'News Archiv',
			'template' => 'acm_news_archive',
			'fields'   => array(
				'label'          => self::text( 'Ueberzeile (Archiv)' ),
				'title'          => self::heading_html( 'Titel' ),
				'intro'          => self::editor( 'Einleitung unter Titel' ),
				'posts_per_page' => self::text( 'Beitraege pro Seite' ),
				'load_more_text' => self::text( 'Mehr laden Button Text' ),
			),
		);
	}

	/**
	 * Shared Ludwig root fields.
	 *
	 * @return array<string,mixed>
	 */
	protected static function ludwig_root_fields() {
		return array(
			'document_title'   => self::text( 'Document Title' ),
			'meta_description' => self::textarea( 'Meta Description' ),
		);
	}

	/**
	 * Ludwig hero.
	 *
	 * @param string $label Admin label.
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_hero( $label ) {
		return array(
			'label'    => $label,
			'template' => 'ludwig_hero',
			'fields'   => array_merge(
				array(
					'eyebrow'        => self::text( 'Eyebrow / Badge' ),
					'title'          => self::heading_html( 'Titel' ),
					'subtitle'       => self::editor( 'Untertitel' ),
					'image'          => self::image( 'Bild' ),
					'image_alt'      => self::text( 'Bild Alt-Text' ),
					'image_position' => self::text( 'Bildposition (object-position)' ),
				),
				self::button_fields( 'primary' ),
				self::button_fields( 'secondary' )
			),
		);
	}

	/**
	 * Ludwig trust strip.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_trust_strip() {
		return array(
			'label'    => 'Trust Strip',
			'template' => 'ludwig_trust_strip',
			'fields'   => array(
				'items' => self::repeater(
					'Trust Items',
					array(
						'label' => self::text( 'Label' ),
					),
					'Item hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig problem cards.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_problem_cards() {
		return array(
			'label'    => 'Problem Cards',
			'template' => 'ludwig_problem_cards',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro / Abschlusszeile' ),
				'items' => self::repeater(
					'Karten',
					array(
						'title' => self::text( 'Titel' ),
						'body'  => self::textarea( 'Text' ),
					),
					'Karte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig split story.
	 *
	 * @param string $label Admin label.
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_split_story( $label ) {
		return array(
			'label'    => $label,
			'template' => 'ludwig_split_story',
			'fields'   => array_merge(
				array(
					'title'          => self::heading_html( 'Titel' ),
					'intro'          => self::editor( 'Intro' ),
					'body'           => self::editor( 'Text / Inhalt' ),
					'highlight'      => self::editor( 'Highlight (Box/Zitat)' ),
					'image'          => self::image( 'Bild' ),
					'image_alt'      => self::text( 'Bild Alt-Text' ),
					'image_position' => self::text( 'Bildposition (object-position)' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Ludwig audience tabs.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_audience_tabs() {
		return array(
			'label'    => 'Audience Tabs',
			'template' => 'ludwig_audience_tabs',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'tabs'  => self::repeater(
					'Tabs',
					array_merge(
						array(
							'tab_label'      => self::text( 'Tab Label' ),
							'title'          => self::text( 'Panel Titel' ),
							'body'           => self::editor( 'Panel Text' ),
							'image'          => self::image( 'Panel Bild' ),
							'image_alt'      => self::text( 'Panel Bild Alt-Text' ),
							'image_position' => self::text( 'Bildposition (object-position)' ),
						),
						self::button_fields( 'cta' )
					),
					'Tab hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig pillars with optional CTA.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_pillars_cta() {
		return array(
			'label'    => 'Pillars',
			'template' => 'ludwig_pillars_cta',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'intro' => self::editor( 'Intro' ),
					'items' => self::repeater(
						'Saeulen',
						array(
							'title' => self::text( 'Titel' ),
							'body'  => self::editor( 'Text' ),
						),
						'Saeule hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Ludwig generic credential grid.
	 *
	 * @param string $label Admin label.
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_credential_grid( $label ) {
		return array(
			'label'    => $label,
			'template' => 'ludwig_credential_grid',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'items' => self::repeater(
					'Cards',
					array(
						'title' => self::text( 'Titel' ),
						'body'  => self::textarea( 'Text' ),
					),
					'Card hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig testimonials.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_testimonials() {
		return array(
			'label'    => 'Testimonials',
			'template' => 'ludwig_testimonials',
			'fields'   => array(
				'title'         => self::heading_html( 'Titel' ),
				'intro'         => self::editor( 'Intro' ),
				'summary_value' => self::text( 'Summary Wert' ),
				'summary_label' => self::text( 'Summary Label' ),
				'items'         => self::repeater(
					'Testimonials',
					array(
						'name'       => self::text( 'Name' ),
						'role'       => self::text( 'Rolle' ),
						'text'       => self::textarea( 'Zitat' ),
						'avatar'     => self::image( 'Avatar' ),
						'avatar_alt' => self::text( 'Avatar Alt-Text' ),
					),
					'Testimonial hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig center CTA.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_center_cta() {
		return array(
			'label'    => 'Center CTA',
			'template' => 'ludwig_center_cta',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'body'  => self::editor( 'Text' ),
				),
				self::button_fields( 'primary' ),
				self::button_fields( 'secondary' )
			),
		);
	}

	/**
	 * Ludwig intro/copy sections.
	 *
	 * @param string $label Admin label.
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_intro_copy( $label ) {
		return array(
			'label'    => $label,
			'template' => 'ludwig_intro_copy',
			'fields'   => array_merge(
				array(
					'title'         => self::heading_html( 'Titel' ),
					'intro'         => self::editor( 'Intro' ),
					'body'          => self::editor( 'Body' ),
					'card_title'    => self::text( 'Card Titel' ),
					'card_body'     => self::editor( 'Card Text' ),
					'card_footer'   => self::editor( 'Card Footer' ),
					'info_items'    => self::repeater(
						'Info Items',
						array(
							'title' => self::text( 'Titel' ),
							'body'  => self::editor( 'Text' ),
						),
						'Info Item hinzufuegen'
					),
					'closing_note'  => self::editor( 'Abschlussnotiz' ),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Ludwig timeline.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_timeline() {
		return array(
			'label'    => 'Timeline',
			'template' => 'ludwig_timeline',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'steps' => self::repeater(
					'Steps',
					array(
						'step_label' => self::text( 'Schritt-Nummer / Label' ),
						'title'      => self::text( 'Titel' ),
						'body'       => self::textarea( 'Text' ),
					),
					'Schritt hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig comparison table.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_comparison_table() {
		return array(
			'label'    => 'Comparison Table',
			'template' => 'ludwig_comparison_table',
			'fields'   => array(
				'title'              => self::heading_html( 'Titel' ),
				'left_column_label'  => self::text( 'Linke Spalte' ),
				'right_column_label' => self::text( 'Rechte Spalte' ),
				'rows'               => self::repeater(
					'Zeilen',
					array(
						'left_text'  => self::textarea( 'Linker Text' ),
						'right_text' => self::textarea( 'Rechter Text' ),
					),
					'Zeile hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig quote callout.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_quote_callout() {
		return array(
			'label'    => 'Quote Callout',
			'template' => 'ludwig_quote_callout',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'body'  => self::editor( 'Text' ),
				'quote' => self::textarea( 'Zitat / Pull-Quote' ),
			),
		);
	}

	/**
	 * Ludwig feature grid.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_feature_grid() {
		return array(
			'label'    => 'Feature Grid',
			'template' => 'ludwig_feature_grid',
			'fields'   => array(
				'title'  => self::heading_html( 'Titel' ),
				'intro'  => self::editor( 'Intro' ),
				'items'  => self::repeater(
					'Items',
					array(
						'title' => self::text( 'Titel' ),
						'icon'  => self::svg_code( 'Icon (SVG)' ),
						'body'  => self::editor( 'Text' ),
					),
					'Item hinzufuegen'
				),
				'groups' => self::repeater(
					'Gruppen',
					array(
						'title' => self::text( 'Gruppen-Titel' ),
						'items' => self::repeater(
							'Group Items',
							array(
								'title' => self::text( 'Titel' ),
								'icon'  => self::svg_code( 'Icon (SVG)' ),
								'body'  => self::editor( 'Text' ),
							),
							'Item hinzufuegen'
						),
					),
					'Gruppe hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig checklist split.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_checklist_split() {
		return array(
			'label'    => 'Checklist Split',
			'template' => 'ludwig_checklist_split',
			'fields'   => array_merge(
				array(
					'title'      => self::heading_html( 'Titel' ),
					'intro'      => self::editor( 'Intro' ),
					'left_title' => self::text( 'Linke Ueberschrift' ),
					'right_title'=> self::text( 'Rechte Ueberschrift' ),
					'left_items' => self::repeater(
						'Linke Liste',
						array(
							'text' => self::text( 'Text' ),
							'note' => self::text( 'Hinweis (optional)' ),
						),
						'Item hinzufuegen'
					),
					'right_items' => self::repeater(
						'Rechte Liste',
						array(
							'text' => self::text( 'Text' ),
							'note' => self::text( 'Hinweis (optional)' ),
						),
						'Item hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Ludwig feature checklist CTA.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_feature_checklist_cta() {
		return array(
			'label'    => 'Feature Checklist CTA',
			'template' => 'ludwig_feature_checklist_cta',
			'fields'   => array_merge(
				array(
					'title' => self::heading_html( 'Titel' ),
					'intro' => self::editor( 'Intro' ),
					'items' => self::repeater(
						'Checklist',
						array(
							'text' => self::text( 'Text' ),
							'note' => self::text( 'Hinweis (optional)' ),
						),
						'Item hinzufuegen'
					),
				),
				self::button_fields( 'cta' )
			),
		);
	}

	/**
	 * Ludwig pricing cards.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_pricing_cards() {
		return array(
			'label'    => 'Pricing Cards',
			'template' => 'ludwig_pricing_cards',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'plans' => self::repeater(
					'Plaene',
					array_merge(
						array(
							'badge'     => self::text( 'Badge' ),
							'title'     => self::text( 'Titel' ),
							'subtitle'  => self::text( 'Untertitel' ),
							'amount'    => self::text( 'Preis' ),
							'period'    => self::text( 'Preis-Zeitraum' ),
							'footnote'  => self::text( 'Fussnote' ),
							'featured'  => self::checkbox( 'Featured' ),
							'features'  => self::repeater(
								'Features',
								array(
									'text' => self::text( 'Text' ),
								),
								'Feature hinzufuegen'
							),
						),
						self::button_fields( 'cta' )
					),
					'Plan hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig FAQ.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_faq() {
		return array(
			'label'    => 'FAQ',
			'template' => 'ludwig_faq',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'items' => self::repeater(
					'Fragen',
					array(
						'question' => self::text( 'Frage' ),
						'answer'   => self::editor( 'Antwort' ),
					),
					'Frage hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig case study.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_case_study() {
		return array(
			'label'    => 'Case Study',
			'template' => 'ludwig_case_study',
			'fields'   => array(
				'label'           => self::text( 'Label' ),
				'title'           => self::heading_html( 'Titel' ),
				'situation_title' => self::text( 'Situation Titel' ),
				'situation_body'  => self::editor( 'Situation Text' ),
				'measures_title'  => self::text( 'Massnahmen Titel' ),
				'measure_items'   => self::repeater(
					'Massnahmen',
					array(
						'text' => self::text( 'Text' ),
						'note' => self::text( 'Hinweis (optional)' ),
					),
					'Massnahme hinzufuegen'
				),
				'results_title'   => self::text( 'Ergebnis Titel' ),
				'result_items'    => self::repeater(
					'Ergebnisse',
					array(
						'text' => self::text( 'Text' ),
						'note' => self::text( 'Hinweis (optional)' ),
					),
					'Ergebnis hinzufuegen'
				),
				'closing_note'    => self::editor( 'Abschlussnotiz' ),
			),
		);
	}

	/**
	 * Ludwig contact cards.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_contact_cards() {
		return array(
			'label'    => 'Contact Cards',
			'template' => 'ludwig_contact_cards',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'items' => self::repeater(
					'Kontaktkarten',
					array_merge(
						array(
							'title' => self::text( 'Titel' ),
							'body'  => self::textarea( 'Text' ),
						),
						self::button_fields( 'cta' )
					),
					'Kontaktkarte hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig article cards.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_article_cards() {
		return array(
			'label'    => 'Article Cards',
			'template' => 'ludwig_article_cards',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'items' => self::repeater(
					'Artikel',
					array_merge(
						array(
							'category'  => self::text( 'Kategorie' ),
							'title'     => self::text( 'Titel' ),
							'meta'      => self::text( 'Meta' ),
							'image'     => self::image( 'Bild' ),
							'image_alt' => self::text( 'Bild Alt-Text' ),
						),
						self::button_fields( 'cta' )
					),
					'Artikel hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig customer videos.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_customer_videos() {
		return array(
			'label'    => 'Customer Videos',
			'template' => 'ludwig_customer_videos',
			'fields'   => array(
				'title' => self::heading_html( 'Titel' ),
				'intro' => self::editor( 'Intro' ),
				'items' => self::repeater(
					'Videos',
					array(
						'kicker'     => self::text( 'Kicker' ),
						'title'      => self::text( 'Titel' ),
						'body'       => self::editor( 'Text' ),
						'video'      => self::video( 'Video' ),
						'poster'     => self::image( 'Poster' ),
						'poster_alt' => self::text( 'Poster Alt-Text' ),
					),
					'Video hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig contact form split.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_contact_form_split() {
		return array(
			'label'    => 'Kontaktformular',
			'template' => 'ludwig_contact_form_split',
			'fields'   => array(
				'booking_title' => self::heading_html( 'Booking Titel' ),
				'booking_intro' => self::editor( 'Booking Intro' ),
				'booking_cards' => self::repeater(
					'Booking Cards',
					array_merge(
						array(
							'title' => self::text( 'Titel' ),
							'text'  => self::textarea( 'Text' ),
						),
						self::button_fields( 'primary' ),
						self::button_fields( 'secondary' )
					),
					'Booking Card hinzufuegen'
				),
				'form_title'    => self::heading_html( 'Form Titel' ),
				'form_intro'    => self::editor( 'Form Intro' ),
			),
		);
	}

	/**
	 * Ludwig location map.
	 *
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_location_map() {
		return array(
			'label'    => 'Location Map',
			'template' => 'ludwig_location_map',
			'fields'   => array(
				'title'     => self::heading_html( 'Titel' ),
				'embed_url' => self::url( 'Embed URL' ),
				'info_items' => self::repeater(
					'Info Items',
					array(
						'title' => self::text( 'Titel' ),
						'body'  => self::textarea( 'Text' ),
					),
					'Info Item hinzufuegen'
				),
			),
		);
	}

	/**
	 * Ludwig legal document.
	 *
	 * @param bool $allow_highlight Whether highlight cards should be exposed.
	 * @return array<string,mixed>
	 */
	protected static function layout_ludwig_legal_document( $allow_highlight = false ) {
		$section_fields = array(
			'title' => self::text( 'Abschnittstitel' ),
			'body'  => self::editor( 'Abschnittstext' ),
		);

		if ( $allow_highlight ) {
			$section_fields['is_highlight_card'] = self::checkbox( 'Als Highlight Card rendern' );
		}

		return array(
			'label'    => 'Legal Document',
			'template' => 'ludwig_legal_document',
			'fields'   => array(
				'headline' => self::heading_html( 'Headline' ),
				'intro'    => self::editor( 'Einleitung' ),
				'sections' => self::repeater(
					'Abschnitte',
					$section_fields,
					'Abschnitt hinzufuegen'
				),
			),
		);
	}
}
