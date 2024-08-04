#!/bin/bash

# Define the file to update and the regex pattern to search for
FILE="sermon-filter-plugin/sermon-filter-plugin.php"
VERSION_REGEX="^Version:\s*(.*)$"

# Extract the current version number
CURRENT_VERSION=$(grep -i "Version:" $FILE | awk '{print $2}')
echo "Current version: $CURRENT_VERSION"

# Split version into parts
IFS='.' read -r -a VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

# Increment the patch version number
PATCH=$((PATCH + 1))
NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo "New version: $NEW_VERSION"

# Replace the old version number with the new one in the file
sed -i '' -e "s/Version: $CURRENT_VERSION/Version: $NEW_VERSION/" $FILE

# Output the new version for the GitHub Actions step
echo "new_version=$NEW_VERSION" >> $GITHUB_ENV
