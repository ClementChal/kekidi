# Kekidi?! — Guide de conception et d’implémentation

Ce guide complète [DESIGN.md](DESIGN.md), référence de l’identité, de la palette, des échelles et des composants. Il décrit la maquette sous forme de règles applicables dans ce dépôt, puis les comportements propres au WordPress actuel. Aucun fichier de l’ancien outil de conception n’est nécessaire.

## 1. Sources et ordre de priorité

| Ressource du dépôt | Responsabilité |
| --- | --- |
| [DESIGN.md](DESIGN.md) | Invariants visuels et valeurs par défaut |
| [functions.php](wordpress/kekidi/functions.php) | Chargement des ressources et intégration du thème |
| [Modèles](wordpress/kekidi/templates/) et [compositions](wordpress/kekidi/patterns/) | Structure des pages et contenus réutilisables, directement modifiables |
| [theme.json](wordpress/kekidi/theme.json) | Palette, polices et réglages du thème, directement modifiables |
| [style.css](wordpress/kekidi/style.css) | Styles effectivement livrés, avec adaptations finales |
| [design-tokens.json](design-tokens.json) | Inventaire documentaire des 42 variables CSS de base |
| [brand-spec.md](brand-spec.md) | Posture de marque et règles éditoriales |
| [reponses-kekidi.xml](wordpress/reponses-kekidi.xml) | Trois réponses initiales à importer ; pas le contenu courant de la base |
| [Contact](wordpress/kekidi-contact/README.md), [Quiz](wordpress/kekidi-quiz/README.md), [Scrutins](wordpress/kekidi-scrutins/README.md) | Contrats des fonctionnalités et tests |

Le CSS contient des surcharges successives ; conserver leur ordre. Pour connaître une valeur effective, lire la dernière règle applicable et les adaptations du thème, pas uniquement la déclaration initiale. Le JSON de tokens et le frontmatter de DESIGN.md ne sont pas des feuilles de style chargées à l’exécution.

En cas de divergence entre un ancien document et une règle réellement rendue, constater et documenter l’écart avant toute modification de l’interface. Une illustration historique n’autorise pas à remplacer un comportement fonctionnel actuel.

## 2. Identité et fondations

Direction : une affiche citoyenne accueillante, lisible et tactile. Le caractère repose sur les grandes lettres rondes, les aplats pastel, le contour d’encre et l’ombre décalée sans flou. Les pages de lecture gardent une colonne calme ; l’accueil peut porter l’affiche inclinée.

Les valeurs exactes de palette, polices, espacements et rayons sont dans DESIGN.md et l’inventaire des tokens. Les couleurs de base sont conservées en OKLCH relatif ; éviter des conversions approximatives. Texte et contours restent encre sombre sur fond clair. Les préférences de lecture du navigateur restent applicables.

### Correspondance de la bibliothèque et de WordPress

`--color-base-100/200/300` correspondent à papier/blanc/jaune ; `--color-base-content` et `--color-primary-content` à l’encre ; `--color-primary` au rose ; `--color-neutral` à l’encre. Malgré ce nom de bibliothèque, le bouton de navigation principal est **menthe**, car son composant possède une surcharge explicite. Une validation de quiz/contact reste rose.

La feuille principale du thème contient les styles communs et les adaptations WordPress. Les boutons sont ciblés par leurs classes natives WordPress. La variable `--surface` du quiz possède une adaptation locale pour conserver son blanc de référence. Ne pas enlever cette adaptation en remplaçant le composant par une classe générique de bibliothèque.

Les fichiers Nunito 800 et DM Sans 400/600 et leurs licences sont locaux. La pile monospace est déclarée pour les données techniques éventuelles ; elle ne devient pas une police de lecture.

## 3. Accueil et composants partagés

### Structure de page

1. Marque et navigation vers les questions, le collectif et le fonctionnement.
2. Hero : « Les questions sont dans la rue. Les réponses, ici. », introduction, bouton « Explorer les questions », affiche et légende.
3. Section « Ça mérite une question. » avec cartes de réponses et de quiz.
4. Bande menthe « De l’affiche au déclic. », trois étapes : question, scan, approfondissement.
5. Collectif, texte explicatif et retour aux questions.
6. Footer partagé et accès au contact.

À partir de 801 px, hero en deux colonnes ; en dessous, pile verticale. Le hero ne conserve pas deux colonnes étroites entre 601 et 800 px. Les trois liens de navigation restent présents à 320 px. Les sections sont nommées pour la navigation par régions.

### Identité et navigation

