# Installer et mettre à jour Kekidi

Pour une première installation, suivre [INSTALLATION-VIERGE.md](INSTALLATION-VIERGE.md). Les prérequis, dépendances, versions et commandes d’archives sont décrits dans le [README](../README.md).

## Mise à jour d’un site existant

1. Sauvegarder les fichiers et la base, dont médias, messages de contact et métadonnées de scrutins.
2. Vérifier la version et le contenu des ZIP de `dist/`, puis tester sur un site Studio dédié.
3. Dans **Apparence → Thèmes → Ajouter → Téléverser**, sélectionner l’archive du thème et remplacer le thème existant.
4. Dans **Extensions → Ajouter → Téléverser**, remplacer les extensions concernées. Forminator doit être installé et actif avant Kekidi Contact.
5. Purger le cache et vérifier les pages affectées, le contact et les imports de scrutins si concernés.

Ne pas supprimer un thème ou une extension avant de le remplacer. Ne pas réimporter le XML initial, remplacer la base de production par celle de Studio ou réinitialiser les pages pour une mise à jour de code.

Le thème se maintient directement dans `wordpress/kekidi/`. Les adaptations faites dans l’Éditeur de site doivent être vérifiées avant de remplacer les fichiers : certaines sont enregistrées en base et peuvent avoir priorité.

Les articles utilisent `/reponses/<slug>/`. Préserver toute adresse déjà diffusée ou imprimée ; un changement de titre ou de format réponse/quiz ne doit pas modifier le slug.

## Recette de mise à jour en préproduction

1. Relever les versions WordPress, PHP, MySQL ou MariaDB et Forminator sur l’hébergement cible. Utiliser les mêmes versions en préproduction.
2. Préparer une sauvegarde de la base et des fichiers. Restaurer une copie isolée, avec des soumissions fictives et des scrutins publiés. Désactiver les notifications sortantes sur cette copie.
3. Avant la mise à jour, relever les permaliens, l’identifiant du formulaire, le nombre de soumissions et les références des instantanés liés aux articles.
4. Remplacer le thème et les extensions par les ZIP candidats en conservant la base. Comparer les valeurs relevées, ouvrir les anciens scrutins, puis enregistrer un message fictif et vérifier sa présence dans Forminator.
5. Tester la restauration de la sauvegarde sur cette même préproduction. Consigner les versions, les archives et le résultat avant de livrer.
