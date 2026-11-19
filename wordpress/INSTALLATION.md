# Installer Kekidi dans WordPress

Le thème reprend la maquette Open Design avec des blocs WordPress natifs. Gutenberg est déjà intégré : ne pas installer le plugin Gutenberg pour cette procédure. WordPress 6.6 ou plus récent est nécessaire.

Sur les navigateurs qui prennent en charge l’API Speculation Rules, le thème demande le préchargement discret d’une page interne quand son lien devient visible, après le chargement de la page courante. Les visiteurs connectés à l’administration ne déclenchent pas ce préchargement.

Deux fichiers sont prêts :

- `themes/kekidi-theme.zip` : le thème à téléverser sans le décompresser.
- `reponses-kekidi.xml` : les trois réponses existantes et leurs catégories.

Le thème n'ajoute ni ne supprime de contenu à l'activation. L'import XML ajoute les réponses. Ne faire cet import qu'une fois pour éviter les doublons. Sur un site contenant déjà du travail, conserver une sauvegarde des fichiers et de la base avant l'installation.

## 1. Installer le thème

Dans `https://kekidi.great-site.net/wp-admin/` :

1. Ouvrir **Apparence → Thèmes → Ajouter un thème → Téléverser un thème**.
2. Choisir `themes/kekidi-theme.zip`, puis **Installer maintenant**.
3. Activer **Kekidi**.

Le modèle d'accueil affiche le contenu d'une page WordPress intitulée **Accueil**. Pour une première installation, créer cette page, y insérer la composition **Accueil éditable** de la catégorie **Kekidi**, puis la choisir comme page d'accueil dans **Réglages → Lecture**. Si WordPress affiche son article d'exemple, le passer en brouillon dans **Articles** : tous les articles publiés sont considérés comme des réponses et apparaissent dans la liste.

Pour mettre à jour un site qui utilise déjà l'ancien thème Kekidi, préparer la page **avant** de téléverser le nouveau ZIP :

1. Créer une page **Accueil** et y insérer la composition actuelle **Accueil Kekidi**.
2. Dans **Réglages → Lecture**, choisir **Une page statique**, puis **Accueil** comme page d'accueil.
3. Téléverser et activer le nouveau ZIP.
4. Dans **Pages → Accueil**, remplacer les anciennes sections par la composition **Accueil éditable** (catégorie **Kekidi**) et enregistrer.

Cette dernière insertion active le mode « contenu uniquement » sur les sections : les éditeurs peuvent modifier les textes depuis **Pages → Accueil**, tandis que les commandes de mise en page sont masquées/verrouillées par défaut. L'en-tête, la navigation et le pied de page restent des éléments communs du thème.

## 2. Importer les réponses

1. Ouvrir **Outils → Importer → WordPress**.
2. Installer l'importateur officiel proposé par WordPress, puis lancer l'importateur.
3. Sélectionner `reponses-kekidi.xml`.
4. Attribuer les contenus à ton utilisateur existant. Aucun compte supplémentaire n'est nécessaire.
5. Terminer l'import. Les contenus ne comportent pas de pièces jointes à télécharger.

L'importateur est la seule extension nécessaire à cette procédure ; il peut être désactivé ensuite. Aucune extension n'est nécessaire pour le fonctionnement du thème.

## 3. Ajouter le rôle « Super éditeur » (optionnel)

Pour donner à un compte les droits d'un éditeur, l'accès à l'Éditeur de site et la consultation des statistiques Koko :

1. Dans **Extensions → Ajouter une extension → Téléverser une extension**, choisir `extensions/kekidi-super-editeur.zip`.
2. Installer puis activer l'extension.
3. Dans **Comptes → Ajouter un compte** (ou en modifiant un compte existant), choisir **Super éditeur**.

Koko Analytics doit être installé et activé pour que son menu de statistiques apparaisse. Ce rôle ne peut pas gérer les extensions, les thèmes, les comptes ni les réglages WordPress.

## 4. Régler les adresses

Dans **Réglages → Permaliens**, choisir **Structure personnalisée** et saisir :

```text
/reponses/%postname%/
```

Enregistrer, puis ouvrir une réponse depuis le site pour vérifier que l'adresse fonctionne. Si le serveur renvoie une erreur 404, revenir provisoirement au réglage précédent et vérifier la réécriture des URL chez l'hébergeur.

Les adresses attendues sont :

- `https://kekidi.great-site.net/reponses/espace-public/`
- `https://kekidi.great-site.net/reponses/arbres-en-ville/`
- `https://kekidi.great-site.net/reponses/decisions-locales/`

