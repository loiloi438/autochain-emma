#!/usr/bin/env bash
set -euo pipefail

BRANCH=feature/render-deploy

git checkout -b "$BRANCH"

git add render.yaml backend/Dockerfile

git commit -m "Add Render manifest and backend Dockerfile for deployment"

git push -u origin "$BRANCH"

echo "Pushed branch $BRANCH to origin."
