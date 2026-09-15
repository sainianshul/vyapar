<?php
$dir = __DIR__ . '/app/Notifications';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Remove ShouldQueue interface
    $content = preg_replace('/implements ShouldQueue/i', '', $content);
    
    // Connect SafeFcmChannel
    $content = preg_replace('/\/\/ Will add \'fcm\' when firebase package is installed/i', '$channels[] = \App\Channels\SafeFcmChannel::class;', $content);
    
    // For notifications that had // Push written
    $content = preg_replace('/\$channels = \[\]; \/\/ Push/i', '$channels = [\App\Channels\SafeFcmChannel::class];', $content);
    
    file_put_contents($file, $content);
}
echo "Notifications updated.\n";
