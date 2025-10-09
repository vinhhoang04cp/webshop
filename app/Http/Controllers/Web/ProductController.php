<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin UI.
     */
    public function index(Request $request)
    {
        try {
            // Gọi API để lấy danh sách products
            $response = Http::get(url('/api/products'));
            
            if ($response->successful()) {
                $responseData = $response->json();
                $products = $responseData['data']['data'] ?? [];
                
                // Nếu có search, filter dữ liệu
                if ($request->has('search') && $request->search) {
                    $searchTerm = strtolower($request->search);
                    $products = array_filter($products, function($product) use ($searchTerm) {
                        return str_contains(strtolower($product['name']), $searchTerm) ||
                               str_contains(strtolower($product['description'] ?? ''), $searchTerm);
                    });
                }
                
                // Pagination thủ công
                $perPage = 12;
                $currentPage = $request->get('page', 1);
                $offset = ($currentPage - 1) * $perPage;
                $paginatedProducts = array_slice($products, $offset, $perPage);
                
                // Lấy danh sách categories cho dropdown
                $categoriesResponse = Http::get(url('/api/categories'));
                $categoriesData = $categoriesResponse->json();
                $categories = $categoriesData['data']['data'] ?? [];
                
                return view('dashboard.products.index', compact(
                    'paginatedProducts', 
                    'products', 
                    'categories'
                ));
            } else {
                return view('dashboard.products.index', [
                    'paginatedProducts' => [],
                    'products' => [],
                    'categories' => [],
                    'error' => 'Không thể tải danh sách sản phẩm'
                ]);
            }
        } catch (\Exception $e) {
            return view('dashboard.products.index', [
                'paginatedProducts' => [],
                'products' => [],
                'categories' => [],
                'error' => 'Lỗi kết nối API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        try {
            // Lấy danh sách categories cho dropdown
            $response = Http::get(url('/api/categories'));
            $responseData = $response->json();
            $categories = $responseData['data']['data'] ?? [];
            
            return view('dashboard.products.create', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không thể tải form tạo sản phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer',
            'image_url' => 'nullable|url',
        ]);

        try {
            // Lấy token của user hiện tại
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để tạo product
            $response = Http::withToken($token)->post(url('/api/products'), [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
            ]);

            if ($response->successful()) {
                return redirect()->route('dashboard.products.index')
                    ->with('success', 'Sản phẩm đã được tạo thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể tạo sản phẩm';
                return redirect()->route('dashboard.products.create')
                    ->with('error', $error)
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.create')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            // Gọi API để lấy chi tiết product
            $response = Http::get(url('/api/products/' . $id));
            
            if ($response->successful()) {
                $responseData = $response->json();
                $product = $responseData['data'] ?? $responseData;
                return view('dashboard.products.show', compact('product'));
            } else {
                return redirect()->route('dashboard.products.index')
                    ->with('error', 'Không tìm thấy sản phẩm');
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        try {
            // Lấy thông tin product
            $productResponse = Http::get(url('/api/products/' . $id));
            
            if (!$productResponse->successful()) {
                return redirect()->route('dashboard.products.index')
                    ->with('error', 'Không tìm thấy sản phẩm');
            }
            
            $productData = $productResponse->json();
            $product = $productData['data'] ?? $productData;
            
            // Lấy danh sách categories
            $categoriesResponse = Http::get(url('/api/categories'));
            $categoriesData = $categoriesResponse->json();
            $categories = $categoriesData['data']['data'] ?? [];
            
            return view('dashboard.products.edit', compact('product', 'categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer',
            'image_url' => 'nullable|url',
        ]);

        try {
            // Lấy token của user hiện tại
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để cập nhật product
            $response = Http::withToken($token)->put(url('/api/products/' . $id), [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
            ]);

            if ($response->successful()) {
                return redirect()->route('dashboard.products.index')
                    ->with('success', 'Sản phẩm đã được cập nhật thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể cập nhật sản phẩm';
                return redirect()->route('dashboard.products.edit', $id)
                    ->with('error', $error)
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.edit', $id)
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        try {
            // Lấy token của user hiện tại
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để xóa product
            $response = Http::withToken($token)->delete(url('/api/products/' . $id));

            if ($response->successful()) {
                return redirect()->route('dashboard.products.index')
                    ->with('success', 'Sản phẩm đã được xóa thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể xóa sản phẩm';
                return redirect()->route('dashboard.products.index')
                    ->with('error', $error);
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }
}