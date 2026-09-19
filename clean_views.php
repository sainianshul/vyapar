<?php

function cleanView($file) {
    $content = file_get_contents($file);

    // Remove price unit
    $content = preg_replace('/<div class="col-md-6 mb-3">\s*<label class="form-label required">Price Unit<\/label>.*?<\/div>/s', '', $content);
    // Remove condition
    $content = preg_replace('/<div class="mb-3">\s*<label class="form-label required">Condition<\/label>.*?<\/div>/s', '', $content);
    // Remove is_negotiable
    $content = preg_replace('/<div class="mb-3">\s*<label class="form-check form-switch">\s*<input class="form-check-input" type="checkbox" name="is_negotiable".*?<\/div>/s', '', $content);
    // Remove is_featured
    $content = preg_replace('/<div class="mb-3">\s*<label class="form-check form-switch">\s*<input class="form-check-input" type="checkbox" name="is_featured".*?<\/div>/s', '', $content);
    // Remove is_verified
    $content = preg_replace('/<div class="mb-4">\s*<label class="form-check form-switch">\s*<input class="form-check-input" type="checkbox" name="is_verified".*?<\/div>/s', '', $content);
    
    // Change "Price" to "Target Budget"
    $content = str_replace('<label class="form-label required">Price</label>', '<label class="form-label required">Target Budget</label>', $content);
    
    // Change col-md-6 to col-md-12 for target_budget since unit is removed
    $content = str_replace('<div class="col-md-6 mb-3">
                                <label class="form-label required">Target Budget</label>', '<div class="col-md-12 mb-3">
                                <label class="form-label required">Target Budget</label>', $content);

    // Fix undefined target_budget_unit
    $content = str_replace('{{ $requirement->target_budget_unit ?? \'N/A\' }}', 'N/A', $content);

    file_put_contents($file, $content);
}

cleanView('resources/views/admin/requirements/create.blade.php');
cleanView('resources/views/admin/requirements/edit.blade.php');

echo "Cleaned views.\n";
