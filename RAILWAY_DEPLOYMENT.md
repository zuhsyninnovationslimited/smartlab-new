# Deploy SmartLab on Railway

This repository is a monorepo containing a public Laravel/React service and a private FastAPI simulation service.

## 1. Push the project to GitHub

Create a new private repository and push the complete `SmartLab` folder.

## 2. Create a Railway project and PostgreSQL service

Create a new Railway project, add PostgreSQL, and keep it in the same project as the application services.

## 3. Create the public `smartlab-web` service

- Deploy from the GitHub repository.
- Keep the service root at the repository root.
- Railway will detect the root `Dockerfile` and `railway.json`.
- Generate a public domain after the service becomes healthy.
- Attach a persistent volume mounted at:

  ```text
  /var/www/html/storage/app/public
  ```

### Web-service variables

Use Railway variable references for the PostgreSQL plugin where possible:

```text
APP_NAME=SmartLab
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_KEY=base64:<generated-32-byte-key>
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}
DB_SSLMODE=prefer

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
RUN_MIGRATIONS=true
RUN_SEEDER=true

SIMULATION_API_URL=http://${{smartlab-simulation.RAILWAY_PRIVATE_DOMAIN}}:${{smartlab-simulation.PORT}}
SIMULATION_TIMEOUT=20
SMART_LAB_API_KEY=<long-random-shared-secret>

ADMIN_EMAIL=<your-admin-email>
ADMIN_PASSWORD=<strong-unique-admin-password>
```

Generate `APP_KEY` locally:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

After the first successful deployment and seed, change:

```text
RUN_SEEDER=false
```

## 4. Create the private `smartlab-simulation` service

- Add another service from the same GitHub repository.
- Set its **Root Directory** to:

  ```text
  /smart-lab-sim-api
  ```

- Do not generate a public domain.
- Railway will use `smart-lab-sim-api/Dockerfile` and its `railway.json`.

### Simulation-service variables

```text
SMART_LAB_API_KEY=<the-same-long-random-shared-secret>
```

Railway provides the service `PORT`; the container command reads it automatically.

## 5. Verify deployment

Confirm:

- `https://your-domain/up` returns a successful Laravel health response.
- The landing page loads and registration creates only a Student account.
- Seeded instructor login works.
- Both experiments display their manual, video, and quiz.
- A passed quiz unlocks its simulation.
- Uploaded files remain after a redeploy.
- The AI assistant provides its built-in response without an external key.

## 6. Optional external AI provider

Add these only when needed:

```text
OPENAI_API_KEY=<provider-key>
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4.1-mini
```

Any OpenAI-compatible endpoint that implements `/chat/completions` can be used by changing the base URL and model.

## 7. Production hardening checklist

- Replace every seeded password.
- Disable production seeding after initial setup.
- Back up PostgreSQL and uploaded files.
- Restrict administrator accounts.
- Configure a custom domain and HTTPS.
- Review upload size and storage cost.
- Enable a real email provider before activating password-reset flows.
- Run the automated test suites after material changes.
