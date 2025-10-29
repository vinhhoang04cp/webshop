{{-- 
    Component hiển thị sao đánh giá
    Props:
    - $rating: Điểm đánh giá từ 0-5 (tùy chọn, mặc định 0)
--}}

@php
    $rating = $rating ?? 0;
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
@endphp

<div class="text-warning d-inline-block">
    {{-- Sao đầy --}}
    @for($i = 1; $i <= $fullStars; $i++)
        <i class="fas fa-star"></i>
    @endfor
    
    {{-- Sao nửa --}}
    @if($hasHalfStar)
        <i class="fas fa-star-half-alt"></i>
    @endif
    
    {{-- Sao rỗng --}}
    @for($i = ($fullStars + ($hasHalfStar ? 1 : 0)); $i < 5; $i++)
        <i class="far fa-star"></i>
    @endfor
</div>
