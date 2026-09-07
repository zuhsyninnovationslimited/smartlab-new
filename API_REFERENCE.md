# API Reference

All application endpoints use the `/api/v1` prefix.

## Public

- `POST /register`
- `POST /login`

## Authenticated

- `GET /me`
- `POST /logout`
- `GET /dashboard`
- `GET /experiments`
- `GET /experiments/{slug}`
- `POST /experiments/{slug}/progress`
- `POST /quizzes/{quiz}/submit`
- `POST /experiments/{slug}/simulate`
- `GET /simulation-history`
- `GET /progress`
- `GET /reports`
- `POST /reports`
- `POST /ai/ask`
- `GET /ai/history`

## Instructor or Administrator

- `POST /experiments`
- `PUT /experiments/{id}`
- `DELETE /experiments/{id}`
- `POST /manuals`
- `POST /manuals/{id}`
- `DELETE /manuals/{id}`
- `POST /videos`
- `POST /videos/{id}`
- `DELETE /videos/{id}`
- `POST /quizzes/upsert`
- `POST /reports/{id}/grade`
- `GET /users`
- `POST /users`
- `PUT /users/{id}`

Bearer tokens are issued by login and registration. File endpoints use `multipart/form-data`.
