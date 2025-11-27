<?php
// client/dashboard.php

$user_id = $_SESSION['id'];
$firstname = $_SESSION['firstname'];

// Fetch client's upcoming appointments
$sql = "SELECT a.id, a.appointment_date, v.firstname as vet_firstname, v.lastname as vet_lastname, p.name as pet_name
        FROM appointments a
        JOIN users v ON a.vet_id = v.id
        JOIN pets p ON a.pet_id = p.id
        WHERE p.owner_id = ? AND a.status = 'scheduled' AND a.appointment_date >= NOW() AND v.role = 'veterinarian'
        ORDER BY a.appointment_date ASC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$next_appointment = $result->fetch_assoc();

?>

<h2>Hello, <?php echo e($firstname); ?>!</h2>
<p>Welcome to your personal clinic dashboard. Here you can manage your pets and appointments.</p>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>Book a Visit</h3>
        <p>Schedule a new appointment for one of your furbabies.</p>
        <a href="?page=book_appointment" class="btn btn-primary">Book Now</a>
    </div>

    <?php if ($next_appointment): 
        $dt = new DateTime($next_appointment['appointment_date']);
    ?>
    <div class="dashboard-card">
        <h3>Your Next Appointment</h3>
        <p>
            <strong>Pet:</strong> <?php echo e($next_appointment['pet_name']); ?><br>
            <strong>With:</strong> <?php echo get_vet_name(e($next_appointment['vet_firstname']) . ' ' . e($next_appointment['vet_lastname'])); ?><br>
            <strong>On:</strong> <?php echo $dt->format('l, F j, Y \a\t g:i A'); ?>
        </p>
        <a href="?page=my_appointments" class="btn btn-secondary">View All</a>
    </div>
    <?php else: ?>
    <div class="dashboard-card">
        <h3>No Upcoming Appointments</h3>
        <p>You have no scheduled appointments. Book one today!</p>
    </div>
    <?php endif; ?>
</div>

