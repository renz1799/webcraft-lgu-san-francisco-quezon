# CORE COMMIT STRATEGY (Core System)

This document defines the **commit message format, commit splitting rules, staging discipline, and workflow expectations** for the Core System repository.

The goal is to keep Git history:

* Clear
* Reviewable
* Architecture-focused
* Easy to scan later
* Useful for debugging, rollback, and refactoring

This is not just a naming rule.
It is a **change management standard** for the Core System.

---

# Core Principle

Commits must represent **one logical change**.

Not:

* one coding session
* one day of work
* everything currently modified

A commit must answer:

**What architectural or behavioral change happened here?**

If the answer contains multiple unrelated changes, split the commit.

---

# Primary Goal

Core System commit history must read like an architecture timeline.

A good Git log should explain:

* what changed
* where it changed
* why it changed
* whether it was a feature, refactor, fix, or cleanup

The log should be understandable without opening every diff.

---

# Commit Message Format

Use this format:

```text
<type>(<scope>): <short summary>
```

Optional additional context may be added using multiple `-m` lines.

Example:

```bash
git commit -m "refactor(print): introduce paper profile architecture" \
-m "Separate report content from paper layout" \
-m "Introduce config driven paper definitions" \
-m "Prepare system for scalable multi paper support"
```

---

# Commit Types

Use these commit types consistently.

## feat

Use when adding a new capability or behavior.

Examples:

* new report feature
* new workflow capability
* new paper profile support
* new module behavior

Example:

```text
feat(print): add letter portrait paper support
```

---

## refactor

Use when changing structure or architecture without changing the intended feature behavior.

Examples:

* folder restructuring
* service extraction
* layout separation
* config consolidation
* controller simplification

Example:

```text
refactor(print): separate paper layouts from report content
```

---

## fix

Use when correcting a defect or broken behavior.

Examples:

* route mismatch
* preview and PDF mismatch
* incorrect pagination
* broken asset resolution

Example:

```text
fix(print): correct audit log pdf route binding
```

---

## perf

Use when improving performance without changing intended behavior.

Examples:

* query optimization
* payload reduction
* print generation efficiency

Example:

```text
perf(audit): optimize print report dataset retrieval
```

---

## docs

Use when updating standards, architecture notes, or internal documentation.

Examples:

* standard updates
* glossary additions
* print architecture docs
* commit strategy docs

Example:

```text
docs(core): add commit strategy standard
```

---

## chore

Use for maintenance work that is not a feature, fix, refactor, or performance change.

Examples:

* cleanup
* deleting unused files
* housekeeping
* tooling maintenance

Example:

```text
chore(print): remove legacy unused print partials
```

---

# Scope Rules

The scope identifies the system area affected.

Use stable, recognizable scopes.

Recommended scopes:

* `core`
* `print`
* `audit`
* `tasks`
* `dts`
* `reports`
* `auth`
* `drive`
* `notifications`
* `access`
* `profile`

Examples:

```text
refactor(core): simplify shared service contract patterns
feat(tasks): add reassignment workflow support
fix(auth): resolve login redirect loop
```

---

# Combined Scope Rule

Use combined scopes only when one logical change truly spans multiple domains.

Example:

```text
refactor(print,audit): migrate audit logs to paper profile architecture
```

Use combined scopes sparingly.

If changes can be split cleanly, prefer separate commits.

---

# Summary Line Rules

The summary line must be:

* specific
* short
* architecture-aware
* readable in Git log output

Good:

```text
refactor(print): introduce paper profile configuration
feat(print): add paper selector to workspace controls
fix(print): resolve preview route name mismatch
```

Bad:

```text
update print
fix stuff
changes
improvements
final update
```

Never use vague summaries.

---

# Multi `-m` Message Rules

Use additional `-m` lines to explain:

* architectural intent
* boundaries of the change
* why the change matters

Use them when the change affects structure, standards, or long-term maintainability.

Example:

```bash
git commit -m "refactor(print): resolve paper profiles in print service" \
-m "Merge paper defaults with module profile overrides" \
-m "Pass resolved profile to preview and PDF rendering" \
-m "Keep controller orchestration only"
```

Avoid overly long paragraphs.
Each `-m` line should read like a changelog bullet.

---

# Commit Splitting Rule

A commit must contain one logical change only.

Split commits by responsibility.

