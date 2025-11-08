// Appointment Scheduling UI Component

class AppointmentUI {
    constructor() {
        this.scheduler = scheduler;
        this.initUI();
    }

    initUI() {
        // Initialize UI elements and event listeners
        this.datePicker = document.getElementById('appointmentDate');
        this.doctorSelect = document.getElementById('doctorSelect');
        this.timeSlotsContainer = document.getElementById('timeSlots');
        this.bookButton = document.getElementById('bookAppointment');

        if (this.datePicker && this.doctorSelect) {
            this.datePicker.addEventListener('change', () => this.updateAvailableSlots());
            this.doctorSelect.addEventListener('change', () => this.updateAvailableSlots());
        }

        if (this.bookButton) {
            this.bookButton.addEventListener('click', () => this.bookAppointment());
        }
    }

    async updateAvailableSlots() {
        const doctorId = this.doctorSelect.value;
        const date = this.datePicker.value;

        if (!doctorId || !date) return;

        // Fetch available slots
        const slots = await this.scheduler.getAvailableSlots(doctorId, date);
        this.renderTimeSlots(slots);
    }

    renderTimeSlots(slots) {
        this.timeSlotsContainer.innerHTML = '';

        slots.forEach(slot => {
            const slotElement = document.createElement('div');
            slotElement.className = 'time-slot';
            slotElement.textContent = `${slot.startTime} - ${slot.endTime}`;
            slotElement.addEventListener('click', () => this.selectTimeSlot(slot));
            this.timeSlotsContainer.appendChild(slotElement);
        });
    }

    selectTimeSlot(slot) {
        // Handle time slot selection
        this.selectedSlot = slot;
    }

    async bookAppointment() {
        if (!this.selectedSlot) {
            alert('Please select a time slot');
            return;
        }

        // Get patient ID from session or form
        const patientId = 1; // Replace with actual patient ID

        // Book appointment
        const result = await this.scheduler.addAppointment({
            doctorId: this.doctorSelect.value,
            patientId: patientId,
            startTime: this.selectedSlot.startTime,
            endTime: this.selectedSlot.endTime
        });

        if (result.success) {
            alert('Appointment booked successfully');
            this.updateAvailableSlots();
        } else {
            alert(result.message);
        }
    }
}

// Initialize UI
const appointmentUI = new AppointmentUI();

export default appointmentUI;