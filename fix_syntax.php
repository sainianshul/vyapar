<?php

$files = [
    "c:/Users/anshu/Desktop/code/schotech/vcancares/app/DataTables/Booking/BookingDataTable.php",
    "c:/Users/anshu/Desktop/code/schotech/vcancares/app/DataTables/Request/RequestDataTable.php",
    "c:/Users/anshu/Desktop/code/schotech/vcancares/app/DataTables/Support/TicketDataTable.php"
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $content = str_replace('\$avatar =', '\$avatar =', $content); // Wait, if I write \$avatar, it'll still evaluate. 
    // In PHP single quotes, '\\' is \ and .
    // The string in file is literally \ =.
    $content = str_replace('\ =', ' =', $content);
    file_put_contents($file, $content);
}
