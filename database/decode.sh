#!/bin/bash
convert_from_base64() {
    local input_file="database.git"
    local output_file="database.sqlite"
	
	if [ -n "$output_file" ] && [ -f "$output_file" ]; then
		echo "❌ File Is Exists: $output_file"
		sleep 3
		return 1
	fi
    
    if [ -z "$output_file" ]; then
        output_file="${input_file%.*}"  # حذف پسوند
    fi
    
    base64 -d "$input_file" > "$output_file"
    echo "✅ $output_file"
	sleep 3
}

convert_from_base64  "database.git" "database.sqlite"