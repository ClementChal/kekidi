# Contact Kekidi — Forminator

Page locale : http://localhost:8881/contact/
Messages : **Forminator → Soumissions → Contact Kekidi**.

## Ce que fait chaque élément

- **Thème Kekidi 1.3.1** : modèle Contact, en-tête, footer et styles conformes à OpenDesign.
- **Forminator** : champs, validation serveur, protection honeypot, enregistrement et consultation des messages. Version testée : **1.58.4**. Aucun compte externe nécessaire pour ce formulaire.
- **Kekidi — Contact 2.0.0** : crée une seule fois le formulaire **Contact Kekidi**, supprime sa notification e-mail par défaut, retire l’IP des données du message et adapte ses états au design.

La composition **Page Contact Kekidi** utilise le bloc natif Forminator et l’identifiant créé sur le site courant : aucun ID local à recopier en production. Cet ID et l’URL de la page restent stables lors des mises à jour. Réactiver l’extension ne réinitialise pas un formulaire existant.

L’introduction reste modifiable dans Gutenberg. Les champs et leurs libellés se modifient dans **Forminator → Formulaires → Contact Kekidi → Modifier**. Les notifications e-mail sont désactivées au départ ; ne pas en ajouter tant que les messages doivent rester uniquement dans l’admin. Le panneau de succès et les messages d’interface sont fournis par l’extension.

Les messages Forminator sont conservés dans ses propres tables, indépendamment du thème. L’extension Kekidi ne supprime ni les anciens messages Jetpack ni les formulaires à sa désactivation. Les anciens messages Jetpack ne sont pas automatiquement importés dans Forminator.

## Mise en production, dans cet ordre

1. Sauvegarder les fichiers et la base de production. S’il existe des messages Jetpack, les exporter avant de supprimer Jetpack.
2. Installer et activer **Forminator**, par **WPMU DEV**, depuis **Extensions → Ajouter une extension**. Aucune connexion WPMU DEV ni offre payante nécessaire pour ce formulaire.
3. Téléverser `extensions/kekidi-contact.zip` et choisir **Remplacer l’extension actuelle par l’extension téléversée**. Vérifier que **Kekidi — Contact 2.0.0** est actif. Cette version dépend de Forminator, plus de Jetpack.
4. Téléverser `themes/kekidi-theme.zip` et remplacer le thème existant. Vérifier **Kekidi 1.3.1**.
5. Modifier la page **Contact** existante pour conserver `/contact/`. Choisir le modèle **Contact Kekidi**. Si elle est vide, insérer **+ → Compositions → Kekidi → Page Contact Kekidi**. Si elle contient l’ancien formulaire Jetpack, retirer uniquement ce formulaire et insérer le bloc **Forminator** en sélectionnant **Contact Kekidi** ; conserver l’introduction déjà saisie.
6. Prévisualiser, puis publier ou mettre à jour la page. Purger le cache et envoyer un message depuis une fenêtre privée.
7. Vérifier sa présence dans **Forminator → Soumissions**, formulaire **Contact Kekidi**.
8. Désactiver Jetpack ; le supprimer s’il ne sert à aucune autre fonctionnalité et après export de ses éventuels anciens messages.

**Ne pas importer la base Studio.** Les articles, quiz, médias et textes existants restent en production. Ne pas supprimer le thème ou l’extension avant de les remplacer.

Les administrateurs accèdent aux soumissions. Pour donner cet accès aux éditeurs ou super éditeurs, ouvrir **Forminator → Réglages → Permissions**, ajouter le rôle et accorder uniquement **Soumissions**. Les autres droits du rôle ne changent pas. [Documentation officielle des permissions](https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#permissions).

## Tests locaux

- Jetpack désactivé pendant tous les tests de cette version.
- Composition reçue et visible dans l’inserteur Gutenberg ; bloc Forminator chargé et contenu validé.
- Saisie vide et e-mail incorrect refusés ; requête directe invalide refusée par le serveur.
- Bouton désactivé pendant l’envoi, protection contre le double clic.
- Panne réseau simulée : erreur lisible, champs conservés, nouvelle tentative possible.
- Enregistrement réel, confirmation, zéro appel à `wp_mail` et absence d’IP dans les entrées.
- Contrôle des droits admin/visiteur ; aucun accès public aux messages.
- Absence de débordement à 320, 390 et 1440 px ; champs de 52/56/160 px et panneau conforme à la référence à 390 px.

Contrôles reproductibles :

- `node wordpress/kekidi-contact/tests/browser.mjs` avec Chromium de test sur 9223 et Studio sur 8881. Ce test crée un message reconnaissable au préfixe `KEKIDI-FORMINATOR-TEST :`.
- Studio WP-CLI : `eval-file …/kekidi-contact/tests/wordpress.php --user=admin` après le test navigateur. Pour contrôler l’absence d’e-mails sans risque, un filtre temporaire `pre_wp_mail` doit bloquer et compter les appels dans l’option `kekidi_test_mail_calls`.

Backups avant migration : `themes/backups/avant-forminator-20261112-115736/` (extension, CSS et base locale). Les anciens ZIP sont également sauvegardés dans les sous-dossiers `backups` à chaque création d’archive.

À vérifier après déploiement : réception sur InfinityFree, cache et protection antispam en conditions réelles. Les tests locaux ne couvrent pas Safari ni un téléphone physique. Le honeypot n’empêche pas tous les spams. Les textes de confidentialité et la durée de conservation des messages restent à définir pour le site public.
