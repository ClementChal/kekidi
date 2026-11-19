# Développer Kekidi avec WordPress Studio

L'environnement de référence est le site **Kekidi** dans Studio :

- Site : `http://localhost:8881`
- Administration : `http://localhost:8881/wp-admin/`
- Dossier de l'installation : `<DOSSIER_SITE_STUDIO>`
- Connexion Codex : MCP `wordpress-studio`.

Le thème Kekidi utilise le modèle `front-page.html` de la version 1.1.3 comme base stable : ce modèle affiche le motif `kekidi/accueil` du thème, sans lire le contenu de la page **Accueil**. Les trois réponses sont importées et les contenus d'exemple WordPress sont en brouillon. Les permaliens utilisent `/reponses/%postname%/`. L'environnement Studio utilise SQLite ; le contenu public sur InfinityFree reste indépendant.

## Modifier et vérifier

Le code source du thème reste dans `build-wordpress.ts`. Les styles de référence restent dans `assets/kekidi.css`. Ne pas modifier la maquette pour l'adapter à un défaut WordPress : le thème doit reproduire son rendu. Le générateur reprend les styles de la maquette dans la feuille principale du thème et adapte seulement les conteneurs WordPress.

Après une correction, depuis le dossier du projet :

```sh
node build-wordpress.ts
uv run --no-project python package-wordpress.py
```

L'agent utilise ensuite le **MCP Studio**, outil `wp_cli`, sur le site `Kekidi`, pour installer localement le ZIP régénéré avec remplacement de la version existante :

```text
theme install <CHEMIN_PROJET>/wordpress/themes/kekidi-theme.zip --force --activate
```

Le dossier généré du projet et celui installé dans Studio sont deux copies distinctes. Une régénération ne met donc pas Studio à jour automatiquement : réinstaller le ZIP local après les modifications. Ne pas réimporter les réponses à chaque correction du thème.

Si le code des blocs change, valider les fichiers et compositions concernés avec l'outil MCP `validate_blocks`. Puis comparer les pages rendues aux fichiers HTML Open Design avec `inspect_design` et les captures sur ordinateur et mobile. Les fichiers HTML statiques et leurs styles doivent rester inchangés pendant cette comparaison.

Le test d'intégration se lance via `wp_cli`. Il contrôle la page d'accueil statique, les quatre sections éditables en contenu uniquement, les droits du rôle Éditeur, l'ordre des cartes, leurs liens uniques et la navigation cyclique :

```text
eval-file <CHEMIN_PROJET>/wordpress/verify-theme.php
```

Le rapport `verification-visuelle.json` conserve les résultats de comparaison de la maquette et du site Studio.

Les modifications effectuées dans **Apparence → Éditeur** vivent dans la base locale. Elles ne sont pas automatiquement récupérées par notre générateur et peuvent prendre le pas sur les fichiers du thème. Avant de modifier un modèle, vérifier les personnalisations `wp_template` et `wp_template_part` et préserver le travail éditorial existant.

## Mettre à jour le site public

Après validation locale, choisir la page **Accueil** comme page d'accueil dans **Réglages → Lecture**. Le modèle `front-page.html` rend lui-même le motif `kekidi/accueil` ; le contenu de la page ne doit pas être remplacé par un bloc de réponse. Sauvegarder le thème précédent et la base, puis installer le nouveau `wordpress/themes/kekidi-theme.zip` dans l'administration du site public en remplaçant le thème existant. Les anciennes archives sont rangées dans `wordpress/themes/backups/`. Augmenter la version du thème dans le générateur à chaque livraison pour renouveler les styles en cache.

Ne pas remplacer la base publique par celle de Studio, et ne pas réimporter le XML à chaque mise à jour du thème. Les contenus éditoriaux publics restent conservés. La synchronisation WordPress.com de Studio ne publie pas chez InfinityFree.

## Ancienne pile Docker

Le fichier Compose historique n’est pas repris dans cette copie. Utiliser un site Studio distinct pour tester cette copie ; consulter le README à la racine avant toute installation.
