# Register helper file

In `composer.json` under `autoload`:

```json
"files": [
    "app/helpers_module_labels.php"
]
```

Copy `app/helpers_module_labels.php` to project `app/` (or `app/Support/` and adjust path).

Then:

```bash
composer dump-autoload
php artisan config:clear
php artisan view:clear
```

Alternatively, without composer files autoload, add in `AppServiceProvider::boot()`:

```php
require_once app_path('helpers_module_labels.php');
```
