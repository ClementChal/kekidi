# Kekidi — Quiz

Extension validée dans Studio. Installation publique : mettre à jour le thème Kekidi puis installer et activer `dist/kekidi-quiz-1.1.1.zip` via **Extensions → Ajouter une extension → Téléverser une extension**. Adapter le nom à la version livrée. Cette opération nécessite un compte administrateur ; la création des quiz est ensuite accessible aux éditeurs.

## Tester dans Studio

- Site : http://localhost:8881/
- Exemple : http://localhost:8881/reponses/missions-police/
- Modifier l’exemple : http://localhost:8881/wp-admin/post.php?post=1009&action=edit
- Connexion administrateur via le bouton **WP Admin** de Studio.

Le contenu explicatif de l’exemple est provisoire. Seul le site local a été modifié.

## Créer une réponse quiz

1. **Articles → Ajouter un article** : saisir la question comme titre.
2. Ajouter le bloc **Réponse quiz** (ou la composition **Nouvelle réponse quiz** dans Kekidi).
3. Renseigner de 1 à 5 propositions et cocher les bonnes réponses. Les boutons permettent d’ajouter, déplacer ou supprimer une proposition.
4. Rédiger l’explication dans les blocs sous **On vous explique.** Ajouter des sources si nécessaire.
5. Choisir une catégorie, prévisualiser puis publier.

Une seule bonne réponse donne des boutons radio et « Une seule réponse possible. ». Plusieurs bonnes réponses donnent des cases et « Plusieurs réponses possibles. ». Un quiz incomplet peut être enregistré en brouillon ; sa publication est refusée.

La carte d’accueil affiche automatiquement **Répondre au quiz**. L’adresse suit les mêmes règles que les autres réponses : ne pas changer le **slug** après impression d’un QR code. Changer le titre ou convertir le contenu en réponse classique conserve cette adresse.

## Dernier résultat conservé dans le navigateur

En cas de réussite complète, une seule gerbe de confettis aux couleurs Kekidi apparaît depuis le bouton. Elle est désactivée avec la préférence système de réduction des animations. La bibliothèque [@hiseb/confetti 2.2.0](https://github.com/swetzel/confetti.js) est incluse localement sous licence ISC : aucun appel au CDN depuis le site. Un échec de l’effet ne bloque pas le quiz.

Après validation, seule la valeur booléenne true (tout juste) ou false (au moins une erreur) est enregistrée dans localStorage, sous la clé kekidi:quiz:<identifiant de l’article>. Aucun choix ni brouillon n’est enregistré. Le résultat reste dans ce navigateur, sans envoi au serveur.

À chaque chargement, le formulaire est vierge et non validé, quel que soit le résultat enregistré. Une nouvelle validation remplace le résultat précédent. Une validation vide ne le modifie pas. Le stockage n’est pas encore utilisé pour un score ou pour modifier l’affichage. Le quiz fonctionne si le stockage est bloqué. Les données de l’ancien format sont ignorées et remplacées lors de la prochaine validation.

## Vérifications réalisées

- Depuis la racine du dépôt, `node wordpress/kekidi-quiz/tests/runtime.mjs` : 32 sélections, résultat booléen, formulaire vierge au retour, remplacement du résultat, aucun stockage avant validation, ancien format ignoré, stockage bloqué et message de réussite au singulier.
- Studio WP-CLI `eval-file …/kekidi-quiz/tests/wordpress.php` : droits Éditeur, brouillon incomplet, publication valide, refus d’une publication invalide avec mise à jour du seul statut, conversion classique et permalien stable. Compte et article de test supprimés après le contrôle.
- Blocs de l’exemple validés dans l’éditeur réel ; modification des bonnes réponses et consigne conditionnelle vérifiées dans Gutenberg.
- Chromium : rendu aux largeurs 320, 390 et 1440 px, absence de débordement, correction et restauration après rechargement (contrôles antérieurs à la simplification du stockage), cartes d’accueil et réponses classiques préservées. Contrôle visuel mobile et couleurs comparées à la maquette.

Les essais sur téléphone physique et Safari restent à faire. Le parcours « Modification rapide » possède une protection serveur, mais n’a pas été exercé dans le navigateur.

## Sauvegarde et livraison

Sauvegarder fichiers et base avant mise à jour, et conserver les ZIP publiés dans les Releases. La création d’archives n’effectue aucune sauvegarde du site.

Les styles du quiz se trouvent dans le thème ; le bloc et son comportement sont dans cette extension. Installer les deux archives : `dist/kekidi-1.4.0.zip` et `dist/kekidi-quiz-1.1.1.zip`, avec les versions livrées. Purger le cache après la mise à jour. Aucun import de contenu ou de base n’est nécessaire ; l’exemple Studio n’est pas inclus dans les archives.
