# How to add a new plugin to Testo

This guide walks through everything required to ship a new plugin as a standalone Composer package, end-to-end: from creating the folder in the monorepo to publishing the package on Packagist.

> **Reference implementation:** `plugin/repeat/`. When in doubt, mirror what's there.

## Concepts in 30 seconds

- The monorepo `php-testo/testo` hosts all packages.
- Each plugin lives under `plugin/<short-name>/` and is registered in the root `composer.json` as a path repository.
- Release-please tracks the plugin's version independently in `resources/version.json`.
- A split-publish workflow mirrors `plugin/<short-name>/` into a dedicated `php-testo/<short-name>` repository on every release tag, and Packagist serves `testo/<short-name>` from there.
- The standalone repository is **read-only**; all development happens in the monorepo.

## Pre-requisites

- Push access to the `php-testo` GitHub organization.
- `RELEASE_TOKEN` (in monorepo secrets) has `Contents: Read and write` on the future target repository — top up the token's scope if it is a fine-grained PAT pinned to specific repos.
- `composer` 2+ available locally.

## In the monorepo

### 1. Create the plugin folder

```
plugin/<short-name>/
├── composer.json
├── README.md
├── <ShortName>.php          # optional — only if the plugin exposes a top-level class
├── src/                     # PSR-4 internals
│   └── ...
└── tests/
    └── ...
```

If extracting from core (the most common case), use `git mv` to keep history:

```bash
git mv core/<Module>           plugin/<short-name>/src
git mv core/<Module>.php       plugin/<short-name>/<ShortName>.php   # if present
git mv tests/<Module>          plugin/<short-name>/tests
```

After moving the source, recreate the `Internal/` (or any sub-namespace) layer if it existed, so PSR-4 mapping inside the plugin stays intact:

```bash
mkdir plugin/<short-name>/src/Internal
git mv plugin/<short-name>/src/<MovedFile>.php plugin/<short-name>/src/Internal/<MovedFile>.php
```

### 2. composer.json template

```json
{
    "name": "testo/<short-name>",
    "description": "<one-line description> for the Testo testing framework.",
    "license": "BSD-3-Clause",
    "type": "library",
    "keywords": ["testo", "<short-name>"],
    "authors": [
        {
            "name": "Aleksei Gagarin (roxblnfk)",
            "homepage": "https://github.com/roxblnfk"
        }
    ],
    "require": {
        "php": ">=8.2",
        "testo/testo": "*"
    },
    "autoload": {
        "psr-4": { "Testo\\<UpperShortName>\\": "src/" },
        "files": ["<ShortName>.php"]
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "extra": {
        "branch-alias": {
            "dev-1.x": "0.1.x-dev"
        }
    }
}
```

Notes:

- `Testo\\<UpperShortName>\\` is the unique namespace for the plugin's internals (e.g. `Testo\\Repeat\\` for `plugin/repeat`).
- The `files` entry eagerly loads the plugin's top-level class. **Drop the `files` key entirely if the plugin has no top-level class** — do not put a PSR-4 root on `Testo\\` (that breaks autoload performance for everyone).
- The single allowed file in `Testo\` from a plugin is `<ShortName>.php` matching the package short name (`Repeat.php` → `\Testo\Repeat`). No exceptions.

### 3. README

Copy the layout of `plugin/repeat/README.md`: logo header, sponsorship/documentation badges, the read-only mirror banner, an `About` section without API specifics, and an `Install` section with Packagist badges. Replace `repeat`-specific bits with your plugin's short name.

### 4. Wire the plugin into the root composer.json

In root `composer.json`:

- **`require`** (alphabetically):
  ```json
  "testo/<short-name>": "^1.0@dev"
  ```
  > Composer rejects a bare `"@dev"` constraint here. Pin to `^1.0@dev` for consistency with the rest of the plugins — the `@dev` stability flag combined with the package's `branch-alias: dev-1.x → 0.1.x-dev` lets Composer resolve the path-repo development version.
- **`repositories`**:
  ```json
  {
      "type": "path",
      "url": "plugin/<short-name>",
      "options": { "symlink": true }
  }
  ```
- **`autoload-dev.psr-4`** — map test namespace if the plugin has tests under `Tests\<UpperShortName>\`:
  ```json
  "Tests\\<UpperShortName>\\": "plugin/<short-name>/tests/"
  ```

Then install:

```bash
composer require "testo/<short-name>:^1.0@dev"
```

The plugin should land in `vendor/testo/<short-name>` as a symlink/junction.

### 5. Wire the plugin's test suite

In root `testo.php`, replace any reference to the old `tests/<Module>/suites.php` with the new path:

```php
require 'plugin/<short-name>/tests/suites.php',
```

### 6. release-please config

In `.github/.release-please-config.json`, add the plugin under `packages`:

```json
"plugin/<short-name>": {
    "package-name": "testo/<short-name>",
    "component": "<short-name>",
    "include-component-in-tag": true,
    "changelog-path": "CHANGELOG.md"
}
```

In `resources/version.json`, add the initial version:

```json
"plugin/<short-name>": "0.1.0"
```

### 7. split-publish workflow

In `.github/workflows/split-publish.yml`, append a new job mirroring `split-repeat`. The trigger pattern catches the component-prefixed tags release-please will produce:

```yaml
on:
  push:
    tags:
      - '<short-name>-[0-9]*'   # add to the existing tags list
