# Développer Kekidi avec WordPress Studio

## Développer avec les sources liées

Depuis la racine du dépôt :

```sh
./scripts/dev-site.sh dev     # Démarre WordPress et le rechargement automatique
./scripts/dev-site.sh start   # Démarre WordPress seul
./scripts/dev-site.sh stop    # Arrête WordPress
```

[Site Studio](http://localhost:8885/) · [WP Admin](http://localhost:8885/wp-admin/) · [BrowserSync](http://127.0.0.1:3000/)

Modifie le thème dans `wordpress/kekidi/` et les extensions dans `wordpress/kekidi-*/`. Les liens symboliques rendent ces changements accessibles à Studio dès la requête suivante. Si le port change, lance `./scripts/dev-site.sh dev http://localhost:PORT`. **Ctrl+C** arrête le rechargement. Utilise ensuite `stop` pour arrêter WordPress.

Le site `~/Studio/kekidi-dev` autorise les fichiers externes pour suivre les liens. Évite de remplacer ou de supprimer les composants liés via WP Admin. Si un modèle ne change pas, vérifie les personnalisations enregistrées dans **Apparence → Éditeur**.

## Contrôler les changements

```sh
git status --short
git diff
node wordpress/kekidi-quiz/tests/runtime.mjs
composer install            # Première installation des outils PHP
composer lint:wp
```

Tests dans WordPress, avec le site démarré :

```sh
repo="$PWD"
dev="$HOME/Studio/kekidi-dev"

studio wp eval-file "$repo/wordpress/verify-theme.php" --path "$dev"
studio wp eval-file "$repo/wordpress/kekidi-quiz/tests/wordpress.php" --path "$dev" --user=admin
studio wp eval-file "$repo/wordpress/kekidi-scrutins/tests/wordpress.php" --path "$dev" --user=admin
studio wp eval-file "$repo/wordpress/kekidi-scrutins/tests/data.php" --path "$dev" --skip-plugins=kekidi-scrutins --user=admin
```

[Test navigateur et paramètres Chromium](../README.md#contrôles-github-et-standards-php) · [Références de design](../DESIGN.md)

## Construire et tester les ZIP

Augmente la version du composant modifié. Lance les commandes sur **Kekidi Recette ZIP**, séparé du site lié. Pour l’installation du thème, remplace `VERSION` par la version à tester :

```sh
repo="$PWD"
recette="$HOME/Studio/kekidi-recette-zip"
version=VERSION

studio start --path "$recette" --skip-browser --skip-log-details
studio wp package install wp-cli/dist-archive-command:3.1.0 --path "$recette"  # Une seule fois
studio wp eval-file "$repo/scripts/build-zips.php" --path "$recette"
studio wp theme install "$repo/dist/kekidi-$version.zip" --force --activate --path "$recette"
studio stop --path "$recette"
```

La commande valide les cinq ZIP et range leurs versions antérieures dans `dist/backup/`. Pour une extension, utilise `studio wp plugin install CHEMIN_DU_ZIP --force --activate --path "$recette"`. Installe Forminator avant Kekidi Contact.

[Commandes pour les cinq archives](../README.md#produire-une-livraison) · [Installation vierge](INSTALLATION-VIERGE.md)
