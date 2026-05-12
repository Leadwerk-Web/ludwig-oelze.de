from __future__ import annotations

import html
import json
import re
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
MAPPING = ROOT / "leadwerk_importer" / "manifest" / "mapping.json"
BUSINESS_SEO_CONFIG = ROOT / "leadwerk_theme" / "config" / "ludwig-business-seo.json"
SITE_URL = "https://ludwigoelze.com"
LOGO_URL = f"{SITE_URL}/assets/images/logo.png"

_seo_config_cache: dict | None = None


def load_business_seo_config() -> dict:
    """Mirror leadwerk_theme/inc/ludwig-structured-data.php defaults + ludwig-business-seo.json."""
    global _seo_config_cache
    if _seo_config_cache is not None:
        return _seo_config_cache

    defaults: dict = {
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": 4.9,
            "bestRating": 5,
            "worstRating": 1,
            "reviewCount": 50,
        },
        "googleBusinessProfileUrl": "",
    }

    if not BUSINESS_SEO_CONFIG.is_file():
        _seo_config_cache = defaults
        return _seo_config_cache

    try:
        raw = json.loads(BUSINESS_SEO_CONFIG.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        _seo_config_cache = defaults
        return _seo_config_cache

    if not isinstance(raw, dict):
        _seo_config_cache = defaults
        return _seo_config_cache

    ar = raw.get("aggregateRating")
    if isinstance(ar, dict):
        defaults = {
            **defaults,
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": ar.get("ratingValue", defaults["aggregateRating"]["ratingValue"]),
                "bestRating": ar.get("bestRating", defaults["aggregateRating"]["bestRating"]),
                "worstRating": ar.get("worstRating", defaults["aggregateRating"]["worstRating"]),
                "reviewCount": int(ar.get("reviewCount", defaults["aggregateRating"]["reviewCount"])),
            },
        }

    gbp = raw.get("googleBusinessProfileUrl")
    if isinstance(gbp, str):
        defaults["googleBusinessProfileUrl"] = gbp.strip()

    _seo_config_cache = defaults
    return _seo_config_cache


def ludwig_same_as_urls(seo: dict) -> list[str]:
    base = [
        "https://www.facebook.com/ludwig.finanzmakler/",
        "https://www.linkedin.com/in/ludwig-oelze-6656b8173",
        "https://www.instagram.com/ludwig_finanzmakler",
    ]
    gbp = (seo.get("googleBusinessProfileUrl") or "").strip()
    if gbp and (gbp.startswith("http://") or gbp.startswith("https://")):
        merged = [gbp] + base
        seen: set[str] = set()
        out: list[str] = []
        for url in merged:
            if url not in seen:
                seen.add(url)
                out.append(url)
        return out
    return base

LEGAL_SOURCE_KEYS = {
    "ludwig-impressum-v1",
    "ludwig-datenschutz-v1",
    "ludwig-erstinformation-v1",
    "ludwig-teilnahmebedingungen-v1",
    "ludwig-vorgangsabfrage-v1",
    "ludwig-404-v1",
}

EXTRA_STATIC_PAGES = [
    {
        "source_key": "ludwig-teilnahmebedingungen-v1",
        "source_file": "teilnahmebedingungen.html",
        "slug": "teilnahmebedingungen",
        "title": "Teilnahmebedingungen",
    },
    {
        "source_key": "ludwig-vorgangsabfrage-v1",
        "source_file": "vorgangsabfrage.html",
        "slug": "vorgangsabfrage",
        "title": "Vorgangsabfrage",
    },
]

PAGE_TYPE_BY_SOURCE_KEY = {
    "ludwig-kontakt-v1": "ContactPage",
    "ludwig-ueber-ludwig-v1": "AboutPage",
    "ludwig-wissen-v1": "CollectionPage",
}


def clean_text(value: str) -> str:
    value = re.sub(r"<[^>]+>", " ", value or "")
    value = html.unescape(value)
    value = re.sub(r"\s+", " ", value)
    return value.strip()


def find_meta_description(markup: str) -> str:
    patterns = [
        r'<meta\s+name=["\']description["\']\s+content=["\']([^"\']*)["\']',
        r'<meta\s+content=["\']([^"\']*)["\']\s+name=["\']description["\']',
    ]
    for pattern in patterns:
        match = re.search(pattern, markup, flags=re.I | re.S)
        if match:
            return clean_text(match.group(1))
    return ""


