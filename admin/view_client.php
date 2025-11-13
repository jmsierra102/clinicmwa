<?php
// admin/view_client.php

if (!isset($_GET['id'])) {
    echo "No client specified.";
    exit;
}

$client_id = $_GET['id'];

// Fetch client details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'client'");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$client_result = $stmt->get_result();
$client = $client_result->fetch_assoc();
$stmt->close();

if (!$client) {
    echo "Client not found.";
    exit;
}

// Fetch client's pets
$stmt = $conn->prepare("SELECT * FROM pets WHERE owner_id = ?");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$pets_result = $stmt->get_result();
$pets = $pets_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch client's appointments
$stmt = $conn->prepare("
    SELECT a.*, p.name as pet_name, v.name as vet_name
    FROM appointments a
    JOIN pets p ON a.pet_id = p.id
    JOIN veterinarians v ON a.vet_id = v.id
    WHERE p.owner_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$appointments = $appointments_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<h2>Client Details</h2>
<div class="card">
    <div class="card-header">
        <h3><?php echo e($client['firstname'] . ' ' . $client['lastname']); ?></h3>
    </div>
    <div class="card-body">
        <p><strong>Email:</strong> <?php echo e($client['email']); ?></p>
        <p><strong>Registered:</strong> <?php echo (new DateTime($client['created_at']))->format('M d, Y'); ?></p>
    </div>
</div>

<h3>Pets</h3>
<?php if (count($pets) > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Age</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $pet): ?>
                    <tr>
                        <td><?php echo e($pet['name']); ?></td>
                        <td><?php echo e($pet['species']); ?></td>
                        <td><?php echo e($pet['breed']); ?></td>
                        <td><?php echo e($pet['age']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>This client has no registered pets.</p>
<?php endif; ?>

<h3>Appointments</h3>
<?php if (count($appointments) > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Pet</th>
                    <th>Veterinarian</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo (new DateTime($appointment['appointment_date']))->format('M d, Y h:i A'); ?></td>
                        <td><?php echo e($appointment['pet_name']); ?></td>
                        <td><?php echo get_vet_name($appointment['vet_name']); ?></td>
                        <td><?php echo e($appointment['reason']); ?></td>
                        <td><span class="status-<?php echo e($appointment['status']); ?>"><?php echo e(ucfirst($appointment['status'])); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>This client has no appointments.</p>
<?php endif; ?>

<a href="?page=clients" class="button">Back to Clients</a>
