<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header('Location: ../pages/login.php');
    exit;
}

include '../includes/buyerheader.php';


$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['change_password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'] ?? null;

    // Use Prepared Statement for secure update
    $update_query = "UPDATE users SET name = ?, email = ?, address = ?, phone = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);

    $update_stmt->bind_param("ssssi", $name, $email, $address, $phone, $user_id);

    if ($update_stmt->execute()) {
        $update_stmt->close();
        $message = "Profile updated successfully!";
    } else {
        $update_stmt->close();
        $message = "Error updating profile: " . $conn->error;
    }
}

// --- Handle Password Change ---
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $message = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $message = "Password must contain at least one number.";
    } elseif (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $new_password)) {
        $message = "Password must contain at least one special character (!@#$%^&* etc).";
    } else {
        // Verify current password
        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $hashed_password)) {
            // Hash new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $update_pass_query = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_pass_stmt = $conn->prepare($update_pass_query);
            $update_pass_stmt->bind_param("si", $new_hashed_password, $user_id);

            if ($update_pass_stmt->execute()) {
                $message = "Password changed successfully!";
                // Clear password fields in session
                $_SESSION['password_changed'] = true;
            } else {
                $message = "Error updating password: " . $conn->error;
            }
            $update_pass_stmt->close();
        } else {
            $message = "Current password is incorrect.";
        }
    }
}

// Fetch current user data (RE-FETCH after potential update)
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();
?>

