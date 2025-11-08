// Scheduling Algorithm Implementation

class Scheduler {
    constructor() {
        this.appointments = [];
        this.doctors = [];
    }

    addDoctor(doctor) {
        this.doctors.push(doctor);
    }

    addAppointment(appointment) {
        // Check for conflicts
        const conflict = this.appointments.find(apt => 
            apt.doctorId === appointment.doctorId &&
            apt.startTime < appointment.endTime &&
            apt.endTime > appointment.startTime
        );

        if (conflict) {
            return { success: false, message: 'Time conflict detected' };
        }

        this.appointments.push(appointment);
        return { success: true, message: 'Appointment scheduled successfully' };
    }

    getAvailableSlots(doctorId, date) {
        const doctor = this.doctors.find(d => d.id === doctorId);
        if (!doctor) return [];

        // Generate slots based on doctor's working hours
        const slots = [];
        let currentTime = new Date(date);
        currentTime.setHours(doctor.startHour, 0, 0, 0);

        while (currentTime.getHours() < doctor.endHour) {
            const endTime = new Date(currentTime);
            endTime.setMinutes(currentTime.getMinutes() + 30);

            const isAvailable = !this.appointments.some(apt => 
                apt.doctorId === doctorId &&
                apt.startTime < endTime &&
                apt.endTime > currentTime
            );

            if (isAvailable) {
                slots.push({
                    startTime: new Date(currentTime),
                    endTime: new Date(endTime)
                });
            }

            currentTime.setMinutes(currentTime.getMinutes() + 30);
        }

        return slots;
    }

    // Additional methods for conflict resolution and optimization
}

// Export the scheduler
const scheduler = new Scheduler();

export default scheduler;