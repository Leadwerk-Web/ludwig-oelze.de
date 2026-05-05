<?php
declare(strict_types=1);

function leadwerk_repo_root(): string {
	return dirname(__DIR__);
}

function leadwerk_read_json_file(string $path): array {
	if (!is_file($path)) {
		throw new RuntimeException('Datei nicht gefunden: ' . $path);
	}

	$json = file_get_contents($path);
	if (!is_string($json) || '' === $json) {
		throw new RuntimeException('Datei konnte nicht gelesen werden: ' . $path);
	}

	$data = json_decode($json, true);
	if (!is_array($data)) {
		throw new RuntimeException('Ungueltiges JSON: ' . $path);
	}

	return $data;
}

function leadwerk_write_json_file(string $path, array $data): void {
	$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		throw new RuntimeException('JSON konnte nicht serialisiert werden: ' . $path);
	}

	$dir = dirname($path);
	if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
		throw new RuntimeException('Verzeichnis konnte nicht erstellt werden: ' . $dir);
	}

	file_put_contents($path, $json . PHP_EOL);
}

function leadwerk_relative_path(string $absolutePath, string $root): string {
	$root = rtrim(str_replace('\\', '/', $root), '/');
	$path = str_replace('\\', '/', $absolutePath);

	if (0 === strpos($path, $root . '/')) {
		return substr($path, strlen($root) + 1);
	}

	return ltrim($path, '/');
}

function leadwerk_collect_source_files_from_mapping(string $root): array {
	$mapping = leadwerk_read_json_file($root . '/leadwerk_importer/manifest/mapping.json');
	$sourceFiles = array();

	foreach (array('pages', 'news_articles') as $group) {
		foreach ((array) ($mapping[$group] ?? array()) as $item) {
			if (!is_array($item)) {
				continue;
			}

			$sourceFile = trim((string) ($item['source_file'] ?? ''));
			if ('' !== $sourceFile) {
				$sourceFiles[$sourceFile] = true;
			}
		}
	}

	if (is_file($root . '/bilder-inventar.html')) {
		$sourceFiles['bilder-inventar.html'] = true;
	}

	$files = array_keys($sourceFiles);
	sort($files, SORT_NATURAL | SORT_FLAG_CASE);

	return $files;
}

function leadwerk_add_sync_pair(array &$pairs, string $sourceRel, string $targetRel): void {
	$key = $sourceRel . '|' . $targetRel;
	$pairs[$key] = array(
		'source' => str_replace('\\', '/', $sourceRel),
		'target' => str_replace('\\', '/', $targetRel),
	);
}

function leadwerk_collect_directory_files(string $root, string $relativeDir): array {
	$absoluteDir = $root . '/' . $relativeDir;
	if (!is_dir($absoluteDir)) {
		return array();
	}

	$files = array();
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($absoluteDir, FilesystemIterator::SKIP_DOTS)
	);

	foreach ($iterator as $fileInfo) {
		if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
			continue;
		}

		$files[] = leadwerk_relative_path($fileInfo->getPathname(), $root);
	}

	sort($files, SORT_NATURAL | SORT_FLAG_CASE);

	return $files;
}

function leadwerk_build_sync_pairs(string $root): array {
	$pairs = array();

	foreach (leadwerk_collect_source_files_from_mapping($root) as $sourceFile) {
		leadwerk_add_sync_pair($pairs, $sourceFile, 'leadwerk_importer/source_assets/' . $sourceFile);
		leadwerk_add_sync_pair($pairs, $sourceFile, 'leadwerk_theme/source_shells/' . $sourceFile);
	}

	$sharedFiles = array(
		'css/styles.css' => array(
			'leadwerk_theme/css/styles.css',
			'leadwerk_importer/source_assets/css/styles.css',
		),
		'js/main.js' => array(
			'leadwerk_theme/js/main.js',
			'leadwerk_importer/source_assets/js/main.js',
		),
	);

	foreach ($sharedFiles as $sourceRel => $targets) {
		foreach ($targets as $targetRel) {
			leadwerk_add_sync_pair($pairs, $sourceRel, $targetRel);
		}
	}

	$directoryMirrors = array(
		'assets/images' => array(
			'leadwerk_theme/assets/images',
			'leadwerk_importer/source_assets/assets/images',
		),
		'assets/videos' => array(
			'leadwerk_theme/assets/videos',
			'leadwerk_importer/source_assets/assets/videos',
		),
		'Ludwig_prev_foto' => array(
			'leadwerk_theme/Ludwig_prev_foto',
			'leadwerk_importer/source_assets/Ludwig_prev_foto',
		),
		'Fotos' => array(
			'leadwerk_theme/Fotos',
			'leadwerk_importer/source_assets/Fotos',
		),
	);

	foreach ($directoryMirrors as $sourceDir => $targets) {
		foreach (leadwerk_collect_directory_files($root, $sourceDir) as $sourceRel) {
			$relativeFile = ltrim(substr($sourceRel, strlen($sourceDir)), '/');
			foreach ($targets as $targetDir) {
				leadwerk_add_sync_pair($pairs, $sourceRel, $targetDir . '/' . $relativeFile);
			}
		}
	}

	$entries = array_values($pairs);
	usort(
		$entries,
		static function (array $left, array $right): int {
			return array($left['source'], $left['target']) <=> array($right['source'], $right['target']);
		}
	);

	return $entries;
}

function leadwerk_sync_pairs(string $root, array $pairs): array {
	$entries = array();

	foreach ($pairs as $pair) {
		$sourceRel = (string) ($pair['source'] ?? '');
		$targetRel = (string) ($pair['target'] ?? '');
		$sourceAbs = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $sourceRel);
		$targetAbs = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $targetRel);

		if (!is_file($sourceAbs)) {
			throw new RuntimeException('Quell-Datei fehlt: ' . $sourceRel);
		}

		$targetDir = dirname($targetAbs);
		if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
			throw new RuntimeException('Zielverzeichnis konnte nicht erstellt werden: ' . $targetDir);
		}

		if (!copy($sourceAbs, $targetAbs)) {
			throw new RuntimeException('Datei konnte nicht kopiert werden: ' . $sourceRel . ' -> ' . $targetRel);
		}

		$sourceHash = sha1_file($sourceAbs);
		$targetHash = sha1_file($targetAbs);

		if (!is_string($sourceHash) || !is_string($targetHash)) {
			throw new RuntimeException('SHA1 konnte nicht berechnet werden: ' . $sourceRel);
		}

		$entries[] = array(
			'source' => $sourceRel,
			'target' => $targetRel,
			'source_sha1' => $sourceHash,
			'target_sha1' => $targetHash,
			'size' => filesize($sourceAbs),
		);
	}

	return $entries;
}

function leadwerk_build_sync_manifest_payload(string $root): array {
	$pairs = leadwerk_build_sync_pairs($root);
	$entries = leadwerk_sync_pairs($root, $pairs);

	return array(
		'generated_at' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
		'source_root' => $root,
		'entries' => $entries,
	);
}
