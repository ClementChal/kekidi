# AGENTS.md — Kekidi?!

Kekidi?! est un projet WordPress qui prolonge en ligne des questions affichées
dans l’espace public.

## Repères

- Lire le [README](README.md) pour repérer les composants, les tests et la procédure de livraison.
- Consulter le [guide de développement local](wordpress/DEVELOPPEMENT-LOCAL.md) pour identifier le site Studio de test et vérifier s’il utilise les sources liées ou des ZIP.
- Consulter le [design system](DESIGN.md) avant un changement de rendu et le [guide d’installation](wordpress/INSTALLATION.md) avant une mise à jour d’un site existant.
- Vérifier `git status --short` et préserver les modifications en cours.

## Modifier le projet

- Travailler dans les sources versionnées sous `wordpress/`. Le thème porte la présentation, les extensions portent les fonctionnalités et leurs données. Réutiliser les blocs et API WordPress existants.
- Si un changement du thème ne se voit pas, vérifier les personnalisations enregistrées dans l’Éditeur de site. Elles peuvent prendre le dessus sur les fichiers du thème.
- Préserver les contenus éditoriaux, les médias, les données et les permissions. Les URL publiques peuvent être imprimées sur des affiches. Si une URL change, prévoir une redirection et vérifier l’ancien lien.
- Pour toute migration de données existantes, prévoir une sauvegarde et un retour arrière. Tester sur un site dédié et déployer en production uniquement sur demande explicite.

## Vérifier et livrer

- Tester le parcours modifié dans WordPress avec le rôle concerné et suivre la recette du [README](README.md#recette-fonctionnelle).
- Pour une livraison, générer les ZIP à partir des sources et vérifier leur contenu selon le [README](README.md#produire-une-livraison).
- Indiquer les contrôles exécutés, leurs résultats et leurs limites. Mettre à jour le guide concerné si la procédure change.