Marque liée à l’accueil, nom accessible « Kekidi?! — accueil ». Symbole « ?! » décoratif masqué aux technologies d’assistance. Navigation native, libellés explicites et cibles de 44 px minimum ; aucun faux état d’onglet. Sur les pages internes, retour avec flèche gauche et en-tête compact ; le symbole peut disparaître sur mobile.

Les ancres des sections sont `questions`, `collectif` et `principe`. Dans un élément partagé entre pages, les liens vers ces sections doivent résoudre vers l’accueil et son ancre, pas vers une ancre inexistante de l’article courant.

### Boutons

Padding `.8rem 1.4rem`, hauteur minimale 52 px, texte DM Sans 600 à 15 px, rayon pilule et contour 2 px. Au survol : déplacement −1/−2 px, ombre 7 px ; en pression : déplacement +3/+3 px, ombre 2 px. Le fond de navigation devient jaune ; le bouton de validation rose s’éclaircit avec du blanc.

Dans Gutenberg, cibler l’élément interactif `.wp-element-button` et non ajouter un second padding au conteneur `.wp-block-button`. Sur mobile, CTA d’accueil et boutons de contact occupent la largeur disponible.

### Cartes et catégories

Une carte blanche contient un seul lien couvrant badge, H3 et pied. Padding du lien 24 px ou 20 px sur mobile, rayon intérieur ajusté au contour ; titre sans ellipse. Le pied reste en bas des cartes d’une même rangée. Survol et focus soulignent l’action sur fond menthe ; pression sur fond jaune. Ne pas couper le focus par un débordement masqué.

Badges de 28 px minimum, DM Sans 600 à 12 px, rayon pilule, contour 1 px. Leur alternance jaune/menthe/ciel dépend de la position ; aucune légende métier n’est à inventer. La carte d’un quiz porte « Répondre au quiz » ; celle d’une réponse, « Lire la réponse ».

La grille passe de trois colonnes à une à 800 px. Le WordPress actuel affiche douze articles par page puis une pagination ; le nombre de trois réponses initiales ne doit pas être figé dans l’interface.

### Affiche et fonctionnement

Affiche rose, rayon 32 px, ombre 8 px ; signature en haut, question centrée à 12ch maximum, bas séparé par un contour, tampon jaune et légende extérieure. Hauteurs minimales 370 px sur grand écran, 330 px à ≤800 px, 320 px sur mobile. Haut et bas autorisent les retours. Aucun faux QR code ou élément faussement cliquable.

Fonctionnement : trois numéros dans des cercles blancs de 38 px. Trois colonnes sur ordinateur/tablette ; une ligne par étape sur mobile avec numéro à gauche et texte à droite. Flux de grille explicitement en lignes, sans colonne implicite héritée.

## 4. Réponses, contenu et navigation

Colonne extérieure de 820 px maximum, gouttières incluses. Question H1 à 30ch maximum, panneau jaune de réponse courte, explication puis sources éventuelles. Réponse courte dominante visuellement, mais balisée comme paragraphe ; jamais de deuxième H1. Padding du panneau `clamp(24px,5vw,48px)`. Autoriser sa hauteur à suivre le texte et sa légère inclinaison existante.

Le texte éditorial utilise DM Sans 18 px / 1,65. H2 Nunito 800 à 24 px, H3 20 px, titres suivants 18 px. Paragraphes espacés de 16 px ; nouvelles rubriques de 32 px ; images proportionnelles dans la colonne et légendes de 14 px. Ces règles couvrent aussi les blocs ajoutés hors des compositions, en lecture et dans l’éditeur. Exclure les panneaux, sources et interfaces de quiz de cette règle générale quand ils possèdent leur propre échelle.

Les sources utilisent des libellés précis, jamais une URL brute comme unique indication. Elles apparaissent seulement si renseignées. Liens externes documentaires : nouvel onglet, `noopener noreferrer`, indication de destination accessible. Les URLs longues peuvent revenir à la ligne. Les liens éditoriaux utilisent menthe/jaune/rose pour repos/survol/activation et une distinction après visite.

Sur une réponse classique, retour menthe à gauche et suite à droite sur ordinateur ; pile et retour pleine largeur sur mobile. Dans un quiz avant correction, retour secondaire et navigation compacte ; après validation, retour au bouton habituel. La dernière réponse mène à la première ; une réponse unique n’a pas de suivante.

Les URLs d’articles suivent `/reponses/<slug>/`. Préserver un slug déjà diffusé, notamment dans un QR code ; changement de titre ou conversion réponse/quiz ne doit pas le modifier. Les chiffres, noms de groupes et textes des exemples ne sont pas des valeurs immuables du design.

## 5. Quiz classique : états à conserver

