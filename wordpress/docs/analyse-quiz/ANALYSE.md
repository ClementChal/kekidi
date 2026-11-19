# Réponses quiz — analyse avant implémentation

**10 novembre 2026 · Proposition à valider**

> Suite à cette analyse, une implémentation locale a été réalisée à la demande de l’utilisateur. Elle ajoute la conservation des choix dans localStorage et une consigne simple/multiple conditionnelle. Voir [le parcours de test et ses limites](../../kekidi-quiz/README.md). Les constats ci-dessous décrivent l’état avant implémentation ; aucun ZIP ni déploiement public n’a été effectué.

## Recommandation

Conserver les **articles WordPress** pour les deux formats et ajouter un **bloc Gutenberg « Réponse quiz »**, fourni par une petite extension `Kekidi — Quiz`. Le thème conserve la présentation ; l’extension fournit l’édition et le comportement du quiz.

Un article contient une réponse classique ou un quiz. Le format n’entre pas dans son adresse : une conversion conserve l’article, son identifiant et son slug.

**Aucun code du thème, aucune extension et aucun contenu WordPress n’ont été modifiés pendant cette analyse.** Studio a été démarré pour lire sa configuration. Le site InfinityFree n’a pas été inspecté ni modifié.

## 1. État actuel vérifié

| Élément | Constat |
|---|---|
| Thème local actif | Kekidi 1.1.8 |
| Environnement local | WordPress 7.1.3, PHP 8.4, Studio avec SQLite |
| Adresses | `/reponses/%postname%/` |
| Réponses publiées | `espace-public`, `arbres-en-ville`, `decisions-locales` |
| Édition actuelle | Composition « Nouvelle réponse », constituée de blocs natifs |
| Liste d’accueil | Articles publiés, ordre croissant des identifiants, 12 par page |
| Liens des cartes | Adresse fournie par WordPress ; libellé « Lire la réponse » fixé dans le thème |
| Modèle des articles | `single.html` : titre, contenu de l’article et navigation commune |
| Rôle Éditeur | Peut modifier et publier des articles, sans modifier les modèles du thème |
| Rôle Super éditeur | Droits d’édition, plus accès aux réglages du thème |
| Modèles personnalisés en base | Aucun `wp_template` ni `wp_template_part` trouvé localement |
| Extensions de quiz | Aucune installée localement |

### Écarts dans les outils actuels

- `verify-theme.php` attend encore un accueil issu du contenu d’une page avec quatre sections verrouillées. Le thème réel utilise le motif `kekidi/accueil`. Ce contrôle doit être réaligné sur le fonctionnement retenu avant de servir de preuve de non-régression.
- Certaines instructions d’`INSTALLATION.md` décrivent également cet ancien accueil éditable. Elles ne doivent pas guider une nouvelle migration sans correction.
- Le dépôt Git commence dans `wordpress/`. Le générateur `build-wordpress.ts`, le CSS source et le script de packaging sont dans son dossier parent, hors de ce dépôt. Leur conservation versionnée devra être traitée dans la livraison ; ne conserver que les fichiers générés rendrait les prochaines évolutions difficiles à reproduire.
- La version de WordPress et les éventuelles personnalisations du site public restent à vérifier avant déploiement. Le thème annonce actuellement WordPress 6.6 minimum : ne pas utiliser une API plus récente sans vérifier cette compatibilité.

## 2. Ce que prévoit exactement la maquette

Référence consultée :

`/home/cchalencon@corp.wisebim.fr/open-design/.od/projects/3a760bf8-a027-475e-9de4-bbc911a67bec`

Les fichiers déterminants sont `questions.json`, `build-site.ts`, `assets/quiz.ts`, `assets/kekidi.css` et `reponses/missions-police.html`. Le HTML et les styles calculés priment sur les descriptions parfois anciennes du README.

### Parcours et états

