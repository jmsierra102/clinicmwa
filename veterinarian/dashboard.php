<?php
// veterinarian/dashboard.php

$user_id = $_SESSION['id'];
$firstname = $_SESSION['firstname'];

?>

<h2>Hello, Dr. <?php echo e($firstname); ?>!</h2>
<p>Welcome to your veterinarian dashboard. Here you can manage your appointments and patient medical records.</p>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>View Appointments</h3>
        <p>See your upcoming and past appointments.</p>
        <a href="?page=appointments" class="btn btn-primary">View Appointments</a>
    </div>
    <div class="dashboard-card">
        <h3>Patients</h3>
        <p>View and manage your patients' records.</p>
        <a href="?page=patients" class="btn btn-primary">View Patients</a>
    </div>
</div>