# Installation sur un WordPress vierge

Procédure vérifiée le 19 novembre 2026 sur un site Studio neuf. Le code se livre sous forme de ZIP versionnés ; la base et les médias sont propres au site installé.

## Environnement et dépendances testés

| Élément | Version | Rôle |
| --- | --- | --- |
| WordPress | 7.1.5 | Installation neuve Studio, base SQLite |
| PHP | 8.4.27 | Moteur Studio |
| Thème Kekidi | 1.4.0 | Présentation et compositions |
| Forminator (`forminator`) | 1.58.4 | Dépendance obligatoire du contact |
| Kekidi Contact | 2.0.0 | Création du formulaire et adaptation au thème |
| Kekidi Quiz | 1.1.1 | Quiz classiques |
| Kekidi Scrutins | 1.0.1 | Quiz Assemblée et Sénat |
| Kekidi Super éditeur | 1.0.0 | Rôle optionnel |
| Koko Analytics (`koko-analytics`) | 2.5.3 | Statistiques optionnelles |
| WordPress Importer (`wordpress-importer`) | 0.9.7 | Import initial uniquement, puis désactivation |

Les versions tierces ci-dessus décrivent le test effectué. Elles ne garantissent pas la compatibilité de versions futures et ne dispensent pas de vérifier les mises à jour avant une mise en production. `Requires Plugins: forminator` déclare la dépendance dans Kekidi Contact ; cette déclaration ne fixe aucune version.

## Installation manuelle dans Studio

1. Créer un nouveau site vide, dans son propre dossier. Ne pas importer une sauvegarde de l'ancien site. Dans **Réglages → Général → Langue du site**, choisir **Français** pour localiser l’administration et les dates.
2. Constituer les archives avec WP-CLI et vérifier leur contenu selon [Produire une livraison](../README.md#produire-une-livraison). Le thème est directement maintenu, sans étape de génération.
3. Dans l'administration du nouveau site, téléverser `dist/kekidi-1.4.0.zip` depuis **Apparence → Thèmes**, puis l'activer. Adapter le numéro à la version livrée.
4. Installer et activer Forminator, par WPMU DEV. Pour reproduire exactement le test, utiliser la version 1.58.4 du répertoire WordPress.org.
5. Installer et activer les ZIP `dist/kekidi-contact-2.0.0.zip`, `dist/kekidi-quiz-1.1.1.zip` et `dist/kekidi-scrutins-1.0.1.zip`. Activer Forminator avant Kekidi Contact.
6. Facultatif : installer Koko Analytics et `dist/kekidi-super-editeur-1.0.0.zip` pour les statistiques et le rôle Super éditeur.
7. Importer une seule fois `wordpress/reponses-kekidi.xml` via **Outils → Importer → WordPress**. L'importateur peut ensuite être désactivé. Le XML ajoute trois articles ; il ne crée pas de quiz de démonstration.
8. Créer une page **Accueil** et la définir comme page statique dans **Réglages → Lecture**. Avec Kekidi 1.4.0, le fichier `front-page.html` affiche directement la composition `kekidi/accueil` : laisser cette page vide. L'instruction ancienne demandant d'insérer « Accueil éditable » ne s'applique pas à cette version.
9. Passer l'article d'exemple WordPress en brouillon. Choisir les permaliens personnalisés `/reponses/%postname%/`.
10. Ouvrir l'administration avec un compte administrateur après activation de Kekidi Contact. Vérifier le formulaire **Contact Kekidi** dans Forminator. Il est créé avec un identifiant propre au nouveau site.
11. Créer une page **Contact**, slug `contact`, choisir le modèle **Contact Kekidi**, insérer la composition **Page Contact Kekidi**, puis publier.
12. Depuis une fenêtre privée, envoyer un message fictif. Vérifier son enregistrement dans **Forminator → Soumissions**. Les notifications e-mail de ce formulaire sont désactivées par défaut.
13. Créer un article de test avec le bloc **Réponse quiz**, puis un article avec le bloc de scrutins. Vérifier une réponse correcte, une incorrecte et le comportement après rechargement.

Un rôle Super éditeur n'obtient pas automatiquement l'accès aux messages Forminator. Si nécessaire, accorder uniquement **Soumissions** dans les permissions Forminator.

## Résultat du test Studio

- Site : **Kekidi Test installation**.
- Adresse : `http://localhost:8882/` ; administration : `http://localhost:8882/wp-admin/`.
- Dossier : `/home/cchalencon@corp.wisebim.fr/Studio/kekidi-test-installation`.
- Trois réponses XML importées ; accueil et contact configurés ; article WordPress d'exemple en brouillon.
- Quatre articles de démonstration publiés : quiz classique, Assemblée 7987 pour/contre et Sénat 2025-33. Ces articles existent dans la base de test, pas dans le XML initial.
- Import réel des données Assemblée et Sénat réussi.
- Blocs de contact et des démonstrations validés dans Gutenberg avant publication.
- Tests serveur des quiz classique et scrutins réussis : publication, validation, stockage et permissions.
- Contact testé dans le navigateur : erreurs client et serveur, double clic, panne simulée, texte conservé, succès réel et composition visible dans Gutenberg.
- Message fictif enregistré, absence de notification e-mail et d'IP vérifiée en base, accès visiteur refusé et accès administrateur autorisé.
- Un filtre temporaire bloquait et comptait les e-mails pendant le test ; il a été retiré après vérification. Ce filtre ne fait pas partie des livrables.
- Quiz classique et scrutins testés à 320, 390 et 1440 px : résultat correct/incorrect, stockage local et absence de débordement. Réduction des animations et import de scrutin dans Gutenberg également vérifiés.
- Droits et restrictions du rôle Super éditeur vérifiés.
- Accueil et trois réponses vérifiés : titre principal unique et absence de débordement ; captures d'accueil et contact sur ordinateur et mobile.
- WordPress Importer désactivé après utilisation.

Pour une nouvelle recette, configurer les URL et identifiants des scripts navigateur selon le [README](../README.md#contrôles-github-et-standards-php). `verify-theme.php` contrôle la structure d’accueil actuelle, les cartes, les droits Éditeur et la navigation.

Aucun déploiement public n'a été effectué. Restent hors de ce test : hébergement de production/MySQL, autres navigateurs et téléphone physique, audit complet des secrets et vérification des versions tierces plus récentes.
