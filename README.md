# Kekidi?! — WordPress

Kekidi?! relie des questions affichées dans la rue à des réponses consultables sur téléphone. Ce dépôt contient le thème à blocs, quatre extensions maison, leurs tests et la documentation du projet.

Le thème et les extensions se maintiennent directement dans leurs fichiers WordPress. Les livraisons sont des ZIP versionnés constitués avec WP-CLI. L’installation WordPress, les extensions tierces, la base, les médias et les comptes se préparent séparément.

## Démarrer

1. Installer **WordPress Studio** et disposer de l’exécutable système **zip**. Studio fournit PHP et WP-CLI. Node.js 24 est nécessaire aux tests JavaScript, pas à la création des archives. Python et uv ne sont pas requis.
2. Créer un site Studio vide, dans un dossier distinct du dépôt. Ne pas utiliser le site de production pour les tests.
3. Installer la commande d’archives compatible avec le WP-CLI 2.12 fourni par Studio :

   ```text
   package install wp-cli/dist-archive-command:3.1.0
   ```

4. Constituer les archives selon **Produire une livraison**, puis exécuter le test de distribution.
5. Installer le thème, les extensions et les contenus en suivant [l’installation sur un WordPress vierge](wordpress/INSTALLATION-VIERGE.md). Installer **Forminator avant Kekidi Contact**.

Les commandes WP-CLI ci-dessous sont les arguments `command` de l’outil MCP Studio `wp_cli` : cibler explicitement son site de test, remplacer `/CHEMIN_DEPOT` par le chemin absolu du dépôt, sans préfixe `wp`. Pour un WP-CLI installé dans le terminal, ajouter le préfixe `wp` ; son installation nécessite PHP CLI dans le PATH. La connexion MCP est décrite dans [MCP.md](wordpress/MCP.md).

La commande additionnelle est installée dans le compte utilisateur, pas dans WordPress. Elle exige aussi l’exécutable `zip` accessible aux sous-processus. Sous Debian/Ubuntu : `sudo apt install zip`. Sa version 3.1.0 est testée avec WP-CLI 2.12 ; ne pas la remplacer aveuglément par une branche exigeant WP-CLI 3.

## Environnement et dépendances

| Élément | Version testée | Rôle |
| --- | --- | --- |
| WordPress / PHP | 7.1.5 / 8.4.27 | Site Studio, avec DOM, SimpleXML et ZIP disponibles |
| WP-CLI | 2.12.0 | Installation, contrôles et distribution |
| dist-archive-command | 3.1.0 | Package WP-CLI additionnel pour créer les ZIP |
| Node.js | 24.18.0 | Tests JavaScript sans compilation |
| Thème Kekidi | 1.4.0 | Présentation, modèles et compositions |
| Kekidi Contact | 2.0.0 | Création du formulaire et adaptation au thème |
| Forminator (`forminator`) | 1.58.4 | Dépendance obligatoire de Contact, installée séparément |
| Kekidi Quiz | 1.1.1 | Bloc de quiz classique et correction |
| Kekidi Scrutins | 1.0.1 | Bloc de scrutin, import contrôlé et instantanés |
| Kekidi Super éditeur | 1.0.0 | Rôle optionnel avec accès à l’Éditeur de site |
| Koko Analytics (`koko-analytics`) | 2.5.3 | Statistiques optionnelles, installées séparément |
| WordPress Importer | 0.9.7 | Import initial du XML, puis désactivation |
| daisyUI / confettis | 5.6.3 / @hiseb/confetti 2.2.0 | Ressources embarquées avec leurs licences |

Il n’y a pas d’étape de compilation npm ou Composer dans ce projet. Les blocs utilisent les API JavaScript WordPress et leurs fichiers de dépendances `.asset.php`. Le CSS daisyUI est précompilé : Tailwind n’est pas nécessaire. Composer est facultatif pour ajouter les outils de qualité PHP.

Le thème et les extensions déclarent WordPress 6.6 et PHP 7.4 comme minima ; ces en-têtes ne prouvent pas la compatibilité de toutes les fonctions sur ces versions. Les versions du tableau décrivent l’environnement effectivement testé, pas les dernières versions publiées. Studio utilise SQLite ; tester aussi les parcours concernés sur l’hébergement cible MySQL/MariaDB.

## Où modifier quoi

