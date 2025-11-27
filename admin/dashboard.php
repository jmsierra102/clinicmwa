<?php
// admin/dashboard.php

// Fetch stats for the admin dashboard
$appointments_count = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$pets_count = $conn->query("SELECT COUNT(*) as count FROM pets")->fetch_assoc()['count'];
$clients_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'")->fetch_assoc()['count'];
$vets_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'veterinarian'")->fetch_assoc()['count'];

?>

<h2>Admin Dashboard</h2>
<p>Welcome to the administration panel. Here you can manage the clinic's operations.</p>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <h3>Total Appointments</h3>
        <p><?php echo $appointments_count; ?></p>
    </div>
    <div class="dashboard-card">
        <h3>Registered Pets</h3>
        <p><?php echo $pets_count; ?></p>
    </div>
    <div class="dashboard-card">
        <h3>Happy Clients</h3>
        <p><?php echo $clients_count; ?></p>
    </div>
    <div class="dashboard-card">
        <h3>Veterinarians</h3>
        <p><?php echo $vets_count; ?></p>
    </div>
</div>
