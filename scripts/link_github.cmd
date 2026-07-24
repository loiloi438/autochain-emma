@echo off
REM Usage: link_github.cmd <git_remote_url> [branch]
SETLOCAL ENABLEDELAYEDEXPANSION
IF "%~1"=="" (
  echo Usage: %~nx0 ^<git_remote_url^> [branch]
  echo Example: %~nx0 git@github.com:username/autochain-emma.git feature/render-deploy
  exit /b 1
)
SET REMOTE_URL=%~1
IF "%~2"=="" (
  SET BRANCH=main
) ELSE (
  SET BRANCH=%~2
)

ngit remote get-url origin >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
  echo Remote 'origin' already exists.
  echo To update: git remote set-url origin %REMOTE_URL%
) ELSE (
  git remote add origin %REMOTE_URL%
  echo Added remote origin -> %REMOTE_URL%
)

ngit rev-parse --verify %BRANCH% >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
  git checkout -b %BRANCH%
) ELSE (
  git checkout %BRANCH%
)

ngit push -u origin %BRANCH%

echo Pushed branch %BRANCH% to %REMOTE_URL%.
ENDLOCAL