| État | Rendu et comportement à reproduire |
|---|---|
| Carte d’accueil | Même carte que les autres questions ; action « Répondre au quiz » et nom accessible adapté |
| Ouverture du quiz | Titre de la question, consigne « Plusieurs réponses possibles. », propositions A à E, bouton rose pleine largeur. Pas de panneau jaune de réponse courte |
| Sélection | Cases à cocher à droite ; toute la proposition est cliquable. Le fond de la proposition sélectionnée change, sans déplacement ni ombre sur l’option |
| Validation vide | Message « Sélectionnez au moins une réponse avant de valider. », focus sur la première case. Rien n’est corrigé ni verrouillé |
| Sélection exacte | « Bien joué ! » sur un panneau jaune ; message « Vous avez trouvé toutes les bonnes réponses. » |
| Sélection incorrecte | « Pas tout à fait ! » sur un panneau rose et nombre d’erreurs |
| Corrigé | Toutes les bonnes réponses deviennent menthe et cochées. Toutes les fausses sont décochées. Les oublis et les mauvaises sélections portent un contour en tirets |
| Après validation | Cases verrouillées, bouton de validation masqué, explication et sources révélées. Le retour à l’accueil devient un bouton menthe |
| Nouvelle tentative | Aucun bouton « Recommencer » dans la maquette ni stockage propre au quiz. La restauration des cases après rechargement ou navigation arrière dépend aussi du navigateur et devra être testée |

**Règle de correction :** la sélection doit correspondre exactement au corrigé. Chaque bonne réponse oubliée et chaque fausse réponse sélectionnée compte pour une erreur. Le résultat n’est pas un pourcentage ni un score cumulé.

**Contenus :** une question par page, de **1 à 5 propositions**, au moins une bonne réponse. Le résumé existe dans les données mais n’est pas affiché dans un panneau de réponse courte sur le quiz.

### Repères visuels vérifiés dans le navigateur

- Colonne de 820 px maximum ; en-tête du quiz aligné sur cette largeur.
- Titre de 28 px à 320/390 px de large, 36 px à 1 440 px de large.
- Propositions de 60 px minimum, contour de 2 px, rayon de 24 px ; case de 24 × 24 px à droite.
- Fond pastel initial calculé avec les couleurs du site ; reprendre la formule CSS exacte plutôt qu’une couleur approchée.
- Le contour des erreurs est en tirets de **2 px**, contrairement à la mention de 4 px dans le README.
- Le détail « Bonne réponse oubliée » ou « Réponse incorrecte » est destiné aux lecteurs d’écran, sans ajout de texte visible dans les options.
- Sur mobile, l’en-tête masque le médaillon comme dans les réponses existantes. Le libellé Kekidi?! reste visible.
- Révélation de l’explication : 240 ms, durée réduite sur mobile, sans animation avec réduction des mouvements. Cela n’ajoute aucune transition de navigation entre les pages.

Captures de **la maquette**, et non d’une implémentation WordPress : [mobile initial](assets/reference-mobile-initial.png), [mobile avec corrigé exact](assets/reference-mobile-correct.png), [mobile avec erreurs](assets/reference-mobile-wrong.png), [ordinateur initial](assets/reference-desktop-initial.png).

### Autres évolutions présentes dans la référence

1. **Accueil :** cartes en défilement horizontal natif, sans rotation automatique ; trois cartes visibles sur grand écran, deux à partir de 1 024 px, une carte de 86 % de large sur mobile.
2. **Réponses classiques :** panneau jaune incliné de −1°, puis −0,5° sur mobile.
3. **Contenu de démonstration :** « missions-police » remplace « arbres-en-ville » dans la liste JSON. L’ancien fichier HTML reste toutefois présent dans la maquette. Dans WordPress, cette différence ne doit pas entraîner la suppression de l’article existant.
4. **Liens complémentaires :** la maquette ajoute un paragraphe « À lire aussi ». Il s’agit de contenu éditorial à rendre modifiable, pas d’un texte à imposer à toutes les réponses existantes.

Le périmètre des points 1 et 2 est à confirmer : ils modifient aussi les pages existantes, au-delà du nouveau format quiz.

