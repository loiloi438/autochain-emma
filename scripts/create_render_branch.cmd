@echo off
set BRANCH=feature/render-deploy

ngit checkout -b %BRANCH%
git add render.yaml backend/Dockerfile
git commit -m "Add Render manifest and backend Dockerfile for deployment"
git push -u origin %BRANCH%
echo Pushed branch %BRANCH% to origin.