| État | Présentation et comportement |
| --- | --- |
| Initial | Choix ciel clair, lettres à gauche, contrôle 24 px à droite, explication cachée avec `inert` et `aria-hidden`. |
| Sélection | Fond ciel plus marqué ; aucun déplacement ni ombre sur la ligne ou la case. |
| Validation vide | Message jaune ; focus sur le premier choix ; aucun résultat enregistré. |
| Corrigé | Toutes les bonnes réponses menthe et cochées ; mauvaises réponses décochées et fond initial ; choix erronés et oublis bordés de tirets de 2 px. |
| Résultat | « Bien joué ! » sur jaune ou « Pas tout à fait ! » sur rose ; focus sur le résultat, annonce accessible. |
| Après correction | Choix verrouillés, bouton de validation masqué, explication et sources révélées ; détail de correction disponible aux lecteurs d’écran. |

Une à cinq propositions et au moins une correcte. Le corrigé doit correspondre exactement à la sélection ; ne pas considérer « au moins une bonne réponse » comme une réussite. Dans WordPress, une seule bonne réponse utilise des radios et « Une seule réponse possible. », plusieurs des cases et « Plusieurs réponses possibles. ». Message de réussite au singulier quand nécessaire.

WordPress stocke uniquement le dernier booléen juste/faux dans `localStorage`, après validation ; aucun choix restauré au rechargement. Révélation en 240 ms, durée réduite sur mobile, sans animation avec la préférence de réduction. Réussite complète : une seule gerbe de 180 confettis, palette rose/menthe/jaune/ciel, sans capturer les interactions.

## 6. Quiz de scrutin : présentation et lecture des données

Contexte à 14 px, mesure à reconnaître à 16 px, légende, liste et compteur. Liste d’options ciel, nom de groupe et logo 40 px avant le contrôle. Après validation : panneau de résultat menthe ou rose, graphique horizontal et explication. Les options de l’Assemblée suivent l’ordre GDR, LFI, Écologistes, Socialistes, LIOT, Démocrates, Ensemble, Horizons, Droite républicaine, UDR, RN pour les groupes référencés ; le Sénat suit les groupes du scrutin importé. Ne pas figer ce catalogue pour toutes les législatures.

Le graphique est trié par pourcentage décroissant dans le sens demandé. Chaque barre représente 100 % des pour + contre + abstentions de son groupe : menthe, rose, ciel. Hauteur 24 px, contour 2 px, rayon 8 px ; libellés à 16 px, valeurs à 13 px avec chiffres tabulaires. Logos de graphique ramenés à 32 px sur mobile. Valeurs textuelles et effectifs dépliables complètent la couleur.

Les largeurs utilisent les valeurs exactes ; les libellés s’arrondissent à une décimale, avec une somme affichée pouvant différer de 100 %. Majorité strictement supérieure à 50 %. Absents et non-votants exclus du dénominateur ; non-inscrits inclus dans les totaux mais pas dans les options. « Choix incorrect » et « Groupe oublié » expliquent les différences avec la sélection.

WordPress autorise de valider sans sélection : c’est juste seulement si aucun groupe n’a de majorité dans le sens demandé. Résultat enregistré comme booléen local ; formulaire vierge au rechargement. Le formulaire disparaît après correction, puis focus sur le résultat. Même effet de confettis que le quiz classique, uniquement pour une réussite complète.

L’explication se compose de blocs Gutenberg éditables. Source et méthode sont proposées comme contenu initial ; après personnalisation, ne pas réinjecter de paragraphes calculés. Un nouvel import ne remplace pas le texte éditorial ni ne publie le brouillon. Logos dans `wordpress/kekidi-scrutins/assets/logos/`, provenance dans son `SOURCES.md` ; sigle de repli si un logo ne charge pas.

## 7. Contact : tous les états

Page de 820 px maximum. Introduction, titre « À vous de poser la question. », panneau menthe à rayon 32 px et ombre 8 px. Padding 32 px ; mobile 24 px et rayon 24 px. Labels DM Sans 600 visibles au-dessus des champs. Fonds papier, contours 2 px, rayons 16 px ; e-mail de 52 px, sélecteur natif de 56 px, zone de question de 160 px redimensionnable. Focus encre 3 px décalé de 3 px.

| État | Contrat |
| --- | --- |
| Initial | Objet « Je souhaite… » avec « Poser une question » ou « Proposer une question », e-mail et question. Bouton rose « Valider mon message ». |
| Invalide | Message sous chaque champ concerné, associé au contrôle ; `aria-invalid`, contour marqué et focus sur la première erreur. Vérifications aussi côté serveur. |
| Envoi | « Envoi en cours… », bouton désactivé, formulaire `aria-busy`, double clic bloqué. |
| Échec serveur/réseau | Panneau rose, contenu saisi conservé, focus sur le retour et bouton « Réessayer l’envoi ». |
| Succès | Introduction et formulaire masqués ; panneau menthe avec icône, « Merci, votre message est envoyé ! » et retour aux questions ; focus sur la confirmation. |

