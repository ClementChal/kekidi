"""Vérifier les livrables locaux puis créer le ZIP installable du thème."""

import json
import re
import xml.etree.ElementTree as ET
from datetime import datetime
from pathlib import Path
from shutil import copy2
from zipfile import ZIP_DEFLATED, ZipFile


def check_blocks(content: str, label: str) -> int:
    """Contrôler les commentaires de blocs imbriqués et leurs attributs JSON."""
    stack: list[str] = []
    count = 0
    for match in re.finditer(r"<!--\s*(/?)wp:([\w/-]+)(.*?)-->", content, re.S):
        closing, name, attributes = match.groups()
        attributes = attributes.strip()
        if closing:
            assert stack and stack.pop() == name, f"Bloc mal fermé : {label} ({name})"
        else:
            count += 1
            standalone = attributes.endswith("/")
            if standalone:
                attributes = attributes[:-1].strip()
            if attributes:
                json.loads(attributes)
            if not standalone:
                stack.append(name)
    assert not stack, f"Blocs ouverts : {label} {stack}"
    assert count, f"Aucun bloc : {label}"
    assert "wp:html" not in content, f"Bloc HTML brut non éditable : {label}"
    return count


def main() -> None:
    root = Path(__file__).resolve().parent
    theme = root / "wordpress/kekidi"
    for name in ("style.css", "functions.php", "theme.json", "templates/index.html"):
        assert (theme / name).is_file(), f"Fichier manquant : {name}"
    config = json.loads((theme / "theme.json").read_text())
    assert config["version"] == 3
    prefetch_script = (theme / "assets/kekidi-prefetch.js").read_text()
    assert "IntersectionObserver" in prefetch_script
    assert "entry.intersectionRatio <= 0" in prefetch_script
    assert "rootMargin: '0px'" in prefetch_script
    assert "kekidi-prefetch" in (theme / "functions.php").read_text()
    front_page = (theme / "templates/front-page.html").read_text()
    home_pattern = (theme / "patterns/accueil.php").read_text()
    assert '"slug":"kekidi/accueil"' in front_page
    assert '"templateLock":"contentOnly"' not in home_pattern
    for font in config["settings"]["typography"]["fontFamilies"]:
        for face in font["fontFace"]:
            for src in face["src"]:
                assert (theme / src.removeprefix("file:./")).is_file(), src

    block_count = 0
    for path in [*theme.glob("templates/*.html"), *theme.glob("parts/*.html"), *theme.glob("patterns/*.php")]:
        text = path.read_text()
        block_count += check_blocks(text, str(path.relative_to(root)))
        for slug in re.findall(r'"slug":"kekidi/([\w-]+)"', text):
            assert (theme / f"patterns/{slug}.php").is_file(), slug
        for slug in re.findall(r'"slug":"(header|footer)"', text):
            assert (theme / f"parts/{slug}.html").is_file(), slug

    for path in (root / "assets").iterdir():
        assert path.read_bytes() == (theme / "assets" / path.name).read_bytes(), path.name

    namespaces = {"wp": "http://wordpress.org/export/1.2/", "content": "http://purl.org/rss/1.0/modules/content/"}
    channel = ET.parse(root / "wordpress/reponses-kekidi.xml").getroot().find("channel")
    assert channel is not None
    questions = json.loads((root / "questions.json").read_text())
    items = channel.findall("item")
    assert len(items) == len(questions)
    for item, question in zip(items, questions, strict=True):
        assert item.findtext("title") == question["question"]
        assert item.findtext("wp:post_name", namespaces=namespaces) == question["id"]
        assert item.findtext("wp:post_type", namespaces=namespaces) == "post"
        assert item.findtext("wp:status", namespaces=namespaces) == "publish"
        assert item.findtext("wp:comment_status", namespaces=namespaces) == "closed"
        content = item.findtext("content:encoded", namespaces=namespaces)
        assert content
        block_count += check_blocks(content, question["id"])
        assert ('"className":"sources"' in content) == bool(question["sources"])
        assert "Écrire ici" not in content

    output = root / "wordpress/themes/kekidi-theme.zip"
    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists():
        backup = output.parent / "backups" / f"kekidi-theme-backup-{datetime.now():%Y%m%d-%H%M%S-%f}.zip"
        backup.parent.mkdir(parents=True, exist_ok=True)
        copy2(output, backup)
        print(f"Sauvegarde du thème précédent : {backup}")
    with ZipFile(output, "w", ZIP_DEFLATED) as archive:
        for path in sorted(theme.rglob("*")):
            if path.is_file():
                archive.write(path, path.relative_to(theme.parent))
    with ZipFile(output) as archive:
        assert archive.testzip() is None
        assert "kekidi/style.css" in archive.namelist()
        assert all(name.startswith("kekidi/") for name in archive.namelist())

    plugin = root / "wordpress/kekidi-super-editeur"
    plugin_main = plugin / "kekidi-super-editeur.php"
    plugin_text = plugin_main.read_text()
    assert "view_koko_analytics" in plugin_text
    assert "edit_theme_options" in plugin_text
    plugin_output = root / "wordpress/extensions/kekidi-super-editeur.zip"
    plugin_output.parent.mkdir(parents=True, exist_ok=True)
    if plugin_output.exists():
        backup = plugin_output.parent / "backups" / f"kekidi-super-editeur-{datetime.now():%Y%m%d-%H%M%S-%f}.zip"
        backup.parent.mkdir(parents=True, exist_ok=True)
        copy2(plugin_output, backup)
    with ZipFile(plugin_output, "w", ZIP_DEFLATED) as archive:
        for path in sorted(plugin.rglob("*")):
            if path.is_file():
                archive.write(path, path.relative_to(plugin.parent))
    with ZipFile(plugin_output) as archive:
        assert archive.testzip() is None
        assert "kekidi-super-editeur/kekidi-super-editeur.php" in archive.namelist()

    quiz = root / "wordpress/kekidi-quiz"
    quiz_output = root / "wordpress/extensions/kekidi-quiz.zip"
    if quiz_output.exists():
        backup = quiz_output.parent / "backups" / f"kekidi-quiz-{datetime.now():%Y%m%d-%H%M%S-%f}.zip"
        backup.parent.mkdir(parents=True, exist_ok=True)
        copy2(quiz_output, backup)
    with ZipFile(quiz_output, "w", ZIP_DEFLATED) as archive:
        for path in sorted(quiz.rglob("*")):
            if path.is_file() and "tests" not in path.relative_to(quiz).parts:
                archive.write(path, path.relative_to(quiz.parent))
    with ZipFile(quiz_output) as archive:
        assert archive.testzip() is None
        for name in ("kekidi-quiz.php", "block/block.json", "block/render.php", "block/editor.js", "block/view.js"):
            assert f"kekidi-quiz/{name}" in archive.namelist()
        assert all(name.startswith("kekidi-quiz/") for name in archive.namelist())

    contact = root / "wordpress/kekidi-contact"
    contact_output = root / "wordpress/extensions/kekidi-contact.zip"
    if contact_output.exists():
        backup = contact_output.parent / "backups" / f"kekidi-contact-{datetime.now():%Y%m%d-%H%M%S-%f}.zip"
        backup.parent.mkdir(parents=True, exist_ok=True)
        copy2(contact_output, backup)
    with ZipFile(contact_output, "w", ZIP_DEFLATED) as archive:
        for path in sorted(contact.rglob("*")):
            if path.is_file() and "tests" not in path.relative_to(contact).parts:
                archive.write(path, path.relative_to(contact.parent))
    with ZipFile(contact_output) as archive:
        assert archive.testzip() is None
        assert "kekidi-contact/kekidi-contact.php" in archive.namelist()
        assert "kekidi-contact/view.js" in archive.namelist()
        assert all(name.startswith("kekidi-contact/") for name in archive.namelist())

    scrutins = root / "wordpress/kekidi-scrutins"
    scrutins_output = root / "wordpress/extensions/kekidi-scrutins.zip"
    if scrutins_output.exists():
        backup = scrutins_output.parent / "backups" / f"kekidi-scrutins-{datetime.now():%Y%m%d-%H%M%S-%f}.zip"
        backup.parent.mkdir(parents=True, exist_ok=True)
        copy2(scrutins_output, backup)
    with ZipFile(scrutins_output, "w", ZIP_DEFLATED) as archive:
        for path in sorted(scrutins.rglob("*")):
            if path.is_file() and "tests" not in path.relative_to(scrutins).parts:
                archive.write(path, path.relative_to(scrutins.parent))
    with ZipFile(scrutins_output) as archive:
        assert archive.testzip() is None
        for name in ("kekidi-scrutins.php", "block/block.json", "block/render.php", "block/editor.js", "block/view.js", "block/view.asset.php", "vendor/confetti-2.2.0.min.js", "vendor/confetti-LICENSE", "includes/storage.php", "assets/logos/SOURCES.md"):
            assert f"kekidi-scrutins/{name}" in archive.namelist()
        assert all(name.startswith("kekidi-scrutins/") and "tests" not in Path(name).parts for name in archive.namelist())

    print(f"Extension scrutins créée : {scrutins_output} ({scrutins_output.stat().st_size:,} octets).")
    print(f"Vérifications locales : {block_count} blocs, {len(items)} réponses, styles et polices présents ; ZIP intègre.")
    print(f"Archive créée : {output} ({output.stat().st_size:,} octets).")
    print(f"Extension créée : {plugin_output} ({plugin_output.stat().st_size:,} octets).")
    print(f"Extension quiz créée : {quiz_output} ({quiz_output.stat().st_size:,} octets).")
    print(f"Extension contact créée : {contact_output} ({contact_output.stat().st_size:,} octets).")
    print("Ces contrôles ne remplacent pas un essai dans WordPress et son éditeur.")


if __name__ == "__main__":
    main()
