document.addEventListener('DOMContentLoaded', function() {
    // Function to update appointment status
    function updateAppointmentStatus(appointmentId, newStatus) {
        const formData = new FormData();
        formData.append('appointment_id', appointmentId);
        formData.append('status', newStatus);

        fetch('/admin/update-appointment-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the status badge
                const statusBadge = document.querySelector(`#status-badge-${appointmentId}`);
                if (statusBadge) {
                    statusBadge.className = `badge bg-${getStatusColor(newStatus)}`;
                    statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                }
                // Show success message
                alert('Appointment status updated successfully!');
                // Reload the page to reflect changes
                window.location.reload();
            } else {
                alert('Failed to update appointment status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the appointment status.');
        });
    }

    // Function to get status color for badge
    function getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'confirmed': 'success',
            'cancelled': 'danger',
            'completed': 'info'
        };
        return colors[status] || 'secondary';
    }

    // Add event listeners to all status update buttons
    document.querySelectorAll('.status-update-btn').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-appointment-id');
            const newStatus = this.getAttribute('data-status');
            if (confirm(`Are you sure you want to mark this appointment as ${newStatus}?`)) {
                updateAppointmentStatus(appointmentId, newStatus);
            }
        });
    });
}));