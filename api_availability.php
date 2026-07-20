<?php
require_once 'db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD.']);
    exit;
}

$mechanics = $conn->query("SELECT * FROM mechanics ORDER BY id");

$result = [];

while ($mech = $mechanics->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE mechanic_id = ? AND appointment_date = ?");
    $stmt->bind_param("is", $mech['id'], $date);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $booked = (int)$row['cnt'];
    $max_cars = (int)$mech['max_cars'];

    $result[] = [
        'id' => $mech['id'],
        'name' => $mech['name'],
        'max_cars' => $max_cars,
        'booked' => $booked,
        'available' => $max_cars - $booked,
    ];
}

echo json_encode($result);