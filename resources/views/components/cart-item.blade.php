{{-- 
    Component Cart Item
    Props:
    - $item: Object cart item (bắt buộc)
--}}

<tr>
    <td>
        <div class="d-flex align-items-center">
            <img src="{{ $item->product->image_url ?? 'https://via.placeholder.com/80x80/667eea/ffffff?text=' . urlencode($item->product->name) }}" 
                 alt="{{ $item->product->name }}" 
                 style="border-radius: 8px; margin-right: 15px; width: 80px; height: 80px; object-fit: cover;">
            <div>
                <h6 class="mb-0">
                    <a href="{{ route('product.show', $item->product->product_id) }}" class="text-decoration-none text-dark">
                        {{ $item->product->name }}
                    </a>
                </h6>
                @if($item->product->category)
                    <small class="text-muted">{{ $item->product->category->name }}</small>
                @endif
            </div>
        </div>
    </td>
    <td class="align-middle">{{ number_format($item->price ?? $item->product->price ?? 0, 0, ',', '.') }}₫</td>
    <td class="align-middle">
        {{-- Form cập nhật số lượng --}}
        <div class="d-flex align-items-center gap-2" style="width: 180px;">
            {{-- Giảm số lượng --}}
            <form action="{{ route('cart.update', $item->cart_item_id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                    <i class="fas fa-minus"></i>
                </button>
            </form>
            
            {{-- Hiển thị số lượng --}}
            <span class="fw-bold mx-2" style="min-width: 30px; text-align: center;">{{ $item->quantity }}</span>
            
            {{-- Tăng số lượng --}}
            <form action="{{ route('cart.update', $item->cart_item_id) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </form>
        </div>
    </td>
    <td class="fw-bold align-middle">
        {{ number_format(($item->price ?? $item->product->price ?? 0) * $item->quantity, 0, ',', '.') }}₫
    </td>
    <td class="align-middle">
        {{-- Form xóa sản phẩm --}}
        <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-link text-danger p-0" title="Xóa sản phẩm">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
</tr>

