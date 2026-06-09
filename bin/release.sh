#!/usr/bin/env sh
#
# release.sh — cut a release in one step.
#
# Tags the current commit and pushes the tag to origin, which triggers the
# "Create WordPress Plugin Release" GitHub Actions workflow (.github/workflows/release.yml).
# That workflow stamps {{VERSION}}, builds the distributable zip, and attaches it to a
# GitHub Release — which the plugin's self-updater (src/Api/UpdateChecker.php) reads.
#
# Usage:
#   bin/release.sh 1.2.3
#   bin/release.sh v1.2.3      # leading "v" is optional; it is normalised in
#
set -eu

if [ $# -ne 1 ]; then
	echo "Usage: bin/release.sh <version>   (e.g. bin/release.sh 1.2.3)" >&2
	exit 1
fi

# Normalise: strip a leading "v" if present, then re-add it so tags are always vX.Y.Z.
VERSION="${1#v}"
TAG="v${VERSION}"

# Must be on a clean tree — the release builds from this exact commit.
if [ -n "$(git status --porcelain)" ]; then
	echo "Error: working tree is not clean. Commit or stash changes before releasing." >&2
	exit 1
fi

BRANCH="$(git rev-parse --abbrev-ref HEAD)"
if [ "$BRANCH" != "main" ]; then
	printf "Warning: you are on '%s', not 'main'. Continue? [y/N] " "$BRANCH"
	read -r answer
	case "$answer" in
		[yY]*) ;;
		*) echo "Aborted."; exit 1 ;;
	esac
fi

if git rev-parse "$TAG" >/dev/null 2>&1; then
	echo "Error: tag $TAG already exists." >&2
	exit 1
fi

# Run the coding-standards check before tagging, if the tool is installed.
if [ -x vendor/bin/phpcs ]; then
	echo "Running phpcs..."
	vendor/bin/phpcs
fi

echo "Tagging $TAG..."
git tag -a "$TAG" -m "Release version ${VERSION}"

echo "Pushing $TAG to origin..."
git push origin "$TAG"

echo ""
echo "Done. The release workflow is now building $TAG."
echo "Watch it at: https://github.com/namiorg/wp-location-collection/actions"
echo "The Release will appear at: https://github.com/namiorg/wp-location-collection/releases"
