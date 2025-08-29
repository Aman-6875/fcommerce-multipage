<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\FacebookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = auth('client')->user();
        $products = $client->products()
            ->with('facebookPage')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $client = auth('client')->user();
        $facebookPages = $client->facebookPages()->where('is_connected', true)->get();
        
        if ($facebookPages->isEmpty()) {
            return redirect()->route('client.facebook.index')
                ->with('error', 'Please connect a Facebook page first to add products.');
        }

        return view('client.products.create', compact('facebookPages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $client = auth('client')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_link' => 'nullable|url',
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        // Verify Facebook page belongs to client
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        if (!$facebookPage) {
            return back()->withErrors(['facebook_page_id' => 'Invalid Facebook page selected.']);
        }

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('products', $imageName, 'public');
            $imageUrl = asset('storage/' . $imagePath);
        }

        $product = Product::create([
            'client_id' => $client->id,
            'facebook_page_id' => $request->facebook_page_id,
            'name' => $request->name,
            'description' => $request->description,
            'sku' => null,
            'price' => $request->price,
            'sale_price' => null,
            'stock_quantity' => 0,
            'image_url' => $imageUrl,
            'product_link' => $request->product_link ?: null,
            'category' => 'General',
            'tags' => null,
            'specifications' => null,
            'track_stock' => false,
            'is_active' => $request->boolean('is_active', true),
            'weight' => $request->weight ?: null,
            'sort_order' => 0
        ]);

        return redirect()->route('client.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Ensure product belongs to the authenticated client
        if ($product->client_id !== auth('client')->id()) {
            abort(404);
        }

        return view('client.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Ensure product belongs to the authenticated client
        if ($product->client_id !== auth('client')->id()) {
            abort(404);
        }

        $client = auth('client')->user();
        $facebookPages = $client->facebookPages()->where('is_connected', true)->get();

        return view('client.products.edit', compact('product', 'facebookPages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure product belongs to the authenticated client
        if ($product->client_id !== auth('client')->id()) {
            abort(404);
        }

        $client = auth('client')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_link' => 'nullable|url',
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        // Verify Facebook page belongs to client
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        if (!$facebookPage) {
            return back()->withErrors(['facebook_page_id' => 'Invalid Facebook page selected.']);
        }

        // Handle image upload
        $imageUrl = $product->image_url; // Keep existing image if no new one uploaded
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image_url) {
                $oldImagePath = str_replace(asset('storage/'), '', $product->image_url);
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('products', $imageName, 'public');
            $imageUrl = asset('storage/' . $imagePath);
        }

        $product->update([
            'facebook_page_id' => $request->facebook_page_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_url' => $imageUrl,
            'product_link' => $request->product_link ?: null,
            'is_active' => $request->boolean('is_active', true),
            'weight' => $request->weight ?: null
        ]);

        return redirect()->route('client.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Ensure product belongs to the authenticated client
        if ($product->client_id !== auth('client')->id()) {
            abort(404);
        }

        // Delete associated image if it exists
        if ($product->image_url) {
            $imagePath = str_replace(asset('storage/'), '', $product->image_url);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $product->delete();

        return redirect()->route('client.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Get products for modal selection (AJAX endpoint)
     */
    public function getModalProducts(Request $request, $pageId)
    {
        $client = auth('client')->user();
        
        // Verify the page belongs to the client
        $facebookPage = FacebookPage::where('client_id', $client->id)
            ->where('id', $pageId)
            ->first();
            
        if (!$facebookPage) {
            return response()->json(['error' => 'Page not found'], 404);
        }
        
        $query = Product::where('client_id', $client->id)
            ->where('facebook_page_id', $pageId)
            ->where('is_active', true);
        
        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }
        
        $products = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'sale_price', 'image_url', 'category', 'stock_quantity']);
            
        // Get unique categories for filter dropdown
        $categories = Product::where('client_id', $client->id)
            ->where('facebook_page_id', $pageId)
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
        
        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'page_name' => $facebookPage->page_name
        ]);
    }
}
