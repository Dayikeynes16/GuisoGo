<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use Concerns\SyncsModifierGroups;

    public function create(Request $request): Response
    {
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data['restaurant_id'] = $request->user()->restaurant_id;
        $data['sort_order'] = Product::where('category_id', $data['category_id'])->max('sort_order') + 1;
        $data['is_active'] = $data['is_active'] ?? true;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', config('filesystems.media_disk', 'public'));
        }

        $modifierGroups = $data['modifier_groups'] ?? [];
        unset($data['image'], $data['modifier_groups']);

        $product = Product::query()->create($data);

        $this->syncModifierGroups($product, $modifierGroups);

        return redirect()->route('menu.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $product->load('modifierGroups.options');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? $product->is_active;

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk(config('filesystems.media_disk', 'public'))->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', config('filesystems.media_disk', 'public'));
        }

        $modifierGroups = $data['modifier_groups'] ?? [];
        unset($data['image'], $data['modifier_groups']);

        $product->update($data);

        $this->syncModifierGroups($product, $modifierGroups);

        return redirect()->route('menu.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if ($product->image_path) {
            Storage::disk(config('filesystems.media_disk', 'public'))->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('menu.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);

        return redirect()->route('menu.index');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Product::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $restaurantId = $request->user()->restaurant_id;

        foreach ($request->input('ids') as $index => $id) {
            Product::query()
                ->where('id', $id)
                ->where('restaurant_id', $restaurantId)
                ->update(['sort_order' => $index]);
        }

        return redirect()->route('menu.index');
    }
}
