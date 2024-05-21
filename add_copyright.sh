#!/bin/bash

# Directories to search for PHP files (relative to the root of the GitHub repository)
directories=(
    "app/Filament/Pages"
    "app/Filament/Resources"
)

# Copyright text
copyright='// C 2024 | All rights Reserved | Made in India <3'

# Function to prepend copyright to a file if not already there
prepend_copyright() {
    file=$1
    # Check if the copyright is already in the file
    if ! grep -q "$copyright" "$file"; then
        # Prepend the copyright
        echo -e "<?php\n\n$copyright\n\n$(cat "$file")" > "$file"
        echo "Updated $file"
    fi
}

# Export the function to be available to `find` command
export -f prepend_copyright

# Loop through directories and process PHP files
for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        find "$dir" -type f -name '*.php' -exec bash -c 'prepend_copyright "$0"' {} \;
    else
        echo "Directory $dir does not exist"
    fi
done
