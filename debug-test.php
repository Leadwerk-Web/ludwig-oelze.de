<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('ABSPATH', __DIR__ . '/');
require_once __DIR__ . '/leadwerk_importer/includes/class-leadwerk-acf-filler.php';

class TestFiller extends Leadwerk_ACF_Filler {
    public function __construct() { }
    public function debug($section_node) {
        $children = $this->query_nodes( $section_node, '(.//*[contains(@class,"two-col")])[1]/*' );
        echo "Found " . count($children) . " children\n";
        foreach ( $children as $child ) {
            if ($child instanceof DOMElement) {
                echo "Child: " . $child->tagName . ' class="' . $child->getAttribute('class') . "\"\n";
            }
        }
    }
}

$html = file_get_contents(__DIR__ . '/scratch.html');
$filler = new TestFiller();

$dom = new DOMDocument( '1.0', 'UTF-8' );
libxml_use_internal_errors( true );
$dom->loadHTML( '<?xml encoding="utf-8" ?><body>' . $html . '</body>' );
libxml_clear_errors();
$xpath = new DOMXPath( $dom );

$section_node = $xpath->query('//section')->item(0);
$filler->debug($section_node);
