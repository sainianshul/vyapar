<?php
$dir = __DIR__ . '/app/Notifications';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Add use ShouldQueue if not present
    if (!str_contains($content, 'use Illuminate\Contracts\Queue\ShouldQueue;')) {
        $content = str_replace('use Illuminate\Bus\Queueable;', "use Illuminate\Bus\Queueable;\nuse Illuminate\Contracts\Queue\ShouldQueue;", $content);
    }
    
    // Add implements ShouldQueue if not present
    if (preg_match('/class\s+(\w+)\s+extends\s+Notification\s*\{/i', $content, $matches)) {
        $className = $matches[1];
        $content = preg_replace('/class\s+' . $className . '\s+extends\s+Notification\s*\{/i', "class $className extends Notification implements ShouldQueue\n{", $content);
    }
    
    // Fix any return []; in via() method if it has $channels[] before it.
    // E.g., "$channels[] = \App\Channels\SafeFcmChannel::class; \n return [];" => return $channels;
    $content = preg_replace('/(\$channels(?:\[\])?\s*=[^;]+;)\s*return\s*\[\];/si', '$1'."\n        return \$channels;", $content);
    
    file_put_contents($file, $content);
}
echo "Done fixing ShouldQueue\n";
