// Function to fetch doctor's available time slots
async function fetchDoctorAvailability(doctorId, date) {
    try {
        const response = await fetch(`/api/doctor-availability.php?doctor_id=${doctorId}&date=${date}`);
            if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        return data.available_slots || [];
    } catch (error) {
        console.error('Error fetching doctor availability:', error);
        return [];
    }
}

// Function to format time for display
function formatTime(timeString) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// Function to update time slot options
// Function to update time slots
function updateTimeSlots(availableSlots) {
    const timeSelect = document.getElementById('time');
    timeSelect.innerHTML = '<option value="">Select time</option>';
    
    // Get current date and time for validation
    const now = new Date();
    const currentDate = now.toISOString().split('T')[0];
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    
    availableSlots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = formatTime(slot);
        
        // Disable past time slots for today
        if (dateInput.value === currentDate) {
            const [slotHour, slotMinute] = slot.split(':').map(Number);
            if (slotHour < currentHour || (slotHour === currentHour && slotMinute <= currentMinute)) {
                option.disabled = true;
            }
        }
        
        timeSelect.appendChild(option);
    });

    // Disable the select if no slots are available
    timeSelect.disabled = availableSlots.length === 0;
    if (availableSlots.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No available slots';
        timeSelect.appendChild(option);
    }
}

// Initialize form validation and event listeners
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('appointmentForm');
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
    
    // Update time slots when doctor or date changes
    async function updateAvailability() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (doctorId && date) {
            const availableSlots = await fetchDoctorAvailability(doctorId, date);
            
            // Filter out past time slots if the selected date is today
            if (date === new Date().toISOString().split('T')[0]) {
                const currentHour = new Date().getHours();
                availableSlots = availableSlots.filter(slot => {
                    const slotHour = parseInt(slot.split(':')[0]);
                    return slotHour > currentHour;
                });
            }
            
            updateTimeSlots(availableSlots);
        } else {
            updateTimeSlots([]);
        }
    }
    
    // Event listeners
    doctorSelect.addEventListener('change', updateAvailability);
    dateInput.addEventListener('change', function() {
        const selected = new Date(this.value);
        const day = selected.getUTCDay();
        
        // Validate weekend selection
        if (day === 0 || day === 6) {
            alert('Weekends are not available for appointments. Please select a weekday.');
            this.value = '';
            updateTimeSlots([]);
            return;
        }
        
        updateAvailability();
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!doctorSelect.value || !dateInput.value || !timeSelect.value) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return;
        }
        
        const selectedDateTime = new Date(`${dateInput.value}T${timeSelect.value}`);
        const now = new Date();
        const minBookingTime = new Date(now.getTime() + (60 * 60 * 1000)); // 1 hour from now
        
        if (selectedDateTime < minBookingTime) {
            e.preventDefault();
            alert('Appointments must be booked at least 1 hour in advance');
            return;
        }
    });
});