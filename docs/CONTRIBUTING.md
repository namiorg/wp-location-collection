# Contributing to WP Location Collection

Thank you for contributing to this project! This document covers the development setup, build process, and how to create a local release. For the full multi-remote Git workflow (promoting changes to production, tagging releases, and syncing tags), see [docs/WORKFLOW.md](WORKFLOW.md).

---

## Prerequisites

- **PHP 8.3+**
- **Composer** – used to install all development dependencies including Phing and the coding-standards tools
- **Git** – the build script relies on `git describe --tags` to determine the version number
- A local WordPress installation with Gravity Forms activated (see [README.md](../README.md) for the full requirements list)

---

## Initial Setup

See [docs/WORKFLOW.md](WORKFLOW.md#initial-setup) for instructions on cloning the repository and configuring the Git remotes.

Clone the development repository and install all dependencies:

```bash
git clone git@github.com:namiorg/wp-location-collection-dev.git
cd wp-location-collection-dev
composer install
```

`composer install` installs both the production dependencies and the dev-only tools:

| Tool | Purpose |
|---|---|
| `phing/phing` | Build automation (runs `build.xml`) |
| `wp-coding-standards/wpcs` | WordPress coding-standards sniffs for `phpcs` |
| `wp-cli/i18n-command` | Generates the `.pot` translation file |
| `symfony/var-dumper` | Debugging helper |

---

## Code Style

Lint the plugin source against the WordPress coding standards with:

```bash
vendor/bin/phpcs
```

The ruleset is defined in `phpcs.dist.xml`.

---

## Build Process (Phing)

[Phing](https://www.phing.info/) is the build tool used to assemble a production-ready copy of the plugin. The build configuration lives in `build.xml` and exposes three targets:

| Target | Description |
|---|---|
| `prepare` | Creates the `build/wp-location-collection/` output directory |
| `build` | Copies source files (excluding dev-only files), runs `composer install --no-dev`, then stamps the version via `bin/build.sh` |
| `dist` | Runs `build`, then packages the result into `build/wp-location-collection.zip` |

### Running a build

Run the full distribution build (the most common task):

```bash
vendor/bin/phing dist
```

This is equivalent to running all three targets in sequence. The finished zip archive is written to:

```
build/wp-location-collection.zip
```

To run only the intermediate `build` target (copies files and stamps the version but skips zipping):

```bash
vendor/bin/phing build
```

### What gets excluded from the build

The following are deliberately omitted from the build output (see `build.xml` and `.distignore`):

- `bin/`, `build/`, `vendor/`, `tests/`
- Version-control and CI directories (`.git/`, `.circleci/`, `.github/`)
- Editor and tool config files (`.gitignore`, `.editorconfig`, `.distignore`)
- `composer.lock`, `README.md`, and all `*.xml` files

After the copy step, `composer install --no-dev` is run inside the build directory so that only production dependencies are included in the release.

---

## Version Stamping (`bin/build.sh`)

The plugin file (`wp-location-collection.php`) uses `{{VERSION}}` as a placeholder in both the plugin header and the `VERSION` constant:

```php
 * Version:         {{VERSION}}
 ...
const string VERSION = '{{VERSION}}';
```

The `bin/build.sh` script is called automatically by the `build` Phing target. It:

1. Reads the most recent annotated git tag with `git describe --tags --abbrev=0`
2. Appends the short commit hash (`git rev-parse --short HEAD`)
3. Replaces every `{{VERSION}}` occurrence in the **build copy** of the plugin file with the resulting string (e.g. `v1.2.3-a1b2c3d`)

> **Important:** `bin/build.sh` modifies the file inside `build/wp-location-collection/`, not the working-tree source. The source file always contains the literal `{{VERSION}}` placeholder.

To create a build against a specific tag, make sure that tag exists locally before running Phing:

```bash
git tag -a v1.2.3 -m "Release version 1.2.3"
vendor/bin/phing dist
```

---

## Creating a Local Release

Use the following steps to produce a distributable zip on your local machine:

```bash
# 1. Ensure you are on the correct branch/commit and the tag exists
git checkout main
git tag -a v1.2.3 -m "Release version 1.2.3"  # skip if the tag already exists

# 2. Install dependencies (if not already done)
composer install

# 3. Run the dist build
vendor/bin/phing dist
```

The output zip will be at:

```
build/wp-location-collection.zip
```

You can install this zip directly in a WordPress site via **Plugins → Add New → Upload Plugin** to verify the build before pushing to production.

### Cleaning up between builds

Phing does not automatically remove the `build/` directory between runs. If you need a clean build, remove it manually first:

```bash
rm -rf build/
vendor/bin/phing dist
```

---

## Git Workflow

See [docs/WORKFLOW.md](WORKFLOW.md) for details on:

- Setting up the dual-remote (`origin` / `prod`) configuration
- Promoting changes to the production repository via pull request
- Tagging and triggering the automated release workflow
- Syncing tags back to the development repository
