<?php
include_once '../db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /clinic/login.php');
    exit();
}

if (!isset($_GET['pet_id'])) {
    header('Location: ?page=pets');
    exit();
}

$pet_id = $_GET['pet_id'];

// Handle form submission for adding a new medical record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $visit_date = $_POST['visit_date'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['notes'];
    $id = generate_id('MR', $conn, 'medical_records', 'id');

    $stmt = $conn->prepare('INSERT INTO medical_records (id, pet_id, visit_date, diagnosis, treatment, notes) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $id, $pet_id, $visit_date, $diagnosis, $treatment, $notes);
    $stmt->execute();

    header("Location: ?page=medical_records&pet_id=$pet_id");
    exit();
}

// Fetch pet details
$stmt = $conn->prepare('SELECT * FROM pets WHERE id = ?');
$stmt->bind_param('s', $pet_id);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

// Fetch medical records for the pet
$stmt = $conn->prepare('SELECT * FROM medical_records WHERE pet_id = ? ORDER BY visit_date DESC');
$stmt->bind_param('s', $pet_id);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records for <?php echo e($pet['name']); ?></title>
    <link rel="stylesheet" href="/clinic/style.css?v=1.1">
</head>
<body>
    <div class="container">
        <h1>Medical Records for <?php echo e($pet['name']); ?></h1>

        <div class="two-column-layout">
            <div class="column">
                <h2>Add New Medical Record</h2>
                <form action="?page=medical_records&pet_id=<?php echo e($pet_id); ?>" method="POST" class="form-wrapper">
                    <div class="form-group">
                        <label for="visit_date">Visit Date:</label>
                        <input type="date" id="visit_date" name="visit_date" required>
                    </div>

                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <textarea id="diagnosis" name="diagnosis" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="treatment">Treatment:</label>
                        <textarea id="treatment" name="treatment" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes"></textarea>
                    </div>

                    <button type="submit" name="add_record" class="btn btn-primary">Add Record</button>
                </form>
            </div>
            <div class="column">
                <h2>Medical History</h2>
                <div class="records-container">
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $record): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <strong>Visit Date:</strong> <?php echo e(date('F j, Y', strtotime($record['visit_date']))); ?>
                                </div>
                                <div class="record-body">
                                    <p><strong>Diagnosis:</strong> <?php echo e($record['diagnosis']); ?></p>
                                    <p><strong>Treatment:</strong> <?php echo e($record['treatment']); ?></p>
                                    <?php if (!empty($record['notes'])): ?>
                                        <p><strong>Notes:</strong> <?php echo e($record['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No medical records found for this pet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <p><a href="?page=pets">Back to Pets</a></p>
    </div>
</body>
</html>
