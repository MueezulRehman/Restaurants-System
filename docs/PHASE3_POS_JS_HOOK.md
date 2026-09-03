# POS JS hooks (unit qty)

In `counter.blade.php` / `restaurant.blade.php` where you **add to cart**:

```js
// after you have `product` from API/search:
const qty = window.codeibexAskQty ? window.codeibexAskQty(product) : 1;
if (qty === null) return;

let line = { id: product.id, name: product.name, quantity: qty, unitPrice: product.price, ... };
if (window.codeibexNormalizeLine) {
  line = window.codeibexNormalizeLine(line, product);
}
cart.push(line);
```

Include partial once near other POS scripts:

```blade
@include('admin.pos._unit_qty_js')
```

Qty +/- buttons:

```js
const step = window.codeibexQtyStep ? window.codeibexQtyStep(line) : 1;
line.quantity = Math.round((line.quantity + step * dir) * 1000) / 1000;
if (line.quantity <= 0) remove line;
```

Display unit on cart line:

```html
Rs. ${line.unitPrice} / ${line.unit_label || 'pc'}
```