Examples of good split order:

1. configuration
2. backend orchestration
3. view architecture
4. UI behavior
5. docs

Example sequence:

```text
refactor(print): introduce paper profile configuration
refactor(print): resolve paper profiles in print service
refactor(print): convert audit log print to paper profile layout
feat(print): add multi paper selection support
```

This is preferred over one large mixed commit.

---

# Preferred Commit Order for Refactors

When a feature includes multiple layers, commit in this order when practical:

## 1. Architecture / config

Examples:

* new config contract
* new interfaces
* new structure rules

## 2. Backend flow

Examples:

* request updates
* service orchestration
* controller alignment
* repository usage changes

## 3. View or rendering structure

Examples:

* Blade restructuring
* layout extraction
* new rendering paths

## 4. UI capability

Examples:

* selector added
* button behavior updated
* interactive workflow added

## 5. Documentation

Examples:

* standards updated
* architecture documents aligned

This order keeps history readable and easier to review.

---

# Staging Discipline Rule

Do not commit everything shown in `git status` just because it is modified.

Stage files intentionally.

Recommended commands:

```bash
git add <file>
git diff --staged
git status
```

For precise splitting, use:

```bash
git add -p
```

This is strongly recommended for refactors.

---

# Interactive Staging Rule

Use `git add -p` when:

* one file contains multiple logical changes
* refactor and feature edits are mixed together
* you need to split a large change into clean commits

Interactive staging is preferred over creating messy combined commits.

---

# Refactor vs Feature Rule

Do not mix structural refactors and new behavior in the same commit unless separation is not practical.

Use:

```text
refactor(...)
```

for architecture and structure.

Use:

```text
feat(...)
```

for new user-visible capability.

Example:

Bad:

```text
feat(print): add paper profile support and move all layout files
```

Better split:

```text
refactor(print): introduce paper profile layout structure
feat(print): add multi paper profile selection
```

---

# Fix Commit Rule

When correcting a regression introduced during refactor or feature work, use a separate `fix(...)` commit.

Example:

```text
fix(print): correct audit log print route names in controls
```

Do not hide fixes inside unrelated refactor commits after the fact.

---

# Docs Commit Rule

Standards and documentation updates should usually be committed separately from implementation changes.

Examples:

```text
docs(core): add commit strategy standard
docs(print): update workspace rules for paper profiles
docs(print): document service paper profile resolution
```

This makes the standards timeline easier to follow.

---

# Anti-Patterns

Never do these:

* commit everything in one large "update" commit
* mix refactor, feature, fix, and docs in one commit without reason
* use vague messages like `changes`, `update`, `fix stuff`, or `final`
* commit unreviewed staged changes
* skip checking `git diff --staged`
* hide bug fixes inside unrelated commits
* combine unrelated modules in one commit just because they were edited together

---

# Good Examples

## Print architecture sequence

```text
refactor(print): introduce paper profile configuration
refactor(print): resolve paper profiles in print service
refactor(print): convert audit log print to paper profile layout
feat(print): add multi paper selection support
fix(print): correct audit log pdf route binding
```

## Documentation sequence

```text
docs(print): update workspace standard for multi paper support
docs(print): update service standard for paper profile resolution
docs(core): add commit strategy standard
```

## Module feature sequence

```text
refactor(tasks): extract task status transition rules
feat(tasks): add task reassignment workflow
fix(tasks): correct reassignment notification targeting
```

---

# Bad Examples

```text
update
changes
fixes
improvements
working version
final update
misc cleanup
```

These provide no useful historical value.

---

# Recommended Local Workflow

Before committing:

1. Review working tree
2. Group changes by logical responsibility
3. Stage only one logical group
4. Review staged diff
5. Commit with proper type and scope
6. Repeat for the next logical group

Suggested commands:

```bash
git status
git add <files>
git diff --staged
git commit -m "..."
```

For mixed edits:

```bash
git add -p
```

---

# Pull Request / Merge Mindset

Even if working alone, commit as if another senior engineer will review the history.

A good reviewer should be able to understand:

* architecture progression
* feature introduction
* bug fixes
* standards alignment

from commit history alone.

---

# Final Rule

Core System commits must always optimize for:

* clarity
* reviewability
* architecture traceability
* future maintainability

A clean Git history is part of the system design.

Not an afterthought.
