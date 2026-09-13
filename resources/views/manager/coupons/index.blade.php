@extends('manager.layout.master')
@section('title', 'Coupons')
@section('page-content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Coupons</h1>
        <p class="mt-1 text-sm text-gray-500">Create discount codes for online orders.</p>
    </div>
    <section class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-hut-dark">Create coupon</h2>
        <form method="POST" action="{{ route('manager.coupons.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">@csrf
            <input name="code" required maxlength="50" placeholder="Code e.g. SAVE10"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm uppercase">
            <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <option value="percent">Percent off</option>
                <option value="fixed">Fixed amount off</option>
            </select>
            <input name="value" required type="number" min="0.01" step="0.01" placeholder="Discount value"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="minimum_order" type="number" min="0" step="0.01" placeholder="Minimum order"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="max_discount" type="number" min="0" step="0.01" placeholder="Max discount (optional)"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="starts_at" type="datetime-local" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="ends_at" type="datetime-local" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <input name="usage_limit" type="number" min="1" placeholder="Usage limit"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save
                coupon</button>
        </form>
    </section>
    <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Discount</th>
                        <th class="px-4 py-3">Minimum</th>
                        <th class="px-4 py-3">Usage</th>
                        <th class="px-4 py-3">Validity</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $coupon->code }}</td>
                            <td class="px-4 py-3">
                                {{ $coupon->type === 'percent' ? $coupon->value . '%' : 'Rs. ' . number_format($coupon->value, 2) }}
                            </td>
                            <td class="px-4 py-3">Rs. {{ number_format($coupon->minimum_order, 2) }}</td>
                            <td class="px-4 py-3">{{ $coupon->usage_count }} / {{ $coupon->usage_limit ?: '∞' }}</td>
                            <td class="px-4 py-3 text-xs">
                                {{ $coupon->starts_at?->format('d M Y H:i') ?: 'Now' }}<br>{{ $coupon->ends_at?->format('d M Y H:i') ?: 'No expiry' }}
                            </td>
                            <td class="px-4 py-3">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.coupons.destroy', $coupon) }}">@csrf
                                    @method('DELETE')<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50" aria-label="Delete coupon" title="Delete coupon"><x-icons.trash class="h-4 w-4" /></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No coupons created.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $coupons->links() }}</div>
    </section>
@endsection