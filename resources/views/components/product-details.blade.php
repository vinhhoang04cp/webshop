{{-- 
    Component Product Details
    Props:
    - $details: Object chi tiết sản phẩm (bắt buộc)
--}}

@if($details)
    <div class="mb-4">
        <h5 class="text-primary mb-3">
            <i class="fas fa-info-circle me-2"></i>Thông tin chi tiết:
        </h5>
        <div class="row">
            <div class="col-md-6">
                @if($details->color)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-palette me-2 text-info"></i>Màu sắc:</strong> 
                        <span class="badge bg-secondary ms-2">{{ $details->color }}</span>
                    </div>
                @endif
                
                @if($details->storage)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-hdd me-2 text-warning"></i>Bộ nhớ trong:</strong> 
                        <span class="badge bg-info ms-2">{{ $details->storage }}</span>
                    </div>
                @endif
                
                @if($details->ram)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-memory me-2 text-success"></i>RAM:</strong> 
                        <span class="badge bg-success ms-2">{{ $details->ram }}</span>
                    </div>
                @endif
                
                @if($details->screen_size)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-tv me-2 text-primary"></i>Màn hình:</strong> 
                        <span class="text-primary fw-bold">{{ $details->screen_size }}</span>
                    </div>
                @endif
                
                @if($details->chip)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-microchip me-2 text-danger"></i>Chip xử lý:</strong> 
                        <span class="text-danger fw-bold">{{ $details->chip }}</span>
                    </div>
                @endif
            </div>
            
            <div class="col-md-6">
                @if($details->battery)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-battery-full me-2 text-success"></i>Pin:</strong> 
                        <span class="text-success fw-bold">{{ $details->battery }}</span>
                    </div>
                @endif
                
                @if($details->camera_main)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-camera me-2 text-info"></i>Camera chính:</strong> 
                        <span class="text-info fw-bold">{{ $details->camera_main }}</span>
                    </div>
                @endif
                
                @if($details->camera_front)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-camera-retro me-2 text-warning"></i>Camera trước:</strong> 
                        <span class="text-warning fw-bold">{{ $details->camera_front }}</span>
                    </div>
                @endif
                
                @if($details->os)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-desktop me-2 text-primary"></i>Hệ điều hành:</strong> 
                        <span class="badge bg-primary ms-2">{{ $details->os }}</span>
                    </div>
                @endif
                
                @if($details->special_features)
                    <div class="mb-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-star me-2 text-warning"></i>Tính năng đặc biệt:</strong> 
                        <div class="mt-2">
                            <span class="text-muted">{{ $details->special_features }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

