<?php
// get_available_slots.php
require_once 'db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';

if (empty($date)) {
    echo json_encode([]);
    exit;
}

// Get all possible time slots
$start = new DateTime('8:00');
$end = new DateTime('17:00');
$interval = new DateInterval('PT30M');
$times = new DatePeriod($start, $interval, $end);

$all_slots = [];
foreach ($times as $time) {
    $time_formatted = $time->format('H:i');
    if ($time_formatted >= '12:00' && $time_formatted < '13:00') continue;
    $all_slots[$time_formatted] = $time->format('h:i A');
}

// Get booked slots for the selected date
$sql = "SELECT DATE_FORMAT(appointment_date, '%H:%i') as time FROM appointments WHERE DATE(appointment_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = $row['time'];
}

// Determine available slots
$available_slots = [];
foreach ($all_slots as $value => $label) {
    if (!in_array($value, $booked_slots)) {
        $available_slots[] = ['value' => $value, 'label' => $label];
    }
}

echo json_encode($available_slots);
