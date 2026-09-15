<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/DataTables'));
$phpFiles = [];
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

// Also add controllers
$phpFiles[] = 'app/Http/Controllers/Admin/NurseController.php';
$phpFiles[] = 'app/Http/Controllers/Admin/BookingController.php';
$phpFiles[] = 'app/Http/Controllers/Admin/SupportController.php';

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // Replace $colors array
    $content = preg_replace('/\\$colors\s*=\s*\[.*?\];\s*/s', '', $content);
    
    // Replace the avatar block
    // Pattern: $initial = mb_strtoupper(mb_substr(..., 0, 2)); $colorClass = [...];
    $pattern = '/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(([^,]+).*?\)\);\s*\\$colorClass\s*=\s*\\$colors\[.*?\];\s*\\$avatar\s*=\s*\'<span class="symbol.*?>\'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';/s';
    
    // Wait, the variables could be $user->name, $patient->name, $row->name etc. 
    // We captured the variable in ([^,]+). E.g., $user->name. We need to extract the base object, i.e., $user.
    // Let's do a more robust string replacement since there's so many variations.
    
    // Let's just remove $initial = ...; and $colorClass = ...; and replace $avatar = ...;
    // Actually, let's use preg_replace_callback.
    $content = preg_replace_callback('/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(([^,-]+)->name,\s*0,\s*[12]\)\);\s*\\$colorClass\s*=\s*\\$colors\[ord\(\\$initial\)\s*%\s*count\(\\$colors\)\];\s*\\$avatar\s*=\s*\'<span class="symbol symbol-38px symbol-circle"><span class="symbol-label fw-bold fs-6 \'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';/s', function($matches) {
        return '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . ' . $matches[1] . '->avatar_html . \'</div>\';';
    }, $content);

    file_put_contents($file, $content);
}

