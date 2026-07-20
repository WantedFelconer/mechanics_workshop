<?php
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $new_date = $_POST['appointment_date'];
    $new_mechanic_id = (int)$_POST['mechanic_id'];

    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE mechanic_id = ? AND appointment_date = ? AND id != ?");
    $stmt->bind_param("isi", $new_mechanic_id, $new_date, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt2 = $conn->prepare("SELECT max_cars FROM mechanics WHERE id = ?");
    $stmt2->bind_param("i", $new_mechanic_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $mech = $result2->fetch_assoc();

    if ($row['cnt'] >= $mech['max_cars']) {
        $message = 'Selected mechanic is fully booked on this date. Choose another mechanic or date.';
        $message_type = 'error';
        $stmt->close();
        $stmt2->close();
    } else {
        $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, mechanic_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $new_date, $new_mechanic_id, $id);
        if ($stmt->execute()) {
            $message = 'Appointment updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to update appointment.';
            $message_type = 'error';
        }
        $stmt->close();
        $stmt2->close();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Appointment deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to delete appointment.';
        $message_type = 'error';
    }
    $stmt->close();
}

$total = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
$today = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['c'];
$future = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date > CURDATE()")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-header">
    <div class="header-inner">
        <div class="logo">
            <div class="logo-icon">&#9881;</div>
            <div class="logo-text">
                <h1>Car Workshop</h1>
                <p class="subtitle">Admin Panel</p>
            </div>
        </div>
        <div class="nav-links">
            <a href="index.php">&#128197; Book Appointment</a>
            <a href="admin.php">&#128196; Admin Panel</a>
            <a href="logout.php" class="logout-link">&#128682; Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a>
        </div>
    </div>
</div>

<div class="container-wide">

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <span class="alert-icon"><?php echo $message_type === 'success' ? '&#9989;' : '&#9888;'; ?></span>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <div class="stats-bar">
        <div class="stat-item stat-total">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total Appointments</div>
        </div>
        <div class="stat-item stat-today">
            <div class="stat-number"><?php echo $today; ?></div>
            <div class="stat-label">Today</div>
        </div>
        <div class="stat-item stat-future">
            <div class="stat-number"><?php echo $future; ?></div>
            <div class="stat-label">Upcoming</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon">&#128196;</div>
            <h2>Appointment List</h2>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Phone</th>
                        <th>Car License</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Mechanic</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT a.*, m.name as mechanic_name FROM appointments a JOIN mechanics m ON a.mechanic_id = m.id ORDER BY a.appointment_date DESC, a.id DESC");

                    if ($result->num_rows === 0):
                    ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">&#128203;</div>
                                    <p>No appointments found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $today_dt = date('Y-m-d'); ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            if ($row['appointment_date'] === $today_dt) {
                                $badge = '<span class="badge badge-today">Today</span>';
                            } elseif ($row['appointment_date'] > $today_dt) {
                                $badge = '<span class="badge badge-upcoming">Upcoming</span>';
                            } else {
                                $badge = '<span class="badge badge-past">Past</span>';
                            }
                            ?>
                            <tr>
                                <td style="font-weight:600;color:var(--gray);"><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['car_license']); ?></td>
                                <td style="white-space:nowrap;"><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?php echo $badge; ?></td>
                                <td><?php echo htmlspecialchars($row['mechanic_name']); ?></td>
                                <td>
                                    <form method="POST" action="admin.php" class="edit-form">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="date" name="appointment_date" value="<?php echo $row['appointment_date']; ?>" required style="width:130px;">
                                        <select name="mechanic_id" required style="width:130px;">
                                            <?php
                                            $mechanics = $conn->query("SELECT * FROM mechanics");
                                            while ($mech = $mechanics->fetch_assoc()):
                                            ?>
                                                <option value="<?php echo $mech['id']; ?>" <?php echo $mech['id'] == $row['mechanic_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($mech['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button type="submit" name="update" class="btn btn-small btn-success">&#128260; Update</button>
                                        <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this appointment?')">&#128465; Delete</a>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="help-box">
        <h3>&#128161; Help / Instructions</h3>
        <ul>
            <li>This panel shows all booked appointments sorted by date.</li>
            <li>To change the appointment date or mechanic, modify the fields and click <strong>Update</strong>.</li>
            <li>When updating, the system checks if the selected mechanic has available slots on the new date.</li>
            <li>Click <strong>Delete</strong> to remove an appointment.</li>
        </ul>
    </div>

</div>

</body>
</html>
<?php $conn->close(); ?>