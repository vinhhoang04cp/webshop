<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index(Request $request)
    {
        try {
            // Gọi API để lấy danh sách categories
            $response = Http::get(url('/api/categories'));
            
            if ($response->successful()) {
                $apiData = $response->json();
                
                // API trả về cấu trúc phức tạp với nested data
                $categories = $apiData['data']['data'] ?? [];
                
                // Nếu có search, filter dữ liệu
                if ($request->has('search') && $request->search) {
                    $searchTerm = strtolower($request->search);
                    $categories = array_filter($categories, function($category) use ($searchTerm) {
                        return str_contains(strtolower($category['name']), $searchTerm);
                    });
                }
                
                // Pagination thủ công (đơn giản)
                $perPage = 10;
                $currentPage = $request->get('page', 1);
                $offset = ($currentPage - 1) * $perPage;
                $paginatedCategories = array_slice($categories, $offset, $perPage);
                
                return view('dashboard.categories.index', compact('paginatedCategories', 'categories'));
            } else {
                return view('dashboard.categories.index', [
                    'paginatedCategories' => [],
                    'categories' => [],
                    'error' => 'Không thể tải danh sách danh mục'
                ]);
            }
        } catch (\Exception $e) {
            return view('dashboard.categories.index', [
                'paginatedCategories' => [],
                'categories' => [],
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