| Besoin | Source |
| --- | --- |
| Styles, responsive, états et version du thème | [wordpress/kekidi/style.css](wordpress/kekidi/style.css) |
| Palette, polices et réglages de l’éditeur | [theme.json](wordpress/kekidi/theme.json) |
| Intégration du thème et chargement des ressources | [functions.php](wordpress/kekidi/functions.php) |
| Structure des pages, en-têtes, footer, compositions | [templates/](wordpress/kekidi/templates/), [parts/](wordpress/kekidi/parts/), [patterns/](wordpress/kekidi/patterns/) |
| Polices, daisyUI et préchargement | [assets/ du thème](wordpress/kekidi/assets/) |
| Contact, quiz et scrutins | [kekidi-contact/](wordpress/kekidi-contact/), [kekidi-quiz/](wordpress/kekidi-quiz/), [kekidi-scrutins/](wordpress/kekidi-scrutins/) |
| Rôle Super éditeur | [kekidi-super-editeur.php](wordpress/kekidi-super-editeur/kekidi-super-editeur.php) |
| Articles, pages et catégories du site | Administration WordPress et blocs Gutenberg |
| Champs et labels du formulaire existant | Forminator ; la création initiale est définie dans [form.php](wordpress/kekidi-contact/form.php) |
| Réponses initiales de démonstration | [reponses-kekidi.xml](wordpress/reponses-kekidi.xml) ; fixture de contrôle dans [reponses.json](wordpress/tests/fixtures/reponses.json) |
| Règles visuelles | [DESIGN.md](DESIGN.md), [design-system.md](design-system.md), [brand-spec.md](brand-spec.md) |

Les fichiers du thème sont les sources directement modifiables. Modifier la version dans l’en-tête de `style.css`, et les versions d’extensions dans leurs fichiers PHP principaux. Ne pas supprimer les dossiers `vendor/` embarqués dans les extensions : ils contiennent du code et des licences nécessaires.

Le XML est un jeu initial indépendant du thème. Le modifier ne met à jour aucun article installé. Articles courants, messages Contact et instantanés de scrutins vivent en base. Les personnalisations de modèles, parties et styles enregistrées dans **Apparence → Éditeur** peuvent primer sur les fichiers : les récupérer dans les sources en préservant le travail éditorial avant une livraison.

## Produire une livraison

Depuis Studio, exécuter les cinq commandes suivantes avec le chemin du dépôt. Les noms incluent les versions actuelles ; les adapter après une modification de code :

```text
dist-archive /CHEMIN_DEPOT/wordpress/kekidi /CHEMIN_DEPOT/dist/kekidi-1.4.0.zip --create-target-dir
dist-archive /CHEMIN_DEPOT/wordpress/kekidi-contact /CHEMIN_DEPOT/dist/kekidi-contact-2.0.0.zip --create-target-dir
dist-archive /CHEMIN_DEPOT/wordpress/kekidi-quiz /CHEMIN_DEPOT/dist/kekidi-quiz-1.1.1.zip --create-target-dir
dist-archive /CHEMIN_DEPOT/wordpress/kekidi-scrutins /CHEMIN_DEPOT/dist/kekidi-scrutins-1.0.1.zip --create-target-dir
dist-archive /CHEMIN_DEPOT/wordpress/kekidi-super-editeur /CHEMIN_DEPOT/dist/kekidi-super-editeur-1.0.0.zip --create-target-dir
```

Chaque composant possède un `.distignore` pour exclure tests, README de développement, configurations locales et dépendances de développement. Les ressources, bibliothèques et licences embarquées restent incluses. Ne pas écraser une livraison publiée : augmenter la version, conserver le ZIP précédent et omettre `--force`. Si l’archive existe déjà, WP-CLI demande confirmation ; l’appel MCP sans entrée interactive échoue sans la remplacer. Pour un essai local non publié, déplacer l’archive avant de la reconstruire.

La création des archives ne lance pas les tests, ne produit pas une sauvegarde de base, n’installe rien dans Studio et ne déploie rien en production. Exécuter ensuite le contrôle :

```text
eval-file /CHEMIN_DEPOT/wordpress/tests/distribution.php
```

Il vérifie la structure des blocs, les ressources et références du thème, le XML initial, Forminator, les droits déclarés du rôle et les cinq ZIP correspondant aux versions des sources. Il compare le contenu de chaque fichier distribué et refuse les fichiers de développement dans les archives. Le contrôle PHP ne remplace pas la validation des blocs dans Gutenberg.

Les ZIP sont dans `dist/`, ignoré par Git. Les joindre à une Release GitHub pour une livraison identifiée et conservée.

## Installer et développer dans Studio

Studio installe WordPress dans un dossier distinct du dépôt. Une modification des sources n’y est pas appliquée automatiquement. Installer le ZIP local par **Apparence → Thèmes → Ajouter/Téléverser** ou **Extensions → Ajouter/Téléverser**, en remplaçant l’élément existant sans le supprimer.

