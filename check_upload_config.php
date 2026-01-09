<?php
echo "PHP Upload Configuration:\n";
echo "=========================\n\n";

$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
];

foreach ($settings as $key => $value) {
    echo "$key: $value\n";
}

echo "\nStorage Directory Writable: ";
echo is_writable('storage/app/public') ? 'YES ✓' : 'NO ✗';
echo "\n";

echo "Public Storage Directory Writable: ";
echo is_writable('public/storage') ? 'YES ✓' : 'NO ✗';
echo "\n";

echo "\nVideos Directory Writable: ";
echo is_writable('public/storage/videos') ? 'YES ✓' : 'NO ✗';
echo "\n";

echo "Sounds Directory Writable: ";
echo is_writable('public/storage/sounds') ? 'YES ✓' : 'NO ✗';
echo "\n";
