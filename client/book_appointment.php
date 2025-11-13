<?php
// client/book_appointment.php

$user_id = $_SESSION['id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $vet_id = $_POST['vet_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];

    // Combine date and time
    $full_datetime = $appointment_date . ' ' . $appointment_time;

    // Basic validation
    if (empty($pet_id) || empty($vet_id) || empty($full_datetime)) {
        $message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
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
            $stmt->bind_param("ssssss", $new_id, $pet_id, $vet_id, $full_datetime, $reason, $status);

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

// Fetch data for form dropdowns
$pets = $conn->prepare("SELECT id, name FROM pets WHERE owner_id = ?");
$pets->bind_param("s", $user_id);
$pets->execute();
$pets_result = $pets->get_result();

$vets_result = $conn->query("SELECT id, name FROM veterinarians ORDER BY name ASC");

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
                    <option value="<?php echo e($vet['id']); ?>"><?php echo get_vet_name($vet['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="appointment_date">Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required>
        </div>

        <div class="form-group">
            <label for="appointment_time">Time</label>
            <input type="time" id="appointment_time" name="appointment_time" required>
        </div>

        <div class="form-group">
            <label for="reason">Reason for Visit</label>
            <textarea id="reason" name="reason" placeholder="e.g., Annual check-up, not feeling well..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" <?php if ($pets_result->num_rows == 0) echo 'disabled'; ?>>Book Appointment</button>
    </form>
</div>
