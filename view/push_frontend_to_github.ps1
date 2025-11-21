param(
  [string]$RemoteUrl = "https://github.com/baieb540/project.ww.git",
  [string]$Branch = "main"
)

Write-Host "Push frontend to GitHub helper" -ForegroundColor Cyan

# locate repo root (script lives in repo root)
$RepoRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$FrontendPath = Join-Path $RepoRoot "frontend"

if (-not (Test-Path $FrontendPath)) {
  Write-Error "Could not find folder: $FrontendPath. Run this script from the project root where the 'frontend' folder exists."
  exit 1
}

# check that git is available
try {
  git --version > $null 2>&1
} catch {
  Write-Error "'git' is not installed or not in PATH. Please install Git for Windows and try again: https://git-scm.com/download/win"
  exit 1
}

$TempDir = Join-Path $RepoRoot ("temp-frontend-push-" + (Get-Random -Maximum 1000000))
Write-Host "Creating temporary folder: $TempDir"
New-Item -ItemType Directory -Path $TempDir | Out-Null

Write-Host "Copying 'frontend' contents to temporary folder..."
Copy-Item -Path (Join-Path $FrontendPath '*') -Destination $TempDir -Recurse -Force

Set-Location -LiteralPath $TempDir

Write-Host "Initializing temporary git repo..."
git init > $null
git add .
git commit -m "Add frontend" --no-gpg-sign

Write-Host "Adding remote: $RemoteUrl"
git remote add origin $RemoteUrl

Write-Host "Setting branch to '$Branch' and pushing to remote..."
git branch -M $Branch

try {
  git push -u origin $Branch
  Write-Host "Push completed successfully." -ForegroundColor Green
  Write-Host "Temporary folder left at: $TempDir" -ForegroundColor Yellow
} catch {
  Write-Error "Push failed. Common causes: authentication required, incorrect remote URL, or network issue."
  Write-Host "You can retry manually from the temp folder: `cd $TempDir` and run the git push command again after fixing authentication." -ForegroundColor Yellow
  exit 1
}

Write-Host "Done. If you want, delete the folder: $TempDir" -ForegroundColor Cyan
