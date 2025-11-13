<?php
// admin/appointments.php

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle POST requests for status updates or edits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $status = $_POST['status'];
        $sql = "UPDATE appointments SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $status, $id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Appointment status updated.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating status.</div>';
        }
    } elseif ($action == 'edit' && $id) {
        // Logic for editing a full appointment
        $pet_id = $_POST['pet_id'];
        $vet_id = $_POST['vet_id'];
        $appointment_date = $_POST['appointment_date'] . ' ' . $_POST['appointment_time'];
        $reason = $_POST['reason'];
        $status = $_POST['status'];

        $sql = "UPDATE appointments SET pet_id = ?, vet_id = ?, appointment_date = ?, reason = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $pet_id, $vet_id, $appointment_date, $reason, $status, $id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Appointment updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating appointment.</div>';
        }
        $action = 'list';
    }
}

echo $message;

if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_data = $result->fetch_assoc();
    
    $pets = $conn->query("SELECT p.id, p.name, u.firstname, u.lastname FROM pets p JOIN users u ON p.owner_id = u.id");
    $vets = $conn->query("SELECT id, name FROM veterinarians");
?>
    <h2>Edit Appointment</h2>
    <div class="form-wrapper">
        <form action="?page=appointments&action=edit&id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label>Pet</label>
                <select name="pet_id" required>
                    <?php while($pet = $pets->fetch_assoc()): ?>
                        <option value="<?php echo e($pet['id']); ?>" <?php echo $current_data['pet_id'] == $pet['id'] ? 'selected' : ''; ?>>
                            <?php echo e($pet['name'] . ' (' . $pet['firstname'] . ' ' . $pet['lastname'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Veterinarian</label>
                <select name="vet_id" required>
                    <?php while($vet = $vets->fetch_assoc()): ?>
                        <option value="<?php echo e($vet['id']); ?>" <?php echo $current_data['vet_id'] == $vet['id'] ? 'selected' : ''; ?>>
                            <?php echo get_vet_name($vet['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" value="<?php echo e(substr($current_data['appointment_date'], 0, 10)); ?>" required>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="appointment_time" value="<?php echo e(substr($current_data['appointment_date'], 11, 5)); ?>" required>
            </div>
            <div class="form-group">
                <label>Reason for Visit</label>
                <textarea name="reason"><?php echo e($current_data['reason']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="scheduled" <?php echo $current_data['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="completed" <?php echo $current_data['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $current_data['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Appointment</button>
            <a href="?page=appointments" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php
} else {
?>
    <h2>Manage Appointments</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Pet</th>
                    <th>Owner</th>
                    <th>Veterinarian</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT a.id, a.appointment_date, a.reason, a.status, p.name as pet_name, 
                               u.firstname, u.lastname, v.name as vet_name
                        FROM appointments a
                        JOIN pets p ON a.pet_id = p.id
                        JOIN users u ON p.owner_id = u.id
                        JOIN veterinarians v ON a.vet_id = v.id
                        ORDER BY a.appointment_date DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $dt = new DateTime($row['appointment_date']);
                        echo "<tr>";
                        echo "<td>" . $dt->format('M d, Y @ h:i A') . "</td>";
                        echo "<td>" . e($row['pet_name']) . "</td>";
                        echo "<td>" . e($row['firstname'] . ' ' . $row['lastname']) . "</td>";
                        echo "<td>" . get_vet_name($row['vet_name']) . "</td>";
                        echo "<td>" . e($row['reason']) . "</td>";
                        echo "<td>" . e(ucfirst($row['status'])) . "</td>";
                        echo '<td class="actions">';
                        echo '<a href="?page=appointments&action=edit&id=' . e($row['id']) . '">Edit</a>';
                        echo '</td>';
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="7">No appointments found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