Exemples via l’outil `wp_cli`, ciblé sur son site de test :

```text
theme install /CHEMIN_DEPOT/dist/kekidi-1.4.0.zip --force --activate
plugin install /CHEMIN_DEPOT/dist/kekidi-quiz-1.1.1.zip --force --activate
```

Pour une installation neuve : page **Accueil** vide choisie dans **Réglages → Lecture** ; le modèle rend directement la composition `kekidi/accueil`. Importer le XML une seule fois, passer l’article d’exemple en brouillon et régler les permaliens `/reponses/%postname%/`.

Installer Forminator avant Kekidi Contact, puis ouvrir l’administration en administrateur pour créer le formulaire **Contact Kekidi**. Créer `/contact/`, choisir le modèle **Contact Kekidi** et insérer **Page Contact Kekidi**. Le formulaire reçoit un ID propre au site ; ne pas réutiliser celui d’un autre environnement.

Utiliser un site dédié aux tests et relever son URL dans Studio. Son nom, son dossier et son port dépendent de chaque environnement. Le bouton **WP Admin** ouvre l’administration ; aucun mot de passe ne doit être conservé dans le dépôt.

## Tests

### JavaScript sans WordPress

Depuis la racine du dépôt :

```sh
node wordpress/kekidi-quiz/tests/runtime.mjs
```

Ce test vérifie les 32 sélections, le stockage booléen, le formulaire vierge au rechargement et le fonctionnement sans stockage disponible.

### Intégration WordPress

Démarrer le site de test avec les extensions nécessaires actives, puis exécuter dans l’outil `wp_cli` :

```text
eval-file /CHEMIN_DEPOT/wordpress/verify-theme.php
eval-file /CHEMIN_DEPOT/wordpress/kekidi-quiz/tests/wordpress.php --user=admin
eval-file /CHEMIN_DEPOT/wordpress/kekidi-scrutins/tests/wordpress.php --user=admin
eval-file /CHEMIN_DEPOT/wordpress/kekidi-scrutins/tests/data.php --skip-plugins=kekidi-scrutins --user=admin
```

Adapter `admin` au compte du site. Les contrôles d’intégration créent et suppriment des articles et comptes temporaires : utiliser le site de test. `data.php` charge directement ses sources ; `--skip-plugins=kekidi-scrutins` évite une double déclaration avec l’extension installée. Ce test utilise des fixtures sans accès réseau.

Pour tout bloc ajouté ou modifié, utiliser `validate_blocks` avec le thème et les extensions actifs avant de publier les contenus. Les scripts navigateur utilisent Chromium/CDP et contiennent des URL, ports et IDs fixes : les adapter à son site avant exécution. Les [tests Contact](wordpress/kekidi-contact/README.md) créent un message fictif et nécessitent un dispositif temporaire de blocage/comptage des e-mails, à retirer après vérification.

### Recette fonctionnelle

Vérifier les pages touchées en visiteur et avec le rôle éditorial adapté : accueil et liens, réponses, quiz juste/faux/vide, rechargement vierge, import réel de scrutin, texte éditorial préservé, contact invalide, panne/réessai et message présent dans Forminator. Contrôler mobile 320/390 px et ordinateur 1440 px, clavier, focus, réduction des animations et Gutenberg.

La recette détaillée figure dans [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md). Safari, téléphone physique et hébergement cible/MySQL restent à vérifier séparément. Le test du thème contrôle l’accueil statique, sa composition, les cartes, les droits Éditeur et la navigation cyclique. Aucun lint global, aucune CI et aucune matrice complète de versions PHP ne sont encore configurés.

## Données, permissions et sécurité

Les réponses et quiz sont des articles WordPress. Le navigateur conserve seulement le dernier résultat booléen du quiz, sans envoyer les choix au serveur. Les instantanés immuables de scrutins sont des métadonnées protégées : ils doivent être inclus dans une sauvegarde de base, le seul bloc HTML ne suffit pas.

Forminator conserve les messages Contact ; les notifications e-mail sont désactivées initialement et l’IP est retirée des données du message par Kekidi Contact. L’accès aux soumissions se configure séparément dans Forminator. Le rôle Super éditeur donne accès à l’Éditeur de site et à Koko, sans gestion des comptes ou extensions ; il n’accorde pas automatiquement la lecture des messages.

