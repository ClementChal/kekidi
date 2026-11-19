# Kekidi — Scrutins

Quiz de scrutins pour les articles WordPress. Le thème fournit le design ; cette extension fournit le bloc, l’import et le corrigé. Aucun nouveau type de publication ni changement des permaliens.

## Créer un quiz

1. **Articles → Ajouter un article**. Rédiger le titre de la question et enregistrer un brouillon.
2. Insérer **Quiz scrutin**, ou **Compositions → Kekidi → Nouvelle réponse scrutin**.
3. Choisir la chambre et le numéro. Assemblée : législature 17. Sénat : année de début de session, par exemple **2025** pour 2025–2026.
4. Cliquer sur **Charger le scrutin**. Les groupes et les décomptes sont vérifiés avant enregistrement. Un message explique les anomalies ou les refus.
5. Choisir **majoritairement pour** ou **majoritairement contre**, adapter la présentation de la mesure puis modifier l’explication dans les blocs Gutenberg. « On vous explique. » est un H2 modifiable ; le résumé, les sources et la méthode sont des textes initiaux que vous pouvez garder, modifier ou supprimer.
6. Les logos par défaut viennent de la maquette. Un logo peut être remplacé par une image de la médiathèque, ou par le sigle du groupe.
7. Utiliser **Prévisualiser**, tester les réponses, choisir une catégorie puis publier. Conserver ensuite le slug pour les QR codes imprimés.

Un seul quiz par article. Ne pas y insérer la composition de réponse classique en plus. Les éditeurs disposent déjà des droits nécessaires ; aucun accès administrateur n’est requis pour importer.

## Règles du jeu

- Un groupe est correct si son nombre de votes dans le sens choisi dépasse strictement la moitié des **pour + contre + abstentions**. Les non-votants sont exclus du dénominateur.
- Les non-inscrits restent dans les totaux officiels, sans devenir un choix du quiz.
- La validation est toujours possible, même sans sélection. Une sélection vide est juste uniquement lorsqu’aucun groupe n’atteint le seuil.
- Après validation : résultat, groupes oubliés ou incorrects, graphique, effectifs, explication et source officielle.
- Une réponse entièrement juste déclenche la même gerbe de 180 confettis que le quiz classique, aux couleurs du site. La bibliothèque est locale, sans CDN. Une seule gerbe par validation, désactivée avec la réduction des animations ; un échec décoratif ne bloque pas le résultat.
- `localStorage["kekidi:quiz:<ID article>"]` contient uniquement `true` ou `false`. Une nouvelle validation remplace cette valeur. Le formulaire revient vierge au rechargement et aucun résultat n’est envoyé au serveur.

## Données et conservation

L’Assemblée utilise les décomptes structurés de CLAIR, comparés groupe par groupe à la page officielle de l’Assemblée. Le titre et la date viennent aussi de la page officielle. Le champ `positionMajoritaire` ne décide jamais du corrigé. Le Sénat est lu sur sa page officielle, avec contrôle des totaux.

L’import est réservé aux personnes autorisées à modifier l’article. Les destinations réseau sont fixes ; taille, redirections et durée sont bornées. Les décomptes ne sont pas des champs éditables.

Chaque import crée un instantané immuable dans une métadonnée protégée de l’article ; le bloc ne contient que sa référence. Deux imports simultanés de scrutins différents conservent chacun leur instantané. Importer ne modifie pas le contenu publié : **Enregistrer** rend la nouvelle référence publique. Les révisions anciennes conservent leurs références.

À la première ouverture d’un ancien article avec cette version, les textes auparavant ajoutés au rendu sont proposés comme blocs éditables, en conservant les paragraphes déjà rédigés. Aucun changement public tant que l’article n’est pas enregistré. Ensuite, les textes ne sont plus injectés au rendu ni réinitialisés par un import ou un changement de sens : relire l’explication lorsque le scrutin ou la question change. Inclure la base de données dans les sauvegardes : un ZIP de thème ou une copie du seul contenu du bloc ne contient pas les instantanés.

Aucune requête aux sources ni à un service de logos pendant le jeu. Les images sont locales ; les intégrations externes ne sont pas proposées dans l’explication. Certains emblèmes représentent un parti associé au groupe ; les noms et effectifs restent ceux du scrutin historique. La source officielle et la date d’import sont accessibles après validation.

## Exemples Studio

- `/reponses/scrutin-7987-pour/`
- `/reponses/scrutin-7987-contre/`
- `/reponses/scrutin-senat-2025-33/`

## Vérifications

Tests PHP via le `wp_cli` de Studio, avec `eval-file <chemin>/tests/data.php` et `eval-file <chemin>/tests/wordpress.php`. Les tests de stockage créent puis suppriment leurs articles et utilisateur temporaires. Les captures de sources servent aux tests hors réseau ; elles ne sont pas destinées à l’archive de production.

`tests/browser.mjs` teste les exemples Studio à 1440, 390 et 320 px avec Chromium CDP sur le port 9223 : validation vide, résultat exact, rechargement, liens protégés, sigle de secours et import Gutenberg sans mutation du publié. Adapter les identifiants si les exemples sont recréés ailleurs.

La compatibilité réseau avec l’hébergement cible reste à vérifier avant livraison. Un refus d’import conserve les données existantes. Livrer le thème et `dist/kekidi-scrutins-1.0.1.zip` avec leurs versions correspondantes, selon la procédure du README à la racine.
