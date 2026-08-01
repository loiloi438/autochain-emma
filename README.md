# AutoChain Emma+

Gestion de parc automobile avec double numerique blockchain.

## Structure

- `backend/` Laravel 11 API (Sanctum + Spatie Permission)
- `frontend/` Vue 3 SPA (Vite + Pinia + ethers.js)
- `blockchain/` Hardhat + Solidity (`VehicleRegistry`)

## Prefrequis

- Laragon (PHP 8.3, MySQL, Composer)
- Node.js 18+
- MetaMask

## Demarrage rapide

### 1. Base de donnees

Dans Laragon, demarrer MySQL puis:

```bash
cd backend
php artisan migrate:fresh --seed
```

Comptes demo (mot de passe: `password`) :

- `admin@autochain.test`
- `gestionnaire@autochain.test`
- `chauffeur@autochain.test`
- `garage@autochain.test`
- `auditeur@autochain.test`

### 2. API Laravel

```bash
cd backend
php artisan serve
```

API: `http://127.0.0.1:8000/api`

### 3. Blockchain locale

```bash
cd blockchain
npm install
npx hardhat test
npx hardhat node
```

Dans un autre terminal:

```bash
cd blockchain
npx hardhat run scripts/deploy.js --network localhost
```

Le script exporte l'ABI + adresse vers:

- `shared/VehicleRegistry.json`
- `frontend/src/abi/VehicleRegistry.json`
- `backend/storage/app/blockchain/VehicleRegistry.json`

Importez un compte Hardhat dans MetaMask (reseau `http://127.0.0.1:8545`, chainId `31337`).

### 4. Frontend

```bash
cd frontend
npm install
npm run dev
```

UI: `http://127.0.0.1:5173`

## Fonctionnalites MVP

- Roles: Super Admin, Gestionnaire, Chauffeur, Garagiste, Auditeur
- Vehicules, affectations, documents hashes SHA-256
- Alertes CT / assurance / vidange (`php artisan autochain:generate-alerts`)
- Timeline mixte (badge certifie blockchain vs administratif)
- MetaMask pour km, maintenance et ancrage de documents
- Consultation publique auditeur sans donnees nominatives on-chain

## Principe RGPD

Seuls les identifiants techniques, km, hashs et evenements critiques sont stockes on-chain.
Noms, documents confidentiels et logique metier restent off-chain dans Laravel.
