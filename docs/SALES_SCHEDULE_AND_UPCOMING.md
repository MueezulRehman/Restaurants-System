# Sales schedule & upcoming offers

## Is start/end datetime implemented?

**Yes** (Item Sales pack):

| Field | Purpose |
|-------|---------|
| `starts_at` | Optional — sale becomes live at this time |
| `ends_at` | Optional — sale stops after this time |
| `is_active` | Master switch |

Logic (`ItemPromotion::isLive()` / `currentlyActive()`):

- Must be `is_active = true`
- `starts_at` null **or** <= now  
- `ends_at` null **or** >= now  

## Who publishes sales?

| Actor | What they control |
|-------|-------------------|
| **Manager** | Item-level sales (which products, % or Rs, schedule). Shown on **that business menu** + upcoming banner |
| **Super Admin** | Homepage badge text, banner image, whether listing shows “Sale live” for businesses that have any live promotion |

Managers do **not** need Super Admin approval to run a sale on their own menu. That is the professional multi-tenant pattern.

## Upcoming (not yet started)

- Same ItemPromotion rows with future `starts_at`
- Storefront **Offers** banner lists them as “Coming”
- Price stays full until start

## Professional UX

1. Manager creates sales with schedule  
2. Menu shows live discounted price + badge  
3. Banner shows Live + Coming offers  
4. Homepage (main domain) can badge businesses with live sales  
5. Checkout **always** recalculates price server-side (cannot be faked from browser)
