<?php
// Create uploads directory structure
$directories = [
    'uploads',
    'uploads/profile'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Copy default profile picture if it doesn't exist
$defaultProfile = 'uploads/profile/profilepic.jpg';
if (!file_exists($defaultProfile)) {
    copy('assets/images/profilepic.jpg', $defaultProfile);
}

echo "Upload directories created successfully!\n";
