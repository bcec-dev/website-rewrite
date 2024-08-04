#!/bin/bash

# Path to the plugin file
PLUGIN_FILE="sermon-filter-plugin/sermon-filter-plugin.php"

# Extract the current version number
CURRENT_VERSION=$(grep -oP "define\('SFB_VERSION', '\K[^']+" "$PLUGIN_FILE")

# Increment the version number
IFS='.' read -r -a VERSION_PARTS <<< "$CURRENT_VERSION"
((VERSION_PARTS[2]++))
NEW_VERSION="${VERSION_PARTS[0]}.${VERSION_PARTS[1]}.${VERSION_PARTS[2]}"

# Update the version number in the plugin file
sed -i "s/define('SFB_VERSION', '$CURRENT_VERSION')/define('SFB_VERSION', '$NEW_VERSION')/" "$PLUGIN_FILE"

# Output the new version number
echo "Version bumped to $NEW_VERSION"
