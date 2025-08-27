<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\FacebookPage;
use Illuminate\Http\Request;

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
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'image_url' => 'nullable|url',
            'product_link' => 'nullable|url',
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'weight' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean'
        ]);

        // Verify Facebook page belongs to client
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        if (!$facebookPage) {
            return back()->withErrors(['facebook_page_id' => 'Invalid Facebook page selected.']);
        }

        $product = Product::create([
            'client_id' => $client->id,
            'facebook_page_id' => $request->facebook_page_id,
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku ?: null,
            'price' => $request->price,
            'sale_price' => $request->sale_price ?: null,
            'stock_quantity' => $request->stock_quantity,
            'image_url' => $request->image_url ?: null,
            'product_link' => $request->product_link ?: null,
            'category' => $request->category,
            'tags' => $request->tags ? explode(',', $request->tags) : null,
            'specifications' => $request->specifications ? json_decode($request->specifications, true) : null,
            'track_stock' => $request->boolean('track_stock', true),
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
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'image_url' => 'nullable|url',
            'product_link' => 'nullable|url',
            'facebook_page_id' => 'required|exists:facebook_pages,id',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'weight' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean'
        ]);

        // Verify Facebook page belongs to client
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        if (!$facebookPage) {
            return back()->withErrors(['facebook_page_id' => 'Invalid Facebook page selected.']);
        }

        $product->update([
            'facebook_page_id' => $request->facebook_page_id,
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku ?: null,
            'price' => $request->price,
            'sale_price' => $request->sale_price ?: null,
            'stock_quantity' => $request->stock_quantity,
            'image_url' => $request->image_url ?: null,
            'product_link' => $request->product_link ?: null,
            'category' => $request->category,
            'tags' => $request->tags ? explode(',', $request->tags) : null,
            'specifications' => $request->specifications ? json_decode($request->specifications, true) : null,
            'track_stock' => $request->boolean('track_stock', true),
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