La maquette affiche toutes les entrées de son JSON sans pagination ; WordPress en affiche actuellement douze par page. Si le nouveau défilement est retenu, il faudra adapter ensemble la requête, ses wrappers et la pagination. L’ordre actuel des articles sera conservé : placer le nouvel exemple en deuxième position comme dans la démonstration demanderait une règle de tri éditorial distincte.

## 3. Création par un éditeur

Parcours proposé : **Articles → Ajouter → saisir la question → insérer « Nouvelle réponse quiz » → remplir les champs → Prévisualiser → Publier**.

| Champ | Édition et contrôle |
|---|---|
| Question | Titre natif de l’article, utilisé aussi par la carte d’accueil |
| Catégorie | Catégorie WordPress existante |
| Propositions | Liste de 1 à 5 textes ; ajout, suppression et réorganisation. Lettres A à E automatiques |
| Bonnes réponses | Case « Bonne réponse » pour chaque proposition ; au moins une doit être cochée |
| Explication | Paragraphes et liens éditables dans Gutenberg, sous le titre fixe « On vous explique. » |
| Sources | Section facultative, éditable avec les blocs usuels ; aucun titre vide si elle est absente |
| Résumé | Extrait natif, pour résumer la question sans créer de panneau jaune dans le quiz |
| Adresse | Permalien natif, conservé indépendamment des textes et du format |

Le rendu, les messages de résultat et la mise en page seront fournis automatiquement. L’éditeur n’aura ni HTML ni JavaScript à écrire, ni modèle du site à modifier.

L’explication restera visible pendant l’édition pour pouvoir la rédiger. Le comportement interactif se vérifiera en prévisualisation. Les contrôles de publication signaleront les textes vides, le dépassement des cinq propositions et l’absence de bonne réponse ; une vérification serveur devra couvrir les mêmes règles.

## 4. Choix d’architecture

| Solution | Intérêt | Limites |
|---|---|---|
| **Bloc Gutenberg dans une extension Kekidi — recommandé** | Champs adaptés au besoin, reprise précise de la maquette, articles et URL conservés, données indépendantes du thème | Petit composant spécifique à maintenir |
| Bloc intégré directement au thème | Un seul ZIP à installer | Le contenu interactif dépend du thème ; un changement de thème peut rendre le bloc indisponible |
| Extension de quiz existante, notamment Sensei | Éditeur et fonctions de quiz déjà maintenus | Les quiz classiques Sensei sont associés aux leçons. Les blocs interactifs utilisables dans des articles sont proposés dans une offre dédiée ou Pro ; leur comportement exact et leurs styles devraient être adaptés |

