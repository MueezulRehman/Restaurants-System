@extends('manager.layout.master')
@section('title', 'Wholesale Operations')
@section('page-content')
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-hut-dark">Wholesale operations</h1>
                <p class="mt-1 text-sm text-gray-500">Manage price-list groups and sales representative ownership.</p>
            </div><a href="{{ route('manager.wholesale.commissions') }}"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-hut-dark">Commission
                report</a>
        </div>
        @if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <div class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('manager.wholesale.price-lists.store') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf<h2
                    class="font-semibold text-hut-dark">Create price list</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2"><input name="name" required placeholder="Price list name"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="customer_group"
                        placeholder="Customer group" class="rounded-lg border-gray-200 px-3 py-2 text-sm"></div><button
                    class="mt-4 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save price list</button>
            </form>
            <form method="POST" action="{{ route('manager.wholesale.representatives.store') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf<h2
                    class="font-semibold text-hut-dark">Add sales representative</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3"><input name="name" required placeholder="Name"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="phone" placeholder="Phone"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"><input name="commission_rate" type="number"
                        step="0.01" min="0" max="100" placeholder="Commission %"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"></div><button
                    class="mt-4 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save
                    representative</button>
            </form>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('manager.wholesale.price-list-items.store') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf<h2 class="font-semibold">Set product
                    price</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3"><select name="wholesale_price_list_id" required
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Price list</option>@foreach($priceLists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }}</option>@endforeach
                    </select><select name="menu_item_id" required class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Product</option>@foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach
                    </select><input name="price" required type="number" min="0" step="0.01" placeholder="Wholesale price"
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm"></div><button
                    class="mt-4 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save product
                    price</button>
            </form>
            <form method="POST" action="{{ route('manager.wholesale.customers.representative') }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">@csrf @method('PATCH')
                <h2 class="font-semibold">Assign representative</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2"><select name="customer_id" required
                        class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Wholesale customer</option>@foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                    </select><select name="sales_representative_id" class="rounded-lg border-gray-200 px-3 py-2 text-sm">
                        <option value="">Unassigned</option>@foreach($representatives as $representative)
                        <option value="{{ $representative->id }}">{{ $representative->name }}</option>@endforeach
                    </select></div><button
                    class="mt-4 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Assign
                    representative</button>
            </form>
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Price lists</h2>@forelse($priceLists as $list)
                    <div class="mt-3 flex justify-between border-b border-gray-100 pb-3 text-sm">
                        <span><strong>{{ $list->name }}</strong><span
                                class="ml-2 text-gray-500">{{ $list->customer_group ?: 'All wholesale customers' }}</span></span><span
                            class="text-gray-500">{{ $list->items_count }} prices</span>
                </div>@empty<p class="mt-3 text-sm text-gray-500">No price lists created.</p>@endforelse
            </section>
            <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Representatives</h2>@forelse($representatives as $representative)
                    <div class="mt-3 border-b border-gray-100 pb-3 text-sm"><strong>{{ $representative->name }}</strong><span
                            class="ml-2 text-gray-500">{{ $representative->phone ?: 'No phone' }} ·
                {{ $representative->commission_rate }}%</span></div>@empty<p class="mt-3 text-sm text-gray-500">No
                        representatives created.</p>@endforelse
            </section>
        </div>
    </div>
@endsection