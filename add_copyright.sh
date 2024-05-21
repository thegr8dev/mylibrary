#!/bin/bash

# Define the directory to search for PHP files
DIRECTORY="app/Filament"

# Define the current year
YEAR=$(date +"%Y")

# Define the copyright text
COPYRIGHT="/*
 - Copyright (c) $YEAR @thegr8dev
 -
 - This source code is licensed under the MIT license found in the
 - LICENSE file in the root directory of this source tree.
 -
 - Made in India.
 */"

# Function to update or add the copyright text
process_file() {
    local FILE="$1"
    local TEMP_FILE=$(mktemp)

    # Read the first line of the file to check for the <?php tag
    local FIRST_LINE=$(head -n 1 "$FILE")
    if [[ "$FIRST_LINE" == "<?php" ]]; then
        # Check if the file already contains a copyright notice
        if grep -q "Copyright (c) [0-9]\{4\} @thegr8dev" "$FILE"; then
            # Update the existing copyright notice with the new year
            sed -i "s/Copyright (c) [0-9]\{4\} @thegr8dev/Copyright (c) $YEAR @thegr8dev/" "$FILE"
            echo "Updated copyright year in $FILE"
        else
            # Insert a blank line after the <?php tag and then add the copyright text
            awk -v text="$COPYRIGHT" 'NR==1{print; print ""; print text; next}1' "$FILE" > "$TEMP_FILE"
            mv "$TEMP_FILE" "$FILE"
            echo "Added copyright text to $FILE"
        fi
    else
        echo "File $FILE does not start with <?php tag"
    fi
}

# Find all PHP files in the specified directory and its subdirectories
find "$DIRECTORY" -name "*.php" | while read -r FILE; do
    process_file "$FILE"
done

echo "Script completed."
