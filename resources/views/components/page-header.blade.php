{{-- 
    Component Page Header
    Props:
    - $title: Tiêu đề trang (bắt buộc)
    - $icon: Icon class (tùy chọn) - VD: 'fas fa-info-circle'
    - $breadcrumbs: Array breadcrumb items (tùy chọn)
--}}

@php
    $icon = $icon ?? null;
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

<section class="page-header">
    <div class="container">
        <h1>
            @if($icon)
                <i class="{{ $icon }}"></i>
            @endif
            {{ $title }}
        </h1>
        
        @if(count($breadcrumbs) > 0)
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    @foreach($breadcrumbs as $breadcrumb)
                        @if(isset($breadcrumb['url']))
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['text'] }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">{{ $breadcrumb['text'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
</section>

<style>
.page-header {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 60px 0 40px;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0;
}

.breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: white;
}

.breadcrumb-item.active {
    color: white;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255, 255, 255, 0.6);
}
</style>

