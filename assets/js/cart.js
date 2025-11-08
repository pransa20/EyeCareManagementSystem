// Function to update cart count
function updateCartCount() {
    fetch('/api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                // Show/hide badge based on count
                cartCountElement.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Update cart count when page loads
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});

// Update cart count when items are added/removed
document.addEventListener('cartUpdated', () => {
    updateCartCount();
});