Les [quiz Sensei](https://senseilms.com/documentation/quizzes/) et les [blocs interactifs Sensei](https://senseilms.com/documentation/interactive-blocks/) ont été examinés. Le besoin présent est une question autonome, sans parcours de formation ni progression enregistrée. Je recommande donc le bloc Kekidi pour conserver le parcours et le rendu exacts de la maquette.

### Répartition proposée

- **Extension `Kekidi — Quiz` :** enregistrement du bloc, interface de saisie, validation, rendu du formulaire et script de correction. Une seule instance par article.
- **Thème Kekidi :** styles repris de la maquette, classe de page quiz, libellés des cartes, en-tête et navigation de retour adaptés au format.
- **Contenu de l’article :** propositions et corrigé dans les attributs du bloc ; explication et sources dans ses blocs internes. Les révisions et l’export du contenu restent ceux des articles.
- **Reconnaissance du format :** présence du bloc quiz dans l’article. Éviter une seconde case « Type quiz » ou un modèle à sélectionner séparément, qui pourrait contredire le contenu.
- **Composition de départ :** « Nouvelle réponse quiz », non synchronisée, à côté de « Nouvelle réponse ». Chaque article possède ses propres textes et son propre corrigé.

Les scripts du quiz ne seront chargés que sur les contenus concernés. Aucun appel réseau n’est nécessaire pour corriger les réponses. Les ressources seront livrées prêtes à installer : aucune compilation ne devra être exécutée chez InfinityFree.

## 5. Adresses stables et QR codes

Exemple de nouvelle adresse :

`https://kekidi.great-site.net/reponses/missions-police/`

- Conserver la structure `/reponses/%postname%/` et les articles existants. Ne pas créer un préfixe `/quiz/` ou un paramètre temporaire pour choisir le format.
- Utiliser le permalien WordPress dans les cartes et la navigation. Passer d’une réponse classique à un quiz ne doit modifier ni l’identifiant ni le slug.
- Imprimer le QR code de l’adresse HTTPS publiée, jamais celui d’un brouillon ou d’une prévisualisation locale.
- Une modification de texte, de choix, de corrigé ou de design ne doit pas modifier cette adresse.

**Protection recommandée :** proposer un verrou « URL utilisée pour un QR code » qui empêche un éditeur de changer accidentellement le slug. Un administrateur conserverait une procédure explicite de changement avec une redirection permanente vérifiée. Ce verrou serait une protection supplémentaire à développer, pas une fonction déjà présente.

WordPress conserve certains anciens slugs pour ses redirections, mais cela ne remplace pas la vérification des liens imprimés. La disponibilité d’un QR code dépend aussi du maintien du domaine, du chemin et de la publication de l’article.

## 6. Points techniques qui demandent une adaptation

| Point | Pourquoi la copie brute de la maquette ne suffit pas |
|---|---|
| Contenu actuel | Le générateur et l’export XML ne connaissent que la réponse classique. Ils ignoreraient le nouveau type sans adaptation |
| Cartes | « Lire la réponse » est fixé dans deux filtres du thème. Le texte visible et le nom accessible devront tous deux suivre le format |
| Grille d’accueil | Le CSS propre à WordPress force encore trois colonnes et une colonne sur mobile. Il écraserait le défilement horizontal de la référence |
| Boutons | Le générateur remplace `.btn` par `.wp-element-button`. Le script de la maquette qui remplace toute la classe du lien de retour ne peut pas être recopié tel quel |
| Enveloppe du quiz | Il faut appliquer `.quiz-page` au bon conteneur sans remplacer le contenu des autres articles ni l’accueil |
| Sélecteurs JavaScript | La référence utilise des identifiants globaux. Le bloc devra avoir des identifiants uniques et limiter ses recherches à son propre contenu |
| Explication et sources | Le thème doit conserver les liens éditoriaux et les protections `noopener noreferrer` dans le contenu révélé |
| Gutenberg | Un formulaire collé dans un bloc HTML ne donne pas les champs d’édition attendus. Le bloc doit être enregistré et validé dans l’éditeur |
| Versions | Les styles et scripts devront recevoir de nouveaux numéros de version pour éviter les essais sur des fichiers en cache |

Les fichiers concernés seront principalement : le générateur `build-wordpress.ts`, le CSS source `assets/kekidi.css`, le packaging, les fichiers générés du thème et une nouvelle extension dans le dépôt WordPress. La documentation de création des articles sera mise à jour ; la charte graphique suivra également si l’inclinaison des panneaux classiques est retenue. L’extension Super éditeur conservera son rôle distinct ; aucun droit d’administration supplémentaire n’est nécessaire pour rédiger un quiz.

## 7. Données, accessibilité et limites

- Conserver le comportement de la référence : aucun cookie, stockage de réponses ou envoi de résultat ajouté par le quiz. Les statistiques de consultation restent un sujet distinct.
- Le corrigé est nécessairement lisible dans le code envoyé au navigateur. Ce format convient à une découverte pédagogique ; il ne fournit pas un examen protégé contre la consultation du corrigé.
- Échapper les libellés et contrôler les données lors de l’enregistrement et du rendu. Un éditeur ne doit pas pouvoir injecter un script dans une proposition.
- Conserver les cases natives, le clavier, le focus sur le résultat, les annonces accessibles, les textes de correction masqués visuellement et la réduction des mouvements.
- La maquette nécessite JavaScript pour révéler l’explication. Prévoir un état de secours explicite si le script ne charge pas ; son contenu exact reste à choisir sans modifier l’apparence normale.
- La désactivation de l’extension rendra le quiz interactif indisponible. Les données doivent rester conservées dans l’article pour permettre une réactivation et une récupération du contenu.
- L’explication de démonstration est du Lorem ipsum. Elle sert aux comparaisons locales et devra être remplacée avant toute publication publique.

## 8. Vérifications réalisées et recette à prévoir

### Réalisé pendant l’analyse

- Lecture du HTML, des données, du CSS, du script et des contrôles de la maquette.
- Exécution de `verify-quiz.ts` **sans régénérer la maquette** : les 32 combinaisons du quiz d’exemple passent, ainsi que les contrôles de sélection vide et de révélation.
- Inspection dans Chromium à 320, 390 et 1 440 px : aucun débordement horizontal du quiz ; mesures et captures conservées ci-dessus.
- Essais dans la maquette : sélection vide, sélection exacte, sélection mêlant bonne réponse, fausse réponse et oublis. Dans ce dernier cas, le résultat annonce quatre erreurs et affiche les contours en tirets de 2 px.
- Lecture des options, rôles, articles, extensions et modèles locaux via Studio.

### À réaliser après implémentation

| Domaine | Critères de validation |
|---|---|
| Fidélité | Comparaisons à contenu identique entre HTML de référence et WordPress ; états initial, sélectionné, erreur vide, réussite et échec |
| Écrans | 320, 390, 600, 768, 1 024 et 1 440 px ; téléphone réel, Safari iOS et navigateur Chromium |
| Édition | Création par un Éditeur et un Super éditeur ; 1 à 5 choix ; sauvegarde, réouverture, prévisualisation et restauration d’une révision |
| Validité | Aucun bloc invalide ; erreurs de saisie utiles ; contrôle serveur des données incorrectes |
| Correction | Combinaisons exactes, oublis, fausses sélections, zéro sélection, double validation et navigation arrière |
| URLs | Même adresse après modification des textes et conversion de format ; anciennes réponses toujours accessibles ; QR code testé sur l’adresse publiée |
| Accueil | Cartes classiques et quiz mélangées, bon libellé, un seul lien par carte, navigation suivante correcte |
| Non-régression | Accueil existant, réponses classiques, polices, boutons, liens visités, sources et rôles conservés |
| Accessibilité | Parcours clavier, focus, annonces, réduction des mouvements et vérification avec lecteur d’écran |
| Chargement | Pas de stockage ni d’appel réseau du quiz ; traitement du JavaScript absent ; versions des ressources et purge des caches |

## 9. Livraison proposée

1. Confirmer le périmètre : quiz seul ou reprise également du défilement horizontal et de l’inclinaison des panneaux classiques.
2. Sauvegarder le ZIP stable et la base locale avant les changements. Établir les contrôles de référence des anciennes réponses.
3. Ajouter le bloc et son édition, puis les adaptations ciblées du thème. Préserver les contenus et modèles déjà édités.
4. Créer le quiz de démonstration uniquement dans Studio et comparer ses états à la maquette.
5. Faire valider le parcours de création et le rendu local par l’utilisateur.
6. Après validation seulement, préparer le ZIP du thème et celui de l’extension, accompagnés des instructions de mise à jour. Installer l’extension avant de publier des articles contenant le bloc.

Le déploiement public ne devra pas remplacer la base InfinityFree par celle de Studio ni réimporter toutes les réponses. Le quiz sur la Police sera un nouvel article : l’adresse existante des arbres restera disponible.

## Sources techniques

- [WordPress : métadonnées et ressources d’un bloc](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/).
- [WordPress : attributs de bloc](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/).
- [WordPress : support des blocs, dont `contentRole` depuis 6.9](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/). Ne pas supposer cette API disponible sur une installation 6.6.
- [WordPress : récupération du permalien](https://developer.wordpress.org/reference/functions/get_permalink/).
- [WordPress : conservation des anciens slugs](https://developer.wordpress.org/reference/functions/wp_check_for_changed_slugs/).
- [Sensei : quiz liés aux leçons](https://senseilms.com/documentation/quizzes/) et [blocs interactifs](https://senseilms.com/documentation/interactive-blocks/).
