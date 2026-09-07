$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

$bytes = New-Object byte[] 32
$rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$rng.GetBytes($bytes)
$rng.Dispose()
$appKey = "base64:" + [Convert]::ToBase64String($bytes)

$secretBytes = New-Object byte[] 32
$rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
$rng.GetBytes($secretBytes)
$rng.Dispose()
$sharedSecret = ([BitConverter]::ToString($secretBytes)).Replace("-", "").ToLower()

$content = Get-Content ".env"
$content = $content -replace '^APP_KEY=.*$', "APP_KEY=$appKey"
$content = $content -replace '^SMART_LAB_API_KEY=.*$', "SMART_LAB_API_KEY=$sharedSecret"
Set-Content ".env" $content

Write-Host "SmartLab environment prepared." -ForegroundColor Green
Write-Host "Starting PostgreSQL, FastAPI, Laravel, and React production build..." -ForegroundColor Cyan
docker compose up --build
