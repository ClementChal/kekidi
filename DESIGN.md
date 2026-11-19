---
name: Kekidi?!
description: Une affiche citoyenne accueillante, au service de réponses lisibles et de questions partagées.
colors:
  bg: "oklch(from #FFF8F0 l c h)"
  surface: "oklch(100% 0 0)"
  fg: "oklch(from #1F1F1F l c h)"
  muted: "oklch(42% .014 40)"
  accent: "oklch(from #FFB5D8 l c h)"
  surface-action: "oklch(from #B5E6D3 l c h)"
  surface-note: "oklch(from #FFE89A l c h)"
  surface-info: "oklch(from #B5DBFF l c h)"
typography:
  display:
    fontFamily: "Nunito, Trebuchet MS, sans-serif"
    fontSize: "clamp(2.75rem,5.5vw,5rem)"
    fontWeight: 800
    lineHeight: 1.08
    letterSpacing: "-.025em"
  title:
    fontFamily: "Nunito, Trebuchet MS, sans-serif"
    fontSize: "clamp(2rem,4vw,3rem)"
    fontWeight: 800
    lineHeight: 1.12
  body:
    fontFamily: "DM Sans, Verdana, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  reading:
    fontFamily: "DM Sans, Verdana, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.65
  label:
    fontFamily: "DM Sans, Verdana, sans-serif"
    fontSize: ".9375rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: ".02em"
rounded:
  control: "999px"
  card: "1.5rem"
  panel: "2rem"
spacing:
  space-1: ".5rem"
  space-2: "1rem"
  space-3: "1.5rem"
  space-4: "2rem"
  space-5: "3rem"
  space-6: "5rem"
components:
  button-navigation:
    backgroundColor: "{colors.surface-action}"
    textColor: "{colors.fg}"
    rounded: "{rounded.control}"
    padding: ".8rem 1.4rem"
  button-submit:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.fg}"
    rounded: "{rounded.control}"
    padding: ".8rem 1.4rem"
  question-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.fg}"
    rounded: "{rounded.card}"
    padding: "1.5rem"
---

# Design system Kekidi?!

## Overview

**Creative North Star: « Une affiche citoyenne accueillante »**

Kekidi?! prolonge une question croisée dans la rue : une affiche attire l’attention, puis une page donne une réponse ou invite à répondre à un quiz. Le dessin reste chaleureux, direct et lisible sur téléphone, y compris debout et à l’extérieur.

L’identité associe papier crème, encre sombre, lettres rondes, aplats pastel, contours francs et ombres nettes. L’accueil porte l’expression de marque ; les articles privilégient la lecture ; les quiz et le contact rendent chaque état compréhensible.

Cette référence est autonome : aucune application de maquette ni aucun dossier extérieur au dépôt n’est nécessaire. Elle conserve les invariants visuels et les adaptations WordPress existantes. Le frontmatter décrit les valeurs par défaut observées ; il ne charge aucun thème à l’exécution.

- [design-system.md](design-system.md) : anatomie, états, responsive, accessibilité et maintenance.
- [design-tokens.json](design-tokens.json) : inventaire des variables CSS partagées.
- [brand-spec.md](brand-spec.md) : identité et ton de marque.
- [style.css](wordpress/kekidi/style.css) : styles communs, états et adaptations WordPress ; [functions.php](wordpress/kekidi/functions.php) : chargement des ressources et intégration du thème.
- [theme.json](wordpress/kekidi/theme.json) : palette et polices exposées dans Gutenberg.

## Colors

| Couleur | Token CSS | Emploi |
| --- | --- | --- |
| Papier crème | `--bg` | Fond général et champs |
| Blanc | `--surface` | Cartes, pilules et contrôles |
| Encre | `--fg`, `--border` | Texte, icônes, contours et ombres |
| Encre atténuée | `--muted` | Contexte et légendes |
| Rose | `--accent` | Signature, affiche, validation quiz/contact, résultat incorrect |
| Menthe | `--surface-action` | Navigation principale, fonctionnement, contact, corrigé juste |
| Jaune | `--surface-note` | Réponse courte, survol de navigation, avertissement, réussite du quiz classique |
| Ciel | `--surface-info` | Choix des quiz, catégories, abstentions du graphique |

La couleur dépend du composant : le jaune ne signifie pas systématiquement une alerte. Les badges alternent jaune, menthe et ciel sans correspondance métier. Dans les graphiques, **pour = menthe, contre = rose, abstention = ciel**. Les textes et contours complètent toujours les couleurs.

Les slugs WordPress sont `cream`, `white`, `ink`, `pink`, `green`, `yellow`, `blue`. Les variables de présentation suivent les préréglages de couleur et de police de l’Éditeur de site. Toute évolution maintient leur correspondance entre `theme.json`, les styles et les documents.

## Typography

