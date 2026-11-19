# Kekidi?! — WordPress

Kekidi?! relie des questions affichées dans la rue à des réponses consultables sur téléphone. Ce dépôt contient le thème à blocs, quatre extensions maison et les outils permettant de produire leurs archives installables.

Le dépôt fournit le code, les ressources, les tests et la documentation du projet. L’installation WordPress, les extensions tierces, la base de données, les médias et les comptes se préparent séparément.

## Démarrer

1. Installer **Node.js 24 ou plus récent**, **uv** et **WordPress Studio**. Le packaging utilise Python 3.10 ou plus récent, sans paquet tiers.
2. Ouvrir un terminal à la racine du dépôt et générer les livrables :

   ```sh
   node build-wordpress.ts
   uv run --no-project python package-wordpress.py
   ```

3. Créer un site Studio vide dans un dossier distinct du dépôt.
4. Installer le thème, les extensions et les contenus en suivant [l’installation sur un WordPress vierge](wordpress/INSTALLATION-VIERGE.md). Installer **Forminator avant Kekidi Contact**.
5. Vérifier l’accueil, les réponses, un exemple de chaque quiz et un envoi de contact avant de commencer une modification.

Le générateur écrit dans `wordpress/kekidi/` et remplace l’export `wordpress/reponses-kekidi.xml`. Le packaging vérifie les fichiers, puis produit les ZIP. Il sauvegarde les archives précédentes lorsqu’elles existent ; il n’installe rien dans Studio et ne déploie rien en production.

Il n’y a actuellement ni installation npm/Composer, ni serveur applicatif à lancer depuis ce dépôt. Les scripts de génération utilisent les bibliothèques standard de Node et Python. Le WordPress local est géré par Studio.

## Prérequis et versions

| Élément | Version ou référence testée | Remarque |
| --- | --- | --- |
| Node.js | 24.18.0 | Exécution directe du TypeScript et tests JavaScript |
| uv | 0.11.26 | Lancement du packaging Python |
| Python | 3.14.6 | Packaging compatible avec les constructions Python 3.10+ utilisées ; pas de matrice multi-version exécutée |
| WordPress | 7.1.5 | Installation vierge testée le 19 novembre 2026 |
| PHP | 8.4.27 | Environnement Studio testé ; prise en charge DOM nécessaire à la lecture des sources de scrutins |
| Base locale | SQLite, fournie par Studio | Ne valide pas à elle seule l’hébergement de production MySQL/MariaDB |
| daisyUI | 5.6.3 | CSS précompilé embarqué, pas de compilation Tailwind |
| Confettis | @hiseb/confetti 2.2.0 | Bibliothèque embarquée dans les extensions de quiz |

Le thème et les extensions déclarent WordPress 6.6 et PHP 7.4 comme minima dans leurs en-têtes. Ces déclarations ne constituent pas une preuve de compatibilité de l’ensemble sur ces versions ; l’environnement ci-dessus est celui effectivement testé. Utiliser un environnement maintenu et vérifier les versions tierces avant une livraison.

## Dépendances et responsabilités

| Élément | Version testée | Fonction et installation |
| --- | --- | --- |
| Thème Kekidi | 1.4.0 | Présentation, modèles et compositions ; ZIP généré |
| Kekidi Contact | 2.0.0 | Formulaire de contact et adaptation du rendu ; requiert Forminator |
| Forminator (`forminator`) | 1.58.4 | Extension tierce à installer séparément ; validation, stockage et consultation des messages |
| Kekidi Quiz | 1.1.1 | Bloc de quiz classique, correction et validation de publication |
| Kekidi Scrutins | 1.0.1 | Bloc de scrutin, import contrôlé et instantanés stockés en base |
| Kekidi Super éditeur | 1.0.0 | Facultatif ; rôle avec droits éditoriaux, Éditeur de site et consultation Koko |
| Koko Analytics (`koko-analytics`) | 2.5.3 | Facultatif ; statistiques, à installer séparément |
| WordPress Importer (`wordpress-importer`) | 0.9.7 | Import initial du XML ; désactiver après utilisation |

