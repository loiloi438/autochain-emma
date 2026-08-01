Deployment checklist and basic guide

1) Prerequisites
- Docker & Docker Compose installed on the host
- Domain and DNS configured (optional)

2) Build & run locally (Docker)

```bash
# from repo root
docker compose build
docker compose up -d
```

- Backend will be available at `http://localhost:8000`
- Frontend at `http://localhost:3000`

3) Database & migrations

The Dockerfile runs `php artisan migrate` on container start (non-fatal). For production, run migrations manually:

```bash
# shell into container
docker compose exec backend sh
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

4) Environment variables
- Configure `.env` (backend) before building for production. At minimum set:
  - `APP_KEY` (generated with `php artisan key:generate --show`)
  - `APP_URL`
  - `DB_CONNECTION`, `DB_*`
  - `VITE_API_URL` for the frontend build step

5) Serving in production
- Recommended: run backend under PHP-FPM + Nginx, and serve frontend statically (nginx). The provided Dockerfiles are a simple starting point.
- For managed platforms (Vercel/Netlify): deploy the `frontend` `dist/` as a static site and point `VITE_API_URL` to your backend host.
- For platforms like Render or Fly: use the `docker-compose` or build separate images.

6) MetaMask / Web3 notes
- Ensure CORS allows the frontend origin and `supports_credentials` is true if using cookies/sanctum.
- Use HTTPS for wallets and production.

7) CI/CD
- Provide a GitHub Actions workflow to build images and push to registry, then update the host.

If you want, I can:
- Add a `Dockerfile` variant using PHP-FPM + Nginx for production.
- Add GitHub Actions for build and push.
- Create a Playwright-based E2E test that injects a mock `window.ethereum` to simulate MetaMask for automated E2E runs.
