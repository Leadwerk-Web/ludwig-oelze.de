<?php
$value = array(
    array(
        'acf_fc_layout' => 'split_story',
        'title' => '<h2>Ein Ansprechpartner. Für alles.</h2>',
        'intro' => '<p>Ich bin Ludwig...</p>',
        'body' => '<p>Dort, wo besondere Sorgfalt...</p>',
        'image' => 57
    )
);

$texts = array();
array_walk_recursive(
    $value,
    function ( $item, $key ) use ( &$texts ) {
        if ( is_string( $item ) && ! in_array( $key, array( 'acf_fc_layout', 'cta_page_key', 'cta_url', 'image_position', 'body_class' ), true ) ) {
            $texts[] = trim( $item );
        }
    }
);

$content = implode( ' ', array_filter( $texts ) );
echo "Extracted:\n" . $content . "\n";
