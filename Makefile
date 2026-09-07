.PHONY: up down build test frontend-test api-test sim-test
up:
	docker compose up --build

down:
	docker compose down

build:
	docker compose build

test: frontend-test api-test sim-test

frontend-test:
	cd frontend && npm ci && npm run build

api-test:
	cd smartlab-api && composer install && php artisan test

sim-test:
	cd smart-lab-sim-api && python -m pip install -r requirements-dev.txt && PYTHONPATH=. pytest
