<?php
include('../includes/adminheader.php');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $error = "";

    $query = "SELECT user_id, name, password FROM users WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['user_id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div class="container mt-5 mb-5">
    <div class="login-container">
        <h2 class="text-center">Admin Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="adminlogin.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required
                    placeholder="Enter your email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required
                    placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-warning btn-login">Login</button>
        </form>
    </div>
</div>