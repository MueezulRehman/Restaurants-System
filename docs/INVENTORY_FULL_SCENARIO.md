# Codeibex Inventory & Stock — Full Scenario

**How stock works from entry → batch → POS / online checkout**

---

## 1. Two stock models (by business type)

| Business type | What is tracked | Where quantity lives |
|---------------|-----------------|----------------------|
| Restaurant / Cafe / Retail (simple) | Menu items | `menu_items.stock_quantity` when `track_stock = true` |
| Retail with variants | Product variants | `product_variants.quantity_available` |
| Pharmacy | Medicine **batches** | `medicine_batches.quantity` (FEFO / expiry aware) |

Modules (`stock`, `inventory`, `medical`) only control **who can open the screens**. Tables exist in the tenant schema either way.

---

## 2. Stock IN (increase)

### A. Simple menu item
1. Manager opens menu item → enable **Track stock**
2. Set `stock_quantity` and optional `low_stock_threshold`
3. Or use **Stock adjustments** screen (reason: purchase / correction)

### B. Variants
1. Product has variants (size/colour/SKU)
2. Each variant has `quantity_available`
3. Adjustments via `StockService::adjust()` → writes `stock_adjustments` audit row

### C. Pharmacy batches (professional medical flow)
1. **Purchases** → record supplier buy  
2. Creates `MedicineBatch`: batch_number, expiry_date, purchase_price, selling_price, **quantity**  
3. Inventory audit log notes “Purchase received”  
4. Cannot sell expired batches (POS blocks)

---

## 3. Stock OUT (decrease) — POS

`PosController@store` (channel = `pos`):

1. Cart line types: `menu_item` | `variant` | `deal` | `medicine_batch`
2. **Before** creating order:
   - Menu item with `track_stock`: require `stock_quantity >= qty`
   - Variant: require `quantity_available >= qty`
   - Medicine batch: require `batch.quantity >= qty` + not expired + allergy/Rx checks
3. Order created as **delivered** immediately
4. Stock moved:
   - Decrement menu_item / variant / batch quantities
   - `StockAdjustment` (or equivalent) with reason `sale`, notes with order number
5. Cash sale → Cashbook entry
6. Low stock can trigger notification (OrderController path also has low-stock alerts)

**POS inventory sync = same database, same rows.** There is no separate “POS stock file”. POS and online both read/write tenant DB quantities.

---

## 4. Stock OUT — Online checkout (gap fixed in this pack)

Previously online checkout **priced** items but **did not** always:

- Apply item sale price server-side  
- Check / decrement `track_stock` items  

**This pack fixes that:**

1. `StorefrontPricing::unitPriceForMenuItem()` applies live `ItemPromotion`  
2. If `track_stock` and insufficient qty → 422 error  
3. After order create → decrement `stock_quantity` + optional StockAdjustment  
4. PlatformNotification for offline managers  

**Note:** Online channel typically sells menu items/deals, not pharmacy batches (POS does batches). That is correct professionally.

---

## 5. Order confirm path (manager accepts online order)

`OrderController` status updates can also decrement stock when moving toward confirmed/prepared (existing logic for tracked items). Avoid double-decrement: if online checkout already decremented at place-order, status changes should not subtract again.  

**Professional rule (recommended):**

- **POS**: decrement at sale (order already delivered)  
- **Online**: decrement at **place order** (this pack) OR only when manager sets status to `confirmed` — pick one.  
- This pack uses **decrement at place order** for online (inventory reserved).

If you prefer reserve-on-confirm only, remove the decrement block from CheckoutController and keep OrderController decrement.

---

## 6. End-to-end scenarios

### Scenario A — Restaurant burger (track_stock)

```
1. Manager sets Burger track_stock=true, stock_quantity=50
2. Manager adds 20% sale, starts_at=now, ends_at=+3 days
3. Customer opens menu → sees Rs 500 → Rs 400 + "20% OFF"
4. Customer checks out qty 2
5. Server prices at 400 each (ignores any lower cart price)
6. Stock 50 → 48, StockAdjustment logged
7. Manager sees order + PlatformNotification + live Echo event
```

### Scenario B — Pharmacy batch

```
1. Purchase: Panadol batch B1, qty 100, expiry 2027-01-01
2. POS sells batch B1 qty 2
3. Checks expiry + qty
4. batch.quantity 100 → 98
5. Order delivered + cashbook
```

### Scenario C — Upcoming sale (not yet started)

```
1. Manager creates sale starts_at = next Friday, 15% off
2. Storefront banner: "Coming: Burger — 15% off from Fri"
3. Until start, price stays full; after start, live pricing applies
```

---

## 7. POS ↔ inventory “sync”

There is **no external sync job**. Professional model:

| Layer | Behaviour |
|-------|-----------|
| Single source of truth | Tenant DB quantities |
| POS | Reads live qty, writes on sale in same transaction |
| Online | Same tables (after this pack) |
| Purchases / adjustments | Increase qty + audit |
| Reports | Stock analysis / low stock from same numbers |

If two cashiers sell the last unit concurrently, DB transaction + qty check prevents negative stock (one request fails with 422).

---

## 8. Gaps and recommendations

| Topic | Status |
|-------|--------|
| Item sale start/end datetime | Implemented in Item Sales forms |
| Upcoming sales visible to users | Banner partial in this pack |
| Menu shows sale price | item-price partial |
| Checkout uses sale price | Patched CheckoutController |
| Online stock decrement | Patched in this pack |
| POS stock | Already solid |
| Pharmacy batches | Already solid |
| Double-decrement risk | Align online vs status-change policy |
| Variant stock on online | Online rarely sells variants; extend if needed |
| Super Admin “homepage sale badge” | Platform settings pack |

---

## 9. Mental model

```
Purchase / Adjustment  →  quantity UP   (+ audit)
POS sale / Online order →  quantity DOWN (+ audit)
Promotion               →  price only (does not change stock)
Deal                    →  fixed package price (separate from item %)
```

Stock is operational truth. Sales are pricing overlays with a schedule.