Les extensions maison de quiz fournissent le comportement ; le thème fournit leurs styles. Livrer les deux lorsque la modification les concerne. Les thèmes et extensions tiers se récupèrent depuis leurs sources officielles et ne sont pas inclus dans les ZIP maison.

La dépendance `Requires Plugins: forminator` est déjà déclarée dans Kekidi Contact. Elle ne fixe pas la version de Forminator et ne remplace pas sa configuration. Gutenberg est intégré à WordPress ; aucune extension Gutenberg distincte n’est nécessaire à cette procédure.

## Où modifier quoi

| Besoin | Source à modifier |
| --- | --- |
| Modèles, compositions, fonctions et palette du thème | [build-wordpress.ts](build-wordpress.ts) |
| Styles partagés, responsive et états | [assets/kekidi.css](assets/kekidi.css) |
| Réponses initiales de l’export | [questions.json](questions.json) ; puis régénération |
| Articles, pages, catégories et textes éditoriaux du site | Administration WordPress et blocs Gutenberg |
| Champs et libellés du formulaire installé | Forminator ; la création initiale est définie dans [form.php](wordpress/kekidi-contact/form.php) |
| Comportement et intégration du contact | [wordpress/kekidi-contact/](wordpress/kekidi-contact/) |
| Bloc et correction du quiz classique | [wordpress/kekidi-quiz/](wordpress/kekidi-quiz/) |
| Import, stockage et correction des scrutins | [wordpress/kekidi-scrutins/](wordpress/kekidi-scrutins/) |
| Rôle Super éditeur | [kekidi-super-editeur.php](wordpress/kekidi-super-editeur/kekidi-super-editeur.php) |
| Vérification et constitution des archives | [package-wordpress.py](package-wordpress.py) |
| Identité et règles de présentation | [DESIGN.md](DESIGN.md), [design-system.md](design-system.md), [brand-spec.md](brand-spec.md) |

`wordpress/kekidi/` contient le thème généré : ne pas corriger uniquement ses fichiers, car une régénération peut les écraser. Les changements de `theme.json` doivent être reportés dans le générateur. Conserver les sources des extensions et leurs tests ; leurs dossiers `vendor/` contiennent des bibliothèques nécessaires, pas des caches à supprimer.

Le contenu éditorial courant n’est pas synchronisé avec `questions.json`. Modifier ce JSON ne met à jour aucun article installé. Les instantanés de scrutins et messages de contact vivent aussi en base : les fichiers du dépôt ne contiennent pas ces données.

## Installer et développer dans Studio

Studio installe WordPress dans un dossier distinct des sources du dépôt. La génération ne met pas automatiquement le site local à jour. Installer le nouveau ZIP par **Apparence → Thèmes → Ajouter/Téléverser**, puis remplacer le thème existant. Les extensions maison se mettent à jour dans **Extensions → Ajouter/Téléverser**. Ne pas les supprimer avant de les remplacer.

Avec le MCP WordPress Studio, utiliser son outil `wp_cli` en ciblant explicitement le site de test. Exemple d’argument `command`, sans préfixe `wp` :

```text
theme install /CHEMIN_ABSOLU_DU_DEPOT/wordpress/themes/kekidi-theme.zip --force --activate
```

Remplacer le chemin d’exemple par celui du clone. Même principe pour `plugin install /CHEMIN_ABSOLU_DU_DEPOT/wordpress/extensions/kekidi-quiz.zip --force --activate`. Les commandes sont des arguments WP-CLI, pas des commandes shell à transmettre au MCP. La connexion est décrite dans [MCP.md](wordpress/MCP.md).

Pour une installation neuve, retenir les points suivants :