Les imports de scrutins requièrent les permissions d’édition de l’article et des accès réseau aux sources contrôlées. Le jeu utilise ensuite les données et logos locaux, sans requête aux sources officielles. Préserver contrôles de droits, validation des données, échappement des sorties et protections des actions ; un nonce ne remplace pas une autorisation.

## Livrer et revenir en arrière

1. Modifier directement les sources et augmenter les versions concernées. La version du thème sert au cache des styles ; vérifier aussi les versions des ressources des extensions.
2. Produire les archives, vérifier leur distribution et exécuter les tests adaptés sur Studio.
3. Sauvegarder **la base et les fichiers du site cible**, dont médias, messages et instantanés, avant mise à jour.
4. Remplacer uniquement les thèmes/extensions concernés, puis purger le cache et vérifier le résultat sur l’hébergement cible.

Ne pas remplacer la base de production par celle de Studio ni réimporter le XML à chaque livraison. Un ZIP de code ne restaure pas les contenus et ne doit pas les réinitialiser. Un retour arrière réinstalle une version de code compatible ; si une évolution a modifié les données, prévoir une restauration cohérente et son impact sur les nouveaux contenus.

Les thèmes, extensions et bibliothèques embarquées ne se mettent pas tous à jour ensemble :

- **daisyUI** : remplacer `wordpress/kekidi/assets/daisyui.css` par une version officielle précise avec sa licence, augmenter la version du thème et vérifier site/Gutenberg. Le fichier `assets/kekidi.css` à l’intérieur du thème est une ressource de référence conservée ; les styles réellement chargés se modifient dans `style.css`.
- **Confettis** : mettre à jour les bibliothèques et licences des `vendor/` concernés, puis vérifier les deux quiz.
- **Forminator/Koko/WordPress** : tester les mises à jour officielles et revoir les parcours affectés ; actualiser les versions testées dans la documentation.
- **WP-CLI/dist-archive** : vérifier leur compatibilité avant de mettre à jour la chaîne de livraison.

## Sauvegardes, Git et contribution

La commande d’archives ne crée pas de copie automatique de l’ancien ZIP : les noms versionnés et l’absence de `--force` protègent les livraisons existantes. Conserver les ZIP publiés dans les Releases et les sauvegardes complètes sur un support distinct. Les dossiers locaux `wordpress/themes/backups/` et `wordpress/extensions/backups/` peuvent contenir des archives conservées ; ils ne sont pas utilisés par la nouvelle procédure et ne sont pas supprimés.

Le `.gitignore` exclut `dist/`, ZIP, backups, bases, médias, caches, logs et configurations sensibles usuelles. Versionner les sources du thème et des extensions, `.distignore`, ressources, licences, tests et documentation. Ne pas ajouter mots de passe, clés, bases de production ni messages réels. Examiner les fichiers avant publication : les exclusions ne constituent pas un audit des secrets. Les dépendances conservent leurs licences ; aucune licence globale du dépôt n’est encore fournie.

Conserver la séparation entre présentation du thème et fonctionnalités des extensions. Ne pas modifier le cœur WordPress ni les fichiers d’extensions tierces pour adapter Kekidi. Suivre les conventions du fichier et les standards WordPress ; un changement ne doit pas réinitialiser les contenus éditoriaux, permissions ou instantanés existants.

Pour proposer une modification, expliquer le problème résolu, les sources touchées, les tests et limites, les versions à livrer et les effets éventuels sur les données.

| Document | Usage |
| --- | --- |
| [DESIGN.md](DESIGN.md), [design-system.md](design-system.md) | Identité, composants et états |
| [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md) | Installation neuve et recette |
| [DÉVELOPPEMENT LOCAL](wordpress/DEVELOPPEMENT-LOCAL.md) | Travail sur le thème et commandes de livraison |
| [Contact](wordpress/kekidi-contact/README.md), [Quiz](wordpress/kekidi-quiz/README.md), [Scrutins](wordpress/kekidi-scrutins/README.md) | Contrats des fonctionnalités |
| [MCP Studio](wordpress/MCP.md) | Connexion des outils |

Les rapports de recette décrivent les contrôles à leur date ; adapter chemins, ports et identifiants et refaire les contrôles concernés après modification. Références officielles : [structure d’un thème](https://developer.wordpress.org/themes/core-concepts/theme-structure/), [WP-CLI dist-archive](https://developer.wordpress.org/cli/commands/dist-archive/), [JavaScript des blocs](https://developer.wordpress.org/block-editor/getting-started/fundamentals/javascript-in-the-block-editor/), [standards PHP](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/), [sauvegardes](https://developer.wordpress.org/advanced-administration/security/backup/).
