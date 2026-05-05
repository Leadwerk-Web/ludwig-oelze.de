<?php
declare(strict_types=1);

require __DIR__ . '/_leadwerk_sync_lib.php';

$root = leadwerk_repo_root();
$manifestPath = $root . '/leadwerk_importer/manifest/sync-manifest.json';

try {
	$manifest = leadwerk_build_sync_manifest_payload($root);
	leadwerk_write_json_file($manifestPath, $manifest);

	printf(
		"Sync abgeschlossen: %d Bundle-Eintraege nach %s geschrieben.\n",
		count((array) ($manifest['entries'] ?? array())),
		$manifestPath
	);
} catch (Throwable $exception) {
	fwrite(STDERR, 'Sync fehlgeschlagen: ' . $exception->getMessage() . PHP_EOL);
	exit(1);
}