Le menu natif fonctionne même sans prise en charge de `appearance:base-select`. Les styles de menu personnalisable ne doivent pas être requis pour utiliser le formulaire. Le visiteur ne voit aucun sélecteur de scénario de démonstration.

Dans WordPress, Forminator et Kekidi Contact créent un formulaire propre au site et enregistrent les messages dans l’administration. Notifications e-mail désactivées par défaut ; adresse IP retirée des données du message. La consultation requiert les permissions adaptées. Les anciens écrans simulés ne constituent pas un contrat de stockage ou d’envoi.

## 8. États transversaux et accessibilité

- Lien d’évitement visible au focus ; langue française, régions et niveaux de titres cohérents.
- Focus visible, navigation native, labels persistants et distinction qui ne dépend jamais de la seule couleur.
- Aucun contrôle caché encore accessible au clavier ; résultat annoncé et focus déplacé après validation.
- Champs invalides et erreurs expliqués ; bouton désactivé distingué sans rendre le texte illisible.
- Textes longs, titres de plusieurs lignes et URLs sans débordement ; pas de hauteur fixe sur les contenus.
- Avec couleurs forcées, restaurer les contrôles natifs et les bordures nécessaires.
- Avec réduction des mouvements, supprimer transitions, animations, déplacements interactifs et confettis.
- Sans JavaScript, réponses classiques lisibles ; quiz accompagnés d’un message explicite et lien au scrutin officiel quand disponible. Ne pas annoncer une interaction testée sans JavaScript si elle dépend du script.
- 404 : code rose incliné à contour d’encre, explication et retour. Composition en deux colonnes puis pile mobile ; aucun diagnostic technique exposé au visiteur.

## 9. Écarts de comportement conservés dans WordPress

La référence statique sert à comprendre le dessin ; la version fonctionnelle porte les contrats suivants :

| Sujet | Référence statique consultée | Contrat WordPress actuel |
| --- | --- | --- |
| Contact | Succès et panne simulés, aucun enregistrement | Validation serveur et messages Forminator réels |
| Quiz classique | Cases multiples, pas de stockage | Radios si une seule réponse correcte ; dernier résultat booléen local |
| Scrutin vide | Sélection vide refusée | Validation vide permise, corrigée selon les majorités réelles |
| Scrutins | Données d’exemple figées | Imports Assemblée/Sénat, sens pour/contre, instantanés propres aux articles |
| Explication de scrutin | Texte de démonstration généré | Blocs éditoriaux modifiables et préservés |
| Réussite | Pas de gerbe dans les scripts statiques consultés | Confettis locaux avec réduction des mouvements respectée |
| Abstention | Ancien texte documentaire indiquant papier | Ciel dans le CSS de référence et le thème actuel |

Ces distinctions évitent de supprimer une fonctionnalité actuelle au nom d’une ancienne illustration. Aucun changement de comportement ou de rendu n’est effectué par cette documentation.

## 10. Maintenance et recette

Modifier directement le thème dans `wordpress/kekidi/` : `style.css` pour les styles, `theme.json` pour la palette et les polices, `templates/`, `parts/` et `patterns/` pour les blocs. Les comportements restent dans leurs extensions. Constituer les archives avec WP-CLI et les vérifier avant installation. Synchroniser DESIGN.md, l’inventaire des tokens et ce guide lorsque les valeurs changent.

Les personnalisations de modèles, parties et styles enregistrées dans l’Éditeur de site vivent en base et peuvent primer sur les fichiers. Les récupérer et préserver le travail éditorial avant modification. Ne pas présenter un ZIP ou un export XML comme une sauvegarde complète du site.

Recette par type de page : accueil, réponse, quiz classique, scrutin, contact et 404. Vérifier à 320, 390 et 1440 px, autour des seuils 600/768/800 px, au clavier, avec titre long et réduction des animations. Vérifier erreurs et nouvelle tentative du contact, validation vide/correcte/incorrecte des quiz, rechargement vierge et logos manquants. Les tests automatisés ne remplacent pas la vérification visuelle et avec lecteur d’écran.

Pour l’installation vierge et les versions effectivement testées, suivre [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md). Les anciens rapports ou captures restent des observations datées, pas la preuve d’une recette actuelle sur tous les appareils.
