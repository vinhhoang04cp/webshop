{{-- 
    Component Feature Card
    Props:
    - $icon: Icon class (bắt buộc) - VD: 'fas fa-shipping-fast'
    - $title: Tiêu đề (bắt buộc)
    - $description: Mô tả (bắt buộc)
--}}

<div class="text-center p-4">
    <div class="mb-3">
        <i class="{{ $icon }} fa-3x text-primary"></i>
    </div>
    <h5>{{ $title }}</h5>
    <p class="text-muted">{{ $description }}</p>
</div>

<style>
.text-primary {
    color: #667eea !important;
}
</style>

