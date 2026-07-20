<?php
require_once 'db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = trim($_POST['client_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $car_license = trim($_POST['car_license'] ?? '');
    $car_engine = trim($_POST['car_engine'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? '';
    $mechanic_id = (int)($_POST['mechanic_id'] ?? 0);

    $errors = [];

    if ($client_name === '') $errors[] = 'Name is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($phone === '') $errors[] = 'Phone is required.';
    elseif (!ctype_digit($phone)) $errors[] = 'Phone must contain only digits.';
    if ($car_license === '') $errors[] = 'Car license number is required.';
    if ($car_engine === '') $errors[] = 'Car engine number is required.';
    elseif (!ctype_digit($car_engine)) $errors[] = 'Car engine number must contain only digits.';
    if ($appointment_date === '') $errors[] = 'Appointment date is required.';
    if ($mechanic_id <= 0) $errors[] = 'Please select a mechanic.';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM appointments WHERE car_license = ? AND appointment_date = ?");
        $stmt->bind_param("ss", $car_license, $appointment_date);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = 'This vehicle already has an appointment booked on this date.';
            $message_type = 'error';
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE mechanic_id = ? AND appointment_date = ?");
            $stmt->bind_param("is", $mechanic_id, $appointment_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt2 = $conn->prepare("SELECT max_cars FROM mechanics WHERE id = ?");
            $stmt2->bind_param("i", $mechanic_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $mech = $result2->fetch_assoc();

            if ($row['cnt'] >= $mech['max_cars']) {
                $message = 'This mechanic is fully booked on this date. Please select another mechanic or date.';
                $message_type = 'error';
                $stmt->close();
                $stmt2->close();
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO appointments (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("ssssssi", $client_name, $address, $phone, $car_license, $car_engine, $appointment_date, $mechanic_id);

                if ($insert_stmt->execute()) {
                    $message = 'Appointment booked successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Something went wrong. Please try again.';
                    $message_type = 'error';
                }
                $insert_stmt->close();
                $stmt->close();
                $stmt2->close();
            }
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Book Appointment</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-header">
    <div class="header-inner">
        <div class="logo">
            <div class="logo-icon">&#9881;</div>
            <div class="logo-text">
                <h1>Car Workshop</h1>
                <p class="subtitle">Book your appointment with your preferred mechanic</p>
            </div>
        </div>
        <div class="nav-links">
            <a href="index.php">&#128197; Book Appointment</a>
            <a href="login.php">&#128274; Admin Panel</a>
        </div>
    </div>
</div>

<div class="container">

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <span class="alert-icon"><?php echo $message_type === 'success' ? '&#9989;' : '&#9888;'; ?></span>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-icon">&#128664;</div>
            <h2>Available Mechanics &amp; Slots</h2>
        </div>

        <div class="date-selector">
            <label for="avail_date">&#128197; Select Date:</label>
            <input type="date" id="avail_date" name="avail_date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="mechanic-slots" id="mechanic-slots">
            <div class="loading-dots"><span></span><span></span><span></span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon">&#9998;</div>
            <h2>Book Your Appointment</h2>
        </div>

        <form method="POST" action="index.php" onsubmit="return validateForm()">

            <div class="form-section">
                <div class="form-section-title">&#128100; Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="client_name">Full Name</label>
                        <input type="text" id="client_name" name="client_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" placeholder="Enter your address" required></textarea>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">&#128663; Vehicle Details</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="car_license">Car License Number</label>
                        <input type="text" id="car_license" name="car_license" placeholder="e.g. ABC-1234" required>
                    </div>
                    <div class="form-group">
                        <label for="car_engine">Car Engine Number</label>
                        <input type="text" id="car_engine" name="car_engine" placeholder="Enter engine number" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">&#128197; Appointment Details</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date">Appointment Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="mechanic_id">Select Mechanic</label>
                        <select id="mechanic_id" name="mechanic_id" required>
                            <option value="">-- Select Mechanic --</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">&#128197; Book Appointment</button>
        </form>
    </div>

    <div class="help-box">
        <h3>&#128161; Help / Instructions</h3>
        <ul>
            <li>Select a date above to see real-time mechanic availability.</li>
            <li>Each mechanic can handle up to 4 appointments per day.</li>
            <li>You can book multiple cars on the same day, but each car license can only be booked once per date.</li>
            <li>Phone and Engine number must contain only digits.</li>
            <li>Appointment date must be today or a future date.</li>
        </ul>
    </div>

</div>

<script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>