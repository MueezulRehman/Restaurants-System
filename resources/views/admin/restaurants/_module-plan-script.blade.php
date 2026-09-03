{{--
    Single source of truth for Business create/edit forms:
    1. Business Type change → auto-check modules linked to that type (from DB pivot)
    2. Subscription Plan change → enforce plan max_modules (disable extra checkboxes)
    3. Live counter of selected modules vs plan cap

    Expects: $businessTypes, $modules, $subscriptionPlans
    Optional: data-auto-apply-on-load="1" on the form for create page only
--}}
<script>
(function () {
    const businessTypeModules = {!! json_encode(
        $businessTypes->mapWithKeys(fn ($bt) => [$bt->id => $bt->modules()->pluck('key')->toArray()])
    ) !!};

    const planModuleCaps = {!! json_encode(
        $subscriptionPlans->mapWithKeys(fn ($plan) => [$plan->slug => $plan->max_modules])
    ) !!};

    const businessTypeSelect = document.querySelector('select[name="business_type_id"]');
    const planSelect = document.querySelector('select[name="plan"]');
    const moduleCheckboxes = Array.from(document.querySelectorAll('input[name="enabled_modules[]"]'));
    const form = document.querySelector('form[action*="restaurants"]');
    let capNotice = document.getElementById('module-cap-notice');

    if (!capNotice && moduleCheckboxes.length) {
        const modulesBlock = moduleCheckboxes[0].closest('.md\\:col-span-2') || moduleCheckboxes[0].parentElement?.parentElement;
        if (modulesBlock) {
            capNotice = document.createElement('p');
            capNotice.id = 'module-cap-notice';
            capNotice.className = 'text-xs text-gray-500 mt-2';
            modulesBlock.appendChild(capNotice);
        }
    }

    function currentCap() {
        if (!planSelect) return null;
        const cap = planModuleCaps[planSelect.value];
        if (cap === undefined || cap === null || cap === '' || Number(cap) <= 0) {
            return null; // unlimited
        }
        return parseInt(cap, 10);
    }

    function applyCap() {
        const cap = currentCap();
        const checkedCount = moduleCheckboxes.filter(cb => cb.checked).length;

        moduleCheckboxes.forEach(cb => {
            if (cap !== null && !cb.checked && checkedCount >= cap) {
                cb.disabled = true;
            } else {
                cb.disabled = false;
            }
        });

        if (capNotice) {
            if (cap === null) {
                capNotice.textContent = 'This plan allows unlimited modules.';
                capNotice.classList.remove('text-red-600');
                capNotice.classList.add('text-gray-500');
            } else {
                capNotice.textContent = checkedCount + ' / ' + cap + ' modules used for this plan.';
                capNotice.classList.toggle('text-red-600', checkedCount >= cap);
                capNotice.classList.toggle('text-gray-500', checkedCount < cap);
            }
        }
    }

    function applyRecommendedModules() {
        if (!businessTypeSelect) return;
        const recommended = businessTypeModules[businessTypeSelect.value] || [];

        moduleCheckboxes.forEach(cb => {
            const key = cb.getAttribute('data-module-key');
            cb.checked = recommended.includes(key);
            cb.disabled = false;
        });

        // If over plan cap after type change, uncheck excess (keep recommended order)
        const cap = currentCap();
        if (cap !== null) {
            let kept = 0;
            moduleCheckboxes.forEach(cb => {
                if (!cb.checked) return;
                kept++;
                if (kept > cap) {
                    cb.checked = false;
                }
            });
        }

        applyCap();
    }

    if (businessTypeSelect) {
        businessTypeSelect.addEventListener('change', applyRecommendedModules);
    }

    if (planSelect) {
        planSelect.addEventListener('change', function () {
            const cap = currentCap();
            if (cap !== null) {
                let kept = 0;
                moduleCheckboxes.forEach(cb => {
                    if (!cb.checked) return;
                    kept++;
                    if (kept > cap) {
                        cb.checked = false;
                    }
                });
            }
            applyCap();
        });
    }

    moduleCheckboxes.forEach(cb => cb.addEventListener('change', applyCap));

    // Create page: apply type defaults on load if needed
    const autoApply = form && form.getAttribute('data-auto-apply-modules') === '1';
    if (autoApply && businessTypeSelect) {
        const anyChecked = moduleCheckboxes.some(c => c.checked);
        if (!anyChecked) {
            applyRecommendedModules();
        } else {
            applyCap();
        }
    } else {
        applyCap();
    }

    // Before submit: re-enable disabled boxes so selected values still post
    if (form) {
        form.addEventListener('submit', function () {
            moduleCheckboxes.forEach(cb => { cb.disabled = false; });
        });
    }
})();
</script>
