# Hostinger Deploy Setup

This repository deploys automatically from GitHub Actions on every push to `main`.

## Workflow

- Workflow file: `.github/workflows/hostinger-deploy.yml`
- Trigger:
  - push to `main`
  - manual `workflow_dispatch`

## Required GitHub Secrets

Set these in this repository's GitHub settings:

- `HOSTINGER_SSH_HOST`
- `HOSTINGER_SSH_USER`
- `HOSTINGER_SSH_PRIVATE_KEY`

## Required GitHub Variables

Set these in this repository's GitHub settings:

- `HOSTINGER_DEPLOY_PATH`
  - Use the Laravel app root, not the subdomain `public` directory.
  - For this deployment, set it to:
    - `/home/u991999878/domains/webcraft.ph/public_html/lgusanfrancisco`
- `HOSTINGER_SSH_PORT`
  - Optional
  - Defaults to `65002`
- `HOSTINGER_KNOWN_HOSTS`
  - Optional
  - If blank, the workflow uses `ssh-keyscan`
- `HOSTINGER_PHP_BIN`
  - Optional
  - Defaults to `php`
- `HOSTINGER_COMPOSER_BIN`
  - Optional
  - Defaults to `composer`

## Deployment Behavior

On each deploy the workflow:

1. Checks out the repository
2. Installs Node dependencies
3. Builds Vite assets
4. Syncs files to the remote Laravel app root with `rsync`
5. Runs remote Laravel deployment steps:
   - `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`
   - `php artisan migrate --force`
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
   - `php artisan storage:link` if needed

## Important Path Note

Your Hostinger subdomain may point to:

- `/home/u991999878/domains/webcraft.ph/public_html/lgusanfrancisco/public`

But the workflow must deploy to the Laravel project root:

- `/home/u991999878/domains/webcraft.ph/public_html/lgusanfrancisco`

The workflow validates this and fails if `HOSTINGER_DEPLOY_PATH` ends with `/public`.
