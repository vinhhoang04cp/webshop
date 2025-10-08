<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array // Tra ve mot mang chua toan bo danh muc
    {
        // If the resource is a paginator, include pagination metadata
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator
            || $this->resource instanceof \Illuminate\Pagination\Paginator) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $p */
            $p = $this->resource;

            return [
                'data' => $this->collection,
                'meta' => [
                    'total' => $p->total(),
                    'per_page' => $p->perPage(),
                    'current_page' => $p->currentPage(),
                    'last_page' => $p->lastPage(),
                ],
            ];
        }

        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),  // Dem so luong danh muc trong collection
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Categories retrieved successfully',
        ];
    }
}
