# Core Sync Operations

This document defines the shared Core to GSO sync process.

## Repositories

- Core repository: `renz1799/Webcraft-Core-System`
- GSO repository: `renz1799/GSO-System-for-San-Francisco-Quezon-V2`

## Branch Model

- Core source branch: `main`
- GSO base branch: `main`
- GSO sync branch: `codex/core-sync`

Rule: never merge Core changes directly into GSO `main` locally. Always land them through `codex/core-sync` and a PR.

## Source Of Truth Policy

- Core (`Webcraft-Core-System`) is the source of truth for shared architecture, contracts, and generic service behavior.
- GSO must not keep long-lived forks of Core-owned/shared primitives.
- If GSO needs a new behavior, implement it in Core first in a generic way, then sync it down to GSO.
- During conflict resolution for Core-owned/shared primitives (for example `app/Services/Contracts/**`, `app/Services/Tasks/**`, `app/Repositories/Contracts/**`), prefer Core-side changes and refactor GSO module code to match.
- Temporary direct edits in GSO Core-owned files are exception-only and must include `allow-core-touch` plus a follow-up re-alignment plan.

## Automation Flow

1. A commit is pushed to Core `main`.
2. Core workflow dispatches event `core-main-updated` to GSO.
3. GSO workflow syncs Core `main` into `codex/core-sync`.
4. If merge is clean, GSO workflow pushes `codex/core-sync` and creates or updates PR to `main`.
5. If merge has conflicts, workflow uploads `docs/reports/core-sync-conflicts.txt` and fails.

## Workflow Files

- Core dispatcher: `.github/workflows/trigger-gso-core-sync.yml`
- GSO sync: `.github/workflows/core-sync.yml`
- Local sync script: `scripts/sync-core.ps1`

## Required Secrets

Both tokens can be classic PAT with `repo` scope.

| Secret Name | Repository | Purpose |
| --- | --- | --- |
| `GSO_SYNC_DISPATCH_TOKEN` | Core | Allows Core workflow to call GSO `repository_dispatch` |
| `CORE_SYNC_SOURCE_TOKEN` | GSO | Allows GSO workflow to clone/fetch Core private repo |

If using fine-grained PATs instead:

- `GSO_SYNC_DISPATCH_TOKEN`: must access GSO repo and allow dispatch-capable write operations.
- `CORE_SYNC_SOURCE_TOKEN`: must access Core repo with at least `Contents: Read`.

## Required Repository Setting (GSO)

In GSO repo, set:

- `Settings -> Actions -> General -> Workflow permissions`
- Select: `Read and write permissions`
- Check: `Allow GitHub Actions to create and approve pull requests`

Without this, PR creation step fails with GraphQL permission error.

## Daily Sync Usage

### Normal path

1. Push Core changes to `main`.
2. Wait for Core workflow `Trigger GSO Core Sync`.
3. Check GSO workflow `Core Sync`.
4. Review and merge PR `codex/core-sync -> main` in GSO.
5. Update local GSO:

```bash
git checkout main
git pull origin main
```

### When you need to inspect sync branch before merge

```bash
git checkout codex/core-sync
git pull origin codex/core-sync
```

Use this only when reviewing or resolving sync conflicts.

## Manual Local Sync (Fallback)

Run inside GSO repo root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\sync-core.ps1 `
  -CoreRemoteName "core" `
  -CoreRemoteUrl "https://github.com/renz1799/Webcraft-Core-System.git" `
  -CoreBranch "main" `
  -BaseBranch "main" `
  -WorkBranch "codex/core-sync" `
  -ConflictReportPath "docs/reports/core-sync-conflicts.txt"
```

## Conflict Handling

If conflicts occur:

1. Resolve conflicts on `codex/core-sync`.
2. Commit resolved merge.
3. Push `codex/core-sync`.
4. Merge PR into GSO `main`.

Conflict report path:

- `docs/reports/core-sync-conflicts.txt`

## Troubleshooting

### `Missing secret GSO_SYNC_DISPATCH_TOKEN`

- Add `GSO_SYNC_DISPATCH_TOKEN` in Core repo secrets.

### `Repository not found` when fetching Core in GSO workflow

- Verify `CORE_SYNC_SOURCE_TOKEN` exists in GSO secrets.
- Verify token can access Core private repo.

### `Worktree is not clean. Commit or stash your changes`

- Ensure workflow clones Core outside GSO working tree.
- Current workflow uses temp mirror clone for this reason.

### `did not send all necessary objects` / `revision walk setup failed`

- Caused by shallow/incomplete Core source clone.
- Use full mirror clone for Core source in workflow.

### `GitHub Actions is not permitted to create or approve pull requests`

- Enable GSO Actions setting: `Allow GitHub Actions to create and approve pull requests`.

### No PR appears even when Core dispatch succeeds

- Check GSO `Core Sync` run status.
- If run fails before PR step, fix failing step first.
- A PR is only created/updated when sync reaches push + PR steps.

## Local Credential Prompt Cleanup (Optional)

If local Git asks you to choose account `x-access-token`, clear cached entry:

```powershell
@"
protocol=https
host=github.com
username=x-access-token

"@ | git credential-manager erase
```

## Documentation Ownership

This document is shared between Core and GSO. Keep both copies aligned whenever the sync process changes.
