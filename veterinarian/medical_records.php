<?php
// veterinarian/medical_records.php

$pet_id = $_GET['pet_id'] ?? null;
$action = $_GET['action'] ?? 'add';
$record_id = $_GET['record_id'] ?? null;
$message = '';

if (!$pet_id) {
    echo "<h2>Invalid Pet</h2><p>No pet ID provided.</p>";
    return;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_date = $_POST['visit_date'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['notes'];

    if (isset($_POST['add_record'])) {
        $new_id = generate_id('MR', $conn, 'medical_records');
        $sql = "INSERT INTO medical_records (id, pet_id, visit_date, diagnosis, treatment, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $new_id, $pet_id, $visit_date, $diagnosis, $treatment, $notes);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Medical record added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding medical record.</div>';
        }
    } elseif (isset($_POST['update_record']) && $record_id) {
        $sql = "UPDATE medical_records SET visit_date = ?, diagnosis = ?, treatment = ?, notes = ? WHERE id = ? AND pet_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $visit_date, $diagnosis, $treatment, $notes, $record_id, $pet_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Medical record updated successfully.</div>';
            $action = 'add'; // Reset action to "add" mode
        } else {
            $message = '<div class="alert alert-danger">Error updating medical record.</div>';
        }
    }
}

// Fetch pet details
$stmt_pet = $conn->prepare('SELECT p.*, u.firstname, u.lastname FROM pets p JOIN users u ON p.owner_id = u.id WHERE p.id = ?');
$stmt_pet->bind_param('s', $pet_id);
$stmt_pet->execute();
$pet = $stmt_pet->get_result()->fetch_assoc();

if (!$pet) {
    echo "<h2>Pet Not Found</h2><p>The requested pet does not exist.</p>";
    return;
}

// Fetch medical records for the pet
$stmt_records = $conn->prepare('SELECT * FROM medical_records WHERE pet_id = ? ORDER BY visit_date DESC');
$stmt_records->bind_param('s', $pet_id);
$stmt_records->execute();
$records = $stmt_records->get_result()->fetch_all(MYSQLI_ASSOC);

// If editing, fetch the specific record
$current_record = [
    'visit_date' => date('Y-m-d'),
    'diagnosis' => '',
    'treatment' => '',
    'notes' => ''
];
if ($action == 'edit' && $record_id) {
    $stmt_edit_record = $conn->prepare('SELECT * FROM medical_records WHERE id = ? AND pet_id = ?');
    $stmt_edit_record->bind_param('ss', $record_id, $pet_id);
    $stmt_edit_record->execute();
    $result = $stmt_edit_record->get_result();
    if($result->num_rows > 0){
        $current_record = $result->fetch_assoc();
    }
}
?>

<div class="view-header">
    <div>
        <h2>Medical Records for <?php echo e($pet['name']); ?></h2>
        <p><strong>Owner:</strong> <?php echo e($pet['firstname'] . ' ' . e($pet['lastname'])); ?></p>
    </div>
</div>


<?php echo $message; ?>

<div class="records-layout">
    <div class="records-history">
        <h3>Medical History</h3>
        <div class="records-container">
            <?php if (count($records) > 0): ?>
                <?php foreach ($records as $record): ?>
                    <div class="record-card">
                        <div class="record-header">
                            <div><strong>Visit Date:</strong> <?php echo e(date('F j, Y', strtotime($record['visit_date']))); ?></div>
                            <a href="?page=medical_records&pet_id=<?php echo e($pet_id); ?>&action=edit&record_id=<?php echo e($record['id']); ?>" class="edit-link">Edit</a>
                        </div>
                        <div class="record-body">
                            <p><strong>Diagnosis:</strong></p>
                            <p><?php echo nl2br(e($record['diagnosis'])); ?></p>
                            <p><strong>Treatment:</strong></p>
                            <p><?php echo nl2br(e($record['treatment'])); ?></p>
                            <?php if (!empty($record['notes'])): ?>
                                <p><strong>Notes:</strong></p>
                                <p><?php echo nl2br(e($record['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No medical records found for this pet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="add-record-form">
        <h3><?php echo $action == 'edit' ? 'Edit' : 'Add New'; ?> Medical Record</h3>

        <div class="form-wrapper">
            <form action="?page=medical_records&pet_id=<?php echo e($pet_id); ?><?php if($action == 'edit') echo '&action=edit&record_id=' . e($record_id); ?>" method="post">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="update_record" value="1">
                <?php else: ?>
                    <input type="hidden" name="add_record" value="1">
                <?php endif; ?>
                <div class="form-group">
                    <label for="visit_date">Visit Date</label>
                    <input type="date" id="visit_date" name="visit_date" value="<?php echo e($current_record['visit_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea id="diagnosis" name="diagnosis" required><?php echo e($current_record['diagnosis']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="treatment">Treatment</label>
                    <textarea id="treatment" name="treatment" required><?php echo e($current_record['treatment']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"><?php echo e($current_record['notes']); ?></textarea>
                </div>
                <div class="form-actions-bottom">
                    <button type="submit" class="btn btn-primary"><?php echo $action == 'edit' ? 'Update' : 'Add'; ?> Record</button>
                    <?php if ($action == 'edit'): ?>
                        <a href="?page=medical_records&pet_id=<?php echo e($pet_id); ?>" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                    <a href="?page=appointments" class="btn btn-secondary">Back to Appointments</a>
                </div>
            </form>
        </div>
    </div>
</div>
