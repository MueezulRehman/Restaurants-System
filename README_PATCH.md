# TasteHut POS – Full Discount System (All Business Types)

## Recommendation: Do NOT force Restaurant onto Counter page

| | Keep separate views (recommended) | Force restaurant → counter |
|--|-----------------------------------|----------------------------|
| Sizes / toppings / deals / tables | Work | **Break** |
| Barcode / medical batches | Work on retail/medical | OK |
| Discount (item + bill) | Same on all | Same |
| Hardcoding | None | None |

**Best approach used here:** one discount system for every POS mode.
Restaurant keeps its features. Retail/Medical keep theirs.
Same bill discount + per-item discount on all screens.

---

## Features

### 1. Bill-level discount (every receipt)
- Rs or % on the whole bill
- Live Subtotal (before) → Discount → Total (after)

### 2. Per-item discount (every line)
- On each cart line: choose Rs or % discount
- Applied when item is added to the bill
- Shown on receipt (strikethrough original + final)

### 3. Walk-in Customer
- If no customer selected → system attaches Walk-in Customer automatically

### 4. Error-safe
- Discount values return with the form after any error

### 5. All business types
- Restaurant / Fast Food / Cafe → restaurant.blade.php
- Retail / Medical / General → counter.blade.php
- Shared backend in PosController (no hardcoding)

---

## Install

```bash
# from Laravel project root
unzip -o tastehut-pos-full-discount.zip
php artisan migrate
php artisan view:clear
php artisan cache:clear
```

Hard refresh browser (Ctrl+Shift+R).

---

## Files

```
app/Http/Controllers/Admin/PosController.php
app/Models/Order.php
app/Models/OrderItem.php
database/migrations/2026_08_16_000001_add_discount_to_orders_table.php
database/migrations/2026_08_16_000002_add_discount_to_order_items_table.php
database/tenant_migrations/... (same)
resources/views/admin/pos/counter.blade.php
resources/views/admin/pos/restaurant.blade.php
resources/views/admin/pos/receipt.blade.php
```

Fully functional. No hardcoding. No broken restaurant features.
