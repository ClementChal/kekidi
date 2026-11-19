# MCP WordPress Studio

La CLI WordPress Studio installée fournit déjà son serveur MCP. Pour ce projet, utiliser ce serveur natif : aucun plugin MCP Adapter supplémentaire n'est nécessaire et cette connexion n'utilise pas Docker.

## Connecter Codex

Depuis le terminal habituel de l'ordinateur :

```sh
codex mcp add wordpress-studio -- studio mcp
codex mcp get wordpress-studio
```

Si Codex ne trouve pas la commande `studio`, utiliser son chemin absolu affiché par `command -v studio` à la place de `studio` dans la première commande.

Redémarrer la session Codex pour charger les nouveaux outils. Aucun identifiant WordPress n'est nécessaire à l'enregistrement du serveur.

## Fonctions disponibles

Le MCP Studio fournit notamment la liste et la gestion des sites locaux, l'exécution de WP-CLI, la validation des blocs, les captures d'écran et l'inspection du rendu. Il permet donc de diagnostiquer les problèmes de Gutenberg et de vérifier le design sur ordinateur et mobile.

Le serveur voit les sites enregistrés dans Studio. Une installation existante dans Docker n'est pas automatiquement un site Studio. Utiliser `studio list` dans le terminal habituel pour vérifier les sites disponibles ; au besoin, créer un site Studio ou y importer une sauvegarde avant de travailler dessus.

La mise en ligne chez InfinityFree reste effectuée en installant le ZIP du thème validé ; les commandes Studio de synchronisation WordPress.com ne servent pas à publier sur InfinityFree.

## État de la préparation

Le serveur `wordpress-studio` est connecté et répond. Le site actif **Kekidi** est créé dans `<DOSSIER_SITE_STUDIO>` et fonctionne à `http://localhost:8881`. Le thème et les trois réponses ont été installés ; les blocs ont été validés et les pages inspectées grâce au MCP. La pile Docker précédente n'est plus l'environnement de développement de référence.

Documentation : [MCP dans WordPress Studio](https://developer.wordpress.com/docs/developer-tools/studio/mcp-on-studio/).