- Créer une page **Accueil** vide et la choisir dans **Réglages → Lecture**. Le modèle actuel affiche directement la composition `kekidi/accueil`.
- Importer le XML une seule fois et passer l’article d’exemple WordPress en brouillon.
- Choisir les permaliens `/reponses/%postname%/`. Préserver ensuite les slugs déjà diffusés ou imprimés.
- Après activation de Forminator et Kekidi Contact, ouvrir l’administration en administrateur. Le formulaire **Contact Kekidi** reçoit un identifiant propre au site.
- Créer `/contact/`, choisir le modèle **Contact Kekidi** et insérer **Page Contact Kekidi**. Ne pas recopier l’ID du formulaire d’un autre site.

Les modifications enregistrées dans **Apparence → Éditeur** peuvent être stockées en base et prendre le pas sur les fichiers du thème. Avant de modifier un modèle ou une partie, vérifier ces personnalisations et conserver le travail existant ; leur transfert vers les sources est une opération distincte.

Utiliser un site Studio dédié aux tests et relever son URL dans l’application. Le nom du site, son dossier et son port dépendent de l’environnement de chaque contributeur. Ouvrir l’administration avec le bouton **WP Admin** de Studio ; aucun mot de passe n’est à conserver dans le dépôt.

## Vérifier une modification

### Contrôles locaux sans WordPress

Depuis la racine :

```sh
node wordpress/kekidi-quiz/tests/runtime.mjs
```

Ce test vérifie notamment les 32 sélections, le stockage booléen, le rechargement vierge et le fonctionnement sans stockage disponible.

Après une modification du thème ou du packaging, exécuter les deux commandes de génération du démarrage. Le packaging vérifie la structure des blocs et leurs attributs, les ressources, la cohérence du XML et l’intégrité des ZIP. Il crée des archives et éventuellement des backups : ce n’est pas un contrôle en lecture seule. Ses assertions ne remplacent pas une validation dans l’éditeur réel ; ne pas lancer Python avec les assertions désactivées.

### Tests d’intégration dans un site Studio de test

Le site doit être démarré et les extensions nécessaires actives. Dans l’outil MCP `wp_cli`, cibler le site de test et fournir les commandes suivantes avec le chemin absolu du clone :

```text
eval-file /CHEMIN_ABSOLU_DU_DEPOT/wordpress/kekidi-quiz/tests/wordpress.php --user=admin
eval-file /CHEMIN_ABSOLU_DU_DEPOT/wordpress/kekidi-scrutins/tests/wordpress.php --user=admin
eval-file /CHEMIN_ABSOLU_DU_DEPOT/wordpress/kekidi-scrutins/tests/data.php --skip-plugins=kekidi-scrutins --user=admin
```

Adapter `admin` si le compte administrateur du site porte un autre identifiant. Les tests d’intégration créent et suppriment des comptes/articles temporaires ; les exécuter sur le site de test. Le test `data.php` charge directement ses sources : `--skip-plugins=kekidi-scrutins` évite une double déclaration des fonctions avec l’extension installée. Ce test utilise des fixtures, sans requêtes aux sources externes.

Pour les blocs ajoutés ou modifiés, utiliser `validate_blocks` sur le site de test avec le thème et les extensions actifs, avant publication des contenus.

Les tests navigateur sont dans les dossiers `tests/` des extensions. Ils utilisent Chromium/CDP et contiennent des URL, ports et identifiants de contenus fixes : les adapter au site de test avant exécution. Ils ne constituent pas une suite portable lançable sans préparation. Le test du contact crée un message fictif ; suivre les [prérequis Contact](wordpress/kekidi-contact/README.md) pour le blocage/comptage temporaire des e-mails et le contrôle serveur, puis retirer le dispositif de test.

### Recette fonctionnelle

Vérifier les pages concernées en visiteur et avec le rôle éditorial adapté :

