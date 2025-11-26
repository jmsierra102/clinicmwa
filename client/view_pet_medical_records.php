<?php
include_once '../db.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'client') {
    header('Location: /clinic/login.php');
    exit();
}

if (!isset($_GET['pet_id'])) {
    header('Location: ?page=medical_records');
    exit();
}

$pet_id = $_GET['pet_id'];
$user_id = $_SESSION['id'];

// Fetch pet details and verify ownership
$stmt = $conn->prepare('SELECT * FROM pets WHERE id = ? AND owner_id = ?');
$stmt->bind_param('ss', $pet_id, $user_id);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    // Pet not found or does not belong to the current user
    header('Location: ?page=medical_records');
    exit();
}

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

        <h2>Medical History</h2>
        <div class="records-container">
            <?php if (count($records) > 0): ?>
                <?php foreach ($records as $record): ?>
                    <div class="record-card">
                        <div class="record-header">
                            <strong>Visit Date:</strong> <?php echo e(date('F j, Y', strtotime($record['visit_date']))); ?>
                        </div>
                        <div class="record-body">
                            <p><strong>Diagnosis:</strong> <?php echo nl2br(e($record['diagnosis'])); ?></p>
                            <p><strong>Treatment:</strong> <?php echo nl2br(e($record['treatment'])); ?></p>
                            <?php if (!empty($record['notes'])): ?>
                                <p><strong>Notes:</strong> <?php echo nl2br(e($record['notes'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No medical records found for this pet.</p>
            <?php endif; ?>
        </div>

        <p><a href="?page=medical_records">Back to Medical Records</a></p>
    </div>
</body>
</html>
