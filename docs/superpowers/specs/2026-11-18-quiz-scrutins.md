# Quiz scrutins — décisions validées

## Objet
Ajouter aux articles Kekidi un format de quiz sur les votes de l’Assemblée nationale et du Sénat. Les éditeurs importent un scrutin puis rédigent les textes dans Gutenberg. Reproduire la maquette OpenDesign fournie, ses composants et ses états.

## Règles validées avec l’utilisateur
- Deux chambres dès la première version : Assemblée (XVIIe législature initialement) et Sénat (année de début de session explicite).
- L’éditeur choisit « majoritairement pour » ou « majoritairement contre ».
- Majorité stricte : plus de 50 % de pour + contre + abstentions ; non-votants exclus.
- Les décomptes concordant avec la page officielle font référence. Un champ positionMajoritaire contradictoire est signalé mais ne remplace pas le calcul.
- Valider reste possible sans sélection. L’ensemble vide est juste si aucun groupe ne remplit la condition ; sinon les groupes oubliés sont indiqués.
- Non-inscrits hors choix ; inclus dans les contrôles et totaux.
- Aucun réseau externe pendant le rendu ou la partie ; aucune mise à jour silencieuse des articles publiés.
- Données conservées dans WordPress, adresses d’articles et QR codes stables.
- Exemples Studio : Assemblée 7987, variantes pour/contre ; Sénat 2025/33.
- Aucun nouveau ZIP avant validation locale par l’utilisateur.

## Architecture retenue
Extension PHP autonome `kekidi-scrutins`, bloc `kekidi/quiz-scrutin`, articles natifs. Le thème porte les styles et garde ses modèles. Réutiliser les contrôles quiz, tokens, polices, en-têtes et navigation existants.

Les imports contrôlés sont des instantanés immuables dans les métadonnées de l’article ; le bloc conserve leur identifiant et les réglages éditoriaux. Importer prépare une nouvelle référence, sans changer le contenu publié. Les révisions du contenu conservent les anciennes références. Pas de table supplémentaire ni de service Node à héberger.

Les routes d’import et de consultation éditoriale exigent le droit de modifier l’article. Les adresses des sources sont construites par le serveur et leurs réponses sont limitées en taille et durée. Les nombres ne sont pas modifiables depuis le bloc. Les logos connus sont locaux ; les autres utilisent le sigle, avec possibilité de choisir une image de la médiathèque. Ne pas confondre emblème d’un parti et groupe parlementaire.

## Options examinées
Import PHP dans WordPress : retenu pour l’autonomie des éditeurs et l’hébergement existant. Import JSON issu du POC : étape manuelle supplémentaire. Service Node : hébergement et disponibilité supplémentaires, écarté.

## Limites connues
Le Sénat ne garantit pas la compatibilité des anciens balisages. Les appels depuis InfinityFree nécessiteront une vérification après les essais Studio. Les noms historiques et décomptes sont officiels ; les textes pédagogiques sont relus par l’éditeur. Les corrections déclaratives de vote ne réécrivent pas silencieusement les votes enregistrés.
