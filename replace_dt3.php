<?php

$files = [
    'app/DataTables/BlockedPatientDataTable.php',
    'app/DataTables/PatientDataTable.php',
    'app/DataTables/Bid/BidDataTable.php',
    'app/DataTables/Nurses/BaseNursesDataTable.php',
    'app/DataTables/Request/RequestBidsDataTable.php',
    'app/DataTables/Request/RequestNotifiedNursesDataTable.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Remove $colors again just in case
    $content = preg_replace('/\\$colors\s*=\s*\[.*?\];\s*/s', '', $content);
    
    // In BaseNursesDataTable, it uses $user
    $content = preg_replace('/\\$initial\s*=\s*mb_strtoupper.*?\\$avatar\s*=\s*\'<span class="symbol.*?>\'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';\s*\}/s', '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . \$user->avatar_html . \'</div>\'; }', $content);
    
    // Handle ->user
    $content = preg_replace('/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(\\$nurse->user->name.*?\\$nurseInfo\s*=\s*\'\s*<div class="d-flex align-items-center gap-3">\s*<div class="symbol symbol-30px symbol-circle">\s*<span class="symbol-label bg-light-info text-info fw-bold">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span>\s*<\/div>/s', '\$nurseInfo = \'
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-30px symbol-circle">\' . \$nurse->user->avatar_html . \'</div>', $content);
                            
    // Handle BidDataTable.php specifically
    $content = preg_replace('/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(\\$bid->nurse->user->name.*?\\$nurseInfo\s*=\s*\'\s*<div class="d-flex align-items-center gap-3">\s*<div class="symbol symbol-30px symbol-circle">\s*<span class="symbol-label bg-light-info text-info fw-bold">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span>\s*<\/div>/s', '\$nurseInfo = \'
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-30px symbol-circle">\' . \$bid->nurse->user->avatar_html . \'</div>', $content);
                            
    // PatientDataTable / BlockedPatientDataTable (uses )
    $content = preg_replace('/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(\\$patient->name.*?\\$avatar\s*=\s*\'<span class="symbol.*?>\'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';\s*\}/s', '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . \$patient->avatar_html . \'</div>\'; }', $content);

    file_put_contents($file, $content);
}