def find_title(markup: str, fallback: str) -> str:
    match = re.search(r"<title[^>]*>(.*?)</title>", markup, flags=re.I | re.S)
    if match:
        title = clean_text(match.group(1))
        if title:
            return title
    match = re.search(r"<h1[^>]*>(.*?)</h1>", markup, flags=re.I | re.S)
    if match:
        title = clean_text(match.group(1))
        if title:
            return title
    return clean_text(fallback)


def page_url(slug: str) -> str:
    if slug in {"", "home", "index"}:
        return f"{SITE_URL}/"
    return f"{SITE_URL}/{slug}"


def geo_profile(source_key: str) -> dict:
    if source_key == "ludwig-expat-beratung-1-v1":
        return {
            "region": "AE-DU",
            "place": "Dubai",
            "latitude": 25.2048,
            "longitude": 55.2708,
            "country": "AE",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Dubai",
                "addressRegion": "Dubai",
                "addressCountry": "AE",
            },
        }

    return {
        "region": "DE-BW",
        "place": "Baden-Baden",
        "latitude": 48.7606,
        "longitude": 8.2398,
        "country": "DE",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Bismarckstraße 26",
            "postalCode": "76530",
            "addressLocality": "Baden-Baden",
            "addressRegion": "Baden-Württemberg",
            "addressCountry": "DE",
        },
    }


def build_schema(page: dict, markup: str) -> dict:
    source_key = str(page.get("source_key", ""))
    slug = str(page.get("slug", ""))
    canonical = page_url(slug)
    title = find_title(markup, str(page.get("title", "")))
    description = find_meta_description(markup) or "Versicherungsmakler und Baufinanzierungsberater in Baden-Baden."
    geo = geo_profile(source_key)
    seo = load_business_seo_config()
    org_id = f"{SITE_URL}/#organization"
    website_id = f"{SITE_URL}/#website"
    place_id = f"{canonical}#primary-location"
    service_id = f"{canonical}#service"

    web_page = {
        "@type": PAGE_TYPE_BY_SOURCE_KEY.get(source_key, "WebPage"),
        "@id": f"{canonical}#webpage",
        "url": canonical,
        "name": title,
        "description": description,
        "inLanguage": "de-DE",
        "isPartOf": {"@id": website_id},
        "publisher": {"@id": org_id},
        "about": {"@id": org_id},
        "spatialCoverage": {"@id": place_id},
    }

    graph = [
        {
            "@type": "WebSite",
            "@id": website_id,
            "url": f"{SITE_URL}/",
            "name": "Ludwig Oelze",
            "inLanguage": "de-DE",
            "publisher": {"@id": org_id},
        },
        {
            "@type": "LocalBusiness",
            "@id": org_id,
            "name": "Ludwig Oelze",
            "legalName": "Ludwig Oelze",
            "url": f"{SITE_URL}/",
            "description": "Freier Versicherungsmakler und Baufinanzierungsberater in Baden-Baden mit Beratung für Familien, Selbstständige und Expats.",
            "additionalType": [
                "https://schema.org/InsuranceAgency",
                "https://schema.org/FinancialService",
            ],
            "image": LOGO_URL,
            "logo": LOGO_URL,
            "telephone": "+49 176 43689181",
            "email": "finanzen@ludwigoelze.com",
            "priceRange": "€€",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Bismarckstraße 26",
                "postalCode": "76530",
                "addressLocality": "Baden-Baden",
                "addressRegion": "Baden-Württemberg",
                "addressCountry": "DE",
            },
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": 48.7606,
                "longitude": 8.2398,
            },
            "areaServed": [
                {"@type": "City", "name": "Baden-Baden", "addressCountry": "DE"},
                {"@type": "Country", "name": "Deutschland"},
                {"@type": "City", "name": "Dubai", "addressCountry": "AE"},
                {"@type": "Country", "name": "Spanien"},
            ],
            "openingHoursSpecification": {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                "opens": "09:00",
                "closes": "18:00",
            },
            "aggregateRating": seo["aggregateRating"],
            "founder": {
                "@type": "Person",
                "name": "Ludwig Oelze",
                "jobTitle": "Versicherungsmakler",
            },
            "sameAs": ludwig_same_as_urls(seo),
        },
        {
            "@type": "Place",
            "@id": place_id,
            "name": geo["place"],
            "address": geo["address"],
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": geo["latitude"],
                "longitude": geo["longitude"],
            },
        },
        web_page,
        {
            "@type": "BreadcrumbList",
            "@id": f"{canonical}#breadcrumb",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Start",
                    "item": f"{SITE_URL}/",
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": title,
                    "item": canonical,
                },
            ],
        },
    ]

    if source_key not in LEGAL_SOURCE_KEYS:
        service = {
            "@type": "Service",
            "@id": service_id,
            "name": title,
            "url": canonical,
            "description": description,
            "provider": {"@id": org_id},
            "areaServed": {"@id": place_id},
            "serviceType": title,
        }
        if source_key == "ludwig-expat-beratung-1-v1":
            service["name"] = "Krankenversicherung für deutsche Expats in Dubai"
            service["serviceType"] = [
                "Krankenversicherungsberatung",
                "Expat Versicherung Dubai",
                "Internationale Krankenversicherung",
                "UAE Health Insurance Beratung",
            ]
            service["areaServed"] = [
                {"@type": "City", "name": "Dubai", "addressCountry": "AE"},
                {"@type": "Country", "name": "United Arab Emirates"},
            ]
        graph.append(service)
        web_page["mainEntity"] = {"@id": service_id}

    return {"@context": "https://schema.org", "@graph": graph}


