# Best approach: stock timing + business hours

## Stock policy (chosen)

| Channel | When stock decreases |
|---------|----------------------|
| **POS** | At sale (order is delivered immediately) — unchanged |
| **Online** | **Only when manager sets status → confirmed** |
| **Online place order** | **Check** stock only (block if insufficient) — **do not** decrement |
| **Cancel after confirm** | Restore stock once (`OrderStockService::restoreOnCancel`) |

### Why this is best

1. Pending orders the manager rejects do **not** consume inventory.  
2. Kitchen only works on confirmed orders → stock matches what will be prepared.  
3. No double-decrement (idempotent via `stock_adjustments.reference_id = order.id`).  
4. POS stays simple (sell = stock out).

### OrderController change

- **Remove** stock loop from `status === 'delivered'`
- **Add** on first `confirmed`: `OrderStockService::decrementOnConfirm($order)`
- **Add** on `cancelled`: `OrderStockService::restoreOnCancel($order)` (only if previously confirmed)

### CheckoutController

- Keep sale-price server calc  
- Keep stock **assert** (enough qty)  
- **Remove** any decrement / StockAdjustment create from place-order  

---

## Business hours + “closed today”

| Feature | Behaviour |
|---------|-----------|
| Weekly hours | Manager sets open/close per day |
| Closed day in week | Day marked `closed: true` |
| **Closed today** toggle | Overrides hours — business off for the day |
| Message | Optional text shown to customers |
| Online orders | Blocked when closed (unless `accept_orders_when_closed`) |
| Storefront | Badge: Open / Closed · opens 10:00 |

### Customer experience

- Menu still viewable (browse) when closed  
- Checkout blocked with clear message  
- Homepage can show open/closed badge  

---

## Optional gaps status (updated)

| Item | Status |
|------|--------|
| Sale schedule start/end | Done |
| Upcoming banner on menu | Done |
| Server-side sale price | Done |
| Online stock **check** at checkout | Done |
| Online stock **decrement on confirm** | **This pack (best approach)** |
| POS batch / variant | Solid |
| Homepage sale badge | Platform settings pack |
| Business hours + closed today | **This pack** |
| Variant stock on online cart | Optional (rare for food storefront) |
