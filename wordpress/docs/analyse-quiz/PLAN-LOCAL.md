# Réponses quiz — plan d’implémentation locale

> Exécution : superpowers:executing-plans, dans cette session.

**Objectif :** permettre l’édition et l’essai des réponses quiz dans Studio, avant toute création d’archive.

**Architecture :** articles existants et bloc dynamique Gutenberg fourni par `kekidi-quiz`. CSS du quiz repris de la maquette dans le thème ; contenu et corrigé conservés dans l’article. L’extension adapte les cartes et l’enveloppe des articles quiz.

**Technologies :** PHP 7.4+, WordPress 6.6+, API Gutenberg existantes, JavaScript natif, localStorage facultatif au fonctionnement.

**Référence :** ANALYSE.md et maquette OpenDesign indiquée dans ce document.

## Contraintes

- Aucun ZIP ni déploiement public pendant cette étape.
- Conserver les réponses existantes et leurs adresses.
- Consigne et sélection simple/multiple selon le nombre de bonnes réponses.
- Enregistrer uniquement le dernier résultat booléen après validation, sans restaurer le formulaire ni enregistrer les choix. Une nouvelle validation remplace ce résultat.
- Continuer à fonctionner si localStorage est bloqué ou plein.
- Ne pas réintroduire les anciennes transitions de page.
- Périmètre initial : quiz ; les changements globaux de la maquette restent en attente de réponse.

## Tâches

- [x] Sauvegarder l’état local et préparer l’extension : bloc, champs, rendu et validation serveur.
- [x] Reprendre le comportement et les styles du quiz, avec stockage local et consigne conditionnelle.
- [x] Activer l’extension et le thème local ; valider les blocs avant de créer l’exemple Studio.
- [x] Vérifier correction, restauration, stockage indisponible, édition et droits, URL et rendu mobile/ordinateur.
- [x] Documenter le parcours de test et laisser Studio accessible pour validation humaine.

Preuves et parcours : [README de l’extension](../../kekidi-quiz/README.md). Relecture indépendante effectuée ; validation serveur renforcée pour les publications sans contenu dans la requête. Tests humains en attente, aucune archive produite.

## Points de contrôle

Sélections vides ; omissions ; mauvaises réponses ; données stockées invalides ; radio pour une seule bonne réponse ; texte éditorial échappé ; blocs internes conservés ; aucune modification des anciens articles ou URL ; styles de boutons WordPress ; aucune archive générée.
