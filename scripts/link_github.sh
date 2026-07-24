#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <git_remote_url> [branch]

Example: $0 git@github.com:username/autochain-emma.git feature/render-deploy"
  exit 1
fi

REMOTE_URL="$1"
BRANCH="${2:-main}"

# add remote if not present
if git remote get-url origin >/dev/null 2>&1; then
  echo "Remote 'origin' already exists: $(git remote get-url origin)"
  echo "You can update it with: git remote set-url origin $REMOTE_URL"
else
  git remote add origin "$REMOTE_URL"
  echo "Added remote origin -> $REMOTE_URL"
fi

# ensure branch exists locally
if ! git rev-parse --verify "$BRANCH" >/dev/null 2>&1; then
  git checkout -b "$BRANCH"
else
  git checkout "$BRANCH"
fi

# push
git push -u origin "$BRANCH"

echo "Pushed branch $BRANCH to $REMOTE_URL (origin)."
