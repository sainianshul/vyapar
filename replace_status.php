<?php
// Function to do preg_replace safely
function doReplace($file, $pattern, $replacement) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    $newContent = preg_replace($pattern, $replacement, $content);
    file_put_contents($file, $newContent);
}

// 1. NurseController.php
$file = "app/Http/Controllers/Admin/NurseController.php";
doReplace($file, '/\\$statusMap\s*=\s*\[.*?\];\s*\r?\n\s*\\$status\s*=\s*\\$statusMap\[\\$bid->status\]\s*\?\?\s*\[.*?\];/s', '\$statusColor = \$bid->status_color; \$statusText = \$bid->status_text;');
doReplace($file, '/\'<span class="badge \' \.\s*\\$status\[\'class\'\]\s*\.\s*\'">\'\s*\.\s*\\$status\[\'text\'\]\s*\.\s*\'<\/span>\'/s', '\'<span class="badge badge-light-\' . \$statusColor . \'">\' . \$statusText . \'</span>\'');

doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$statusColors\[\\$booking->status\]\s*\?\?\s*\'dark\';/s', '\$color = \$booking->status_color;');
doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$statusColors\[\\$booking->payment_status\]\s*\?\?\s*\'dark\';/s', '\$color = \$booking->payment_status_color;');

// 2. BookingController.php
$file = "app/Http/Controllers/Admin/BookingController.php";
doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n/s', '');
// The BookingController might have:  $color = $statusColors[$session->status] ?? 'dark';
// Let's just catch them by name.
// Wait, session status? We didn't add status color to BookingSession! Let me check that.

// 3. RequestDataTable.php
$file = "app/DataTables/Request/RequestDataTable.php";
doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$statusColors\[\\$request->status\]\s*\?\?\s*\'dark\';/s', '\$color = \$request->status_color;');

// 4. BookingDataTable.php
$file = "app/DataTables/Booking/BookingDataTable.php";
doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$statusColors\[\\$booking->status\]\s*\?\?\s*\'dark\';/s', '\$color = \$booking->status_color;');
doReplace($file, '/\\$payColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$payColors\[\\$booking->payment_status\]\s*\?\?\s*\'dark\';/s', '\$color = \$booking->payment_status_color;');

// 5. BidDataTable.php
$file = "app/DataTables/Bid/BidDataTable.php";
doReplace($file, '/\\$statusColors\s*=\s*\[.*?\];\s*\r?\n\s*\\$color\s*=\s*\\$statusColors\[\\$bid->status\]\s*\?\?\s*\'dark\';/s', '\$color = \$bid->status_color;');

