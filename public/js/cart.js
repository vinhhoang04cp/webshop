// Hàm thêm sản phẩm vào giỏ hàng
function addToCart(productId) {
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => {
        if (response.status === 401) {
            return response.json().then(data => {
                alert(data.message || 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = '/login';
                throw new Error('Unauthorized');
            });
        }
        return response.json();
    })
    .then(data => {
        if(data && data.success) {
            alert(data.message);
            location.reload();
        } else if(data && !data.success) {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        if (error.message !== 'Unauthorized') {
            console.error('Error:', error);
        }
    });
}

