{{-- Include at end of POS scripts: enables fractional qty + unit labels in cart --}}
<script>
(function () {
    // When adding a product from search/API payload that includes unit fields:
    // line.unit_label, line.allow_fractional_qty, line.price uses price_per_unit
    window.codeibexNormalizeLine = function (line, product) {
        if (!product) return line;
        line.unit_label = product.unit_label || product.unit_type || 'pc';
        line.allow_fractional_qty = !!product.allow_fractional_qty;
        if (product.price_per_unit != null && product.price_per_unit !== '') {
            line.unitPrice = parseFloat(product.price_per_unit);
        }
        if (line.allow_fractional_qty && (!line.quantity || line.quantity < 0.001)) {
            line.quantity = 1;
        }
        return line;
    };

    window.codeibexQtyStep = function (line) {
        return line && line.allow_fractional_qty ? 0.1 : 1;
    };

    // Optional: prompt for kg/liter qty when adding fractional items
    window.codeibexAskQty = function (product) {
        const fractional = !!(product && product.allow_fractional_qty);
        const unit = (product && (product.unit_label || product.unit_type)) || 'pc';
        if (!fractional) return 1;
        const raw = prompt('Quantity (' + unit + '):', '1');
        if (raw === null) return null;
        const q = parseFloat(raw);
        if (!q || q <= 0) return null;
        return Math.round(q * 1000) / 1000;
    };
})();
</script>
