<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/DataTables'));
$phpFiles = [];
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // Replace $colors array if it exists
    $content = preg_replace('/\\$colors\s*=\s*\[.*?\];\s*/s', '', $content);
    
    // Replace the avatar block
    $pattern = '/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(([^,-]+)->name,\s*0,\s*[12]\)\);\s*\\$colorClass\s*=\s*\\$colors\[ord\(\\$initial\)\s*%\s*count\(\\$colors\)\];\s*\\$avatar\s*=\s*\'\';\s*if\s*\([^,-]+->profile_photo\)\s*\{\s*\\$avatar\s*=\s*\'<div class="symbol symbol-38px symbol-circle"><img src="\'\s*\.\s*\\\\Illuminate\\\\Support\\\\Facades\\\\Storage::url\([^,-]+->profile_photo\)\s*\.\s*\'" class="object-fit-cover" alt="Pic"><\/div>\';\s*\}\s*else\s*\{\s*\\$avatar\s*=\s*\'<span class="symbol symbol-38px symbol-circle"><span class="symbol-label fw-bold fs-6 \'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';\s*\}/s';
    
    $content = preg_replace_callback($pattern, function($matches) {
        return '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . ' . $matches[1] . '->avatar_html . \'</div>\';';
    }, $content);

    // Some places don't have the if-else for profile_photo (like PatientDataTable maybe)
    $pattern2 = '/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(([^,-]+)->name,\s*0,\s*[12]\)\);\s*\\$colorClass\s*=\s*\\$colors\[ord\(\\$initial\)\s*%\s*count\(\\$colors\)\];\s*\\$avatar\s*=\s*\'<span class="symbol symbol-38px symbol-circle"><span class="symbol-label fw-bold fs-6 \'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';/s';
    
    $content = preg_replace_callback($pattern2, function($matches) {
        return '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . ' . $matches[1] . '->avatar_html . \'</div>\';';
    }, $content);

    file_put_contents($file, $content);
}
