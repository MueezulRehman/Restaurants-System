@extends('manager.layout.master')
@section('title', 'Recipes & Production')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Recipes & production</h1>
            <p class="mt-1 text-sm text-gray-500">Define ingredients, produce batches, and record wastage.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Create recipe</h2>
            <form method="POST" action="{{ route('manager.recipes.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">
                @csrf<select name="menu_item_id" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Finished product</option>@foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select><input name="name" required placeholder="Recipe name"
                    class="rounded-lg border px-3 py-2 text-sm"><input name="yield_quantity" required type="number"
                    min="0.001" step="0.001" placeholder="Yield quantity"
                    class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-4">Save
                    recipe</button></form>
        </section>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Add ingredient</h2>
            <form method="POST" action="{{ route('manager.recipes.ingredients.store') }}"
                class="mt-4 grid gap-3 md:grid-cols-4">@csrf<select name="recipe_id" required
                    class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Recipe</option>@foreach($recipes as $recipe)
                    <option value="{{ $recipe->id }}">{{ $recipe->name }}</option>@endforeach
                </select><select name="menu_item_id" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Ingredient</option>@foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select><input name="quantity" required type="number" min="0.001" step="0.001"
                    placeholder="Quantity per yield" class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Add ingredient</button></form>
        </section>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Produce batch</h2>
            <form method="POST" action="{{ route('manager.recipes.produce') }}" class="mt-4 grid gap-3 md:grid-cols-4">
                @csrf<select name="recipe_id" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Recipe</option>@foreach($recipes as $recipe)
                    <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ $recipe->product->name }})</option>@endforeach
                </select><input name="quantity" required type="number" min="0.001" step="0.001"
                    placeholder="Quantity to produce" class="rounded-lg border px-3 py-2 text-sm"><input name="notes"
                    placeholder="Production notes" class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Produce</button></form>
        </section>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Record wastage</h2>
            <form method="POST" action="{{ route('manager.recipes.wastage') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf<select name="menu_item_id" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Item</option>@foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                </select><input name="quantity" required type="number" min="0.001" step="0.001"
                    placeholder="Quantity wasted" class="rounded-lg border px-3 py-2 text-sm"><input name="notes"
                    placeholder="Reason" class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Record waste</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Recipe</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Ingredients</th>
                            <th class="px-4 py-3">Yield</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">@forelse($recipes as $recipe)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $recipe->name }}</td>
                            <td class="px-4 py-3">{{ $recipe->product->name }}</td>
                            <td class="px-4 py-3">
                                {{ $recipe->ingredients->map(fn($ingredient) => $ingredient->item->name . ' × ' . $ingredient->quantity)->implode(', ') ?: 'None yet' }}
                            </td>
                            <td class="px-4 py-3">{{ $recipe->yield_quantity }}</td>
                    </tr>@empty<tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">No recipes created.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection