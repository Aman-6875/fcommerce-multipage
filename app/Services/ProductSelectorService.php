<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductSelectorService
{
    /**
     * Parse product selection input from customer
     */
    public function parseProductSelection(
        string $input,
        int $clientId,
        array $config,
        string $language
    ): array {
        $products = $this->getClientProducts($clientId);
        $isMultiple = $config['multiple'] ?? false;
        
        // Clean and parse input
        $productInputs = $this->parseInput($input, $isMultiple);
        
        $selectedProducts = [];
        $errors = [];
        $allSuggestions = [];
        
        foreach ($productInputs as $productInput) {
            $result = $this->findProduct($productInput, $products);
            
            if ($result['success']) {
                $selectedProducts[] = $result['product'];
            } else {
                $errors[] = [
                    'input' => $productInput['original'] ?? $productInput['name'],
                    'reason' => $result['reason'],
                    'suggestions' => $result['suggestions']
                ];
                $allSuggestions = array_merge($allSuggestions, $result['suggestions']);
            }
        }
        
        // Validate selection against config
        $validation = $this->validateSelection($selectedProducts, $config);
        
        if (!empty($errors) || !$validation['valid']) {
            return [
                'success' => false,
                'errors' => $errors,
                'validation_errors' => $validation['errors'],
                'suggestions' => array_unique($allSuggestions),
                'found_products' => $selectedProducts
            ];
        }
        
        return [
            'success' => true,
            'products' => $this->formatSelectedProducts($selectedProducts, $productInputs)
        ];
    }
    
    /**
     * Parse user input into product search terms with quantities
     */
    private function parseInput(string $input, bool $allowMultiple): array
    {
        $input = trim($input);
        
        if (!$allowMultiple) {
            return [$this->extractProductAndQuantity($input)];
        }
        
        // Handle comma-separated products
        $products = array_map('trim', preg_split('/[,;]/', $input));
        
        return array_map(function($product) {
            return $this->extractProductAndQuantity($product);
        }, array_filter($products));
    }
    
    /**
     * Extract product name and quantity from input like "Product x2", "2 Product", etc.
     */
    private function extractProductAndQuantity(string $input): array
    {
        $original = $input;
        $input = trim($input);
        
        // Match patterns like "Product x2", "2x Product", "Product (2)", "2 Product"
        $patterns = [
            '/^(.+?)\s*[x×]\s*(\d+)$/iu',        // "Product x2" or "Product ×2"
            '/^(\d+)\s*[x×]\s*(.+)$/iu',         // "2x Product" or "2×Product"
            '/^(.+?)\s*\((\d+)\)$/iu',           // "Product (2)"
            '/^(\d+)\s+(.+)$/u',                 // "2 Product"
            '/^(.+?)\s+(\d+)$/u'                 // "Product 2" (if no other pattern matches)
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                // Determine which match is quantity and which is name
                if (is_numeric($matches[1])) {
                    return [
                        'name' => trim($matches[2]),
                        'quantity' => max(1, (int)$matches[1]),
                        'original' => $original
                    ];
                } else {
                    return [
                        'name' => trim($matches[1]),
                        'quantity' => max(1, (int)$matches[2]),
                        'original' => $original
                    ];
                }
            }
        }
        
        return [
            'name' => $input,
            'quantity' => 1,
            'original' => $original
        ];
    }
    
    /**
     * Find product by name with fuzzy matching
     */
    private function findProduct(array $productInput, Collection $products): array
    {
        $searchName = strtolower(trim($productInput['name']));
        
        if (empty($searchName)) {
            return [
                'success' => false,
                'reason' => 'empty_search',
                'suggestions' => []
            ];
        }
        
        // 1. Exact match (case-insensitive)
        $exactMatch = $products->first(function($product) use ($searchName) {
            return strtolower(trim($product->name)) === $searchName;
        });
        
        if ($exactMatch) {
            return [
                'success' => true,
                'product' => $this->formatProduct($exactMatch, $productInput['quantity'])
            ];
        }
        
        // 2. Exact SKU match
        if (!empty(trim($productInput['name']))) {
            $skuMatch = $products->first(function($product) use ($searchName) {
                return $product->sku && strtolower(trim($product->sku)) === $searchName;
            });
            
            if ($skuMatch) {
                return [
                    'success' => true,
                    'product' => $this->formatProduct($skuMatch, $productInput['quantity'])
                ];
            }
        }
        
        // 3. Partial name matches
        $partialMatches = $products->filter(function($product) use ($searchName) {
            $productName = strtolower(trim($product->name));
            return str_contains($productName, $searchName) || 
                   str_contains($searchName, $productName) ||
                   $this->calculateSimilarity($searchName, $productName) > 75;
        });
        
        if ($partialMatches->count() === 1) {
            return [
                'success' => true,
                'product' => $this->formatProduct($partialMatches->first(), $productInput['quantity'])
            ];
        }
        
        if ($partialMatches->count() > 1) {
            // Sort by similarity and relevance
            $sortedMatches = $partialMatches->map(function($product) use ($searchName) {
                return [
                    'product' => $product,
                    'similarity' => $this->calculateSimilarity($searchName, strtolower($product->name)),
                    'contains' => str_contains(strtolower($product->name), $searchName) ? 1 : 0,
                    'starts_with' => str_starts_with(strtolower($product->name), $searchName) ? 1 : 0
                ];
            })->sortByDesc(function($item) {
                return $item['starts_with'] * 1000 + $item['contains'] * 100 + $item['similarity'];
            });
            
            return [
                'success' => false,
                'reason' => 'multiple_matches',
                'suggestions' => $sortedMatches->take(5)->pluck('product.name')->toArray()
            ];
        }
        
        // 4. Fuzzy search with word matching
        $fuzzyMatches = $this->findFuzzyMatches($searchName, $products);
        
        if ($fuzzyMatches->isNotEmpty()) {
            return [
                'success' => false,
                'reason' => 'fuzzy_matches',
                'suggestions' => $fuzzyMatches->take(3)->pluck('name')->toArray()
            ];
        }
        
        // 5. No matches found - suggest popular products
        $popularProducts = $products->sortByDesc('stock_quantity')->take(3);
        
        return [
            'success' => false,
            'reason' => 'not_found',
            'suggestions' => $popularProducts->pluck('name')->toArray()
        ];
    }
    
    /**
     * Calculate similarity between two strings
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Remove common words that might interfere
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        
        $str1 = $this->removeCommonWords($str1, $commonWords);
        $str2 = $this->removeCommonWords($str2, $commonWords);
        
        similar_text($str1, $str2, $percent);
        
        // Boost score for exact word matches
        $words1 = explode(' ', $str1);
        $words2 = explode(' ', $str2);
        $commonWordsCount = count(array_intersect($words1, $words2));
        
        if ($commonWordsCount > 0) {
            $percent += $commonWordsCount * 10; // Boost for each common word
        }
        
        return min(100, $percent);
    }
    
    /**
     * Remove common words from string
     */
    private function removeCommonWords(string $str, array $commonWords): string
    {
        $words = explode(' ', $str);
        $filteredWords = array_filter($words, function($word) use ($commonWords) {
            return !in_array(strtolower($word), $commonWords) && strlen($word) > 1;
        });
        
        return implode(' ', $filteredWords);
    }
    
    /**
     * Find fuzzy matches using word analysis
     */
    private function findFuzzyMatches(string $searchName, Collection $products): Collection
    {
        $searchWords = explode(' ', $searchName);
        
        return $products->map(function($product) use ($searchWords, $searchName) {
            $productName = strtolower($product->name);
            $productWords = explode(' ', $productName);
            
            // Calculate word match score
            $wordMatches = 0;
            foreach ($searchWords as $searchWord) {
                if (strlen($searchWord) < 2) continue;
                
                foreach ($productWords as $productWord) {
                    if (str_contains($productWord, $searchWord) || str_contains($searchWord, $productWord)) {
                        $wordMatches++;
                        break;
                    }
                }
            }
            
            // Calculate overall similarity
            $similarity = $this->calculateSimilarity($searchName, $productName);
            $score = $wordMatches * 20 + $similarity;
            
            return [
                'product' => $product,
                'score' => $score,
                'word_matches' => $wordMatches,
                'similarity' => $similarity
            ];
        })->where('score', '>', 30)  // Only include decent matches
          ->sortByDesc('score')
          ->pluck('product');
    }
    
    /**
     * Format product for response
     */
    private function formatProduct(Product $product, int $quantity): array
    {
        $effectivePrice = $product->sale_price ?: $product->price;
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $effectivePrice,
            'original_price' => $product->price,
            'sale_price' => $product->sale_price,
            'quantity' => max(1, $quantity),
            'total' => $effectivePrice * max(1, $quantity),
            'image_url' => $product->image_url,
            'category' => $product->category,
            'stock_quantity' => $product->stock_quantity,
            'track_stock' => $product->track_stock,
            'is_available' => $this->checkAvailability($product, $quantity)
        ];
    }
    
    /**
     * Check if product is available in requested quantity
     */
    private function checkAvailability(Product $product, int $quantity): bool
    {
        if (!$product->track_stock) {
            return true; // Assume available if not tracking stock
        }
        
        return $product->stock_quantity >= $quantity;
    }
    
    /**
     * Validate selection against config constraints
     */
    private function validateSelection(array $products, array $config): array
    {
        $errors = [];
        $count = count($products);
        
        // Check minimum products
        if (isset($config['min_products']) && $count < $config['min_products']) {
            $errors[] = [
                'type' => 'min_products',
                'required' => $config['min_products'],
                'actual' => $count
            ];
        }
        
        // Check maximum products
        if (isset($config['max_products']) && $count > $config['max_products']) {
            $errors[] = [
                'type' => 'max_products',
                'limit' => $config['max_products'],
                'actual' => $count
            ];
        }
        
        // Check stock availability
        foreach ($products as $product) {
            if (!$product['is_available']) {
                $errors[] = [
                    'type' => 'out_of_stock',
                    'product' => $product['name'],
                    'requested' => $product['quantity'],
                    'available' => $product['stock_quantity']
                ];
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get client products for selection
     */
    public function getClientProducts(int $clientId): Collection
    {
        return Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get products for a specific Facebook page
     */
    public function getProductsForFacebookPage(int $facebookPageId): Collection
    {
        return Product::where('facebook_page_id', $facebookPageId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Format selected products for final output
     */
    private function formatSelectedProducts(array $products, array $originalInputs): array
    {
        return $products;
    }
    
    /**
     * Get product suggestions based on category or popularity
     */
    public function getProductSuggestions(int $clientId, ?string $category = null, int $limit = 10): array
    {
        $query = Product::where('client_id', $clientId)
            ->where('is_active', true);
            
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sale_price ?: $product->price,
                    'category' => $product->category,
                    'image_url' => $product->image_url
                ];
            })
            ->toArray();
    }
}