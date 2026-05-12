# Ludwig Business-SEO (JSON-LD)

## Datei `ludwig-business-seo.json`

- **`aggregateRating`**: Muss mit den tatsächlichen Google-Business-Bewertungen übereinstimmen (`ratingValue`, `reviewCount`, optional `bestRating` / `worstRating`).
- **`googleBusinessProfileUrl`**: Vollständige URL zum Google-Business-Profil (z. B. Maps- oder g.page-Link). Leer lassen, wenn noch kein Eintrag gepflegt wird; die URL wird dann nicht in `sameAs` aufgenommen.

## Statische HTML-Seiten aktualisieren

Nach jeder Änderung an dieser JSON-Datei im Repository-Root ausführen:

```bash
python scripts/apply-ludwig-structured-data.py
```

Das Skript schreibt das JSON-LD in alle per `leadwerk_importer/manifest/mapping.json` referenzierten HTML-Dateien (Kommentarblöcke „Ludwig Structured Data“), jeweils im **Repository-Root**, unter `leadwerk_theme/source_shells/` und unter `leadwerk_importer/source_assets/`, sofern die Datei dort existiert. Es nutzt dieselbe Konfiguration wie das WordPress-Theme (`leadwerk_theme/inc/ludwig-structured-data.php`).

## WordPress / Importer

- **Live-Seiten**: Das Theme liest `ludwig-business-seo.json` aus diesem Ordner bei jedem Seitenaufruf (kein separater Build-Schritt auf dem Server nötig).
- **Importer**: Durch die drei Pfade im Skript bleiben Root-, Shell- und `source_assets`-Kopien der HTML-Dateien ohne manuelles Kopieren abgleichbar, sofern die Dateinamen mit `mapping.json` übereinstimmen.
