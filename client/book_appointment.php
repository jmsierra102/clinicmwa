<?php
// client/book_appointment.php

$user_id = $_SESSION['id'];
$message = '';

/**
 * Validates if the selected appointment time is within clinic hours.
 *
 * @param DateTime $dateTime The appointment datetime object.
 * @return bool True if valid, false otherwise.
 */
function is_valid_appointment_time(DateTime $dateTime) {
    $day_of_week = $dateTime->format('N'); // 1 (for Monday) through 7 (for Sunday)
    $time = $dateTime->format('H:i');

    // Rule 1: No Sundays
    if ($day_of_week == 7) {
        return false;
    }

    // Rule 2: Check time boundaries (8:00 AM to 5:00 PM)
    if ($time < '08:00' || $time >= '17:00') {
        return false;
    }

    // Rule 3: No lunch break (12:00 PM to 1:00 PM)
    if ($time >= '12:00' && $time < '13:00') {
        return false;
    }

    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $vet_id = $_POST['vet_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];

    // Combine date and time
    $full_datetime_str = $appointment_date . ' ' . $appointment_time;
    $full_datetime_obj = new DateTime($full_datetime_str);

    // Basic validation
    if (empty($pet_id) || empty($vet_id) || empty($full_datetime_str)) {
        $message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    } elseif (!is_valid_appointment_time($full_datetime_obj)) {
        $message = '<div class="alert alert-danger">The selected time is outside of clinic hours. Please choose a time between 8:00 AM and 5:00 PM, Monday to Saturday (excluding 12:00 PM - 1:00 PM).</div>';
    } else {
        // Final server-side check for double-booking
        $conflict_sql = "SELECT id FROM appointments WHERE appointment_date = ?";
        $conflict_stmt = $conn->prepare($conflict_sql);
        $conflict_stmt->bind_param("s", $full_datetime_str);
        $conflict_stmt->execute();
        $conflict_stmt->store_result();

        if ($conflict_stmt->num_rows > 0) {
            $message = '<div class="alert alert-danger">This time slot has just been booked. Please select a different time.</div>';
        } else {
            // Check that the selected pet belongs to the user
            $check_sql = "SELECT id FROM pets WHERE id = ? AND owner_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $pet_id, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $new_id = generate_id('AP', $conn, 'appointments');
                $status = 'scheduled';
                
                $sql = "INSERT INTO appointments (id, pet_id, vet_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $new_id, $pet_id, $vet_id, $full_datetime_str, $reason, $status);

                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Appointment booked successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">There was an error booking your appointment. Please try again.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Invalid pet selected.</div>';
            }
        }
    }
}

// Fetch data for form dropdowns
$pets = $conn->prepare("SELECT id, name FROM pets WHERE owner_id = ?");
$pets->bind_param("s", $user_id);
$pets->execute();
$pets_result = $pets->get_result();

$vets_result = $conn->query("SELECT id, firstname, lastname FROM users WHERE role = 'veterinarian' ORDER BY lastname, firstname ASC");

?>

<h2>Book an Appointment</h2>
<p>Schedule a visit for your pet. Appointments can be booked up to two months in advance.</p>

<?php echo $message; ?>

<div class="form-wrapper">
    <form action="?page=book_appointment" method="post">
        <div class="form-group">
            <label for="pet_id">Which pet is this for?</label>
            <select id="pet_id" name="pet_id" required>
                <option value="">-- Select your pet --</option>
                <?php
                if ($pets_result->num_rows > 0) {
                    while($pet = $pets_result->fetch_assoc()) {
                        echo '<option value="' . e($pet['id']) . '">' . e($pet['name']) . '</option>';
                    }
                } else {
                    echo '<option value="" disabled>You have no pets. Please add one first.</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="vet_id">Preferred Veterinarian</label>
            <select id="vet_id" name="vet_id" required>
                <option value="">-- Select a veterinarian --</option>
                <?php while($vet = $vets_result->fetch_assoc()): ?>
                    <option value="<?php echo e($vet['id']); ?>"><?php echo get_vet_name(e($vet['firstname']) . ' ' . e($vet['lastname'])); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="appointment_date">Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required>
        </div>

        <div class="form-group">
            <label for="appointment_time">Time</label>
            <select id="appointment_time" name="appointment_time" required>
                <option value="">-- Select a date first --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="reason">Reason for Visit</label>
            <textarea id="reason" name="reason" placeholder="e.g., Annual check-up, not feeling well..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" <?php if ($pets_result->num_rows == 0) echo 'disabled'; ?>>Book Appointment</button>
    </form>
</div>
