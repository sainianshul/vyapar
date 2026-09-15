<?php
$files = [
    "app/Http/Controllers/Admin/NurseController.php",
    "app/Http/Controllers/Admin/BookingController.php",
    "app/DataTables/BlockedPatientDataTable.php",
    "app/DataTables/Request/RequestDataTable.php",
    "app/DataTables/PatientDataTable.php",
    "app/DataTables/Nurses/BaseNursesDataTable.php",
    "app/DataTables/Support/TicketDataTable.php",
    "app/DataTables/Booking/BookingDataTable.php",
    "app/DataTables/Bid/BidDataTable.php"
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Remove $colors lines
    $content = preg_replace('/.*\\$colors\s*=\s*\[\'bg-light-.*?\];\s*\r?\n/', '', $content);
    
    // Replace NurseController & BookingController specific line
    $content = preg_replace('/\\$img\s*=\s*\\$patientUser->profile_photo.*?;/', '\$img = \$patientUser->avatar_html;', $content);
    $content = preg_replace('/\\$img\s*=\s*\\$review->user->profile_photo.*?;/', '\$img = \$review->user->avatar_html;', $content);

    // Replace DataTable logic
    $dtRegex = '/\\$initial\s*=\s*mb_strtoupper\(mb_substr\(\\$user->name,\s*0,\s*[12]\)\);\s*\r?\n\s*\\$colorClass\s*=\s*\\$colors\[ord\(\\$initial\)\s*%\s*count\(\\$colors\)\];\s*\r?\n\s*\\$avatar\s*=\s*\'<span class="symbol symbol-38px symbol-circle"><span class="symbol-label fw-bold fs-6 \'\s*\.\s*\\$colorClass\s*\.\s*\'">\'\s*\.\s*e\(\\$initial\)\s*\.\s*\'<\/span><\/span>\';/';
    
    $content = preg_replace($dtRegex, '\$avatar = \'<div class="symbol symbol-38px symbol-circle">\' . \$user->avatar_html . \'</div>\';', $content);

    file_put_contents($file, $content);
}
