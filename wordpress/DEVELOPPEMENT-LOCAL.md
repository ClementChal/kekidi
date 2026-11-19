# Développer Kekidi avec WordPress Studio

## Sources

Le thème se maintient directement dans `wordpress/kekidi/` : `style.css`, `theme.json`, `functions.php`, `templates/`, `parts/`, `patterns/` et `assets/`. Les quatre extensions possèdent leurs propres dossiers `wordpress/kekidi-*`. Aucun générateur de thème ni script Python n’est requis.

Lire [DESIGN.md](../DESIGN.md) avant de modifier la présentation. Les contenus éditoriaux, réglages, messages et instantanés de scrutins restent en base ; ne pas les remplacer pour tester une modification de code.

## Préparer l’environnement

Créer ou sélectionner un site Studio dédié aux tests. Studio fournit PHP et WP-CLI ; l’outil système `zip` doit être installé. Avec WP-CLI 2.12, installer le package `wp-cli/dist-archive-command:3.1.0`. Node 24 sert aux tests JavaScript. La configuration complète est décrite dans le [README](../README.md).

## Modifier, empaqueter, installer

1. Modifier les fichiers sources et augmenter la version dans `kekidi/style.css` ou le fichier PHP principal de l’extension concernée.
2. Constituer les ZIP avec `dist-archive`, selon les commandes du README. Les livraisons sont dans `dist/`, avec leur version dans le nom ; ne pas écraser une archive publiée.
3. Exécuter `eval-file /CHEMIN_DEPOT/wordpress/tests/distribution.php` avec l’outil Studio `wp_cli` sur le site de test. Les cinq ZIP doivent correspondre aux versions des sources.
4. Installer les ZIP concernés avec remplacement, par l’administration ou l’outil `wp_cli` :

   ```text
   theme install /CHEMIN_DEPOT/dist/kekidi-1.4.0.zip --force --activate
   ```

5. Exécuter les tests adaptés et vérifier dans l’éditeur et le navigateur. Valider les blocs modifiés avec `validate_blocks` avant publication.

Le dépôt et le site installé sont distincts : une modification de fichier dans le dépôt ne met pas automatiquement Studio à jour. Ne pas réimporter le XML à chaque correction du thème.

## Préserver l’édition de contenu

Les changements de modèles, parties et styles faits dans l’Éditeur de site peuvent primer sur les fichiers du thème. Vérifier les personnalisations `wp_template` et `wp_template_part` avant une correction ; exporter et intégrer les changements utiles aux sources sans perdre les autres modifications éditoriales.

## Livrer

Après validation, sauvegarder la base et les fichiers du site cible puis remplacer les seuls ZIP concernés. Conserver les versions précédentes dans les Releases et une sauvegarde complète séparée. La commande d’archives n’effectue ni sauvegarde de site ni déploiement.

Les anciens ZIP et dossiers de backups locaux restent conservés. Les tests navigateur contiennent des URL et identifiants fixes : les adapter au site choisi. Exécuter aussi `eval-file /CHEMIN_DEPOT/wordpress/verify-theme.php` pour l’accueil, les cartes, les droits et la navigation.
