<?php
$dir = __DIR__ . '/app/Notifications';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Replace placeholder with SafeFcmChannel
    $content = preg_replace('/\/\/ Will add \'fcm\' when firebase package is installed/i', '$channels[] = \App\Channels\SafeFcmChannel::class;', $content);
    
    // For pure Push notifications like BidSelectedNotification
    // E.g., "$channels = []; // Push" => "$channels = [\App\Channels\SafeFcmChannel::class];"
    $content = preg_replace('/\$channels\s*=\s*\[\];\s*\/\/\s*Push/i', '$channels = [\App\Channels\SafeFcmChannel::class];', $content);
    
    file_put_contents($file, $content);
}
echo "Done\n";
