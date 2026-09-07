# SmartLab — Production-Ready Virtual Laboratory

SmartLab is a complete role-based learning platform built with **Laravel 12**, **React 19**, **PostgreSQL**, and a private **FastAPI scientific simulation service**. It is designed to run locally with Docker Compose and deploy as a Railway monorepo.

## Included modules

- Student, Instructor, and Administrator authentication and authorization
- Digital laboratory manuals with Markdown content and PDF/Word uploads
- Uploaded, YouTube, and external instructional videos
- Unified pre-lab quiz engine with automatic grading and simulator unlocking
- CO₂ generation simulation with stoichiometry, limiting reagent, ideal-gas volume, yield, and time-series data
- Heat-transfer simulation for conduction, convection, and radiation
- Automatic learning-progress milestones
- Laboratory report submission and instructor grading
- Context-aware AI Lab Assistant with an offline safety-conscious fallback
- Instructor Studio for experiments, manuals, videos, and quizzes
- Administrator user/role management
- Responsive premium UI, health checks, tests, Dockerfiles, and Railway configuration

## Quick start with Docker

1. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

2. Generate a Laravel key and paste it into `APP_KEY`:

   ```bash
   docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```

3. Change `SMART_LAB_API_KEY`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD`.

4. Start the complete stack:

   ```bash
   docker compose up --build
   ```

5. Open `http://localhost:8080`.

The seeder creates the two published experiments and these demonstration users:

| Role | Email | Password |
|---|---|---|
| Student | `student@smartlab.test` | `Student2026!` |
| Instructor | `instructor@smartlab.test` | `Instructor2026!` |
| Administrator | value of `ADMIN_EMAIL` | value of `ADMIN_PASSWORD` |

Change all demonstration passwords before any public deployment.

## Local development without Docker

### Backend

```bash
cd smartlab-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### Scientific engine

```bash
cd smart-lab-sim-api
python -m venv .venv
# Linux/macOS: source .venv/bin/activate
# Windows: .venv\\Scripts\\activate
pip install -r requirements-dev.txt
uvicorn app.main:app --reload --port 8000
```

## Testing

```bash
cd frontend && npm run build
cd ../smartlab-api && php artisan test
cd ../smart-lab-sim-api && PYTHONPATH=. pytest
```

## Deployment

Follow [`RAILWAY_DEPLOYMENT.md`](RAILWAY_DEPLOYMENT.md). The root Dockerfile compiles React and serves it through Laravel/Apache. The FastAPI service is deployed separately from `smart-lab-sim-api` and remains private.

## Important production notes

- Attach persistent storage to `/var/www/html/storage/app/public` or configure an S3-compatible disk for uploads.
- Keep the simulation service private and use the same strong `SMART_LAB_API_KEY` in both services.
- Set `RUN_SEEDER=false` after the first successful production seed.
- Use a real mail provider if password-reset and email-verification workflows are later enabled.
- The AI fallback works without a paid API. Set an OpenAI-compatible key only when external generative responses are required.
