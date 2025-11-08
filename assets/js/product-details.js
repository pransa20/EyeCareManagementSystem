// Function to handle adding items to cart
function addToCart(productId, quantity) {
    fetch('../api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count;
                cartCountElement.style.display = data.cart_count > 0 ? 'inline' : 'none';
            }
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
            
            // Auto dismiss after 3 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        } else {
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            An error occurred while adding the item to cart.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
    });
}

// Add event listener to add to cart form
document.addEventListener('DOMContentLoaded', () => {
    const addToCartForm = document.querySelector('form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const productId = new URLSearchParams(window.location.search).get('id');
            const quantity = document.querySelector('input[name="quantity"]').value;
            addToCart(productId, parseInt(quantity));
        });
    }
});