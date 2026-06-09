# Development & Release Workflow

This document describes the workflow for developing and releasing the WP Location Collection plugin.

The plugin lives in a **single public repository**:

| Repository | URL |
|---|---|
| **WP Location Collection** | https://github.com/namiorg/wp-location-collection |

The repository is public so that the plugin's self-updater (`src/Api/UpdateChecker.php`) can read
the latest GitHub Release **anonymously** — both the release feed and the downloadable zip must be
reachable without authentication, which GitHub only allows for public repos. There is nothing
sensitive in the source: the only runtime secret is the Radar API key, which each site's admin
enters in the WordPress settings and which is never committed.

---

## Initial Setup

Clone the repository and install all dependencies:

```bash
git clone git@github.com:namiorg/wp-location-collection.git
cd wp-location-collection
composer install
```

Confirm your remote with:

```bash
git remote -v
```

Expected output:

```
origin  git@github.com:namiorg/wp-location-collection.git (fetch)
origin  git@github.com:namiorg/wp-location-collection.git (push)
```

> **Migrating from the old two-repo setup?** The project previously used a private
> `wp-location-collection-dev` repo with a second `prod` remote. That split has been retired.
> If you have an old clone, drop the stale remote and repoint `origin`:
> ```bash
> git remote remove prod
> git remote set-url origin git@github.com:namiorg/wp-location-collection.git
> ```

---

## Making Changes

All work happens via pull request into `main`:

```bash
git checkout main
git pull
git checkout -b change/your-change-description
# ...make changes, commit...
git push origin change/your-change-description
```

Open a pull request against `main` and **Squash and Merge** it. Squash-merging keeps the public
commit history clean and lets you curate the final commit message. (`main` is protected to require
PRs and squash merges, so this is enforced.)

---

## Creating a Release

> This plugin follows [Semantic Versioning (SemVer)](https://semver.org/).

Releasing is a single step. From an up-to-date, clean `main`:

```bash
bin/release.sh 1.2.3
```

`bin/release.sh` verifies the tree is clean, runs `phpcs`, creates the annotated tag `v1.2.3`,
and pushes it. Pushing the tag triggers the **Create WordPress Plugin Release** workflow
(`.github/workflows/release.yml`), which stamps the `{{VERSION}}` placeholder, builds the
distributable zip (dev-only files excluded), and attaches it to a new GitHub Release.

### Releasing from the GitHub UI

You can also cut a release without the CLI: go to **Actions → Create WordPress Plugin Release →
Run workflow**, enter the version (e.g. `1.2.3`), and run it. The workflow creates and pushes the
tag for you, then builds and publishes the Release.

### After the release

Once the workflow finishes, the new release appears under
[Releases](https://github.com/namiorg/wp-location-collection/releases) and the zip is available for
download. Installed sites pick it up automatically on their next update check (cached for 24 hours;
admins can force a check immediately — see `UpdateChecker.php`).

---

## Building Locally (optional)

To produce a distributable zip on your machine without cutting a release, see the Phing build
steps in [CONTRIBUTING.md](CONTRIBUTING.md#build-process-phing).