- Accueil et réponses : liens, cartes, ordre de lecture, navigation et slugs stables.
- Quiz : sélection vide, résultat juste/faux, explication, rechargement vierge et animations réduites.
- Scrutins : import réel quand le réseau change, source officielle, groupes, sens pour/contre et conservation du texte éditorial.
- Contact : erreur de saisie, panne/réessai et message réellement présent dans **Forminator → Soumissions**.
- Interface : mobile 320/390 px et ordinateur 1440 px, clavier, focus, absence de débordement et rendu dans Gutenberg.

La recette Studio détaillée et ses limites sont dans [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md). Les tests Safari, téléphone physique et hébergement cible/MySQL ne sont pas couverts par cette seule recette. `wordpress/verify-theme.php` est incompatible avec la structure d’accueil actuelle : utiliser les contrôles décrits ci-dessus pour cette version du thème.

## Contenus, données et permissions

- Les réponses utilisent les articles WordPress. Les quiz sont des blocs d’articles, sans type de publication distinct.
- Les quiz conservent uniquement le dernier résultat booléen dans le navigateur ; aucun choix n’est envoyé au serveur.
- Les instantanés des scrutins sont des métadonnées protégées de l’article. Ils doivent accompagner le contenu dans une sauvegarde ; le seul bloc HTML ne suffit pas.
- Les messages Contact sont enregistrés par Forminator. Les notifications e-mail sont désactivées initialement et l’IP est retirée des données du message par Kekidi Contact.
- Les éditeurs peuvent rédiger les réponses et importer des scrutins. Le rôle Super éditeur ajoute l’accès à l’Éditeur de site et aux statistiques Koko, sans gestion des extensions ni des comptes.
- L’accès aux soumissions Forminator se configure séparément dans ses permissions. Le rôle Super éditeur ne le reçoit pas automatiquement.
- Les accès réseau d’import des scrutins sont distincts du jeu : les données et logos nécessaires au visiteur sont locaux. Tester les imports sur l’hébergement cible.

## Livrer, mettre à jour et revenir en arrière

1. Modifier les sources, augmenter la version du thème dans le générateur et/ou celle des extensions concernées. La version du thème sert au cache des styles ; vérifier aussi les versions des ressources des extensions si elles changent.
2. Générer les livrables et effectuer les contrôles adaptés dans Studio.
3. Sauvegarder **la base et les fichiers du site cible**, dont médias, messages et instantanés de scrutins, avant toute mise à jour.
4. Livrer uniquement les archives nécessaires, en remplaçant thème/extensions existants. Le script n’effectue aucun déploiement automatique. Les ZIP peuvent être joints à une Release GitHub.
5. Purger le cache du site et vérifier sur l’hébergement cible les pages touchées, le contact et les imports réseau concernés.

Le thème est livré dans `wordpress/themes/kekidi-theme.zip`. Les quatre extensions sont dans `wordpress/extensions/`, sous la forme `kekidi-contact.zip`, `kekidi-quiz.zip`, `kekidi-scrutins.zip` et `kekidi-super-editeur.zip`.

Pour une mise à jour de code, ne pas remplacer la base de production par celle de Studio et ne pas réimporter le XML initial. Conserver les contenus éditoriaux du site. La publication sur un hébergement externe à WordPress.com/Pressable ne passe pas automatiquement par Studio Sync.

Pour revenir en arrière, réinstaller une version de code compatible depuis une archive conservée. Si la modification a changé le schéma ou les données, préparer la restauration de la sauvegarde correspondante et son impact sur les nouveaux contenus ; réinstaller un ancien ZIP ne restaure pas la base.

### Bibliothèques et extensions tierces

Les fichiers embarqués ne se mettent pas à jour depuis WordPress :

- **daisyUI** : remplacer `assets/daisyui.css` par une version officielle précise, conserver sa licence, régénérer, puis vérifier le site et Gutenberg. La version actuelle est précompilée ; Tailwind n’est pas nécessaire. Une compilation Tailwind/npm serait une évolution séparée.
- **Confettis** : conserver bibliothèques et licences dans les `vendor/` concernés ; vérifier les deux quiz lors d’une mise à jour partagée.
- **Forminator/Koko/WordPress** : mettre à jour par leurs mécanismes officiels sur un environnement de test, puis revoir les parcours affectés. Mettre à jour les versions testées dans la documentation.

