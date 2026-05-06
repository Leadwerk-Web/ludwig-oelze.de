from __future__ import annotations

import argparse
import json
from datetime import datetime
from pathlib import Path

from PIL import Image


IMAGE_EXTENSIONS = {".png", ".jpg", ".jpeg"}
TEXT_EXTENSIONS = {".html", ".css", ".js", ".mjs", ".php", ".json", ".md"}
WEBP_QUALITY = 85

IGNORED_DIRS = {
    ".git",
    ".next",
    "__pycache__",
    "artifacts",
    "build",
    "dist",
    "node_modules",
}

SOURCE_IMAGE_DIRS = (
    "Ludwig_prev_foto",
    "assets/images/ankuendigung",
    "assets/images/durchblick",
    "assets/images/partners",
)

PROTECTED_IMAGE_NAMES = {
    "apple-touch-icon.png",
    "favicon-192x192.png",
    "favicon-32x32.png",
    "favicon.png",
    "logo-inverted.png",
    "logo.png",
}

MIRROR_PREFIXES = (
    "leadwerk_importer/source_assets",
    "leadwerk_theme",
)


def normalize_path(path: Path) -> str:
    return path.as_posix()


def is_inside_ignored_folder(path: Path) -> bool:
    return any(part in IGNORED_DIRS for part in path.parts)


def collect_source_images(root: Path) -> list[Path]:
    images: list[Path] = []

    for relative_dir in SOURCE_IMAGE_DIRS:
        image_dir = root / relative_dir
        if not image_dir.is_dir():
            continue

        for file_path in image_dir.rglob("*"):
            if not file_path.is_file():
                continue
            if file_path.name.lower() in PROTECTED_IMAGE_NAMES:
                continue
            if file_path.suffix.lower() in IMAGE_EXTENSIONS:
                images.append(file_path)

    return sorted(images, key=lambda item: normalize_path(item.relative_to(root)).lower())


def collect_text_files(root: Path) -> list[Path]:
    text_files: list[Path] = []

    for file_path in root.rglob("*"):
        if not file_path.is_file():
            continue
        if is_inside_ignored_folder(file_path.relative_to(root)):
            continue
        if file_path.suffix.lower() in TEXT_EXTENSIONS:
            text_files.append(file_path)

    return sorted(text_files, key=lambda item: normalize_path(item.relative_to(root)).lower())


def convert_image_to_webp(image_path: Path, quality: int) -> Path:
    webp_path = image_path.with_suffix(".webp")

    with Image.open(image_path) as img:
        converted = img.convert("RGBA") if img.mode in ("RGBA", "LA") else img.convert("RGB")
        converted.save(webp_path, "WEBP", quality=quality, method=6)

    if not webp_path.exists() or webp_path.stat().st_size == 0:
        raise RuntimeError(f"WebP conversion failed: {image_path}")

    return webp_path


def build_replacement_variants(root: Path, old_path: Path, new_path: Path) -> list[tuple[str, str]]:
    old_rel = normalize_path(old_path.relative_to(root))
    new_rel = normalize_path(new_path.relative_to(root))
    old_name = old_path.name
    new_name = new_path.name

    variants = [
        (old_rel, new_rel),
        (f"./{old_rel}", f"./{new_rel}"),
        (old_rel.replace(" ", "%20"), new_rel.replace(" ", "%20")),
        (f"./{old_rel.replace(' ', '%20')}", f"./{new_rel.replace(' ', '%20')}"),
        (old_name, new_name),
        (old_name.replace(" ", "%20"), new_name.replace(" ", "%20")),
        (old_rel.replace("/", "\\"), new_rel.replace("/", "\\")),
    ]

    return sorted(set(variants), key=lambda pair: len(pair[0]), reverse=True)


def read_text_best_effort(text_file: Path) -> tuple[str, str] | None:
    for encoding in ("utf-8", "latin-1"):
        try:
            return text_file.read_text(encoding=encoding), encoding
        except UnicodeDecodeError:
            continue
        except OSError:
            return None
    return None


def update_text_references(root: Path, mappings: list[dict[str, str]], dry_run: bool) -> list[str]:
    changed_files: list[str] = []

    for text_file in collect_text_files(root):
        read_result = read_text_best_effort(text_file)
        if read_result is None:
            continue

        content, _encoding = read_result
        original_content = content

        for item in mappings:
            old_path = Path(item["old_absolute_path"])
            new_path = Path(item["new_absolute_path"])

            for old_value, new_value in build_replacement_variants(root, old_path, new_path):
                content = content.replace(old_value, new_value)

        if content != original_content:
            if not dry_run:
                text_file.write_text(content, encoding="utf-8")
            changed_files.append(normalize_path(text_file.relative_to(root)))

    return changed_files


