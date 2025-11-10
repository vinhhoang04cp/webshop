{{-- 
    Component Feature Card
    Props:
    - $icon: Icon class (bắt buộc) - VD: 'fas fa-shipping-fast'
    - $title: Tiêu đề (bắt buộc)
    - $description: Mô tả (bắt buộc)
--}}

<div class="feature-card-wrapper text-center">
    <div class="feature-shine"></div>
    <div class="feature-icon-wrapper mb-3">
        <div class="icon-ripple"></div>
        <div class="icon-circle"></div>
        <i class="{{ $icon }} fa-3x text-primary"></i>
    </div>
    <h5 class="feature-title">{{ $title }}</h5>
    <p class="feature-description">{{ $description }}</p>
    <div class="feature-number">
        <span>✓</span>
    </div>
</div>

<style>
.feature-card-wrapper {
    padding: 40px 24px;
    background: white;
    border-radius: 20px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 212, 170, 0.1);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(0, 212, 170, 0.12);
    height: 100%;
}

.feature-shine {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: rotate(45deg) translateX(-100%);
    transition: transform 0.6s ease;
    pointer-events: none;
}

.feature-card-wrapper:hover .feature-shine {
    transform: rotate(45deg) translateX(100%);
}

.feature-card-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.feature-card-wrapper:hover::before {
    opacity: 0.06;
}

.feature-card-wrapper:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 20px 50px rgba(0, 212, 170, 0.2);
    border-color: #00d4aa;
}

.feature-icon-wrapper {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.12), rgba(38, 208, 206, 0.12));
    border-radius: 50%;
    position: relative;
    z-index: 1;
    transition: all 0.4s ease;
}

.icon-ripple {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid rgba(0, 212, 170, 0.3);
    animation: ripple 2s ease-out infinite;
    opacity: 0;
}

.feature-card-wrapper:hover .icon-ripple {
    animation: ripple 1.5s ease-out infinite;
}

@keyframes ripple {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.icon-circle {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.2), rgba(38, 208, 206, 0.2));
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.feature-card-wrapper:hover .icon-circle {
    transform: scale(1.4);
    opacity: 0;
}

.feature-card-wrapper:hover .feature-icon-wrapper {
    transform: scale(1.15) rotate(10deg);
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.2), rgba(38, 208, 206, 0.2));
    box-shadow: 0 10px 30px rgba(0, 212, 170, 0.3);
}

.feature-card-wrapper .text-primary {
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.feature-card-wrapper:hover .text-primary {
    transform: scale(1.1);
    filter: drop-shadow(0 4px 8px rgba(0, 212, 170, 0.3));
}

.feature-title {
    font-weight: 700;
    color: #134e4a;
    margin-bottom: 12px;
    font-size: 1.2rem;
    position: relative;
    z-index: 1;
    transition: color 0.3s ease;
}

.feature-card-wrapper:hover .feature-title {
    color: #00d4aa;
}

.feature-description {
    color: #5f8a8b;
    margin: 0;
    line-height: 1.7;
    position: relative;
    z-index: 1;
    font-size: 0.95rem;
}

.feature-number {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    opacity: 0;
    transform: scale(0) rotate(-90deg);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 4px 15px rgba(0, 212, 170, 0.3);
}

.feature-card-wrapper:hover .feature-number {
    opacity: 1;
    transform: scale(1) rotate(0deg);
}
</style>

