<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Lấy danh sách sản phẩm với filter và sort
     */
    public function getProductsList(array $filters = [], int $perPage = 12)
    {
        $query = Product::with('category');

        // Tìm kiếm theo từ khóa
        if (! empty($filters['q'])) {
            $query->where('name', 'like', "%{$filters['q']}%")
                ->orWhere('description', 'like', "%{$filters['q']}%");
        }

        // Lọc theo danh mục
        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // Lọc theo giá tối thiểu
        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        // Lọc theo giá tối đa
        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Sắp xếp
        $sortBy = $filters['sort'] ?? 'latest';
        $this->applySorting($query, $sortBy);

        return $query->paginate($perPage);
    }

    /**
     * Áp dụng sắp xếp cho query
     */
    protected function applySorting($query, string $sortBy)
    {
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest('created_at');
        }
    }

    /**
     * Lấy chi tiết sản phẩm
     */
    public function getProductDetail($productId)
    {
        return Product::with(['category', 'details', 'inventory', 'ratings.user'])
            ->findOrFail($productId);
    }

    /**
     * Lấy sản phẩm liên quan
     */
    public function getRelatedProducts($productId, $categoryId, int $limit = 4)
    {
        return Product::with('category')
            ->where('category_id', $categoryId)
            ->where('product_id', '!=', $productId)
            ->take($limit)
            ->get();
    }

    /**
     * Lấy sản phẩm theo danh mục
     */
    public function getProductsByCategory($categoryId, int $perPage = 12)
    {
        $category = Category::findOrFail($categoryId);

        $products = Product::with('category')
            ->where('category_id', $categoryId)
            ->latest('created_at')
            ->paginate($perPage);

        return [
            'category' => $category,
            'products' => $products,
        ];
    }

    /**
     * Lấy sản phẩm khuyến mãi
     */
    public function getPromotionProducts(int $perPage = 12)
    {
        return Product::with('category')
            ->whereNotNull('original_price')
            ->whereColumn('original_price', '>', 'price')
            ->orderByRaw('((original_price - price) / original_price) DESC')
            ->paginate($perPage);
    }

    /**
     * Tạo đánh giá sản phẩm
     */
    public function createRating(array $data, $productId)
    {
        $userId = Auth::id();

        // Kiểm tra đã đánh giá chưa
        $existingRating = Rating::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existingRating) {
            throw new \Exception('Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Tạo đánh giá mới
        $rating = new Rating;
        $rating->user_id = $userId;
        $rating->product_id = $productId;
        $rating->rating = $data['rating'];
        $rating->review = $data['review'] ?? '';
        $rating->save();

        return $rating;
    }

    /**
     * Lấy số lượng sản phẩm trong giỏ hàng
     */
    public function getCartCount()
    {
        if (! Auth::check()) {
            return 0;
        }

        $cart = Auth::user()->cart;
        if (! $cart) {
            return 0;
        }

        return $cart->items()->sum('quantity');
    }

    /**
     * Lấy danh sách categories
     */
    public function getAllCategories()
    {
        return Category::withCount('products')->get();
    }

    // ==================== ADMIN METHODS ====================

    /**
     * Lấy danh sách sản phẩm cho admin với phân trang
     */
    public function getProductsForAdmin(array $filters = [], int $perPage = 12)
    {
        $query = Product::with('category');

        // Tìm kiếm theo tên hoặc mô tả
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('description', 'like', '%'.$searchTerm.'%');
            });
        }

        return [
            'paginated' => $query->paginate($perPage),
            'all' => Product::all(),
        ];
    }

    /**
     * Lấy sản phẩm với details cho admin
     */
    public function getProductWithDetails($productId)
    {
        return Product::with(['category', 'details'])->findOrFail($productId);
    }

    /**
     * Tạo sản phẩm mới
     */
    public function createProduct(array $data)
    {
        // Xử lý upload ảnh
        $imageUrl = $this->handleImageUpload($data);

        // Tạo product
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'image_url' => $imageUrl,
            'stock_quantity' => $data['stock_quantity'],
        ]);

        // Tạo inventory
        $this->createInventory($product->product_id, $data['stock_quantity']);

        // Tạo product details nếu có
        $this->createOrUpdateProductDetails($product->product_id, $data);

        return $product;
    }

    /**
     * Cập nhật sản phẩm
     */
    public function updateProduct($productId, array $data)
    {
        $product = Product::findOrFail($productId);

        // Xử lý ảnh mới
        $imageUrl = $this->handleImageUpdate($product, $data);

        // Lưu số lượng cũ để tính toán thay đổi
        $oldQuantity = $product->stock_quantity;
        $newQuantity = $data['stock_quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;

        // Cập nhật product
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'image_url' => $imageUrl,
            'stock_quantity' => $newQuantity,
        ]);

        // Cập nhật inventory
        $this->updateInventory($product->product_id, $quantityDifference);

        // Cập nhật hoặc xóa product details
        $this->createOrUpdateProductDetails($product->product_id, $data);

        return $product;
    }

    /**
     * Xóa sản phẩm
     */
    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);

        // Xóa file ảnh nếu có
        $this->deleteProductImage($product);

        // Xóa inventory liên quan
        Inventory::where('product_id', $product->product_id)->delete();

        // Xóa product details liên quan
        ProductDetail::where('product_id', $product->product_id)->delete();

        // Xóa product
        $product->delete();

        return true;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Xử lý upload ảnh khi tạo product
     */
    protected function handleImageUpload(array $data)
    {
        if (! empty($data['image'])) {
            $imageName = time().'_'.$data['image']->getClientOriginalName();
            $imagePath = $data['image']->storeAs('products', $imageName, 'public');

            return '/storage/'.$imagePath;
        }

        return $data['image_url'] ?? null;
    }

    /**
     * Xử lý cập nhật ảnh
     */
    protected function handleImageUpdate($product, array $data)
    {
        $imageUrl = $product->image_url;

        if (! empty($data['image'])) {
            // Xóa ảnh cũ nếu có
            if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
                $oldImagePath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Upload ảnh mới
            $imageName = time().'_'.$data['image']->getClientOriginalName();
            $imagePath = $data['image']->storeAs('products', $imageName, 'public');
            $imageUrl = '/storage/'.$imagePath;
        } elseif (! empty($data['image_url'])) {
            // Xóa ảnh cũ nếu có
            if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
                $oldImagePath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }
            $imageUrl = $data['image_url'];
        }

        return $imageUrl;
    }

    /**
     * Xóa ảnh sản phẩm
     */
    protected function deleteProductImage($product)
    {
        if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
            $imagePath = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Tạo inventory cho sản phẩm mới
     */
    protected function createInventory($productId, $quantity)
    {
        Inventory::create([
            'product_id' => $productId,
            'stock_in' => $quantity,
            'stock_out' => 0,
            'current_stock' => $quantity,
        ]);
    }

    /**
     * Cập nhật inventory
     */
    protected function updateInventory($productId, $quantityDifference)
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $productId],
            [
                'stock_in' => 0,
                'stock_out' => 0,
                'current_stock' => 0,
            ]
        );

        if ($quantityDifference > 0) {
            // Tăng số lượng - nhập kho thêm
            $inventory->stock_in += $quantityDifference;
            $inventory->current_stock += $quantityDifference;
        } elseif ($quantityDifference < 0) {
            // Giảm số lượng - xuất kho
            $inventory->stock_out += abs($quantityDifference);
            $inventory->current_stock += $quantityDifference;
        }

        $inventory->save();
    }

    /**
     * Tạo hoặc cập nhật product details
     */
    protected function createOrUpdateProductDetails($productId, array $data)
    {
        $hasDetails = ! empty($data['color']) || ! empty($data['storage']) ||
                     ! empty($data['ram']) || ! empty($data['screen_size']) ||
                     ! empty($data['chip']) || ! empty($data['battery']) ||
                     ! empty($data['camera_main']) || ! empty($data['camera_front']) ||
                     ! empty($data['os']) || ! empty($data['special_features']);

        if ($hasDetails) {
            $productDetail = ProductDetail::firstOrCreate(
                ['product_id' => $productId],
                []
            );

            $productDetail->update([
                'color' => $data['color'] ?? null,
                'storage' => $data['storage'] ?? null,
                'ram' => $data['ram'] ?? null,
                'screen_size' => $data['screen_size'] ?? null,
                'chip' => $data['chip'] ?? null,
                'battery' => $data['battery'] ?? null,
                'camera_main' => $data['camera_main'] ?? null,
                'camera_front' => $data['camera_front'] ?? null,
                'os' => $data['os'] ?? null,
                'special_features' => $data['special_features'] ?? null,
            ]);
        } else {
            // Xóa product details nếu không có thông tin
            ProductDetail::where('product_id', $productId)->delete();
        }
    }

    // ==================== API METHODS ====================

    /**
     * Lấy danh sách sản phẩm cho API với filters (dùng chung cho Web & API)
     */
    public function getProducts(array $filters = [], int $perPage = 15)
    {
        $query = Product::with(['category', 'details', 'inventory']);

        // Lọc theo category
        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // Lọc theo tên (tìm gần đúng)
        if (! empty($filters['name'])) {
            $query->where('name', 'LIKE', '%'.$filters['name'].'%');
        }

        // Search (name or description)
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Lọc theo giá tối thiểu
        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        // Lọc theo giá tối đa
        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Lọc theo số lượng tồn kho
        if (! empty($filters['stock_quantity'])) {
            $query->where('stock_quantity', $filters['stock_quantity']);
        }

        // Filter by stock status
        if (! empty($filters['stock_status'])) {
            $status = $filters['stock_status'];
            if ($status === 'out_of_stock') {
                $query->where('stock_quantity', 0);
            } elseif ($status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<', 10);
            } elseif ($status === 'in_stock') {
                $query->where('stock_quantity', '>=', 10);
            }
        }

        // Filter by discount (promotion products)
        if (! empty($filters['has_discount'])) {
            $query->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'price');
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Tìm sản phẩm theo ID with optional relationships
     */
    public function findProduct($productId, $withRelations = true)
    {
        $query = Product::query();

        if ($withRelations) {
            $query->with(['category', 'details', 'inventory', 'ratings.user']);
        }

        return $query->find($productId);
    }

    /**
     * Get product statistics (for API)
     */
    public function getProductStats()
    {
        return [
            'total_products' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>=', 10)->count(),
            'low_stock' => Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<', 10)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'total_value' => Product::sum(\DB::raw('price * stock_quantity')),
            'with_discount' => Product::whereNotNull('original_price')->whereColumn('original_price', '>', 'price')->count(),
        ];
    }

    /**
     * Tạo sản phẩm mới cho API
     */
    public function createProductFull(array $data)
    {
        // Tạo product
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'stock_quantity' => $data['stock_quantity'],
            'image_url' => $data['image_url'] ?? null,
        ]);

        // Tự động tạo inventory
        Inventory::create([
            'product_id' => $product->product_id,
            'stock_in' => $data['stock_quantity'] ?? 0,
            'stock_out' => 0,
            'current_stock' => $data['stock_quantity'] ?? 0,
        ]);

        // Tạo ProductDetail nếu có thông tin chi tiết
        $this->createOrUpdateProductDetails($product->product_id, $data);

        return $product->fresh(['category', 'details']);
    }

    /**
     * Cập nhật sản phẩm cho API
     */
    public function updateProductFull($productId, array $data)
    {
        $product = Product::find($productId);

        if (! $product) {
            return null;
        }

        // Lưu số lượng cũ để tính toán thay đổi inventory
        $oldQuantity = $product->stock_quantity;
        $newQuantity = $data['stock_quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;

        // Cập nhật product
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'stock_quantity' => $newQuantity,
            'image_url' => $data['image_url'] ?? $product->image_url,
        ]);

        // Cập nhật inventory
        $this->updateInventory($product->product_id, $quantityDifference);

        // Cập nhật hoặc tạo ProductDetail
        $this->createOrUpdateProductDetails($product->product_id, $data);

        return $product->fresh(['category', 'details']);
    }

    /**
     * Xóa sản phẩm theo ID
     */
    public function deleteProductById($productId)
    {
        $product = Product::find($productId);

        if (! $product) {
            return false;
        }

        // Xóa ProductDetail nếu có
        ProductDetail::where('product_id', $product->product_id)->delete();

        // Xóa Inventory nếu có
        Inventory::where('product_id', $product->product_id)->delete();

        // Xóa sản phẩm
        $product->delete();

        return true;
    }

    /**
     * Lấy danh sách ProductDetail với filters
     */
    public function getProductDetails(array $filters = [])
    {
        $query = ProductDetail::query();

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['color'])) {
            $query->where('color', $filters['color']);
        }

        if (! empty($filters['storage'])) {
            $query->where('storage', $filters['storage']);
        }

        if (! empty($filters['ram'])) {
            $query->where('ram', $filters['ram']);
        }

        if (! empty($filters['chip'])) {
            $query->where('chip', 'LIKE', '%'.$filters['chip'].'%');
        }

        if (! empty($filters['os'])) {
            $query->where('os', 'LIKE', '%'.$filters['os'].'%');
        }

        return $query->paginate(10);
    }

    /**
     * Tìm ProductDetail theo ID
     */
    public function findProductDetail($detailId)
    {
        return ProductDetail::find($detailId);
    }

    /**
     * Tạo ProductDetail mới
     */
    public function createProductDetail(array $data)
    {
        return ProductDetail::create($data);
    }

    /**
     * Cập nhật ProductDetail
     */
    public function updateProductDetail($detailId, array $data)
    {
        $productDetail = ProductDetail::find($detailId);

        if (! $productDetail) {
            return null;
        }

        $productDetail->update($data);

        return $productDetail;
    }

    /**
     * Xóa ProductDetail
     */
    public function deleteProductDetail($detailId)
    {
        $productDetail = ProductDetail::find($detailId);

        if (! $productDetail) {
            return false;
        }

        $productDetail->delete();

        return true;
    }
}
