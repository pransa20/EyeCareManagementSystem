// Quick View Modal Handler
const quickViewModal = document.getElementById('quickViewModal');

// Product data structure
const products = {
    1: {
        name: 'Designer Eyeglasses',
        category: 'Premium Collection',
        price: 5999,
        rating: 4,
        reviews: 24,
        description: 'Premium designer eyeglasses with high-quality lenses and stylish frames. Perfect for both casual and professional wear.',
        images: ['assets/images/hero-image.svg'],
        requiresPrescription: true,
        sizes: ['Small', 'Medium', 'Large'],
        colors: ['Dark', 'Primary', 'Secondary']
    },
    2: {
        name: 'Polarized Sunglasses',
        category: 'UV Protection',
        price: 3499,
        rating: 3.5,
        reviews: 18,
        description: 'High-quality polarized sunglasses offering 100% UV protection. Ideal for outdoor activities and driving.',
        images: ['assets/images/hero-image.svg'],
        requiresPrescription: false,
        sizes: ['Medium', 'Large'],
        colors: ['Black', 'Brown', 'Blue']
    },
    3: {
        name: 'Monthly Contact Lenses',
        category: 'Comfort Wear',
        price: 1999,
        rating: 5,
        reviews: 32,
        description: 'Monthly disposable contact lenses with superior comfort and breathability. Perfect for daily wear.',
        images: ['assets/images/hero-image.svg'],
        requiresPrescription: true,
        sizes: ['-2.00', '-3.00', '-4.00'],
        colors: ['Clear', 'Blue', 'Green']
    },
    4: {
        name: 'Lens Cleaning Kit',
        category: 'Essential Care',
        price: 499,
        rating: 4,
        reviews: 45,
        description: 'Complete lens cleaning kit including solution, microfiber cloth, and carrying case.',
        images: ['assets/images/hero-image.svg'],
        requiresPrescription: false,
        sizes: ['Standard'],
        colors: ['Blue']
    }
};

// Update modal content based on product ID
function updateQuickViewModal(productId) {
    const product = products[productId];
    if (!product) return;

    // Update product details
    quickViewModal.querySelector('h3').textContent = product.name;
    quickViewModal.querySelector('.text-primary.h4').textContent = `NPR ${product.price}`;
    quickViewModal.querySelector('.text-muted.mb-4').textContent = product.description;

    // Update rating
    const ratingContainer = quickViewModal.querySelector('.rating');
    ratingContainer.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('i');
        star.className = `fas fa-star${i <= product.rating ? '' : (i - 0.5 === product.rating ? '-half-alt' : '')}`;
        ratingContainer.appendChild(star);
    }
    ratingContainer.innerHTML += `<span class="text-muted ms-2">(${product.reviews} Reviews)</span>`;

    // Update images
    const mainImage = quickViewModal.querySelector('.product-gallery img.img-fluid');
    mainImage.src = product.images[0];
    mainImage.alt = product.name;

    // Show/hide prescription upload based on product type
    const prescriptionUpload = quickViewModal.querySelector('#prescriptionUpload');
    prescriptionUpload.style.display = product.requiresPrescription ? 'block' : 'none';

    // Update size options
    const sizeGroup = quickViewModal.querySelector('.btn-group');
    sizeGroup.innerHTML = product.sizes.map((size, index) => `
        <input type="radio" class="btn-check" name="size" id="${size.toLowerCase()}" ${index === 0 ? 'checked' : ''}>
        <label class="btn btn-outline-primary" for="${size.toLowerCase()}">${size}</label>
    `).join('');

    // Update color options
    const colorContainer = quickViewModal.querySelector('.d-flex.gap-2');
    colorContainer.innerHTML = product.colors.map(color => `
        <button class="btn btn-outline-${color.toLowerCase()} rounded-circle p-2">
            <span class="d-block rounded-circle bg-${color.toLowerCase()}" style="width: 20px; height: 20px;"></span>
        </button>
    `).join('');

    // Update add to cart button
    const addToCartBtn = quickViewModal.querySelector('#addToCartBtn');
    addToCartBtn.onclick = () => addToCart(productId);
}


// Initialize quick view buttons
document.addEventListener('DOMContentLoaded', () => {
    const quickViewModal = document.getElementById('quickViewModal');
    const addToCartBtn = quickViewModal?.querySelector('#addToCartBtn');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            // Assuming productId is stored in a data attribute like data-product-id
            const productId = quickViewModal.getAttribute('data-product-id');
            if (productId) {
                addToCart(productId);
            } else {
                console.error('Product ID not found in quick view modal.');
            }
        });
    }
});



// Add to cart functionality
window.addToCart = function(productId) {
    const product = products[productId];
    if (!product) return;

    // Validate prescription if required
    if (product.requiresPrescription) {
        const prescriptionFile = document.getElementById('prescriptionFile');
        if (!prescriptionFile.value) {
            alert('Please upload a prescription before adding to cart.');
            return;
        }
    }

    // Get selected options
    const selectedSize = quickViewModal.querySelector('input[name="size"]:checked')?.id;
    const quantity = parseInt(quickViewModal.querySelector('input[type="text"]').value) || 1;

    // Send request to cart API
    fetch('../api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            size: selectedSize,
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

            // Close modal
            const modal = bootstrap.Modal.getInstance(quickViewModal);
            modal.hide();
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