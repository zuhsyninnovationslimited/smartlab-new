# Project Structure

```text
SmartLab/
├── frontend/                 React 19 user interface
│   └── src/
│       ├── components/       Shell, navigation, AI widget, route guards
│       ├── context/          Authentication state
│       ├── pages/            Student, instructor, and admin workspaces
│       └── services/         Axios API client
├── smartlab-api/             Laravel 12 REST API
│   ├── app/
│   │   ├── Http/Controllers/API/
│   │   ├── Http/Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/migrations/  Unified production schema
│   ├── database/seeders/     CO₂ and heat-transfer demonstration content
│   └── tests/                Authentication and workflow tests
├── smart-lab-sim-api/        Private FastAPI scientific engine
│   ├── app/
│   └── tests/
├── docker/                   Apache security and startup scripts
├── Dockerfile                Multi-stage React + Laravel image
├── compose.yaml              Complete local stack
└── railway.json              Public web-service deployment settings
```

## Main data relationships

- An Instructor creates many Experiments.
- An Experiment has Manuals, Instructional Videos, one Pre-Lab Quiz, simulation attempts, progress records, and reports.
- A Quiz has many Questions and Attempts.
- A Student has a single Progress Record per Experiment.
- Experiment Attempts preserve every input parameter and result payload for reproducibility.