Nunito 800 pour marque, titres et réponse courte. DM Sans 400 pour la lecture ; DM Sans 600 pour actions et labels. Les polices et licences restent locales dans `wordpress/kekidi/assets/`, avec `font-display:swap`. Fallbacks CSS : `Trebuchet MS` pour Nunito, `Verdana` pour DM Sans, puis `sans-serif`.

| Rôle effectif | Taille et interligne |
| --- | --- |
| H1 accueil, >600 px | `clamp(2.75rem,5.5vw,5rem)` / 1,08 |
| H1 accueil, ≤600 px | `clamp(2.6rem,10.5vw,3.7rem)` / 1,08 |
| Sections accueil | 32 px ; 28 px sur mobile / 1,12 |
| Question de carte | 26 px ; 24 px sur mobile / 1,2 |
| Question de réponse | `clamp(1.5rem,3.2vw,2rem)` ; 24 px sur mobile / 1,25 |
| Réponse courte | `clamp(2.5rem,6.5vw,4.5rem)` ; mobile `clamp(2.5rem,11vw,3.5rem)` / 1,12 |
| Lecture d’article | 18 px / 1,65 ; H2 24 px, H3 20 px, H4–H6 18 px |
| Introduction et collectif | 18 px / 1,6 |
| Options des quiz | 16 px ; contexte de scrutin 14 px ; valeurs 13 px |
| Boutons | 15 px / 1,4, graisse 600, espacement `.02em` |
| Badges et légendes | Respectivement 12 px et 13 px |

Équivalences pour une racine de 16 px ; respecter les préférences du navigateur. Les tokens généraux ne pilotent pas toutes les tailles locales. Un seul H1 par page ; titres équilibrés et retours autorisés ; aucune troncature de question ou réponse. Corps limité à 65ch quand le composant le prévoit. Les styles de lecture restent appliqués aux blocs ajoutés dans Gutenberg et excluent les interfaces de quiz.

## Layout

Échelle partagée de 8, 16, 24, 32, 48 et 80 px. Les exceptions restent locales : gouttière mobile de 20 px, espacement de choix de 10 px, rayon de champ de 16 px.

| Largeur | Composition |
| --- | --- |
| ≤600 px | Gouttières 20 px. Accueil vertical ; navigation questions/collectif sur une ligne, fonctionnement dessous ; CTA pleine largeur. Cartes, étapes, collectif et footer empilés. |
| 601–800 px | Gouttières 24 px. Hero vertical, affiche centrée limitée à 460 px, cartes en liste ; trois étapes côte à côte, collectif en deux colonnes. |
| 801–1499 px | Conteneur 1184 px, gouttières 32 px. Hero `1.15fr / 1fr`, cartes en trois colonnes, fonctionnement `.8fr / 1.4fr`, collectif en deux colonnes. |
| ≥1500 px | Conteneur d’accueil élargi à 1280 px. |

Conteneurs en `border-box`, padding compris. Réponses, contact et scrutins : maximum 820 px. Adaptation du padding des choix de quiz à 768 px, distincte du breakpoint de grille à 800 px.

L’accueil enchaîne en-tête, accroche et affiche, cartes, fonctionnement, collectif, pied de page. Une réponse classique commence par sa question puis sa réponse courte, sans introduction intercalée. Ne pas masquer un défaut de grille avec `overflow-x:hidden`.

## Elevation & Depth

Ombres d’encre nettes et sans flou : `5px 5px 0 var(--border)` pour cartes et boutons, `8px 8px 0 var(--border)` pour grands panneaux ; 7 px au survol d’un bouton, 2 px en pression. Préserver l’espace pour ombres et focus.

Affiche inclinée de 3°, 2° sur mobile. Le panneau de réponse courte possède déjà une inclinaison de −1°, −0,5° sur mobile ; le code 404 de −4°. Ce sont des positions fixes. Ne pas étendre cet effet aux listes de choix ou au texte courant.

## Shapes

Actions et badges en pilules de 999 px ; cartes à rayon 24 px ; grands panneaux à 32 px, ramenés à 24 px sur mobile selon le composant. Champs à rayon 16 px ; contrôles de quiz de 24 × 24 px et rayon 8 px. Contours structurants de 2 px ; badges à 1 px.

Le symbole « ?! » est dans un cercle rose incliné de −12°, de 40 px sur ordinateur et 32 px sur mobile. Flèches SVG : boîte 24 px, rendu 21 px, trait 1,8 px, `currentColor`, extrémités arrondies. Retour vers la gauche, lecture et suite vers la droite.

## Components

### Navigation, boutons et cartes

Les liens naviguent ; les boutons exécutent une action. Navigation d’au moins 44 px ; boutons principaux d’au moins 52 px. Menthe pour explorer/revenir, rose pour valider, blanc pour une pilule secondaire. Focus encre 3 px décalé de 5 px ; survol de navigation jaune et pression avec réduction d’ombre. Les choix de quiz restent immobiles.

Une carte contient catégorie, question et action « Lire la réponse » ou « Répondre au quiz ». Un seul lien couvre toute la surface et nomme la question. Fond blanc, rayon 24 px, padding 24 px ou 20 px sur mobile ; action en bas, soulignée sur fond menthe au survol/focus. Aucun lien imbriqué.

