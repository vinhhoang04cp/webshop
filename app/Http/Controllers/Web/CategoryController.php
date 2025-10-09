<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Thu vien HTTP client dung de goi API

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index(Request $request) // (Request $request) la tham so truyen tu client qua URL den controller
    {
        try {
            // Lấy tất cả danh mục từ tất cả các trang của API
            $allCategories = [];
            $currentPage = 1;
            
            do {
                // Gọi API để lấy danh sách categories theo từng trang
                $response = Http::get(url('/api/categories'), ['page' => $currentPage]);
                
                if (!$response->successful()) {
                    break;
                }
                
                $apiData = $response->json();
                $pageCategories = $apiData['data']['data'] ?? [];
                $allCategories = array_merge($allCategories, $pageCategories);
                
                // Kiểm tra xem có trang tiếp theo không
                $hasNextPage = isset($apiData['meta']['current_page']) && 
                              isset($apiData['meta']['last_page']) && 
                              $apiData['meta']['current_page'] < $apiData['meta']['last_page'];
                
                $currentPage++;
            } while ($hasNextPage);
            
            if (!empty($allCategories)) { // Kiểm tra nếu có dữ liệu danh mục
                $categories = $allCategories; // Sử dụng tất cả danh mục đã lấy được
                // Lấy mảng categories từ cấu trúc phức tạp
                // Nếu có search, filter dữ liệu
                if ($request->has('search') && $request->search) {
                    $searchTerm = strtolower($request->search);
                    $categories = array_filter($categories, function($category) use ($searchTerm) {
                        return str_contains(strtolower($category['name']), $searchTerm);
                    });
                }
                
                // Pagination thủ công với thông tin chi tiết
                $perPage = 10;
                $currentPage = max(1, (int) $request->get('page', 1));
                $totalItems = count($categories);
                $totalPages = ceil($totalItems / $perPage);
                
                // Ensure current page doesn't exceed total pages
                $currentPage = min($currentPage, max(1, $totalPages));
                
                $offset = ($currentPage - 1) * $perPage;
                $paginatedCategories = array_slice($categories, $offset, $perPage);
                
                // Additional pagination info
                $paginationInfo = [
                    'currentPage' => $currentPage,
                    'perPage' => $perPage,
                    'totalItems' => $totalItems,
                    'totalPages' => $totalPages,
                    'hasMorePages' => $currentPage < $totalPages,
                    'startItem' => $totalItems > 0 ? $offset + 1 : 0,
                    'endItem' => min($offset + $perPage, $totalItems)
                ];
                
                return view('dashboard.categories.index', compact('paginatedCategories', 'categories', 'paginationInfo'));
            } else {
                return view('dashboard.categories.index', [
                    'paginatedCategories' => [],
                    'categories' => [],
                    'paginationInfo' => [
                        'currentPage' => 1,
                        'perPage' => 10,
                        'totalItems' => 0,
                        'totalPages' => 0,
                        'hasMorePages' => false,
                        'startItem' => 0,
                        'endItem' => 0
                    ],
                    'error' => 'Không thể tải danh sách danh mục'
                ]);
            }
        } catch (\Exception $e) {
            return view('dashboard.categories.index', [
                'paginatedCategories' => [],
                'categories' => [],
                'paginationInfo' => [
                    'currentPage' => 1,
                    'perPage' => 10,
                    'totalItems' => 0,
                    'totalPages' => 0,
                    'hasMorePages' => false,
                    'startItem' => 0,
                    'endItem' => 0
                ],
                'error' => 'Lỗi kết nối API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        try {
            // Lấy token của user hiện tại (nếu dùng Sanctum)
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để tạo category
            $response = Http::withToken($token)->post(url('/api/categories'), [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($response->successful()) {
                return redirect()->route('dashboard.categories.index')
                    ->with('success', 'Danh mục đã được tạo thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể tạo danh mục';
                return redirect()->route('dashboard.categories.index')
                    ->with('error', $error);
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        try {
            // Lấy token của user hiện tại
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để cập nhật category
            $response = Http::withToken($token)->put(url('/api/categories/' . $id), [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if ($response->successful()) {
                return redirect()->route('dashboard.categories.index')
                    ->with('success', 'Danh mục đã được cập nhật thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể cập nhật danh mục';
                return redirect()->route('dashboard.categories.index')
                    ->with('error', $error);
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        try {
            // Lấy token của user hiện tại
            $user = auth()->user();
            $token = $user->createToken('web-access')->plainTextToken;
            
            // Gọi API để xóa category
            $response = Http::withToken($token)->delete(url('/api/categories/' . $id));

            if ($response->successful()) {
                return redirect()->route('dashboard.categories.index')
                    ->with('success', 'Danh mục đã được xóa thành công!');
            } else {
                $error = $response->json()['message'] ?? 'Không thể xóa danh mục';
                return redirect()->route('dashboard.categories.index')
                    ->with('error', $error);
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi kết nối API: ' . $e->getMessage());
        }
    }
}
