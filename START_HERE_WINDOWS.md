# Start SmartLab on Windows 11

## Requirements

- Docker Desktop with WSL 2 enabled
- At least 4 GB free memory for Docker
- An internet connection for the first image build

## Automatic start

1. Extract the SmartLab ZIP.
2. Open the extracted `SmartLab` folder.
3. Right-click inside the folder and choose **Open in Terminal**.
4. Run:

   ```powershell
   Set-ExecutionPolicy -Scope Process Bypass
   .\scripts\setup-local.ps1
   ```

5. Wait for the containers to become healthy.
6. Open `http://localhost:8080`.

## Demo login

- Student: `student@smartlab.test` / `Student2026!`
- Instructor: `instructor@smartlab.test` / `Instructor2026!`
- Administrator: the `ADMIN_EMAIL` and `ADMIN_PASSWORD` values in `.env`

## Stop the system

Press `Ctrl+C`, then run:

```powershell
docker compose down
```

To remove the local database and uploaded files as well:

```powershell
docker compose down -v
```