Aucun gestionnaire de dépendances ni verrouillage central ne met actuellement ces éléments à jour. Les numéros de version du README décrivent la dernière recette documentée, pas nécessairement les dernières versions publiées.

## Sauvegardes et fichiers suivis

Lorsqu’un ZIP existe déjà, le packaging le sauvegarde dans `wordpress/themes/backups/` ou `wordpress/extensions/backups/` avant de produire la nouvelle archive. Ces dossiers restent locaux, sont ignorés par Git et peuvent grossir : conserver les versions utiles sur un support de sauvegarde distinct. Ils contiennent du code empaqueté, pas une sauvegarde complète du site.

Le `.gitignore` exclut les ZIP, sauvegardes, bases locales, médias, caches, logs et fichiers de configuration sensibles usuels. Conserver dans Git les sources, fichiers générés du thème, ressources, licences, tests et documentation. Un fichier déjà suivi n’est pas retiré par une nouvelle règle d’exclusion.

Ne pas versionner mots de passe, clés, données réelles des formulaires, bases de production ou configurations personnelles. Avant publication du dépôt, examiner les fichiers ajoutés : les règles d’exclusion ne constituent pas un audit des secrets. Les dépendances embarquées conservent leurs licences ; aucune licence globale du dépôt n’est encore fournie, et les en-têtes des composants ne doivent pas être interprétés comme une licence unique de tous les fichiers.

## Contribuer et trouver la documentation

Préserver la séparation entre présentation du thème et fonctionnalités des extensions. Ne pas modifier le cœur WordPress ni une extension tierce pour adapter Kekidi ; utiliser les mécanismes WordPress existants et limiter les changements au besoin traité.

Pour le PHP, suivre les conventions WordPress et celles du fichier : validation des entrées, échappement adapté au contexte de sortie, contrôle des capacités et protection des actions. Un nonce ne remplace pas une autorisation. Préserver les vérifications des imports, les permissions REST et l’immutabilité des instantanés. Les données sensibles doivent rester hors des logs et des jeux de tests.

Avant de proposer un changement, indiquer le problème résolu, les fichiers sources modifiés, les contrôles exécutés et leurs limites, les versions à livrer et l’impact éventuel sur les données. Il n’y a actuellement ni CI configurée, ni commande globale de lint, ni mise à jour automatique des dépendances dans ce dépôt.

| Document | À consulter pour |
| --- | --- |
| [DESIGN.md](DESIGN.md) | Direction visuelle et invariants |
| [design-system.md](design-system.md) | Composants, états, responsive et maintenance |
| [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md) | Installation neuve et recette documentée |
| [Contact](wordpress/kekidi-contact/README.md) | Forminator, messages et permissions |
| [Quiz](wordpress/kekidi-quiz/README.md) | Création de quiz et stockage local |
| [Scrutins](wordpress/kekidi-scrutins/README.md) | Sources de données, instantanés et création des quiz |
| [MCP Studio](wordpress/MCP.md) | Connexion des outils à Studio |

Pour installer et développer la version actuelle, suivre en priorité le présent README et [INSTALLATION-VIERGE.md](wordpress/INSTALLATION-VIERGE.md). Les guides spécialisés complètent les procédures de chaque fonctionnalité ; vérifier les versions et adapter les chemins, ports et identifiants à son environnement. Les rapports de recette décrivent les contrôles effectués à leur date, sans remplacer une nouvelle validation après modification.

Références officielles : [sécurité des extensions](https://developer.wordpress.org/plugins/security/), [nonces et autorisations](https://developer.wordpress.org/apis/security/nonces/), [sauvegardes WordPress](https://developer.wordpress.org/advanced-administration/security/backup/), [distribution CSS daisyUI](https://daisyui.com/docs/cdn/).
