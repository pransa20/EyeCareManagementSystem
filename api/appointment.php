<?php

class AppointmentScheduler {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addAppointment($doctorId, $patientId, $startTime, $endTime) {
        // Check for conflicts
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
        $stmt->execute([$doctorId, $endTime, $startTime, $startTime, $endTime]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Time conflict detected'];
        }

        // Insert new appointment
        $stmt = $this->db->prepare("INSERT INTO appointments (doctor_id, patient_id, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$doctorId, $patientId, $startTime, $endTime]);

        return ['success' => true, 'message' => 'Appointment scheduled successfully'];
    }

    public function getAvailableSlots($doctorId, $date) {
        // Get doctor's working hours
        $stmt = $this->db->prepare("SELECT start_hour, end_hour FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $doctor = $stmt->fetch();

        if (!$doctor) {
            return [];
        }

        $slots = [];
        $currentTime = new DateTime($date);
        $currentTime->setTime($doctor['start_hour'], 0);

        while ($currentTime->format('H') < $doctor['end_hour']) {
            $endTime = clone $currentTime;
            $endTime->modify('+30 minutes');

            // Check if slot is available
            $stmt = $this->db->prepare("SELECT * FROM appointments WHERE doctor_id = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
            $stmt->execute([$doctorId, $endTime->format('Y-m-d H:i:s'), $currentTime->format('Y-m-d H:i:s'), $currentTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')]);

            if ($stmt->rowCount() === 0) {
                $slots[] = [
                    'start_time' => $currentTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s')
                ];
            }

            $currentTime->modify('+30 minutes');
        }

        return $slots;
    }
}

?>