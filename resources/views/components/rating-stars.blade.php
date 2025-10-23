@php
    $rating = $rating ?? 0;
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
@endphp

<div class="text-warning d-inline-block">
    @for($i = 1; $i <= $fullStars; $i++)
        <i class="fas fa-star"></i>
    @endfor
    
    @if($hasHalfStar)
        <i class="fas fa-star-half-alt"></i>
    @endif
    
    @for($i = ($fullStars + ($hasHalfStar ? 1 : 0)); $i < 5; $i++)
        <i class="far fa-star"></i>
    @endfor
</div>

