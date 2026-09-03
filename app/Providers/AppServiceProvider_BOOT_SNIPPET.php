<?php
// Add inside App\Providers\AppServiceProvider::boot()

if (file_exists(app_path('helpers_module_labels.php'))) {
    require_once app_path('helpers_module_labels.php');
}
