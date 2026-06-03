# Multi-Remote Development Workflow

This document describes the workflow for developing and releasing the WP Location Collection plugin across two repositories.

| Repository | URL | Access |
|---|---|---|
| **Production** | https://github.com/namiorg/wp-location-collection | Write-only (via PR) |
| **Development** | https://github.com/namiorg/wp-location-collection-dev | Read/Write |

All active development happens in the **development** repo. Changes are promoted to **production** via a pull request. Releases are then tagged on the production repo and synced back to development.

---

## Initial Setup

Clone the development repository:

```bash
git clone git@github.com:namiorg/wp-location-collection-dev.git
cd wp-location-collection-dev
```

Confirm your remotes with:

```bash
git remote -v
```

Expected output:

```
origin  git@github.com:namiorg/wp-location-collection-dev.git (fetch)
origin  git@github.com:namiorg/wp-location-collection-dev.git (push)
```

Add the production repository as a second remote named `prod`:

```bash
git remote add prod git@github.com:namiorg/wp-location-collection.git
```

Fetch the production remote's branches and tags:

```bash
git fetch prod
```

---

## Promoting Changes from Dev to Production

### 1. Create a local tracking branch for production's `main`

```bash
git checkout -b prod-main prod/main
```

This creates a local `prod-main` branch that tracks `prod/main`, keeping it clearly separate from the development `main` branch.

### 2. Create a PR branch from `prod-main`

```bash
git checkout -b change/your-change-description
```

### 3. Merge your development changes into the PR branch

Merge the specific branch or commits from development that you want to promote:

```bash
git merge origin/main  # or whichever dev branch has your changes
```

### 4. Push the PR branch to production

```bash
git push prod change/your-change-description
```

### 5. Open and merge the Pull Request

Open a pull request on [namiorg/wp-location-collection](https://github.com/namiorg/wp-location-collection) from `change/your-change-description` into `main`.

> **Important:** Use **Squash and Merge** so you can curate the commit messages included in the merge. Review the message carefully before confirming.

### 6. Update your local `prod-main`

Once the PR is merged, switch back to `prod-main` and pull:

```bash
git checkout prod-main
git pull prod main
```

---

## Creating a Release

> **Note:** This plugin follows [Semantic Versioning (SemVer)](https://semver.org/). The release creation GitHub Actions workflow is **disabled** in the development repo and only runs in production.

Make sure you are on the `prod-main` branch and it is up-to-date (see step 6 above), then create and push an annotated tag, replacing `X.X.X` with the new version number:

```bash
git tag -a vX.X.X -m "Release version X.X.X"
git push prod vX.X.X
```

Pushing the tag triggers the release creation workflow in the production repo. Once the workflow completes, the new release will appear under [Releases](https://github.com/namiorg/wp-location-collection/releases) and the plugin zip will be available for download by dependent sites.

---

## Syncing Tags Back to Development

After creating a release, sync the new tag(s) back to the development repo to keep both repos aligned.

Fetch all tags from production:

```bash
git fetch prod --tags
```

Push tags to the development remote:

```bash
git push origin --tags
```

Expected output when new tags are pushed:

```
remote: Resolving deltas: 100% (x/x), completed with x local objects.
To github.com:namiorg/wp-location-collection-dev.git
 * [new tag]         v0.1.x -> v0.1.x
```

If everything is already in sync:

```
Everything up-to-date
```

If some tags already exist in the development repo, you may see a warning like:

```
To github.com:namiorg/wp-location-collection-dev.git
 * [new tag]         v0.1.4 -> v0.1.4
 ! [rejected]        v0.1.3 -> v0.1.3 (already exists)
error: failed to push some refs to 'github.com:namiorg/wp-location-collection-dev.git'
hint: Updates were rejected because the tag already exists in the remote.
```

This is expected — it simply means that tag was already synced previously. The new tag was still pushed successfully.

---

## Finishing Up

Return to the development branch to continue work:

```bash
git checkout main
```
