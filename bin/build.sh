#!/usr/bin/env sh
# Extract version from tag (remove "v" prefix if present)
TAG_NAME="$(git describe --tags --abbrev=0)" # get most recent tag
COMMIT_HASH="$(git rev-parse --short HEAD)" # get short commit hash

VERSION="${TAG_NAME}-${COMMIT_HASH}"

echo "Version: ${FULL_VERSION}"

# Replace {{VERSION}} placeholder in plugin file
sed -i '' "s/{{VERSION}}/${VERSION}/g" wp-location-collection.php

# Verify the replacement
echo "Updated plugin file:"
grep "Version:" wp-location-collection.php
grep "const string VERSION" wp-location-collection.php
