# Wire menu + checkout to sale_price

## 1. MenuController — eager load promotions + upcoming banner

In `renderMenu()` when loading categories:

```php
$categories = Category::where('is_active', true)
    ->where('restaurant_id', $restaurant->id)
    ->orderBy('sort_order')
    ->with(['availableMenuItems.sizes', 'availableMenuItems.promotions' => function ($q) {
        $q->where('is_active', true)->orderByDesc('id');
    }])
    ->get();

$upcomingPromotions = \App\Models\ItemPromotion::with('menuItem')
    ->where('restaurant_id', $restaurant->id)
    ->where('is_active', true)
    ->where(function ($q) {
        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
    })
    ->orderBy('starts_at')
    ->limit(12)
    ->get();
```

Pass to view: `'upcomingPromotions' => $upcomingPromotions`

## 2. menu.blade.php

Near top of content (after hero):

```blade
@include('customer.menu_partials.upcoming-sales-banner')
```

Replace the price/add buttons block for each item with:

```blade
@include('customer.menu_partials.item-price', ['item' => $item])
```

## 3. CheckoutController

Use the patched controller from this pack (sale price + stock decrement + PlatformNotification).

## 4. Optional start/end datetime

Already in Item Sales create/edit forms (`starts_at`, `ends_at`).

- **Live** = active + now between start/end  
- **Upcoming** = active + starts_at in the future → shown on banner as “Coming”  
- Users see both live and upcoming offers on the storefront banner  

Manager does not need Super Admin to publish item sales. Super Admin only controls homepage-level badge/banner (platform settings).
