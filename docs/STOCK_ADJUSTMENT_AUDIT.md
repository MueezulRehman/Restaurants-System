# Stock Adjustment Audit Logic — Exploration

## What exists today

Codeibex records inventory changes in `stock_adjustments` with:

| Field | Purpose |
|-------|---------|
| `quantity_before` / `quantity_after` / `change_quantity` | Snapshot of the move |
| `reason` | sale, return, purchase, damage, expiry, recount, correction… |
| `reference_id` | Links to order id, purchase id, etc. |
| `user_id` / `created_by` | Who did it |
| `product_variant_id` and/or `menu_item_id` | What was adjusted |

### Write paths

| Source | What it logs |
|--------|----------------|
| **POS sale** | Decrement variant / menu item / medicine batch + adjustment reason `sale` |
| **Online order confirm** (policy pack) | `OrderStockService` → reason `sale`, `reference_id = order.id` |
| **Cancel after confirm** | reason `return`, same reference |
| **StockAdjustmentController** | Manual in/out/correction for variants |
| **Purchase (pharmacy)** | Batch qty up + audit note |
| **StockService::adjust()** | Variant adjustments with reasons |

## Schema drift (important gap)

Two migration shapes conflict:

1. **2026_07_10** — `product_variant_id`, `user_id`, enum `reason`, `change_quantity`
2. **2026_07_13** — `menu_item_id` required, `adjustment_type` in/out/correction, `quantity_changed`, `created_by`

The **model** fillable includes both `menu_item_id` and `product_variant_id`, but not every environment’s DB will have both columns.

### Professional fix

1. Single canonical table supporting:
   - `menu_item_id` nullable  
   - `product_variant_id` nullable  
   - `medicine_batch_id` nullable (optional later)  
   - `user_id` nullable (system/online)  
   - `reason` string/enum  
   - `reference_id` string  
2. All writers go through **one** helper: `StockAudit::record(...)`  
3. Never create adjustments without updating the quantity in the **same DB transaction**.

## Audit rules (recommended)

```
1. Every stock quantity change MUST write one adjustment row.
2. Online: only on confirm (not on pending place-order).
3. POS: on sale in same transaction as order.
4. Idempotent sales: unique-ish key (reason + reference_id + item) — check before insert.
5. Cancels restore with reason=return and same reference_id.
6. user_id nullable for system events; POS/manager fills Auth::id().
```

## Automated “sync”

There is **no** separate POS inventory sync service. The audit log **is** the sync history:

- POS and online both write the same table  
- Reports read `stock_adjustments` + current qty  
- That is the correct SaaS design (one source of truth)

Optional later: nightly job that flags `quantity_after` mismatches vs live qty (integrity check).
