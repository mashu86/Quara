<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'text_color' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('background_image')) {
            $path = ImageOptimizerService::optimizeAndStore($request->file('background_image'), 'categories', 'public');
            $validated['background_image'] = 'storage/' . $path;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'background_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:12288',
            'text_color' => 'required|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('background_image')) {
            if ($category->background_image && str_contains($category->background_image, 'storage/')) {
                $oldPath = str_replace('storage/', '', $category->background_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = ImageOptimizerService::optimizeAndStore($request->file('background_image'), 'categories', 'public');
            $validated['background_image'] = 'storage/' . $path;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function toggleStatus(Category $category)
    {
        $category->status = ($category->status === 'active') ? 'inactive' : 'active';
        $category->save();

        return back()->with('success', 'Category status updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category that contains existing products.');
        }

        if ($category->background_image && str_contains($category->background_image, 'storage/')) {
            $oldPath = str_replace('storage/', '', $category->background_image);
            Storage::disk('public')->delete($oldPath);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}
