<?php
$dir = __DIR__ . '/app/Notifications';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if there is a via method
    if (preg_match('/public function via\(object \$notifiable\):\s*array\s*\{.*?\}/s', $content, $match)) {
        $viaMethod = $match[0];
        
        // If it doesn't already explicitly return or add 'database'
        if (!str_contains($viaMethod, "'database'") && !str_contains($viaMethod, '"database"')) {
            // Find the first line inside the method (usually $channels = ...) and inject 'database' array initialization if needed
            // Or simpler: just replace `return $channels;` with `array_unshift($channels, 'database'); return $channels;`
            
            // If it returns an array directly like `return ['mail'];`
            if (preg_match('/return\s+\[(.*?)\];/', $viaMethod, $returnMatch)) {
                $newReturn = "return ['database', " . $returnMatch[1] . "];";
                $newViaMethod = str_replace($returnMatch[0], $newReturn, $viaMethod);
                $content = str_replace($viaMethod, $newViaMethod, $content);
                file_put_contents($file, $content);
                echo "Updated direct return in " . basename($file) . "\n";
            } 
            // If it uses a variable like $channels = [...]; return $channels;
            else if (str_contains($viaMethod, 'return $channels;')) {
                $newViaMethod = str_replace('return $channels;', "if (!in_array('database', \$channels)) {\n            array_unshift(\$channels, 'database');\n        }\n        return \$channels;", $viaMethod);
                $content = str_replace($viaMethod, $newViaMethod, $content);
                file_put_contents($file, $content);
                echo "Updated array unshift in " . basename($file) . "\n";
            }
        } else {
             echo "Already has database channel " . basename($file) . "\n";
        }
    }
}