```

```yaml
jobs:
  split-<short-name>:
    name: ↪ plugin/<short-name> → php-testo/<short-name>
    if: startsWith(github.ref, 'refs/tags/<short-name>-')
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: 🏷 Extract bare version from component tag
        id: tag
        run: echo "version=${GITHUB_REF_NAME#<short-name>-}" >> "$GITHUB_OUTPUT"

      - name: 🌿 Resolve source branch for the tag
        id: branch
        run: |
          branch=$(git branch --remotes --contains "${GITHUB_REF_NAME}" \
                    | sed 's|origin/||' \
                    | grep -v 'HEAD' \
                    | head -n 1 \
                    | tr -d ' ')
          echo "name=${branch}" >> "$GITHUB_OUTPUT"

      - name: 🚀 Push subtree to php-testo/<short-name>
        uses: symplify/monorepo-split-github-action@v2.3.0
        with:
          tag: ${{ steps.tag.outputs.version }}
          package_directory: plugin/<short-name>
          repository_organization: php-testo
          repository_name: <short-name>
          branch: ${{ steps.branch.outputs.name }}
          user_name: testo-bot
          user_email: bot@testo.dev
        env:
          GITHUB_TOKEN: ${{ secrets.RELEASE_TOKEN }}
```

### 8. Verify locally

```bash
TESTO_CI=1 vendor/bin/testo run
```

The suite count should grow by the number of tests the plugin adds. All previously green tests should still pass.

If the plugin is on a coverage-aware path, also smoke `composer infect` (uses Infection through the `bridge/infection` adapter — verifies the plugin doesn't break mutation testing).

## On GitHub

### 1. Create the target repository

```bash
gh repo create php-testo/<short-name> --public \
    --description "<one-line description> (split-published from php-testo/testo)" \
    --homepage "https://github.com/php-testo/testo"
```

### 2. Disable Issues and Wiki

The mirror is read-only — issues and wikis must live in the monorepo:

```bash
gh repo edit php-testo/<short-name> --enable-issues=false --enable-wiki=false
```

### 3. Default branch (after the first split)

The repository starts empty. After the first split-publish run completes, set the default branch to match the release branch:

```bash
gh repo edit php-testo/<short-name> --default-branch 1.x
```

This matters for Packagist — it reads `composer.json` from the default branch.

### 4. Register on Packagist

Once the first split has populated the repository:

1. Visit https://packagist.org/packages/submit
2. URL: `https://github.com/php-testo/<short-name>`
3. After registration, enable auto-updates by installing the **Packagist** GitHub App on the target repo (or by pasting the Packagist webhook URL into the repo's webhook settings).

## First release flow

1. Commit your changes with a conventional-commit subject scoped to the plugin:
   ```
   feat(<short-name>): bootstrap plugin
   ```
   Touching only `plugin/<short-name>/**` — release-please attributes the commit to the right package by **paths in the diff**, not by scope text. The scope is for changelog readability.
2. Push to the release branch (`1.x`). Release-please opens a PR titled `chore(<short-name>): release 0.1.0`.
3. Merge the PR. Release-please creates a GitHub release with tag `<short-name>-0.1.0` (no `v` prefix — the project convention is `include-v-in-tag: false`).
4. The tag triggers `split-publish.yml`, which pushes `plugin/<short-name>/` into `php-testo/<short-name>:1.x` with a bare tag `0.1.0`.
5. Set the default branch on the target repo (step 3 of "On GitHub" above) and register the package on Packagist.
6. Verify the install path:
   ```bash
   composer require testo/<short-name>:^0.1
   ```

## Common gotchas

- **`Testo\\` in PSR-4** — never. Each plugin gets its own `Testo\\<UpperShortName>\\` PSR-4 root, plus an optional `files` entry for the top-level class. Registering `Testo\\` from a plugin makes Composer's autoloader iterate every plugin on every class lookup.
- **Top-level class name must equal the plugin short name.** `Repeat` from `testo/repeat`, `Assert` from `testo/assert`. No arbitrary classes in `Testo\` from a plugin.
- **`autoload-dev` from a path-repo dependency is ignored** — Composer only loads `autoload-dev` of the root project. That's why test namespaces are mapped in the root `composer.json`.
- **Tag pattern in split-publish** — keep the `[0-9]*` suffix to avoid false triggers from non-release tags like `repeat-experimental`.
- **Branch alias version** — bump `extra.branch-alias.dev-1.x` to match the plugin's current minor (e.g. `0.2.x-dev` after the first 0.2 release). This affects users who require `dev-1.x` directly.

## Reference files

| File | Role |
|---|---|
| `task.md` | Overall monorepo restructuring plan |
| `plugin/repeat/composer.json` | Working example of a plugin manifest |
| `plugin/repeat/README.md` | Working example of a plugin README |
| `.github/.release-please-config.json` | Release config — append new packages here |
| `resources/version.json` | Manifest — append starting version here |
| `.github/workflows/split-publish.yml` | Append a new job per plugin |
