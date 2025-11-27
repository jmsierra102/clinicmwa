<?php
// veterinarian/view_appointment.php

$appointment_id = $_GET['id'] ?? null;
$vet_id = $_SESSION['id'];

if (!$appointment_id) {
    echo "<h2>Invalid Appointment</h2>";
    echo "<p>No appointment ID provided.</p>";
    return;
}

// Fetch appointment details
$sql = "SELECT 
            a.id as appointment_id, a.appointment_date, a.reason, a.status,
            p.id as pet_id, p.name as pet_name, p.species, p.breed, p.age,
            u.id as owner_id, u.firstname as owner_firstname, u.lastname as owner_lastname, u.email as owner_email, u.phone as owner_phone
        FROM appointments a
        JOIN pets p ON a.pet_id = p.id
        JOIN users u ON p.owner_id = u.id
        WHERE a.id = ? AND a.vet_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $appointment_id, $vet_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    echo "<h2>Appointment Not Found</h2>";
    echo "<p>The requested appointment does not exist or you do not have permission to view it.</p>";
    return;
}

$dt = new DateTime($appointment['appointment_date']);

?>

<div class="view-header">
    <h2>Appointment Details</h2>
    <div class="actions-bar">
        <a href="?page=medical_records&pet_id=<?php echo e($appointment['pet_id']); ?>" class="btn btn-primary">Manage Medical Records</a>
        <a href="?page=appointments" class="btn btn-secondary">Back to Appointments</a>
    </div>
</div>


<div class="details-grid-two-col">
    <div class="details-card">
        <h3>
            Appointment Information
        </h3>
        <div class="details-body">
            <p><strong>Date:</strong> <?php echo $dt->format('l, F j, Y'); ?></p>
            <p><strong>Time:</strong> <?php echo $dt->format('g:i A'); ?></p>
            <p><strong>Status:</strong> <span class="status-<?php echo e(strtolower($appointment['status'])); ?>"><?php echo e(ucfirst($appointment['status'])); ?></span></p>
            <p><strong>Reason for visit:</strong></p>
            <p><?php echo e($appointment['reason'] ?: 'N/A'); ?></p>
        </div>
    </div>

    <div class="details-card">
        <h3>
            Patient Details
        </h3>
        <div class="details-body">
            <p><strong>Name:</strong> <?php echo e($appointment['pet_name']); ?></p>
            <p><strong>Species:</strong> <?php echo e($appointment['species']); ?></p>
            <p><strong>Breed:</strong> <?php echo e($appointment['breed']); ?></p>
            <p><strong>Age:</strong> <?php echo e($appointment['age']); ?> years old</p>
        </div>
    </div>

    <div class="details-card">
        <h3>
            Client Information
        </h3>
        <div class="details-body">
            <p><strong>Name:</strong> <?php echo e($appointment['owner_firstname'] . ' ' . $appointment['owner_lastname']); ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo e($appointment['owner_email']); ?>"><?php echo e($appointment['owner_email']); ?></a></p>
            <p><strong>Phone:</strong> <a href="tel:<?php echo e($appointment['owner_phone']); ?>"><?php echo e($appointment['owner_phone'] ?: 'N/A'); ?></a></p>
        </div>
    </div>
</div>