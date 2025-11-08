document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            
            // Show loading state
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitButton.disabled = true;
            
            fetch('/api/contact-submit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset loading state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                
                if (data.success) {
                    // Show success message
                    showAlert('success', 'Thank you! Your message has been sent successfully.');
                    contactForm.reset();
                } else {
                    // Show error message
                    showAlert('danger', data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                // Reset loading state and show error
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                showAlert('danger', 'An error occurred. Please try again later.');
                console.error('Error:', error);
            });
        });
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert alert before the form
        contactForm.parentNode.insertBefore(alertDiv, contactForm);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
}));