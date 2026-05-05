<?php
require_once 'wp-load.php';

$page = get_page_by_path('startseite'); // Homepage
if (!$page) {
    $page = get_post(get_option('page_on_front'));
}

if ($page) {
    echo "Page found: " . $page->post_title . "\n";
    $sections = get_post_meta($page->ID, 'ludwig_home_sections', true);
    if ($sections) {
        // print_r($sections);
        $sections_data = is_string($sections) ? json_decode($sections, true) : $sections;
        foreach($sections_data['sections'] ?? [] as $index => $section) {
             echo "[$index] " . $section['acf_fc_layout'] . "\n";
             if ($section['acf_fc_layout'] === 'split_story') {
                  echo "  Title: " . print_r($section['title'] ?? 'MISSING', true) . "\n";
                  echo "  Intro: " . print_r($section['intro'] ?? 'MISSING', true) . "\n";
                  echo "  Body: " . print_r($section['body'] ?? 'MISSING', true) . "\n";
             }
        }
    } else {
        echo "No ludwig_home_sections meta found.\n";
    }
} else {
    echo "Could not find home page.\n";
}
