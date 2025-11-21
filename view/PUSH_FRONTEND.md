# Push `frontend` to GitHub

This helper explains how to push the local `frontend` folder to a GitHub repository.

Requirements
- Git for Windows installed and available in `PATH` (https://git-scm.com/download/win)

Automatic helper script
- There's a PowerShell script in the project root: `push_frontend_to_github.ps1`.
- Example usage (from the project root in PowerShell):

```powershell
# run with defaults (push to https://github.com/baieb540/project.ww.git on branch main)
.\n+\push_frontend_to_github.ps1

# or supply a different remote/branch
.
\push_frontend_to_github.ps1 -RemoteUrl "https://github.com/baieb540/project.ww.git" -Branch main
```

What the script does
- Verifies `frontend` exists.
- Copies `frontend` contents to a temporary folder.
- Initializes a temporary git repo, commits the files, adds the given remote, and pushes to the chosen branch.

Manual alternative (one-off)

1. Open PowerShell in the project root.
2. Run:

```powershell
# create a temp folder and copy frontend
New-Item -ItemType Directory -Path .\temp-frontend-push
Copy-Item -Path .\frontend\* -Destination .\temp-frontend-push -Recurse -Force
Set-Location -LiteralPath .\temp-frontend-push
git init
git add .
git commit -m "Add frontend"
git remote add origin https://github.com/baieb540/project.ww.git
git branch -M main
git push -u origin main
```

Authentication
- If you get authentication errors, create a Personal Access Token (PAT) and use it when prompted for a password, or set up SSH keys and use the SSH remote URL.

If you want me to try pushing from this environment, install Git on the machine or run the script locally and paste any errors here and I'll help fix them.
