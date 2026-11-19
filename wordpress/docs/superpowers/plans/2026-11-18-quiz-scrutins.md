# Quiz scrutins Implementation Plan

> **For agentic workers:** utiliser superpowers:executing-plans pour réaliser les tâches dans cette session, avec revue indépendante finale.

**Goal:** créer, importer et jouer des quiz de scrutins des deux chambres dans WordPress Studio, sans produire d’archive.

**Architecture:** un bloc Gutenberg référence un instantané protégé et immuable de l’article. Le PHP importe, normalise et compare les sources officielles ; le navigateur calcule le résultat depuis les décomptes du rendu. Les textes restent des blocs natifs.

**Tech Stack:** PHP 7.4+, API HTTP/REST WordPress, DOMDocument/XPath, Gutenberg sans compilation, JavaScript natif, CSS du thème.

**Spec:** `../specs/2026-11-18-quiz-scrutins.md`

## Global Constraints
- Aucune archive créée ; aucun changement en production.
- Préserver les articles, les permaliens, la page d’accueil, Contact et les quiz existants.
- Deux chambres ; sens pour/contre ; seuil strict >50 % ; validation vide toujours possible.
- Réseau uniquement à l’import, avec permissions par article et erreurs explicites.
- Maquette : `/home/cchalencon@corp.wisebim.fr/open-design/.od/projects/3a760bf8-a027-475e-9de4-bbc911a67bec`.

## Review Focus
- Données incohérentes, HTML inattendu, redirections ou réponses trop volumineuses : refus sans écraser un import valide.
- Utilisateur non autorisé ou import d’un autre article : refus.
- Réimport d’un article publié : ancienne référence publique intacte tant que l’article n’est pas enregistré.
- Aucun votant, majorité exactement 50 %, ensemble vide, nombres invalides : calcul déterministe et pas de division par zéro.
- Contenus anciens et logos manquants : lecture possible, fallback sigle, pas de mise en page cassée.

### Task 1: acquisitions et normalisation
Files: `kekidi-scrutins/includes/data.php`, `includes/sources.php`, `tests/data.php`, `tests/fixtures/*`.
Interfaces: `kekidi_scrutins_import(string $chamber, string $period, int $number): array`; `kekidi_scrutins_expected(array $groups, string $direction): array`.
- [x] Capturer les sources 7987, 4042 et Sénat 2025/33 ; écrire les assertions 313/199/5, DEM 28/1/5, Sénat 308 votants et LR 129 contre.
- [x] Observer l’échec, puis porter et vérifier le calcul et les parseurs ; comparer tous les groupes de l’Assemblée à la page officielle.
- [x] Tester les refus et les seuils 50 %, >50 %, zéro votant.

### Task 2: instantanés et permissions
Files: `kekidi-scrutins.php`, `includes/storage.php`, `tests/wordpress.php`.
Interfaces: routes `/kekidi/v1/scrutins/import` et `/kekidi/v1/scrutins/<post_id>/<snapshot_id>` ; `kekidi_scrutins_snapshot(int $post_id, string $id): ?array`.
- [x] Tests refus visiteur/auteur non propriétaire, droit éditeur, snapshot d’un autre article et référence inexistante.
- [x] Imports protégés, taille/durée bornées, stockage dédoublonné et immuable ; pas de requête externe au rendu.
- [x] Publication refusée si bloc incomplet, plusieurs quiz ou sens invalide ; brouillon conservé.

### Task 3: bloc et jeu
Files: `block/block.json`, `editor.js`, `editor.css`, `render.php`, `view.js`, `assets/logos/*`, `tests/browser.mjs`.
- [x] Contrôles natifs d’import (chambre/période/numéro), sens et présentation, choix facultatif d’un logo dans la médiathèque ; texte explicatif en InnerBlocks.
- [x] Rendu conforme à la maquette, validation toujours possible, tableau après résultat, erreur/oubli explicités, fallback sigles et focus accessible.
- [x] Test du vide juste/faux, des sélections exactes/fausses, pour/contre, du rechargement et de la non-régression des quiz existants.

### Task 4: thème et démonstration Studio
Files: `../assets/kekidi.css`, `../build-wordpress.ts`, `../DESIGN.md`, `README.md`, fichiers générés du thème.
- [x] Ajouter seulement les styles vote de la maquette et les adaptations de conteneurs nécessaires, sans changer les modèles d’accueil.
- [x] Installer dans Studio, valider les blocs puis créer trois articles d’exemple avec données réelles ; comparer à 390 et 1440 px.
- [x] Revue indépendante du périmètre puis correction des défauts importants et vérification finale.
- [x] Fournir les URLs et étapes pour les éditeurs ; laisser Studio actif. Ne pas exécuter `package-wordpress.py`.

## Journal
- Autorisation : l’utilisateur a approuvé les règles et les deux chambres ; il demande les exemples Studio avant toute archive.
- Décision : travail dans le dossier partagé demandé ; ne pas modifier le staging Git existant, ne pas créer de commit englobant les changements de l’utilisateur.
- Décision : instantanés immuables en métadonnées, références dans les révisions du bloc ; évite la mutation du publié lors d’un simple import et les signatures liées aux clés du site.

- Task 1 : contrôles PHP passés via Studio sur 7987, 4042 (capture disponible), Sénat 2025/33 ; les erreurs de source sont refusées.
- Task 2 : stockage et permissions passés via tests/wordpress.php ; identifiant de contenu immuable, import sans modification de post_content.
- Décision : aucune signature dépendant des clés du site ; les nombres restent uniquement en métadonnées protégées. Les références historiques survivent aux révisions du bloc.
- Task 3 : bloc, interface éditoriale et rendu écrits ; vérification navigateur en cours.

- Revue indépendante effectuée : droits GET liés au chemin, instantanés stockés sous clés séparées, prise en charge des dates « 1er » et groupes sans votants, titre officiel, mise à jour du titre prérempli après changement de scrutin, aperçu persistant des logos personnalisés et explications sans intégrations distantes. Corrections couvertes par les tests PHP.
- Tests navigateur passés aux trois largeurs : résultat juste/faux, sélection vide, rechargement vierge, localStorage booléen, logos de secours, sens contre, Sénat et import Gutenberg réel sans modification du contenu publié.
- Détecteur de design exécuté sur les fichiers UI modifiés : aucun signalement ; aucune suppression ajoutée.
- Limite : import réseau et rendu testés sous Studio/Chromium ; pas encore sur InfinityFree ni sur un appareil mobile physique. Aucune archive créée.

- Comparaison OpenDesign/Studio : options de 756 px à 1440 px et 350 px à 390 px, texte 16 px et bouton de même couleur ; aucun débordement. Captures initiales et résultats inspectées.
- Vérifications de non-régression passées : typographie des articles à 1440/390/320 px et dans Gutenberg, tests WordPress de l’ancien quiz, ses 32 sélections côté navigateur simulé. Le vrai bouton d’import Gutenberg a chargé 4042 et remplacé le titre automatique sans enregistrer le publié.
