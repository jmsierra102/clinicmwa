<?php
// client/my_appointments.php

$user_id = $_SESSION['id'];
$message = '';

// Handle cancellation
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $appointment_id = $_GET['id'];

    // Verify the appointment belongs to the user and is in the future before cancelling
    $sql = "UPDATE appointments a
            JOIN pets p ON a.pet_id = p.id
            SET a.status = 'cancelled'
            WHERE a.id = ? AND p.owner_id = ? AND a.appointment_date >= NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $appointment_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = '<div class="alert alert-success">Your appointment has been cancelled.</div>';
    } else {
        $message = '<div class="alert alert-danger">Could not cancel this appointment. It may be in the past or an error occurred.</div>';
    }
}

echo $message;

// Fetch upcoming appointments
$upcoming_sql = "SELECT a.id, a.appointment_date, a.reason, a.status, p.name as pet_name, v.name as vet_name
                 FROM appointments a
                 JOIN pets p ON a.pet_id = p.id
                 JOIN veterinarians v ON a.vet_id = v.id
                 WHERE p.owner_id = ? AND a.status = 'scheduled' AND a.appointment_date >= NOW()
                 ORDER BY a.appointment_date ASC";
$stmt_upcoming = $conn->prepare($upcoming_sql);
$stmt_upcoming->bind_param("s", $user_id);
$stmt_upcoming->execute();
$upcoming_result = $stmt_upcoming->get_result();

// Fetch past appointments
$past_sql = "SELECT a.id, a.appointment_date, a.reason, a.status, p.name as pet_name, v.name as vet_name
             FROM appointments a
             JOIN pets p ON a.pet_id = p.id
             JOIN veterinarians v ON a.vet_id = v.id
             WHERE p.owner_id = ? AND (a.status != 'scheduled' OR a.appointment_date < NOW())
             ORDER BY a.appointment_date DESC";
$stmt_past = $conn->prepare($past_sql);
$stmt_past->bind_param("s", $user_id);
$stmt_past->execute();
$past_result = $stmt_past->get_result();

?>

<h2>My Appointments</h2>

<h3>Upcoming Appointments</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Pet</th>
                <th>With</th>
                <th>Reason</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($upcoming_result->num_rows > 0): ?>
                <?php while($row = $upcoming_result->fetch_assoc()): 
                    $dt = new DateTime($row['appointment_date']);
                ?>
                    <tr>
                        <td><?php echo $dt->format('l, M j, Y @ g:i A'); ?></td>
                        <td><?php echo e($row['pet_name']); ?></td>
                        <td><?php echo get_vet_name($row['vet_name']); ?></td>
                        <td><?php echo e($row['reason']); ?></td>
                        <td class="actions">
                            <a href="?page=my_appointments&action=cancel&id=<?php echo e($row['id']); ?>" class="delete" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">You have no upcoming appointments.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h3 style="margin-top: 40px;">Past Appointments</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Pet</th>
                <th>With</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($past_result->num_rows > 0): ?>
                <?php while($row = $past_result->fetch_assoc()): 
                    $dt = new DateTime($row['appointment_date']);
                ?>
                    <tr>
                        <td><?php echo $dt->format('M j, Y'); ?></td>
                        <td><?php echo e($row['pet_name']); ?></td>
                        <td><?php echo get_vet_name($row['vet_name']); ?></td>
                        <td><?php echo e($row['reason']); ?></td>
                        <td><?php echo e(ucfirst($row['status'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">You have no past appointments.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
