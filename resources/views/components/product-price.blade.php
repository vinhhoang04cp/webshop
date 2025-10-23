@if($product->original_price)
    <div class="text-muted small text-decoration-line-through mb-1">
        {{ number_format($product->original_price, 0, ',', '.') }}₫
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="{{ $priceClass ?? 'product-price text-danger' }}">
            {{ number_format($product->price, 0, ',', '.') }}₫
        </span>
        <span class="badge bg-danger" style="font-size: 0.7rem;">
            -{{ number_format((($product->original_price - $product->price) / $product->original_price) * 100, 0) }}%
        </span>
    </div>
@else
    <span class="{{ $priceClass ?? 'product-price' }}">
        {{ number_format($product->price, 0, ',', '.') }}₫
    </span>
@endif

