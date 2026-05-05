<?php
declare(strict_types=1);

require __DIR__ . '/_leadwerk_sync_lib.php';

$strictDrift = in_array('--strict-drift', $argv, true);
$root = leadwerk_repo_root();

try {
	$syncManifest = leadwerk_read_json_file($root . '/leadwerk_importer/manifest/sync-manifest.json');
	$importManifest = leadwerk_read_json_file($root . '/leadwerk_importer/manifest/import-manifest.json');
} catch (Throwable $exception) {
	fwrite(STDERR, 'Verify fehlgeschlagen: ' . $exception->getMessage() . PHP_EOL);
	exit(1);
}

$blocking = array();
$warnings = array();
$syncTargets = array();

foreach ((array) ($syncManifest['entries'] ?? array()) as $entry) {
	if (!is_array($entry)) {
		continue;
	}

	$targetRel = trim((string) ($entry['target'] ?? ''), '\\/');
	$targetAbs = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $targetRel);
	$syncTargets[str_replace('\\', '/', $targetRel)] = true;

	if (!is_file($targetAbs)) {
		$blocking[] = 'Bundled Datei fehlt: ' . $targetRel;
		continue;
	}

	$actualHash = sha1_file($targetAbs);
	if (!is_string($actualHash) || $actualHash !== (string) ($entry['target_sha1'] ?? '')) {
		$blocking[] = 'Bundled Datei passt nicht zum sync-manifest: ' . $targetRel;
	}
}

foreach ((array) ($importManifest['pages'] ?? array()) as $page) {
	if (!is_array($page)) {
		continue;
	}

	$sourceFile = trim((string) ($page['source_file'] ?? ''));
	if ('' === $sourceFile) {
		continue;
	}

	$rootSource = $root . '/' . $sourceFile;
	if (!is_file($rootSource)) {
		$blocking[] = 'Root-Quelle fehlt: ' . $sourceFile;
	}

	foreach (array(
		'leadwerk_importer/source_assets/' . $sourceFile,
		'leadwerk_theme/source_shells/' . $sourceFile,
	) as $targetRel) {
		$targetKey = str_replace('\\', '/', trim($targetRel, '\\/'));
		$targetAbs = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $targetKey);

		if (!isset($syncTargets[$targetKey])) {
			$blocking[] = 'sync-manifest deckt Import-Seite nicht ab: ' . $targetKey;
		}

		if (!is_file($targetAbs)) {
			$blocking[] = 'Bundle-Datei fehlt: ' . $targetKey;
			continue;
		}

		if (is_file($rootSource)) {
			$rootHash = sha1_file($rootSource);
			$targetHash = sha1_file($targetAbs);
			if (!is_string($rootHash) || !is_string($targetHash) || $rootHash !== $targetHash) {
				$blocking[] = 'Bundle-Datei ist nicht mit der Root-Quelle synchron: ' . $targetKey;
			}
		}
	}
}

$importSyncInfo = (array) ($importManifest['sync_manifest'] ?? array());
$syncEntryCount = count((array) ($syncManifest['entries'] ?? array()));
if ((int) ($importSyncInfo['entry_count'] ?? -1) !== $syncEntryCount) {
	$warnings[] = 'import-manifest sync_manifest.entry_count ist veraltet.';
}

if ((string) ($importSyncInfo['generated_at'] ?? '') !== (string) ($syncManifest['generated_at'] ?? '')) {
	$warnings[] = 'import-manifest sync_manifest.generated_at ist veraltet.';
}

if ($blocking) {
	echo "Blocking issues\n";
	foreach (array_values(array_unique($blocking)) as $issue) {
		echo ' - ' . $issue . "\n";
	}
}

if ($warnings) {
	echo "Warnings\n";
	foreach (array_values(array_unique($warnings)) as $warning) {
		echo ' - ' . $warning . "\n";
	}
}

if (!$blocking && (!$warnings || !$strictDrift)) {
	printf(
		"Verify erfolgreich: %d sync entries, %d Import-Seiten geprueft.\n",
		$syncEntryCount,
		count((array) ($importManifest['pages'] ?? array()))
	);
	exit(0);
}

exit(1);