def build_geo_tags(source_key: str) -> str:
    geo = geo_profile(source_key)
    return "\n".join(
        [
            "    <!-- Ludwig Geo Tags -->",
            f'    <meta name="geo.region" content="{geo["region"]}">',
            f'    <meta name="geo.placename" content="{geo["place"]}">',
            f'    <meta name="geo.position" content="{geo["latitude"]};{geo["longitude"]}">',
            f'    <meta name="ICBM" content="{geo["latitude"]}, {geo["longitude"]}">',
            "    <!-- /Ludwig Geo Tags -->",
        ]
    )


def build_schema_tag(schema: dict) -> str:
    body = json.dumps(schema, ensure_ascii=False, indent=2, separators=(",", ": "))
    return "\n".join(
        [
            "    <!-- Ludwig Structured Data -->",
            '    <script type="application/ld+json" id="ludwig-structured-data">',
            body,
            "    </script>",
            "    <!-- /Ludwig Structured Data -->",
        ]
    )


def replace_block(markup: str, label: str, replacement: str) -> str:
    pattern = rf"\s*<!-- {re.escape(label)} -->.*?<!-- /{re.escape(label)} -->"
    cleaned = re.sub(pattern, "", markup, flags=re.S)
    return cleaned


def apply_to_file(page: dict) -> bool:
    source_file = str(page.get("source_file", ""))
    if not source_file or source_file == "404.html":
        return False

    bases = (
        ROOT,
        ROOT / "leadwerk_theme" / "source_shells",
        ROOT / "leadwerk_importer" / "source_assets",
    )
    changed_any = False

    for base in bases:
        path = base / source_file
        if not path.is_file():
            continue

        original = path.read_text(encoding="utf-8")
        if "</head>" not in original:
            continue

        markup = replace_block(original, "Ludwig Geo Tags", "")
        markup = replace_block(markup, "Ludwig Structured Data", "")

        insert = build_geo_tags(str(page.get("source_key", ""))) + "\n\n" + build_schema_tag(
            build_schema(page, markup)
        )
        updated = markup.replace("</head>", f"\n{insert}\n</head>", 1)

        if updated != original:
            path.write_text(updated, encoding="utf-8")
            changed_any = True

    return changed_any


def main() -> None:
    mapping = json.loads(MAPPING.read_text(encoding="utf-8"))
    changed = []
    pages = list(mapping.get("pages", []))
    mapped_files = {str(page.get("source_file", "")) for page in pages}
    pages.extend(page for page in EXTRA_STATIC_PAGES if page["source_file"] not in mapped_files)

    for page in pages:
        if apply_to_file(page):
            changed.append(page.get("source_file"))

    print(f"Structured data updated: {len(changed)} file(s)")
    for name in changed:
        print(f" - {name}")


if __name__ == "__main__":
    main()
