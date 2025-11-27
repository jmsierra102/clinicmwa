<?php
// veterinarian/appointments.php

$vet_id = $_SESSION['id'];
$message = '';

// Fetch upcoming appointments
$upcoming_sql = "SELECT a.id, a.appointment_date, a.reason, a.status, p.name as pet_name, 
                       u.firstname, u.lastname
                FROM appointments a
                JOIN pets p ON a.pet_id = p.id
                JOIN users u ON p.owner_id = u.id
                WHERE a.vet_id = ? AND a.appointment_date >= NOW()
                ORDER BY a.appointment_date ASC";
$stmt_upcoming = $conn->prepare($upcoming_sql);
$stmt_upcoming->bind_param("s", $vet_id);
$stmt_upcoming->execute();
$upcoming_result = $stmt_upcoming->get_result();

// Fetch past appointments
$past_sql = "SELECT a.id, a.appointment_date, a.reason, a.status, p.name as pet_name, 
                    u.firstname, u.lastname
             FROM appointments a
             JOIN pets p ON a.pet_id = p.id
             JOIN users u ON p.owner_id = u.id
             WHERE a.vet_id = ? AND a.appointment_date < NOW()
             ORDER BY a.appointment_date DESC";
$stmt_past = $conn->prepare($past_sql);
$stmt_past->bind_param("s", $vet_id);
$stmt_past->execute();
$past_result = $stmt_past->get_result();

?>

<h2>My Appointments</h2>

<?php echo $message; ?>

<h3>Upcoming Appointments</h3>
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Pet</th>
                <th>Owner</th>
                <th>Reason</th>
                <th>Status</th>
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
                        <td><?php echo e($row['firstname'] . ' ' . $row['lastname']); ?></td>
                        <td><?php echo e($row['reason']); ?></td>
                        <td><span class="status-<?php echo e(strtolower($row['status'])); ?>"><?php echo e(ucfirst($row['status'])); ?></span></td>
                        <td class="actions">
                            <a href="?page=view_appointment&id=<?php echo e($row['id']); ?>" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">You have no upcoming appointments.</td></tr>
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
                <th>Owner</th>
                <th>Reason</th>
                <th>Status</th>
                <th class="actions">Actions</th>
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
                        <td><?php echo e($row['firstname'] . ' ' . $row['lastname']); ?></td>
                        <td><?php echo e($row['reason']); ?></td>
                        <td><span class="status-<?php echo e(strtolower($row['status'])); ?>"><?php echo e(ucfirst($row['status'])); ?></span></td>
                        <td class="actions">
                            <a href="?page=view_appointment&id=<?php echo e($row['id']); ?>" class="btn btn-sm btn-secondary">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">You have no past appointments.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>