# Migration vers les outils WordPress

## Objectif et approche retenue

Maintenir directement le thème à blocs et les extensions, produire des ZIP versionnés avec WP-CLI et retirer le générateur TypeScript ainsi que le packaging Python. Préserver le rendu, les blocs, les URLs et les données WordPress. Node reste utilisé pour les tests JavaScript sans imposer de compilation JSX.

Deux alternatives ont été considérées : regrouper les scripts sous Node conserverait une génération personnalisée sans intérêt pour ce besoin ; compiler tous les blocs avec wp-scripts ajouterait une transformation alors que leur JavaScript actuel est compatible avec WordPress sans compilation. Le thème directement maintenu et WP-CLI correspondent à l’approche discutée pour ce projet.

## Limites

Conserver les dossiers actuels `wordpress/kekidi*` pour éviter des déplacements inutiles et préserver les chemins de tests. Aucun changement fonctionnel, aucune migration de base, aucun déploiement public et aucune suppression de sauvegardes. Le XML reste un jeu initial indépendant du thème. Les contrôles qui dépendaient du générateur deviennent des tests des fichiers distribués, pas un nouveau générateur.

## Outils

PHP 8.4.27, avec DOM et ZIP, et WP-CLI 2.12.0 sont déjà fournis par Studio. La commande additionnelle `wp-cli/dist-archive-command:3.1.0` est compatible avec cette version ; la branche actuelle exige WP-CLI 3. Node 24 est déjà disponible. Composer n’est pas nécessaire pour cette migration ; PHP_CodeSniffer/WPCS pourra être ajouté séparément sans reformatage global mêlé à cette migration.

La commande dist-archive 3.1.0 nécessite également l’exécutable système `zip`. Sous Debian/Ubuntu : `sudo apt install zip`. L’extension PHP ZIP est présente mais ne remplace pas cet exécutable pour cette version de la commande. Le sous-processus WP-CLI ne transmet pas le PATH utilisateur : le binaire placé dans `~/.local/bin` ne suffit pas dans cet environnement.

## Résultat de la migration

Migration réalisée le 19 novembre 2026. Les cinq archives versionnées sont dans `dist/` et ont été réinstallées sur le seul site Studio de test. Les générateurs TypeScript/Python et les ressources racines dupliquées sont retirés ; les fichiers natifs du thème sont désormais les sources directement modifiables. Le jeu initial est conservé en XML et sa fixture JSON dans `wordpress/tests/fixtures/reponses.json`.

Preuves : 85 fichiers exécutables et ressources de production identiques par SHA-256 au relevé initial ; contenu des cinq ZIP comparé aux sources ; exclusions de distribution contrôlées avec le même lecteur de .distignore que la commande officielle. Un faux .env injecté dans le ZIP et une licence manquante sont refusés. Un .env local correctement exclu ne provoque pas de faux échec.

Tests passés : distribution, thème (accueil/cartes/droits/navigation), intégration des deux quiz, données Assemblée/Sénat, 32 sélections du quiz classique et tests navigateur à 320/390/1440 px. Le contact a enregistré un second message fictif sans e-mail ni IP ; le dispositif temporaire de blocage/comptage a été retiré. Les sept articles publiés, l’ID du formulaire 1004 et les instantanés de scrutins sont conservés.

Le README, les guides d’installation/développement, la documentation des extensions et les références de design/tokens suivent la maintenance directe du thème et les ZIP versionnés. Les anciens ZIP et backups sont conservés. Aucun déploiement public ni modification du site Studio Kekidi d’origine n’a eu lieu. Aucun commit de migration n’a été créé.

Restent hors périmètre : Composer/PHPCS, CI, matrice de versions PHP/WordPress, Safari/téléphone physique et validation de l’hébergement public. Les tests navigateur conservent des URL et identifiants fixes à adapter ; ils ont été exécutés ici avec leurs variantes temporaires ciblant le site de test.

## Plan et preuves

1. Enregistrer les sommes SHA-256 des 89 fichiers de distribution et l’état des articles, du formulaire et des instantanés sur le site de test.
2. Ajouter des tests PHP des fichiers et archives : blocs, ressources, dépendances, XML, licence/confettis, dossiers racines des ZIP et exclusion des tests/configurations de développement. Observer leur échec avant la préparation des archives versionnées.
3. Ajouter un `.distignore` à chaque composant. Produire les cinq ZIP avec `wp dist-archive`, sans écraser une livraison existante. Les anciennes sauvegardes restent locales.
4. Vérifier la parité des fichiers des ZIP avec les sources et avec le relevé initial. Conserver les vérifications utiles du script Python, adaptées à un thème directement maintenu.
5. Retirer les deux scripts et les ressources racines dupliquées ; conserver les données initiales comme fixtures de test du XML. Actualiser la documentation et les références de tokens vers les fichiers du thème.
6. Réinstaller les mêmes versions des composants sur le seul site Studio de test, puis exécuter les contrôles serveur et les tests JavaScript. Vérifier les données conservées et les parcours navigateur concernés.

## Retour arrière

Le commit précédent conserve le générateur, le packaging et les sources. Les ZIP précédents et backups ne sont pas supprimés. En cas de défaut d’archive, arrêter la livraison et utiliser le ZIP précédent ; la migration ne modifiant ni schéma ni contenus, aucun remplacement de base n’est prévu.

## Points de revue

Conserver les racines des ZIP et les slugs, les ressources/licences embarquées, les préréglages de thème, la dépendance Forminator, les métadonnées de scrutin et l’identifiant du formulaire. Les contrôles PHP de structure ne remplacent pas la validation des blocs dans Gutenberg. Ne pas promettre que la nouvelle commande d’archives effectue les sauvegardes du script retiré : les livraisons sont conservées par version et les sauvegardes complètes restent séparées.
