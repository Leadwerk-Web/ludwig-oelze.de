<?php
declare(strict_types=1);

require __DIR__ . '/_leadwerk_sync_lib.php';

$root = leadwerk_repo_root();
$mappingPath = $root . '/leadwerk_importer/manifest/mapping.json';
$importManifestPath = $root . '/leadwerk_importer/manifest/import-manifest.json';
$syncManifestPath = $root . '/leadwerk_importer/manifest/sync-manifest.json';

try {
	$mapping = leadwerk_read_json_file($mappingPath);
	$existingImportManifest = is_file($importManifestPath) ? leadwerk_read_json_file($importManifestPath) : array();
	$syncManifest = is_file($syncManifestPath) ? leadwerk_read_json_file($syncManifestPath) : array('entries' => array());

	$importManifest = array(
		'generated_at' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
		'profile' => (string) ($existingImportManifest['profile'] ?? 'ludwig'),
		'site_title' => (string) ($mapping['site_title'] ?? ($existingImportManifest['site_title'] ?? '')),
		'site_tagline' => (string) ($mapping['site_tagline'] ?? ($existingImportManifest['site_tagline'] ?? '')),
		'pages' => array_values((array) ($mapping['pages'] ?? array())),
		'news_articles' => array_values((array) ($mapping['news_articles'] ?? array())),
		'sync_manifest' => array(
			'generated_at' => (string) ($syncManifest['generated_at'] ?? ''),
			'entry_count' => count((array) ($syncManifest['entries'] ?? array())),
		),
	);

	leadwerk_write_json_file($importManifestPath, $importManifest);

	printf(
		"Import-Manifest aktualisiert: %d Seiten, %d News, sync entries=%d.\n",
		count($importManifest['pages']),
		count($importManifest['news_articles']),
		(int) $importManifest['sync_manifest']['entry_count']
	);
} catch (Throwable $exception) {
	fwrite(STDERR, 'Import-Manifest Build fehlgeschlagen: ' . $exception->getMessage() . PHP_EOL);
	exit(1);
}
