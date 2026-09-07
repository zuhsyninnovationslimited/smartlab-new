# SmartLab Validation Report

Validation performed on 16 July 2026.

## Passed checks

- React production build: passed with Vite 8
- React static analysis: passed with ESLint, zero errors
- FastAPI tests: 3 passed
  - health endpoint
  - CO₂ simulation
  - heat-conduction simulation
- Python byte-code compilation: passed
- PHP syntax validation: 59 files passed
- Railway JSON parsing: passed for both services
- Docker Compose YAML parsing: passed
- Shell entrypoint syntax validation: passed
- Stale legacy service-reference scan: passed

## Laravel runtime test note

The Laravel Pest tests are included under `smartlab-api/tests`. They were not executed in the artifact-generation container because Composer and the PHP vendor directory were unavailable there. The root Dockerfile installs the locked Composer dependencies during its build. Run either of the following in a normal development environment:

```bash
cd smartlab-api
composer install
php artisan test
```

or build the complete Docker stack:

```bash
docker compose up --build
```

## Included safeguards

- Public registration always creates a Student role.
- Privileged routes require Instructor or Administrator middleware.
- Simulation access for students requires a passed pre-lab quiz.
- Report attempts are checked against the authenticated student and experiment.
- Uploaded file types and sizes are validated.
- The FastAPI service supports a shared-secret header.
- AI output has a safety-conscious offline fallback and does not reveal active quiz answers.
