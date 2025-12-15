<?php
include('../config/db.php');
include('../includes/sellerheader.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = ""; // Initialize the message variable
$user_id = $_SESSION['user_id'];

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle profile update (existing code)
    if (!isset($_POST['change_password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        // Capture coordinates from visible text inputs
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;

        // Handle Logo Upload
        $logo_sql_part = "";
        $bind_types = "ssssidd";

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $logo_filename = basename($_FILES['logo']['name']);
            $target_dir = "../assets/images/logos/";
            $target_file = $target_dir . $logo_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                $logo_sql_part = ", logo = ?";
            } else {
                $message = "Error uploading logo file.";
            }
        }

        // Secure Update Query
        $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, latitude = ?, longitude = ? {$logo_sql_part} WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);

        // Prepare parameter array dynamically
        $params = [$name, $email, $phone, $address, $latitude, $longitude];

        if (!empty($logo_sql_part)) {
            $params[] = $logo_filename;
        }
        $params[] = $user_id;

        $ref_values = function ($arr) {
            $refs = [];
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        };

        $final_bind_string = ($logo_sql_part ? "ssssddsi" : "ssssddi");

        array_unshift($params, $final_bind_string);
        $bind_result = call_user_func_array([$update_stmt, 'bind_param'], $ref_values($params));

        if ($bind_result && $update_stmt->execute()) {
            $update_stmt->close();
            $message = "Profile updated successfully!";
        } else {
            $update_stmt->close();
            $message = "Error updating profile: " . $conn->error;
        }
    }

    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $message = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $message = "New password must be at least 6 characters long.";
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
}

// Fetch current user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<div class="container mt-5">
    <h2>Edit Your Vendor Profile</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo strpos($message, 'Error') !== false ? 'danger' : 'success'; ?> text-center">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="profile.php" method="POST" enctype="multipart/form-data" id="vendor-profile-form">

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name"
                value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone"
                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"
                required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3 border p-3 rounded bg-light">
            <label class="form-label fw-bold d-block mb-2">Restaurant Geo-Location</label>

            <button type="button" id="set-location-btn" class="btn btn-sm btn-info mb-3">
                <i class="fas fa-crosshairs"></i> Get Current Browser Location
            </button>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="latitude" class="form-label small">Latitude (Decimal)</label>
                    <input type="text" class="form-control" id="latitude" name="latitude" placeholder="e.g., 3.14582"
                        value="<?= htmlspecialchars($user['latitude'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="longitude" class="form-label small">Longitude (Decimal)</label>
                    <input type="text" class="form-control" id="longitude" name="longitude"
                        placeholder="e.g., 101.69123" value="<?= htmlspecialchars($user['longitude'] ?? ''); ?>">
                </div>
            </div>
            <small class="form-text text-muted">Use a map tool to find and enter exact coordinates if not using
                GPS.</small>
        </div>

        <div class="mb-3">
            <label for="logo" class="form-label">Restaurant Logo (for Map Pin)</label>
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
            <?php if (!empty($user['logo'])): ?>
                <small class="form-text text-muted">Current Logo: <?= htmlspecialchars($user['logo']); ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-warning">Update Profile</button>

        <!-- Password Change Section -->
        <hr class="my-4">
        <h4 class="mb-3">Change Password</h4>

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

        <button type="submit" name="change_password" id="change-password-btn" class="btn btn-primary mb-4" disabled>
            <i class="fas fa-key me-1"></i> Change Password
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Existing geolocation code
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const setLocationBtn = document.getElementById('set-location-btn');

        // Function to handle successful location retrieval
        const handleLocation = (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Update the visible TEXT input fields with the GPS coordinates
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);

            setLocationBtn.disabled = false;
            setLocationBtn.innerHTML = '<i class="fas fa-check"></i> Location Set!';

            // Reset button state after a delay
            setTimeout(() => {
                setLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Get Current Browser Location';
            }, 3000);
        };

        // Attach geolocation function to the button
        if (setLocationBtn) {
            setLocationBtn.addEventListener('click', () => {
                if (navigator.geolocation) {
                    setLocationBtn.disabled = true;
                    setLocationBtn.textContent = 'Fetching location...';

                    navigator.geolocation.getCurrentPosition(handleLocation, (error) => {
                        alert('Error getting location. Please allow browser location access: ' + error.message);
                        setLocationBtn.disabled = false;
                        setLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Get Current Browser Location';
                    });
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            });
        }

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

            // Show password strength
            newPassword.addEventListener('keyup', function () {
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) strength++;

                // Update a strength indicator
                const strengthText = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'][strength] || 'Very Weak';
                const strengthColors = ['danger', 'danger', 'warning', 'success', 'success'];
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

        // Toggle password visibility
        function addPasswordToggle() {
            const toggleIcon = '<i class="fas fa-eye"></i>';
            const toggleIconSlash = '<i class="fas fa-eye-slash"></i>';

            [currentPassword, newPassword, confirmPassword].forEach((input, index) => {
                if (!input) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'btn btn-outline-secondary';
                toggleBtn.type = 'button';
                toggleBtn.innerHTML = toggleIcon;
                toggleBtn.style.borderTopLeftRadius = '0';
                toggleBtn.style.borderBottomLeftRadius = '0';

                const div = document.createElement('div');
                div.className = 'input-group-append';
                div.appendChild(toggleBtn);
                wrapper.appendChild(div);

                toggleBtn.addEventListener('click', function () {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? toggleIcon : toggleIconSlash;
                });
            });
        }

        addPasswordToggle();
    });
</script>

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

    #new_password:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    #confirm_password:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Optional: Strength meter */
    .password-strength-meter {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
        background-color: #e9ecef;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width 0.3s ease, background-color 0.3s ease;
    }

    .strength-very-weak {
        width: 25%;
        background-color: #dc3545;
    }

    .strength-weak {
        width: 50%;
        background-color: #fd7e14;
    }

    .strength-fair {
        width: 75%;
        background-color: #ffc107;
    }

    .strength-strong {
        width: 100%;
        background-color: #28a745;
    }

    .strength-very-strong {
        width: 100%;
        background-color: #198754;
    }
</style>

<?php include('../includes/footer.php'); ?>