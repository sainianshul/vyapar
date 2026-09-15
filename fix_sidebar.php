<?php

$files = [
    'resources/views/admin/layouts/partials/_sidebar.blade.php',
    'resources/views/admin/layouts/partials/_quick-search.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $c = file_get_contents($file);
    
    // Replace all route('admin.patients.*') with url('/react/patients')
    $c = preg_replace('/route\(\s*\'admin\.patients\.[^\']+\'\s*\)/', "url('/react/patients')", $c);
    
    // Replace all route('admin.nurses.*') with url('/react/nurses')
    $c = preg_replace('/route\(\s*\'admin\.nurses\.[^\']+\'\s*\)/', "url('/react/nurses')", $c);
    
    // Replace all route('admin.requests.*') with url('/react/requests')
    $c = preg_replace('/route\(\s*\'admin\.requests\.[^\']+\'\s*\)/', "url('/react/requests')", $c);

    file_put_contents($file, $c);
    echo "Fixed routes in $file\n";
}