def write_manifest(root: Path, images: list[Path]) -> Path:
    manifest = {
        "created_at": datetime.now().isoformat(timespec="seconds"),
        "root": normalize_path(root),
        "scope": list(SOURCE_IMAGE_DIRS),
        "protected_image_names": sorted(PROTECTED_IMAGE_NAMES),
        "total_images": len(images),
        "images": [
            {
                "filename": image.name,
                "extension": image.suffix,
                "relative_path": normalize_path(image.relative_to(root)),
                "absolute_path": normalize_path(image.resolve()),
                "target_webp_relative_path": normalize_path(image.with_suffix(".webp").relative_to(root)),
                "target_webp_absolute_path": normalize_path(image.with_suffix(".webp").resolve()),
                "size_bytes": image.stat().st_size,
            }
            for image in images
        ],
    }

    manifest_path = root / "webp-conversion-manifest.json"
    manifest_path.write_text(json.dumps(manifest, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    return manifest_path


def mirror_original_paths(root: Path, old_relative_path: str) -> list[Path]:
    return [root / prefix / old_relative_path for prefix in MIRROR_PREFIXES]


def delete_file_if_safe(path: Path, root: Path, dry_run: bool) -> bool:
    try:
        resolved = path.resolve()
    except OSError:
        return False

    if root not in resolved.parents and resolved != root:
        raise RuntimeError(f"Refusing to delete outside root: {path}")

    if not path.exists() or not path.is_file():
        return False

    if not dry_run:
        path.unlink()

    return True


def run_conversion(root: Path, quality: int, dry_run: bool, delete_originals: bool) -> None:
    root = root.resolve()

    if not root.exists() or not root.is_dir():
        raise ValueError(f"Root folder not found: {root}")

    images = collect_source_images(root)
    manifest_path = write_manifest(root, images)

    print(f"Root: {root}")
    print(f"Scoped images: {len(images)}")
    print(f"Manifest saved: {manifest_path}")

    mappings: list[dict[str, str]] = []
    failed: list[dict[str, str]] = []

    for image_path in images:
        try:
            webp_path = image_path.with_suffix(".webp") if dry_run else convert_image_to_webp(image_path, quality)

            mappings.append(
                {
                    "old_filename": image_path.name,
                    "new_filename": webp_path.name,
                    "old_relative_path": normalize_path(image_path.relative_to(root)),
                    "new_relative_path": normalize_path(webp_path.relative_to(root)),
                    "old_absolute_path": normalize_path(image_path.resolve()),
                    "new_absolute_path": normalize_path(webp_path.resolve()),
                    "old_size_bytes": image_path.stat().st_size,
                    "new_size_bytes": webp_path.stat().st_size if webp_path.exists() else 0,
                }
            )

            print(f"[OK] {image_path.relative_to(root)} -> {webp_path.relative_to(root)}")
        except Exception as exception:
            failed.append({"file": normalize_path(image_path.relative_to(root)), "error": str(exception)})
            print(f"[FAILED] {image_path.relative_to(root)} | {exception}")

    changed_files = update_text_references(root, mappings, dry_run=dry_run)
    for file_name in changed_files:
        print(f"[UPDATED] {file_name}")

    deleted_files: list[str] = []
    if delete_originals:
        for item in mappings:
            old_rel = item["old_relative_path"]
            old_file = root / old_rel
            new_file = root / item["new_relative_path"]

            if not dry_run and (not new_file.exists() or new_file.stat().st_size == 0):
                continue

            if delete_file_if_safe(old_file, root, dry_run=dry_run):
                deleted_files.append(old_rel)
                print(f"[DELETED] {old_rel}")

            for mirror_path in mirror_original_paths(root, old_rel):
                if delete_file_if_safe(mirror_path, root, dry_run=dry_run):
                    deleted_files.append(normalize_path(mirror_path.relative_to(root)))
                    print(f"[DELETED] {mirror_path.relative_to(root)}")

    report = {
        "created_at": datetime.now().isoformat(timespec="seconds"),
        "root": normalize_path(root),
        "quality": quality,
        "dry_run": dry_run,
        "delete_originals": delete_originals,
        "converted_count": len(mappings),
        "failed_count": len(failed),
        "updated_text_files_count": len(changed_files),
        "deleted_files_count": len(deleted_files),
        "converted": mappings,
        "failed": failed,
        "updated_text_files": changed_files,
        "deleted_files": deleted_files,
    }

    report_path = root / "webp-conversion-report.json"
    if not dry_run:
        report_path.write_text(json.dumps(report, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")

    print("Done.")
    if not dry_run:
        print(f"Report saved: {report_path}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Safely convert canonical Ludwig content images to WebP and update references."
    )
    parser.add_argument("--path", default=".", help="Repo root. Default: current folder")
    parser.add_argument("--quality", type=int, default=WEBP_QUALITY, help="WebP quality from 1 to 100")
    parser.add_argument("--dry-run", action="store_true", help="Only list what would happen")
    parser.add_argument("--keep-originals", action="store_true", help="Do not delete converted originals")
    args = parser.parse_args()

    run_conversion(
        root=Path(args.path),
        quality=args.quality,
        dry_run=args.dry_run,
        delete_originals=not args.keep_originals,
    )
