# AutoChain Emma+ — Backend

Backend Laravel pour le projet AutoChain Emma+ (gestion de parc automobile avec preuves blockchain).

## Architecture rapide
- Framework: Laravel
- Auth: Laravel Sanctum
- Roles: spatie/laravel-permission
- Blockchain: smart contract `VehicleRegistry.sol` (ABI: `blockchain/shared/VehicleRegistry.json`)
- Documents: stockés localement (`storage/app/vehicles/...`) avec SHA256; option IPFS pour documents publics.

## Endpoints principaux (API)
- `POST /api/login` — authentification
- `GET /api/me` — profil utilisateur
- `POST /api/wallet` — lier adresse wallet
- `GET /api/vehicles`, `POST /api/vehicles`, `GET/PATCH /api/vehicles/{id}`
- `POST /api/vehicles/{id}/mileage` — relevé kilométrique (driver/manager/admin)
- `POST /api/vehicles/{id}/maintenance` — maintenance (garage/admin)
- `POST /api/vehicles/{id}/documents` — upload documents (manager/admin)
- `POST /api/blockchain/txs` — ingestion et application des transactions on-chain (confirmées)
- `POST /api/blockchain/sync-roles` — synchroniser rôles on-chain → DB

## Configuration (.env)
- `IPFS_ENABLED=false` (par défaut)
- `IPFS_API_URL=http://127.0.0.1:5001`

## Scheduler
La commande `autochain:sync-roles` est planifiée dans `app/Console/Kernel.php` (toutes les heures). Sous Windows utilise le Planificateur de tâches pour exécuter `php artisan schedule:run` chaque minute.

## Tests
- Les tests utilisent sqlite en mémoire (config dans `phpunit.xml`).
- Pour lancer localement (ajuste le chemin vers `php.exe` si nécessaire) :
```powershell
powershell -ExecutionPolicy Bypass -File backend\scripts\run-tests.ps1 -PhpPath 'C:\laragon\bin\php\php-8.2.10\php.exe'
```

## CI
Un workflow GitHub Actions exécute les tests sur push/PR (voir `.github/workflows/backend-tests.yml`).

## Notes de sécurité
- Les données nominatives ne sont pas stockées on-chain (RGPD): seuls des hash/identifiants.
- Scanner et filtrer les uploads en production.

## Prochaines étapes recommandées
1. Remplacer le fallback `ipfs-sim-*` par un service de pinning IPFS en prod.
2. Ajouter tests E2E frontend.
3. Documenter précisément le format `payload` pour `POST /api/blockchain/txs`.


