# Codeibex Phase 2 — Module labels + Al Majid demo

**Author:** Mueez Ul Rehman

## Included

1. `config/module_labels.php` — labels/icons per business type  
2. `App\Support\ModuleLabels` + `module_label()` / `module_icon()` helpers  
3. Layout uses dynamic labels (Menu Items vs Inventory Items vs Medicines)  
4. `AlMajidAryansStoreSeeder` — realistic general store  
5. Migration stub for unit pricing (Phase 3)

## Install

```bash
unzip -o codeibex-phase2.zip

# autoload helper — see COMPOSER_AUTOLOAD.md
composer dump-autoload

php artisan migrate
php artisan db:seed --class=Database\\Seeders\\AlMajidAryansStoreSeeder
php artisan view:clear
php artisan config:clear
```

## Demo login

- Phone: `03001234567`
- Password: `password`
- Store: **Al Majid Aryans Store**
- Public slug: `/al-majid-aryans-store`

## Label examples

| Business type | `menu` module shows as |
|---------------|------------------------|
| Restaurant | Menu Items |
| General Store | Inventory Items |
| Pharmacy | Products / Medicines |
| Retail | Products |

## Next (Phase 3)

Wire `unit_type` + fractional qty into POS cart and stock deduction.
