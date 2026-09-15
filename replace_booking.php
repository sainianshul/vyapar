<?php
$file = 'app/Http/Controllers/Admin/BookingController.php';
$content = file_get_contents($file);

$content = preg_replace('/\\$statusColors\s*=\s*\[.*?\];/s', '', $content);
$content = str_replace('use ($statusColors)', '', $content);
$content = str_replace('use ($statusColors, $booking)', 'use ($booking)', $content);
$content = preg_replace('/\\$color\s*=\s*\\$statusColors\[.*?\]\s*\?\?\s*\'dark\';/', '\$color = \$session->status_color ?? \$bid->status_color;', $content);

file_put_contents($file, $content);
