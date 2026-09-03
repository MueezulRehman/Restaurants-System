# Codeibex Phase 3 — Unit POS + stock by sold qty

**Author:** Mueez Ul Rehman

## Features

1. **unit_type** on items: `piece`, `kg`, `liter`, `dozen`, custom  
2. **price_per_unit** + **allow_fractional_qty**  
3. POS accepts **decimal quantity** (e.g. 1.5 kg)  
4. Stock deducts the **sold quantity** (not always 1)  
5. Friendly validation message map  
6. POS JS helpers for unit labels / fractional step  

## Install

```bash
unzip -o codeibex-phase3.zip
# copy tenant migration into database/tenant_migrations if not already

php artisan migrate
php artisan tenants:migrate   # if using DB-per-business
php artisan view:clear
```

### Item setup (manager)

On menu/inventory item:

- Unit type: kg / dozen / piece / liter  
- Price per unit  
- Track stock + stock quantity (can be decimal for kg)  
- Allow fractional qty (auto for kg/liter)

### POS

- Adding a fractional item prompts for qty (kg)  
- Cart shows price per unit  
- Checkout validates stock against sold qty  

## Optional: global validation language

In `AppServiceProvider::boot()`:

```php
\Illuminate\Support\Facades\Validator::default(
    // or use lang/en/validation.php overrides
);
```

Or merge `FriendlyValidation::messages()` into Form Request `messages()`.

## Search / pagination (recommended pattern)

```php
$query = Model::query()->where('restaurant_id', $id);
if ($search = request('q')) {
    $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('sku', 'like', "%{$search}%");
    });
}
$items = $query->orderByDesc('id')->paginate(20)->withQueryString();
```

Apply on menu-items, customers, orders indexes where missing.
