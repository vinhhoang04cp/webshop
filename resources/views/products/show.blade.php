@extends('layouts.customer') {{-- Ke thua layout chính --}}

@section('title', $product->name . ' - WebShop') {{-- Tiêu đề trang --}}

@push('styles')
<style>
    .product-detail-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .spec-table tr {
        border-bottom: 1px solid #dee2e6;
    }
    
    .spec-table tr:last-child {
        border-bottom: none;
    }
    
    .spec-table td {
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
    }
    
    .spec-badge {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }
    
    .detail-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .detail-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    
    .feature-highlight {
        background: linear-gradient(45deg, #ffc107, #ff8c00);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin: 10px 0;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 5px;
        margin-bottom: 10px;
    }
    
    .rating-input input[type="radio"] {
        display: none;
    }
    
    .rating-input label {
        font-size: 1.5rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .rating-input label:hover,
    .rating-input label:hover ~ label,
    .rating-input input[type="radio"]:checked ~ label {
        color: #ffc107;
    }
    
    .review-item {
        transition: transform 0.2s ease;
    }
    
    .review-item:hover {
        transform: translateX(5px);
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px !important;
    }
</style>
@endpush

@section('content') {{-- Nội dung chính --}}
<div class="container">
    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    @if($product->category) {{-- Kiểm tra nếu sản phẩm có danh mục --}}
                        <li class="breadcrumb-item"> 
                            <a href="{{ route('category.show', $product->category->category_id) }}"> 
                                {{ $product->category->name }} {{-- Hiển thị tên danh mục --}}
                            </a>
                        </li>
                    @endif 
                    <li class="breadcrumb-item active">{{ $product->name }}</li> {{-- Tên sản phẩm --}}
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-5"> 
        <!-- Product Image -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                {{-- $product->image_url hien thi anh san pham qua link url neu khong co thi hien thi hinh mac dinh --}}
                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/500x500/667eea/ffffff?text=' . urlencode($product->name) }}" 
                     alt="{{ $product->name }}" 
                     class="img-fluid"
                     style="width: 100%; height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    @if($product->category) {{-- Kiểm tra nếu sản phẩm có danh mục --}}
                        <span class="category-badge mb-3">{{ $product->category->name }}</span> {{-- Hiển thị tên danh mục --}}
                    @endif
                    
                    <h1 class="mb-3" style="font-size: 2rem; font-weight: 700;">{{ $product->name }}</h1> {{-- Tên sản phẩm --}}
                    
                    <div class="d-flex align-items-center mb-3"> {{-- Đánh giá sao thực tế --}}
                        <div class="text-warning me-3">
                            @php
                                $averageRating = $product->averageRating();
                                $totalRatings = $product->totalRatings();
                                $fullStars = floor($averageRating);
                                $hasHalfStar = ($averageRating - $fullStars) >= 0.5;
                            @endphp
                            
                            {{-- Hiển thị sao đầy --}}
                            @for($i = 1; $i <= $fullStars; $i++)
                                <i class="fas fa-star"></i>
                            @endfor
                            
                            {{-- Hiển thị sao nửa --}}
                            @if($hasHalfStar)
                                <i class="fas fa-star-half-alt"></i>
                            @endif
                            
                            {{-- Hiển thị sao rỗng --}}
                            @for($i = ($fullStars + ($hasHalfStar ? 1 : 0)); $i < 5; $i++)
                                <i class="far fa-star"></i>
                            @endfor
                            
                            <span class="text-muted ms-2">
                                ({{ number_format($averageRating, 1) }}/5 - {{ $totalRatings }} đánh giá)
                            </span>
                        </div>
                        <span class="text-muted">|</span>
                        <span class="ms-3 text-muted">
                            <i class="fas fa-box"></i> 
                            Kho: 
                            {{-- $product->inventory hien thi thong tin kho --}}
                            @if($product->inventory)
                                <strong>{{ $product->inventory->quantity }}</strong> 
                                <div class="text-muted">sản phẩm có sẵn</div>
                            @else
                                <strong class="text-danger">Hết hàng</strong> {{-- Neu khong co thong tin kho thi hien thi het hang --}}
                            @endif
                        </span>
                    </div>

                    <hr>

                    <div class="mb-4">
                        @php
                            // Nếu sản phẩm có original_price, nghĩa là đã được áp dụng coupon từ backend
                            $hasAppliedCoupon = $product->original_price !== null;
                            
                            if ($hasAppliedCoupon) {
                                // Sử dụng giá đã được cập nhật trong database
                                $originalPrice = $product->original_price;
                                $currentPrice = $product->price;
                                $savedAmount = $originalPrice - $currentPrice;
                                
                                // Lấy thông tin coupon đang active cho sản phẩm
                                $bestCoupon = \App\Models\Coupon::where('product_id', $product->product_id)
                                    ->where('is_active', true)
                                    ->whereDate('start_date', '<=', now())
                                    ->whereDate('end_date', '>=', now())
                                    ->first();
                            } else {
                                // Logic cũ: Tính toán động cho coupon chung (product_id = null)
                                $bestCoupon = $product->getBestCoupon();
                                $currentPrice = $product->price;
                                
                                if ($bestCoupon) {
                                    $discountedPrice = $product->getDiscountedPrice();
                                    $originalPrice = $product->price;
                                    $currentPrice = $discountedPrice;
                                    $savedAmount = $originalPrice - $currentPrice;
                                } else {
                                    $originalPrice = null;
                                    $savedAmount = 0;
                                }
                            }
                            
                            $hasCoupon = $bestCoupon !== null;
                        @endphp
                        
                        @if($hasCoupon)
                            {{-- Có coupon giảm giá --}}
                            <div class="mb-3 p-3 bg-warning bg-opacity-10 border border-warning rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-ticket-alt text-warning fa-lg me-2"></i>
                                    <span class="badge bg-warning text-dark px-3 py-2" style="font-size: 1.1em;">
                                        <strong>{{ $bestCoupon->code }}</strong> - Giảm {{ $bestCoupon->discount_display }}
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    Có hiệu lực đến {{ $bestCoupon->end_date->format('d/m/Y') }}
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-baseline gap-3">
                                <h2 class="text-danger mb-0" style="font-size: 2.5rem; font-weight: 700;">
                                    {{ number_format($currentPrice, 0, ',', '.') }}₫
                                </h2>
                                @if($originalPrice && $savedAmount > 0)
                                <span class="text-muted text-decoration-line-through" style="font-size: 1.5rem;">
                                    {{ number_format($originalPrice, 0, ',', '.') }}₫
                                </span>
                                <span class="badge bg-danger" style="font-size: 0.9rem;">
                                    Tiết kiệm {{ number_format($savedAmount, 0, ',', '.') }}₫
                                </span>
                                @endif
                            </div>
                        @else
                            {{-- Không có coupon --}}
                            <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: 700;">
                                {{ number_format($product->price, 0, ',', '.') }}₫ {{-- Giá sản phẩm đã được định dạng --}}
                            </h2>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h5>Mô tả sản phẩm:</h5>
                        <p class="text-muted">{{ $product->description ?? 'Chưa có mô tả cho sản phẩm này.' }}</p>
                        {{-- $product->description hien thi mo ta san pham --}}
                    </div>

                    @if($product->details) {{-- Kiểm tra nếu sản phẩm có thông tin chi tiết --}}
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Thông tin chi tiết:
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Thông tin cơ bản --}}
                                    @if($product->details->color)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-palette me-2 text-info"></i>Màu sắc:</strong> 
                                            <span class="badge bg-secondary ms-2">{{ $product->details->color }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->storage)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-hdd me-2 text-warning"></i>Bộ nhớ trong:</strong> 
                                            <span class="badge bg-info ms-2">{{ $product->details->storage }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->ram)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-memory me-2 text-success"></i>RAM:</strong> 
                                            <span class="badge bg-success ms-2">{{ $product->details->ram }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->screen_size)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-tv me-2 text-primary"></i>Màn hình:</strong> 
                                            <span class="text-primary fw-bold">{{ $product->details->screen_size }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->chip)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-microchip me-2 text-danger"></i>Chip xử lý:</strong> 
                                            <span class="text-danger fw-bold">{{ $product->details->chip }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    {{-- Thông tin kỹ thuật --}}
                                    @if($product->details->battery)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-battery-full me-2 text-success"></i>Pin:</strong> 
                                            <span class="text-success fw-bold">{{ $product->details->battery }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->camera_main)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-camera me-2 text-info"></i>Camera chính:</strong> 
                                            <span class="text-info fw-bold">{{ $product->details->camera_main }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->camera_front)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-camera-retro me-2 text-warning"></i>Camera trước:</strong> 
                                            <span class="text-warning fw-bold">{{ $product->details->camera_front }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->os)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-desktop me-2 text-primary"></i>Hệ điều hành:</strong> 
                                            <span class="badge bg-primary ms-2">{{ $product->details->os }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->details->special_features)
                                        <div class="mb-3 p-3 bg-light rounded">
                                            <strong><i class="fas fa-star me-2 text-warning"></i>Tính năng đặc biệt:</strong> 
                                            <div class="mt-2">
                                                <span class="text-muted">{{ $product->details->special_features }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('cart.add', $product->product_id) }}" method="POST"> {{-- Form thêm vào giỏ hàng --}}
                        @csrf {{-- Token bảo mật --}}
                        <div class="mb-4">
                            <label class="mb-2"><strong>Số lượng:</strong></label> {{-- Nhãn số lượng --}}
                            <div class="input-group" style="width: 150px;"> {{-- Nhập số lượng --}}
                                <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="{{ $product->inventory ? $product->inventory->quantity : 1 }}">
                                {{-- so luong toi da la so luong co trong inventory --}}
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 25px; padding: 12px;"> {{-- Nút thêm vào giỏ hàng click se gui request di --}}
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-info mt-3" style="border-radius: 10px;">
                        <i class="fas fa-truck"></i> Miễn phí vận chuyển cho đơn hàng trên 500k
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ratings and Reviews Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h3 class="mb-4">
                        <i class="fas fa-star text-warning me-2"></i>Đánh giá từ khách hàng
                    </h3>

                    <!-- Rating Form (only for logged in users) -->
                    @auth
                        @php
                            $userRating = $product->ratings->where('user_id', auth()->id())->first();
                        @endphp
                        
                        @if($userRating)
                            <div class="mb-4 p-4 bg-success bg-opacity-10 border border-success rounded">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-check-circle"></i> Bạn đã đánh giá sản phẩm này
                                </h5>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="me-2">Đánh giá của bạn:</span>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $userRating->rating)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ms-2 text-muted">({{ $userRating->rating }}/5)</span>
                                </div>
                                @if($userRating->review)
                                    <div class="mt-2">
                                        <strong>Nhận xét:</strong> {{ $userRating->review }}
                                    </div>
                                @endif
                                <small class="text-muted">Đánh giá vào {{ $userRating->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        @else
                            <div class="mb-4 p-4 bg-light rounded">
                                <h5 class="mb-3">Đánh giá sản phẩm này</h5>
                                <form action="{{ route('product.rating.add', $product->product_id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Xếp hạng của bạn:</label>
                                        <div class="rating-input">
                                            <input type="radio" name="rating" value="5" id="star5" {{ old('rating') == '5' ? 'checked' : '' }}>
                                            <label for="star5"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="4" id="star4" {{ old('rating') == '4' ? 'checked' : '' }}>
                                            <label for="star4"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="3" id="star3" {{ old('rating') == '3' ? 'checked' : '' }}>
                                            <label for="star3"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="2" id="star2" {{ old('rating') == '2' ? 'checked' : '' }}>
                                            <label for="star2"><i class="fas fa-star"></i></label>
                                            <input type="radio" name="rating" value="1" id="star1" {{ old('rating') == '1' ? 'checked' : '' }}>
                                            <label for="star1"><i class="fas fa-star"></i></label>
                                        </div>
                                        @error('rating')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="review" class="form-label">Nhận xét (không bắt buộc):</label>
                                        <textarea name="review" id="review" class="form-control" rows="4" 
                                                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này...">{{ old('review') }}</textarea>
                                        @error('review')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <a href="{{ route('login') }}" class="alert-link">Đăng nhập</a> để đánh giá sản phẩm này.
                        </div>
                    @endauth

                    <!-- Existing Reviews -->
                    @if($product->ratings->count() > 0)
                        <div class="reviews-list">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Các đánh giá ({{ $product->ratings->count() }})</h5>
                                @if($product->ratings->count() > 5)
                                    <button class="btn btn-outline-primary btn-sm" id="toggleAllReviews">
                                        Xem tất cả đánh giá
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Rating Statistics -->
                            @php
                                $ratingStats = [];
                                for($i = 5; $i >= 1; $i--) {
                                    $count = $product->ratings->where('rating', $i)->count();
                                    $percentage = $product->ratings->count() > 0 ? ($count / $product->ratings->count()) * 100 : 0;
                                    $ratingStats[$i] = ['count' => $count, 'percentage' => $percentage];
                                }
                            @endphp
                            
                            <div class="rating-statistics mb-4 p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <div class="display-4 text-warning mb-2">{{ number_format($product->averageRating(), 1) }}</div>
                                            <div class="text-warning mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($product->averageRating()))
                                                        <i class="fas fa-star"></i>
                                                    @elseif($i - 0.5 <= $product->averageRating())
                                                        <i class="fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="far fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $product->ratings->count() }} đánh giá</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @foreach($ratingStats as $star => $stat)
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="me-2">{{ $star }} <i class="fas fa-star text-warning"></i></span>
                                                <div class="progress flex-grow-1 me-2" style="height: 15px;">
                                                    <div class="progress-bar bg-warning" style="width: {{ $stat['percentage'] }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $stat['count'] }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reviews-container">
                                @foreach($product->ratings->sortByDesc('created_at')->take(5) as $rating)
                                    <div class="review-item border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px; font-weight: bold;">
                                                        {{ strtoupper(substr($rating->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $rating->user->name }}</strong>
                                                        <div class="text-warning">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $rating->rating)
                                                                    <i class="fas fa-star"></i>
                                                                @else
                                                                    <i class="far fa-star"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($rating->review)
                                                    <p class="text-muted mb-0 ms-5">{{ $rating->review }}</p>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $rating->created_at->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($product->ratings->count() > 5)
                                    <div class="hidden-reviews" style="display: none;">
                                        @foreach($product->ratings->sortByDesc('created_at')->skip(5) as $rating)
                                            <div class="review-item border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                 style="width: 40px; height: 40px; font-weight: bold;">
                                                                {{ strtoupper(substr($rating->user->name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <strong>{{ $rating->user->name }}</strong>
                                                                <div class="text-warning">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        @if($i <= $rating->rating)
                                                                            <i class="fas fa-star"></i>
                                                                        @else
                                                                            <i class="far fa-star"></i>
                                                                        @endif
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($rating->review)
                                                            <p class="text-muted mb-0 ms-5">{{ $rating->review }}</p>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $rating->created_at->format('d/m/Y') }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
                            <p class="text-muted">Hãy là người đầu tiên đánh giá!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0) {{-- Kiểm tra nếu có sản phẩm liên quan --}}
    <section class="mb-5">
        <h2 class="section-title">Sản phẩm liên quan</h2> {{-- Tiêu đề phần sản phẩm liên quan --}}
        <div class="row g-4">
            @foreach($relatedProducts as $related) {{-- Vòng lặp hiển thị từng sản phẩm liên quan --}}
            <div class="col-md-3"> 
                <div class="product-card">
                    <a href="{{ route('product.show', $related->product_id) }}">
                        <img src="{{ $related->image_url ?? 'https://via.placeholder.com/300x250/764ba2/ffffff?text=' . urlencode($related->name) }}" 
                             alt="{{ $related->name }}" 
                             class="product-image">
                    </a>
                    <div class="product-body">
                        @if($related->category) {{-- Kiểm tra nếu sản phẩm có danh mục --}}
                            <span class="category-badge">{{ $related->category->name }}</span> {{-- Hiển thị tên danh mục --}}
                        @endif
                        <a href="{{ route('product.show', $related->product_id) }}" class="text-decoration-none">
                            <h5 class="product-title">{{ $related->name }}</h5> {{-- Tên sản phẩm --}}
                        </a>
                        <div class="d-flex justify-content-between align-items-center"> {{-- Giá sản phẩm --}}
                            <span class="product-price">{{ number_format($related->price, 0, ',', '.') }}₫</span> {{-- Giá sản phẩm đã được định dạng --}}
                        </div>
                        <form action="{{ route('cart.add', $related->product_id) }}" method="POST" style="display: inline;">
                            @csrf {{-- Token bảo mật --}} 
                            <input type="hidden" name="quantity" value="1"> {{-- Số lượng mặc định là 1 --}}
                            <button type="submit" class="btn-add-cart"> 
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating input hover effect
    const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    ratingLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            // Highlight stars up to current position
            for(let i = ratingLabels.length - 1; i >= ratingLabels.length - 1 - index; i--) {
                ratingLabels[i].style.color = '#ffc107';
            }
        });
        
        label.addEventListener('mouseleave', function() {
            // Reset colors based on checked state
            const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
            ratingLabels.forEach((lbl, idx) => {
                if (checkedInput) {
                    const checkedValue = parseInt(checkedInput.value);
                    if (idx >= ratingLabels.length - checkedValue) {
                        lbl.style.color = '#ffc107';
                    } else {
                        lbl.style.color = '#ddd';
                    }
                } else {
                    lbl.style.color = '#ddd';
                }
            });
        });
    });
    
    // Show selected rating text
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const ratingTexts = {
                '1': 'Rất không hài lòng',
                '2': 'Không hài lòng', 
                '3': 'Bình thường',
                '4': 'Hài lòng',
                '5': 'Rất hài lòng'
            };
            
            // Remove existing rating text
            const existingText = document.querySelector('.rating-selected-text');
            if (existingText) {
                existingText.remove();
            }
            
            // Add new rating text
            const ratingValue = this.value;
            const textElement = document.createElement('small');
            textElement.className = 'rating-selected-text text-muted ms-2';
            textElement.textContent = ratingTexts[ratingValue];
            
            this.closest('.rating-input').parentNode.appendChild(textElement);
        });
    });
    
    // Toggle show/hide all reviews
    const toggleButton = document.getElementById('toggleAllReviews');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const hiddenReviews = document.querySelector('.hidden-reviews');
            if (hiddenReviews.style.display === 'none') {
                hiddenReviews.style.display = 'block';
                this.textContent = 'Ẩn bớt đánh giá';
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-outline-secondary');
            } else {
                hiddenReviews.style.display = 'none';
                this.textContent = 'Xem tất cả đánh giá';
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-outline-primary');
            }
        });
    }
});
</script>
@endpush
