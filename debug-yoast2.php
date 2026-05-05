<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

$post_id = leadwerk_theme_get_page_id('ludwig-home-v1', 'de');
if (!$post_id) {
    $pages = get_pages(array('meta_key' => 'leadwerk_source_key', 'number' => 1));
    if (!empty($pages)) {
        $post_id = $pages[0]->ID;
    }
}

if (!$post_id) {
    echo "No post ID found\n";
    exit;
}

echo "Post ID: $post_id\n";
$group = Leadwerk_Content_Schema::get_group_for_post( $post_id );
echo "Field Name: " . $group['field_name'] . "\n";

$value = leadwerk_theme_get_managed_field_value( $group['field_name'], $post_id );
echo "Is array? " . (is_array($value) ? 'yes' : 'no') . "\n";

$content = leadwerk_theme_get_yoast_analysis_content($post_id);
echo "Yoast Content: " . $content . "\n";
