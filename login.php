<?php
require_once 'db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = 'Please enter both username and password.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: admin.php');
            exit;
        } else {
            $message = 'Invalid username or password.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Workshop - Admin Login</title>
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
            <a href="login.php">&#128274; Admin Login</a>
        </div>
    </div>
</div>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-header">
            <div class="login-icon">&#128274;</div>
            <h1>Admin Login</h1>
            <p>Sign in to manage appointments</p>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="alert-icon">&#9888;</span>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter admin password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">&#128274; Sign In</button>
        </form>


    </div>
</div>

</body>
</html>