### Réponse et sources

Question H1, panneau jaune, explication, sources éventuelles, navigation. Aucun panneau vide ni source inventée. Liens éditoriaux sur fond menthe, jaunes au survol, roses à l’activation ; soulignement distinct après visite. Les sources externes ouvrent un nouvel onglet avec `noopener noreferrer`.

Sur mobile, retour classique pleine largeur puis question suivante. Sur les quiz avant correction, retour secondaire à gauche et suite regroupée à droite avec flèche, écart 8 px. Navigation cyclique ; lien suivant absent si un seul article existe.

### Quiz classique

Une à cinq propositions, au moins une correcte. WordPress utilise une sélection exclusive et une consigne adaptée lorsqu’une seule réponse est correcte. Choix ciel clair, contrôle à droite ; sélection ciel soutenu ; validation rose. Sans sélection : message jaune et focus sur le premier choix.

Après validation : résultat jaune si juste, rose si faux ; bonnes réponses menthe et cochées ; mauvaises réponses décochées ; oublis et choix erronés entourés de tirets de 2 px. Indications de correction accessibles aux lecteurs d’écran. Choix verrouillés, bouton masqué, focus sur le résultat ; explication et sources révélées, inaccessibles au clavier tant qu’elles sont cachées.

WordPress enregistre seulement le dernier booléen juste/faux dans le navigateur, après validation ; le rechargement présente toujours un formulaire vierge. Réussite complète : une seule gerbe de 180 confettis dans les quatre pastels, sauf réduction des animations. Un échec décoratif ne bloque jamais le résultat.

### Quiz de scrutin

Contexte, mesure à reconnaître, groupes, compteur, validation rose. Nom, emblème local de 40 px et case à droite de chaque option. Après validation : résultat menthe ou rose, graphique, explication. Pourcentages à une décimale, chiffres tabulaires à 13 px ; effectifs accessibles par une divulgation native.

Majorité strictement supérieure à 50 % des pour + contre + abstentions ; absents et non-votants exclus. Sens demandé pour ou contre. WordPress autorise une sélection vide, juste seulement si aucun groupe n’atteint le seuil. Non-inscrits dans les totaux sans devenir une option. Graphique trié par pourcentage dans le sens demandé, avec libellés explicites.

Explication en blocs Gutenberg modifiables : titre, paragraphes, source et méthode. Après personnalisation, ne pas réinjecter de paragraphes automatiques. Logos locaux, repli vers le sigle ; provenance dans [SOURCES.md](wordpress/kekidi-scrutins/assets/logos/SOURCES.md).

### Contact

Titre « À vous de poser la question. », introduction, formulaire menthe ; objet, e-mail, question ; bouton rose « Valider mon message ». E-mail 52 px, sélecteur natif 56 px, zone de texte 160 px redimensionnable. Labels visibles et erreurs associées aux champs ; focus sur la première erreur.

Envoi : « Envoi en cours… », bouton désactivé et double clic bloqué. Échec : panneau rose, texte conservé et « Réessayer l’envoi ». Succès : introduction et formulaire remplacés par un panneau menthe, icône, confirmation et retour aux questions. Forminator et Kekidi Contact valident côté serveur et enregistrent réellement les messages, sans notification e-mail par défaut.

### États secondaires et mouvement

404 : nombre rose à contour sombre, titre « Cette page n’a pas trouvé sa question. », explication et retour ; deux colonnes puis pile sur mobile. Footer : marque, phrase de signature et lien « Une question ? » vers `/contact/`.

Transitions de 140–240 ms avec `cubic-bezier(.23,1,.32,1)`. `prefers-reduced-motion:reduce` supprime transitions, animations, déplacements interactifs et confettis. Les couleurs forcées rétablissent les contrôles natifs et des contours de graphique perceptibles.

## Do's and Don'ts

### À faire

- Maintenir contraste encre/pastels, polices locales, contours et ombres nets.
- Préserver les trois liens de navigation sur mobile et les retours naturels des titres.
- Utiliser des blocs éditables et un seul H1 par page.
- Vérifier clavier, focus, noms accessibles, absence de débordement à 320/390/1440 px et réduction des mouvements.
- Actualiser ensemble `theme.json`, styles, tokens et documents lors d’une évolution de règle.

### À éviter

- Ajouter dégradés, ombres floues, mascottes ou animations décoratives sans besoin validé.
- Confondre catégorie, action et résultat juste/faux à travers leur couleur.
- Faire dépendre une règle d’une application de maquette ou d’un chemin personnel.
- Réintroduire un envoi simulé pour le contact ou supprimer les comportements WordPress actuels pour imiter une ancienne démonstration.
- Modifier uniquement le thème installé sans reporter le changement dans le dépôt, ou considérer le XML comme une sauvegarde des personnalisations de l’Éditeur de site.
