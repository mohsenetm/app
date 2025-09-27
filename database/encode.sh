#!/bin/bash
convert_to_base64() {
    local input_file="database.sqlite"
    local output_file="database.git"
    
    if [ -z "$input_file" ] || [ ! -f "$input_file" ]; then
        echo "❌ فایل ورودی وجود ندارد: $input_file"
        return 1
    fi
    
    if [ -z "$output_file" ]; then
        output_file="${input_file}.base64"
    fi
    
    base64 "$input_file" > "$output_file"
    
    echo "✅ $output_file"
	sleep 3
}

convert_to_base64
