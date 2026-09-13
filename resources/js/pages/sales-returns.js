function initSalesReturnsFilter() {
    const search = document.getElementById('return-customer-search');
    const select = document.querySelector('select[name="order_item_id"]');
    if (!search || !select) return;

    search.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();

        Array.from(select.options).forEach((option, index) => {
            if (index === 0) return;
            option.hidden = term !== '' && !(option.dataset.customer || '').includes(term);
        });
    });
}

document.addEventListener('DOMContentLoaded', initSalesReturnsFilter);
