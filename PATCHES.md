# Installation Patches – Multi-Business Homepage

Apply these changes after copying the new files.

---

## 1. Migration

Already provided:
`database/migrations/2026_09_01_000001_add_show_on_homepage_to_restaurants_table.php`

```bash
php artisan migrate
```

---

## 2. Restaurant Model (`app/Models/Restaurant.php`)

Add to `$fillable`:
```php
'show_on_homepage',
'homepage_sort_order',
```

Add to `$casts`:
```php
'show_on_homepage' => 'boolean',
'homepage_sort_order' => 'integer',
```

---

## 3. Routes (`routes/web.php`)

Replace the home route:

**Before:**
```php
Route::get('/', [MenuController::class, 'index'])->name('home');
```

**After:**
```php
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
```

Keep the existing slug route as-is:
```php
Route::get('/{slug}', [MenuController::class, 'showBySlug'])
    ->name('menu.restaurant')
    ->where('slug', '^(?!admin|manager|track|checkout|register|login|logout|account|_debugbar)[a-z0-9\-]+$');
```

---

## 4. RestaurantController – validation

In both `store()` and `update()` validation arrays, add:

```php
'show_on_homepage' => 'nullable|boolean',
'homepage_sort_order' => 'nullable|integer|min:0',
```

After validation, normalize the checkbox:

```php
$validated['show_on_homepage'] = $request->boolean('show_on_homepage');
$validated['homepage_sort_order'] = (int) ($validated['homepage_sort_order'] ?? 0);
```

---

## 5. Admin Edit form (`resources/views/admin/restaurants/edit.blade.php`)

Add this block near Status / Domain fields:

```blade
<div>
    <label class="mb-2 block text-sm font-medium text-gray-700">Homepage Visibility</label>
    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
        <input type="checkbox" name="show_on_homepage" value="1"
            {{ old('show_on_homepage', $restaurant->show_on_homepage) ? 'checked' : '' }}
            class="form-checkbox h-4 w-4 text-hut-dark" />
        <span class="text-sm text-gray-700">Show on public homepage</span>
    </label>
    <p class="mt-1 text-xs text-gray-500">When enabled, this business appears in the main domain listing.</p>
</div>
<div>
    <label class="mb-2 block text-sm font-medium text-gray-700">Homepage Sort Order</label>
    <input type="number" name="homepage_sort_order" min="0"
        value="{{ old('homepage_sort_order', $restaurant->homepage_sort_order ?? 0) }}"
        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first.</p>
</div>
```

---

## 6. Admin Create form (`resources/views/admin/restaurants/create.blade.php`)

Add the same two fields (checkbox defaults to unchecked, sort order 0).

---

## 7. Admin Index (optional badge)

In the Status column you can add:

```blade
@if($restaurant->show_on_homepage)
    <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Homepage</span>
@endif
```

---

## 8. Clear caches

```bash
php artisan migrate
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

---

## Behaviour Summary

| URL | Result |
|-----|--------|
| `yoursite.com/` | Multi-business homepage (searchable) |
| `yoursite.com/pizza-house` | That restaurant’s menu |
| `pizza-house.com` (custom domain) | Only that restaurant’s menu |
| Order placed | Goes to that business’s tenant DB + manager notification |

Done.
