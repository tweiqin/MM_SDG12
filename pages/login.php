<?php
include('../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Both fields are required.";
    } else {
        // Fetch the is_active status
        $query = "SELECT user_id, role, password, is_active FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // Check if the account is active/blocked
                if ($user['is_active'] == 0) {
                    $message = "Your account has been blocked. Please contact customer support.";
                } elseif ($user['is_active'] == 2) {
                    $message = "Your account is pending approval. Please wait for the admin to verify your details.";
                } else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } elseif ($user['role'] == 'seller') {
                        header("Location: ../seller/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                }
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "Email not found.";
        }
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo $message; ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    });
</script>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/end.php'); ?>