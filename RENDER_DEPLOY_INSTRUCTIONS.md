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
- Renseignez `APP_KEY`, `FRONTEND_URL` et `CORS_ALLOWED_ORIGINS` sur le service backend, puis `VITE_API_BASE_URL=https://autchain-backend.onrender.com/api` sur le service frontend. Remplacez les URLs d'exemple par vos domaines réels.
- Si vous voulez, je peux générer le PR description et le diff que vous pourrez coller dans votre plateforme Git.
