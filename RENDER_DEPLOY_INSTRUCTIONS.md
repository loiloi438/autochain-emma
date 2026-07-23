# Déployer sur Render — étapes Git (locale)

Ce fichier contient les commandes pour créer une branche, committer les fichiers ajoutés (`render.yaml`, `backend/Dockerfile`) et pousser sur votre remote.

Exécutez ces commandes depuis la racine du repo :

```bash
# crée une branche dédiée
git checkout -b feature/render-deploy

# ajoute les fichiers générés
git add render.yaml backend/Dockerfile

# commit
git commit -m "Add Render manifest and backend Dockerfile for deployment"

# pousse vers l'origine et crée la branche distante
git push -u origin feature/render-deploy
```

Après push, ouvrez Render (https://render.com), créez un nouveau service "Web Service" et/ou "Static Site" depuis le repo et acceptez le manifeste `render.yaml` lors de la configuration.

Notes:
- Si votre repo contient un `frontend` monorepo, Render trouvera le fichier `render.yaml` et créera les services définis.
- Renseignez les variables d'environnement `APP_KEY` et `VITE_API_URL` dans le dashboard Render pour que l'app fonctionne en production.
- Si vous voulez, je peux générer le PR description et le diff que vous pourrez coller dans votre plateforme Git.
