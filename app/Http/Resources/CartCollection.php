<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{
    public function with($request) // with method de them thong tin vao phan meta, $request la bien chua yeu cau HTTP
    {
        return [
            'status' => true,
            'message' => 'Carts retrieved successfully',
            'timestamp' => now(),
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array // toArray method de chuyen doi collection thanh mang
    {
        return [
            'data' => CartResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),  // Dem so luong gio hang trong collection,
                'count' => $this->count(),              // Dem so luong gio hang trong trang hien tai
                'per_page' => $this->perPage(),         // So luong gio hang tren moi trang
                'current_page' => $this->currentPage(), // Trang hien tai
                'total_pages' => $this->lastPage(),     // Tong so trang
            ],
        ];
    }
}