<div class="container my-5">
    <h2 class="text-center mb-4">Manage Your Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?> text-center">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Profile Update Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="POST" id="profile-form">
                        <input type="hidden" name="user_id" value="<?= $user['user_id']; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="<?= htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    value="<?= htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    value="<?= htmlspecialchars($user['phone']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" id="address" class="form-control"
                                    value="<?= htmlspecialchars($user['address']); ?>" required>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Change Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="POST" id="password-form">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                placeholder="Enter your current password" autocomplete="current-password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                placeholder="Enter new password" autocomplete="new-password">
                            <div class="password-requirements mt-2">
                                <small class="form-text text-muted">Password must contain:</small>
                                <div class="requirements-list">
                                    <div class="requirement" data-type="length">
                                        <span class="requirement-icon"><i class="fas fa-times text-danger"></i></span>
                                        <span class="requirement-text">At least 8 characters</span>
                                    </div>
                                    <div class="requirement" data-type="uppercase">
                                        <span class="requirement-icon"><i class="fas fa-times text-danger"></i></span>
                                        <span class="requirement-text">One uppercase letter (A-Z)</span>
                                    </div>
                                    <div class="requirement" data-type="number">
                                        <span class="requirement-icon"><i class="fas fa-times text-danger"></i></span>
                                        <span class="requirement-text">One number (0-9)</span>
                                    </div>
                                    <div class="requirement" data-type="special">
                                        <span class="requirement-icon"><i class="fas fa-times text-danger"></i></span>
                                        <span class="requirement-text">One special character (!@#$%^&*)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Re-enter new password" autocomplete="new-password">
                            <div id="password-match-feedback" class="mt-2" style="display: none;">
                                <small class="form-text"></small>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="change_password" id="change-password-btn"
                                class="btn btn-primary px-4" disabled>
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .password-requirements {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px;
        border-left: 4px solid #dee2e6;
    }

    .requirements-list {
        margin-top: 5px;
    }

    .requirement {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        padding: 3px 0;
        transition: all 0.3s ease;
    }

    .requirement-icon {
        width: 20px;
        margin-right: 8px;
        text-align: center;
    }

    .requirement-text {
        font-size: 0.85rem;
        transition: color 0.3s ease;
    }

    .requirement-met .requirement-text {
        color: #198754;
        font-weight: 500;
    }

    .requirement-not-met .requirement-text {
        color: #dc3545;
    }

    .card {
        border-radius: 10px;
    }

    .card-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        border-bottom: 2px solid #e9ecef;
    }

    #new_password:focus,
    #confirm_password:focus,
    #current_password:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    #change-password-btn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Password validation functionality
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const changePasswordBtn = document.getElementById('change-password-btn');
        const passwordMatchFeedback = document.getElementById('password-match-feedback');

        // Password requirement checks
        const requirements = {
            length: /.{8,}/,
            uppercase: /[A-Z]/,
            number: /[0-9]/,
            special: /[!@#$%^&*()\-_=+{};:,<.>]/
        };

        // Update requirement indicator
        function updateRequirement(type, isValid) {
            const requirementElement = document.querySelector(`.requirement[data-type="${type}"]`);
            if (requirementElement) {
                const icon = requirementElement.querySelector('.requirement-icon i');
                const text = requirementElement.querySelector('.requirement-text');

                if (isValid) {
                    icon.className = 'fas fa-check text-success';
                    text.style.color = '#198754';
                    requirementElement.classList.add('requirement-met');
                    requirementElement.classList.remove('requirement-not-met');
                } else {
                    icon.className = 'fas fa-times text-danger';
                    text.style.color = '#dc3545';
                    requirementElement.classList.add('requirement-not-met');
                    requirementElement.classList.remove('requirement-met');
                }
            }
        }

        // Check all requirements
        function checkAllRequirements(password) {
            let allValid = true;

            for (const [type, regex] of Object.entries(requirements)) {
                const isValid = regex.test(password);
                updateRequirement(type, isValid);
                if (!isValid) allValid = false;
            }

            return allValid;
        }

        // Check password match
        function checkPasswordMatch() {
            const password = newPassword.value;
            const confirm = confirmPassword.value;

            if (confirm === '') {
                passwordMatchFeedback.style.display = 'none';
                return false;
            }

            if (password === confirm) {
                passwordMatchFeedback.style.display = 'block';
                passwordMatchFeedback.innerHTML = '<small class="form-text text-success"><i class="fas fa-check-circle me-1"></i> Passwords match</small>';
                confirmPassword.style.borderColor = '#198754';
                return true;
            } else {
                passwordMatchFeedback.style.display = 'block';
                passwordMatchFeedback.innerHTML = '<small class="form-text text-danger"><i class="fas fa-times-circle me-1"></i> Passwords do not match</small>';
                confirmPassword.style.borderColor = '#dc3545';
                return false;
            }
        }

        // Enable/disable change password button
        function updateSubmitButton() {
            const currentPassFilled = currentPassword.value.trim() !== '';
            const newPassValid = checkAllRequirements(newPassword.value);
            const passwordsMatch = checkPasswordMatch();
            const confirmFilled = confirmPassword.value.trim() !== '';

            if (currentPassFilled && newPassValid && passwordsMatch && confirmFilled) {
                changePasswordBtn.disabled = false;
                changePasswordBtn.classList.remove('btn-secondary');
                changePasswordBtn.classList.add('btn-primary');
            } else {
                changePasswordBtn.disabled = true;
                changePasswordBtn.classList.remove('btn-primary');
                changePasswordBtn.classList.add('btn-secondary');
            }
        }

        // Add event listeners for real-time validation
        if (newPassword) {
            newPassword.addEventListener('input', function () {
                checkAllRequirements(this.value);
                checkPasswordMatch();
                updateSubmitButton();
            });
        }

        if (confirmPassword) {
            confirmPassword.addEventListener('input', function () {
                checkPasswordMatch();
                updateSubmitButton();
            });
        }

        if (currentPassword) {
            currentPassword.addEventListener('input', updateSubmitButton);
        }

        // Initialize
        updateSubmitButton();

        // Password visibility toggle
        function addPasswordToggle(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'input-group';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'btn btn-outline-secondary';
            toggleBtn.type = 'button';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.style.borderTopLeftRadius = '0';
            toggleBtn.style.borderBottomLeftRadius = '0';

            const div = document.createElement('div');
            div.className = 'input-group-append';
            div.appendChild(toggleBtn);
            wrapper.appendChild(div);

            toggleBtn.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        // Add toggle to all password fields
        addPasswordToggle('current_password');
        addPasswordToggle('new_password');
        addPasswordToggle('confirm_password');
    });
</script>

<?php include '../includes/footer.php'; ?>