Elles diffèrent des fichiers `.html` de la maquette. Si des QR codes pointent déjà vers les anciennes adresses, mettre des redirections avant de les diffuser. Sinon, générer les QR codes à partir des nouvelles adresses vérifiées. Garder les identifiants et le sous-domaine une fois les QR codes imprimés.

Dans **Réglages → Général**, renseigner le titre **Kekidi?!**, la langue française et le fuseau horaire souhaité. Vérifier que les adresses du site utilisent HTTPS avant d'imprimer les QR codes. Ne modifier ces adresses que si le certificat fonctionne déjà.

## 4. Créer une nouvelle réponse

Les réponses utilisent la rubrique **Articles**, ce qui permet de les afficher automatiquement sur l'accueil sans extension supplémentaire.

1. Ouvrir **Articles → Ajouter un article**.
2. Mettre la question dans le titre.
3. Dans l'outil d'insertion **+ → Compositions**, chercher **Nouvelle réponse** dans la catégorie **Kekidi**. Selon la version, WordPress peut employer le mot « Motifs ».
4. Insérer cette composition une seule fois dans l'article vide.
5. Remplacer le texte de la réponse courte et celui des explications.
6. Choisir ou créer une catégorie, par exemple **Cadre de vie**.
7. Si des références existent, ajouter la composition **Sources de la réponse**, remplacer son texte et insérer les liens. Ne pas ajouter de rubrique vide.
8. Vérifier l'identifiant dans les réglages de l'article, prévisualiser, puis publier.

Le modèle **Publication individuelle** donne automatiquement aux articles l'apparence d'une réponse. Le modèle nommé **Réponse Kekidi** est aussi disponible pour les articles et les pages ; une page classique utilisant ce modèle ne sera toutefois pas ajoutée automatiquement à la liste des réponses de l'accueil.

Les réponses sont affichées dans l'ordre de leur création. L'accueil présente douze réponses à la fois, puis une pagination. Les commentaires ne sont pas affichés par le thème ; ils sont fermés sur les trois réponses importées. Pour les nouveaux articles, régler leur ouverture dans **Réglages → Discussion** selon les besoins.

## 5. Modifier les éléments communs

Ouvrir **Apparence → Éditeur** :

- **Styles** : modifier les polices et la palette commune. Éditer les couleurs nommées Crème, Encre, Rose, Menthe, Jaune et Bleu pour que les composants de la maquette suivent les changements.
- **Compositions → Éléments de modèle** : modifier **En-tête accueil**, **En-tête réponses**, **Marque commune** ou **Pied de page commun**. La modification se répercute partout où cet élément est utilisé. Les deux en-têtes préservent les variantes de la maquette ; la marque est partagée.
- **Pages → Accueil** : modifier les textes de présentation, l'affiche, le fonctionnement et la section collectif. La composition **Accueil éditable** garde les sections structurées ; la liste de questions se remplit avec les articles publiés.
- **Modèles → Publication individuelle** : modifier l'enveloppe commune des réponses, par exemple la place du titre ou le retour vers les questions.
- **Modèles → Réponse Kekidi** : modifier l'enveloppe des contenus auxquels ce modèle a été attribué explicitement.

Les textes de chaque réponse restent dans son article. La composition **Nouvelle réponse** fournit une structure de départ indépendante : déplacer un bloc dans un article ne déplace pas celui des autres articles. Les styles et les éléments de modèle partagés se mettent, eux, à jour globalement. Garder le bloc **Contenu de la publication** dans les modèles pour que les réponses restent affichées.

Les modifications effectuées dans l'éditeur de site sont enregistrées dans la base de données WordPress. Une sauvegarde du ZIP du thème ne suffit donc pas à conserver ces modifications : sauvegarder aussi la base de données.

## 6. Vérifier avant de diffuser

Ouvrir l'accueil et les trois réponses sur ordinateur et téléphone. Vérifier la navigation, les liens vers les sources, les couleurs, les polices et le parcours clavier. Faire une réponse d'essai en brouillon pour vérifier l'insertion et la modification de la composition.

Le thème a été activé et les réponses importées dans WordPress Studio. Les modèles et compositions ont été validés dans Gutenberg ; le rendu a été comparé à la maquette aux formats ordinateur et mobile. Le site public InfinityFree n'a pas été modifié depuis cette validation locale.

## Régénérer les fichiers depuis la maquette

Depuis le dossier du projet, avec Node 24 ou plus récent :

```sh
node build-wordpress.ts
uv run --no-project python package-wordpress.py
```

Le générateur utilise `questions.json` et copie les styles et polices locaux. Les fichiers du thème sont générés : les modifications de son code doivent être faites dans `build-wordpress.ts`, puis régénérées. Les modifications effectuées dans WordPress ne sont pas récupérées dans la maquette ; après l'import, WordPress devient la source du contenu éditorial.
