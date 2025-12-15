<?php
include('../config/db.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$message = "";

$latitude = null;
$longitude = null;
$logo_filename = null;

$phone = $_POST['phone'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'buyer';

function format_phone_local_for_display($phone_full)
{
    if (!$phone_full)
        return '';
    $p = str_replace(' ', '', $phone_full);
    if (strpos($p, '+60') === 0) {
        return substr($p, 3);
    }
    return $p;
}

// Server-side validators
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Server expects phone stored as "+60" followed by 9 or 10 digits depending on rule; we'll accept +60 + 9 or 10 (consistent with regex below)
function is_valid_phone_server($phoneFull)
{
    return preg_match('/^\+60(1[0-9]\d{7,8})$/', $phoneFull);
}

// Name: letters, spaces, apostrophe, and @ only
function is_valid_name($name)
{
    return preg_match("/^[A-Za-z@' ]+$/", $name);
}

// Password: min 8 chars, at least one uppercase, one number, one symbol
function is_strong_password($pw)
{
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $pw);
}

// POST handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // sanitize minimal
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'buyer';

    // hidden phone comes as "+60123456789"
    $phone = trim($_POST['phone'] ?? '');

    if ($role === 'seller') {
        $latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : null;
        $longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : null;
    }

    // basic checks
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "All core fields are required.";
    } elseif (!is_valid_name($name)) {
        $message = "Name may only include letters, spaces, apostrophe (') and @.";
    } elseif (!is_valid_email($email)) {
        $message = "Please enter a valid email address.";
    } elseif (!is_valid_phone_server($phone)) {
        $message = "Phone format invalid. Expected +60 followed by a valid mobile number.";
    } elseif (!is_strong_password($password)) {
        $message = "Password must be at least 8 characters and include uppercase letters, numbers and symbols.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        if ($role === 'seller' && ($latitude === '' || $longitude === '')) {
            $message = "As a seller, you must provide your restaurant's latitude and longitude.";
        }
    }

    // handle logo upload if seller
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {

        $logo_filename = basename($_FILES['logo']['name']);
        $target_dir = __DIR__ . "/../assets/images/logos/";
        $target_file = $target_dir . $logo_filename;

        // Check if the directory exists and create it if necessary (for XAMPP uploads)
        if (!is_dir($target_dir)) {
            // Attempt to create the directory with write permissions
            if (!mkdir($target_dir, 0755, true)) {
                $message = "Error: Cannot create logos directory. Check permissions.";
            }
        }

        // Attempt to move the file
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $message = "Error uploading logo file. Check folder permissions for: " . $target_file;
        }
    }

    // DB: check email and insert
    if (empty($message)) {
        $check_query = "SELECT user_id FROM users WHERE email = ?";
        if ($check_stmt = $conn->prepare($check_query)) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $message = "Email is already registered.";
            }
            $check_stmt->close();
        } else {
            $message = "Database error (prepare failed).";
        }
    }

    if (empty($message)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Default active checks
        $is_active = ($role === 'seller') ? 2 : 1; // 2 = Pending, 1 = Active

        $insert_query = "INSERT INTO users (name, email, phone, address, password, role, latitude, longitude, logo, is_active) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($insert_stmt = $conn->prepare($insert_query)) {
            $lat_param = ($latitude !== null && $latitude !== '') ? $latitude : null;
            $lng_param = ($longitude !== null && $longitude !== '') ? $longitude : null;
            $logo_param = $logo_filename !== null ? $logo_filename : null;
            $insert_stmt->bind_param("sssssssssi", $name, $email, $phone, $address, $hashed_password, $role, $lat_param, $lng_param, $logo_param, $is_active);
            if ($insert_stmt->execute()) {
                $insert_stmt->close();
                header("Location: login.php");
                exit;
            } else {
                $message = "Error: " . $insert_stmt->error;
                $insert_stmt->close();
            }
        } else {
            $message = "Database error (prepare failed).";
        }
    }
}

// visible phone local = user-typed digits (no +60, no spaces)
$phone_display_local = format_phone_local_for_display($phone);
?>

<?php include('../includes/header.php'); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .blocked-container {
        opacity: 0.5;
        pointer-events: none;
        user-select: none;
    }

    .unblocked-container {
        opacity: 1;
        pointer-events: auto;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: none !important;
    }

    .is-valid {
        border-color: #198754 !important;
        box-shadow: none !important;
    }

    .error-message {
        display: block;
        min-height: 1em;
    }

    .input-group .btn-toggle {
        border-left: 0;
    }
</style>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-dark text-white text-center">
                    <h3>Register</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" id="registration-form" enctype="multipart/form-data"
                        novalidate>

                        <div class="mb-3 blocked-container" id="name_group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" required
                                value="<?= htmlspecialchars($name); ?>">
                            <small class="text-danger error-message" id="error-name"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="phone_group">
                            <label for="phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text">+60</span>
                                <input type="text" class="form-control" id="phone" placeholder="e.g., 12xxxxxxxx"
                                    autocomplete="tel" inputmode="numeric"
                                    value="<?= htmlspecialchars($phone_display_local); ?>">
                            </div>
                            <input type="hidden" name="phone" id="phone_full" value="<?= htmlspecialchars($phone); ?>">
                            <small class="form-text text-muted">Type digits only. Example: 123456789</small>
                            <small class="text-danger error-message" id="error-phone"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="email_group">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" name="email" id="email" required
                                value="<?= htmlspecialchars($email); ?>">
                            <small class="text-danger error-message" id="error-email"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="address_group">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address"
                                required><?= htmlspecialchars($address); ?></textarea>
                            <small class="text-danger error-message" id="error-address"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="password_group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary btn-toggle" type="button"
                                    data-target="#password" aria-label="Toggle password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-danger error-message" id="error-password"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="confirm_password_group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                                <button class="btn btn-outline-secondary btn-toggle" type="button"
                                    data-target="#confirm_password" aria-label="Toggle confirm password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-danger error-message" id="error-confirm_password"></small>
                        </div>

                        <div class="mb-3 blocked-container" id="role_group">
                            <label for="role" class="form-label">Account Type</label>
                            <select class="form-control" name="role" id="role" required>
                                <option value="" disabled selected>-- Select Account Type --</option>
                                <option value="buyer" <?= ($role == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                                <option value="seller" <?= ($role == 'seller') ? 'selected' : ''; ?>>Seller
                                    (Hotel/Restaurant/Cafe)</option>
                            </select>
                            <small class="text-danger error-message" id="error-role"></small>
                        </div>

                        <div id="seller-location-fields" class="mb-3 border p-3 rounded bg-light blocked-container"
                            style="display:none;">
                            <label class="form-label fw-bold d-block mb-2">Restaurant Geo-Location</label>

                            <div class="mb-3" id="logo_group">
                                <label for="logo" class="form-label">Restaurant Logo (500×500 px)</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*" disabled>
                                <small class="form-text text-muted">Max file size 2MB. Used for map pins.</small>
                                <small class="text-danger error-message" id="error-logo"></small>
                            </div>

                            <button type="button" id="set-location-btn" class="btn btn-sm btn-info mb-3" disabled>
                                <i class="fas fa-crosshairs"></i> Get Current Location
                            </button>

                            <div class="row">
                                <div class="col-md-6 mb-3" id="latitude_group">
                                    <label for="latitude" class="form-label small">Latitude (Decimal)</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude"
                                        placeholder="e.g., 3.14582" value="<?= htmlspecialchars($latitude ?? ''); ?>"
                                        disabled>
                                    <small class="text-danger error-message" id="error-latitude"></small>
                                </div>
                                <div class="col-md-6 mb-3" id="longitude_group">
                                    <label for="longitude" class="form-label small">Longitude (Decimal)</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                        placeholder="e.g., 101.69123" value="<?= htmlspecialchars($longitude ?? ''); ?>"
                                        disabled>
                                    <small class="text-danger error-message" id="error-longitude"></small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100" id="register-btn" disabled>Register</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Sequence (visible phone is 'phone', hidden submit is 'phone_full')
        const fieldSequence = [
            { id: 'name', isMandatory: true },
            { id: 'phone', isMandatory: true },
            { id: 'email', isMandatory: true },
            { id: 'address', isMandatory: true },
            { id: 'password', isMandatory: true },
            { id: 'confirm_password', isMandatory: true },
            { id: 'role', isMandatory: true }
        ];

        const roleSelect = document.getElementById('role');
        const locationFieldsContainer = document.getElementById('seller-location-fields');
        const setLocationBtn = document.getElementById('set-location-btn');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const logoInput = document.getElementById('logo');
        const publishButton = document.getElementById('register-btn');

        const visiblePhoneInput = document.getElementById('phone');      // user types digits only
        const hiddenPhoneInput = document.getElementById('phone_full'); // e.g +60123456789

        // touched tracking (show errors only after blur/touch)
        const touched = {};
        function markTouched(id) { touched[id] = true; }
        function isTouched(id) { return !!touched[id]; }

        // helpers to block/unblock containers
        function blockContainer(containerId) {
            const c = document.getElementById(containerId);
            if (!c) return;
            c.classList.remove('unblocked-container');
            c.classList.add('blocked-container');
            const elems = c.querySelectorAll('input, select, textarea, button');
            elems.forEach(el => el.setAttribute('disabled', true));
        }
        function unblockContainer(containerId) {
            const c = document.getElementById(containerId);
            if (!c) return;
            c.classList.remove('blocked-container');
            c.classList.add('unblocked-container');
            const elems = c.querySelectorAll('input, select, textarea, button');
            elems.forEach(el => el.removeAttribute('disabled'));
        }

        // validation rules
        function isNameValid(v) { return /^[A-Za-z@' ]+$/.test(v); }
        function isPhoneLocalValid(v) {
            // v should be digits only (we remove dashes/spaces on input)
            return /^(1[0-9])(\d{7,8})$/.test(v);
        }
        function isEmailValid(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
        function isAddressValid(v) { return v.trim().length >= 5; }
        function isPasswordStrong(v) { return /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/.test(v); }
        function isDecimal(v) { if (v === null || v === undefined) return false; v = v.toString().trim(); if (v === '') return false; return !isNaN(v); }

        function setErrorById(fieldId, message) {
            const el = document.getElementById(fieldId);
            const err = document.getElementById('error-' + (fieldId === 'phone' ? 'phone' : fieldId));
            if (el) el.classList.add('is-invalid'); el && el.classList.remove('is-valid');
            if (err && message && isTouched(fieldId)) err.textContent = message; else if (err) err.textContent = '';
            const container = el ? el.closest('.mb-3') : null;
            if (container) { container.classList.remove('blocked-container'); container.classList.add('unblocked-container'); }
        }
        function clearErrorById(fieldId) {
            const el = document.getElementById(fieldId);
            const err = document.getElementById('error-' + (fieldId === 'phone' ? 'phone' : fieldId));
            if (el) { el.classList.remove('is-invalid'); el.classList.add('is-valid'); }
            if (err) err.textContent = '';
        }

        function validateFieldById(fieldId) {
            const el = document.getElementById(fieldId);
            if (!el) return true;
            const val = (el.value || '').trim();

            if (fieldId === 'name') {
                if (!isNameValid(val)) { setErrorById('name', "Name may only contain letters, spaces, apostrophe (') and @."); return false; }
                clearErrorById('name'); return true;
            }
            if (fieldId === 'phone') {
                // visible phone local part should be digits only; validate with isPhoneLocalValid
                if (!isPhoneLocalValid(val)) { setErrorById('phone', "Phone invalid. Start with 1x then 7–8 digits (e.g. 12 + 7–8 digits)."); return false; }
                clearErrorById('phone'); return true;
            }
            if (fieldId === 'email') {
                if (!isEmailValid(val)) { setErrorById('email', "Please enter a valid email."); return false; }
                clearErrorById('email'); return true;
            }
            if (fieldId === 'address') {
                if (!isAddressValid(val)) { setErrorById('address', "Address must be at least 5 characters."); return false; }
                clearErrorById('address'); return true;
            }
            if (fieldId === 'password') {
                if (!isPasswordStrong(val)) { setErrorById('password', "Password must be ≥8 chars, include uppercase, number and symbol."); return false; }
                clearErrorById('password'); return true;
            }
            if (fieldId === 'confirm_password') {
                const pw = document.getElementById('password').value || '';
                if (val !== pw) { setErrorById('confirm_password', "Passwords do not match."); return false; }
                clearErrorById('confirm_password'); return true;
            }
            if (fieldId === 'role') {
                if (!val) { setErrorById('role', "Please select an account type."); return false; }
                clearErrorById('role'); return true;
            }
            if (fieldId === 'latitude') {
                if (roleSelect.value === 'seller') {
                    if (!isDecimal(val)) { setErrorById('latitude', "Latitude must be a decimal number."); return false; }
                    clearErrorById('latitude'); return true;
                } else { clearErrorById('latitude'); return true; }
            }
            if (fieldId === 'longitude') {
                if (roleSelect.value === 'seller') {
                    if (!isDecimal(val)) { setErrorById('longitude', "Longitude must be a decimal number."); return false; }
                    clearErrorById('longitude'); return true;
                } else { clearErrorById('longitude'); return true; }
            }
            if (fieldId === 'logo') {
                if (logoInput && logoInput.files && logoInput.files.length > 0) {
                    const f = logoInput.files[0]; const max = 2 * 1024 * 1024;
                    if (f.size > max) { setErrorById('logo', 'Logo must be <= 2MB.'); return false; }
                    if (!f.type.startsWith('image/')) { setErrorById('logo', 'Logo must be an image.'); return false; }
                }
                clearErrorById('logo'); return true;
            }

            return true;
        }

        // remove spaces and dashes immediately and keep digits only for phone input
        visiblePhoneInput.addEventListener('input', function (e) {
            let v = this.value || '';
            // remove spaces and dashes
            v = v.replace(/[\s-]/g, '');
            // remove any non-digit char
            v = v.replace(/\D/g, '');
            // update field (cursor may jump but acceptable for numeric input)
            this.value = v;
            // revalidate
            if (isTouched('phone')) validateFieldById('phone');
            updateFieldState();
        });

        // phone blur marks touched
        visiblePhoneInput.addEventListener('blur', function () {
            markTouched('phone');
            validateFieldById('phone');
            updateFieldState();
        });

        // password toggle buttons
        document.querySelectorAll('.btn-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetSel = this.getAttribute('data-target');
                const target = document.querySelector(targetSel) || document.querySelector(targetSel.replace(/^#/, ''));
                // buttons use data-target "#password" etc. but earlier markup uses id directly; handle both
                let inputEl = null;
                if (target && target.tagName === 'INPUT') inputEl = target;
                else {
                    // fallback: find by id without '#'
                    const id = this.getAttribute('data-target').replace('#', '');
                    inputEl = document.getElementById(id);
                }
                if (!inputEl) return;
                if (inputEl.type === 'password') {
                    inputEl.type = 'text';
                    this.querySelector('i').classList.remove('bi-eye'); this.querySelector('i').classList.add('bi-eye-slash');
                } else {
                    inputEl.type = 'password';
                    this.querySelector('i').classList.remove('bi-eye-slash'); this.querySelector('i').classList.add('bi-eye');
                }
            });
        });

        // other fields: mark touched on blur and validate
        const allInputs = document.querySelectorAll('#registration-form input, #registration-form textarea, #registration-form select');
        allInputs.forEach(input => {
            if (input.id === 'phone') return; // phone handled separately
            input.addEventListener('blur', (e) => {
                markTouched(input.id);
                validateFieldById(input.id);
                updateFieldState();
            });
            input.addEventListener('input', (e) => {
                if (input.disabled) return;
                // validate but show error only if touched
                validateFieldById(input.id);
                updateFieldState();
            });
            input.addEventListener('change', (e) => {
                markTouched(input.id);
                validateFieldById(input.id);
                updateFieldState();
            });
        });

        // update blocking/unblocking based on validation of previous
        function updateFieldState() {
            let allMandatoryValid = true;

            for (let i = 0; i < fieldSequence.length; i++) {
                const field = fieldSequence[i];
                const currentInput = document.getElementById(field.id);
                if (!currentInput) continue;

                let prevValid = true;
                if (i > 0) {
                    const prevId = fieldSequence[i - 1].id;
                    prevValid = validateFieldById(prevId);
                }
                const container = currentInput.closest('.mb-3');
                if (prevValid) {
                    if (container) unblockContainer(container.id);
                } else {
                    if (container) blockContainer(container.id);
                    if (field.isMandatory) allMandatoryValid = false;
                    continue;
                }
                if (field.isMandatory) {
                    const ok = validateFieldById(field.id);
                    if (!ok) allMandatoryValid = false;
                }
            }

            // seller block
            if (roleSelect.value === 'seller') {
                locationFieldsContainer.style.display = 'block';
                // only unblock seller block if role is valid
                if (validateFieldById('role')) {
                    unblockContainer('seller-location-fields');
                } else {
                    blockContainer('seller-location-fields');
                }
                if (!validateFieldById('latitude') || !validateFieldById('longitude')) allMandatoryValid = false;
            } else {
                locationFieldsContainer.style.display = 'none';
                blockContainer('seller-location-fields');
            }

            // ensure phone validated
            if (!validateFieldById('phone')) allMandatoryValid = false;

            if (allMandatoryValid) publishButton.removeAttribute('disabled'); else publishButton.setAttribute('disabled', true);
        }

        // geolocation button
        function handleLocation(position) {
            const lat = position.coords.latitude; const lng = position.coords.longitude;
            latInput.value = lat.toFixed(8); lngInput.value = lng.toFixed(8);
            setLocationBtn.disabled = false;
            setLocationBtn.innerHTML = '<i class="fas fa-check"></i> Location Set!';
            markTouched('latitude'); markTouched('longitude');
            updateFieldState();
            setTimeout(() => { setLocationBtn.innerHTML = '<i class="fas fa-crosshairs"></i> Get Current Location'; }, 3000);
        }
        if (setLocationBtn) {
            setLocationBtn.addEventListener('click', () => {
                if (navigator.geolocation) {
                    setLocationBtn.disabled = true;
                    setLocationBtn.textContent = 'Fetching location...';
                    navigator.geolocation.getCurrentPosition(handleLocation, (err) => {
                        alert('Error getting location. Please manually enter coordinates or try again.');
                        setLocationBtn.disabled = false;
                        setLocationBtn.textContent = 'Get Current Location';
                    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
                } else alert("Geolocation not supported.");
            });
        }

        // on submit: mark touched, normalize phone, final validation
        const form = document.getElementById('registration-form');
        form.addEventListener('submit', (e) => {
            fieldSequence.forEach(f => markTouched(f.id));
            markTouched('latitude'); markTouched('longitude'); markTouched('logo');

            let valid = true;
            fieldSequence.forEach(f => { if (!validateFieldById(f.id)) valid = false; });

            if (roleSelect.value === 'seller') {
                if (!validateFieldById('latitude') || !validateFieldById('longitude')) valid = false;
                if (!validateFieldById('logo')) valid = false;
            }

            // phone normalization: visiblePhoneInput currently digits only (no spaces)
            const visible = visiblePhoneInput.value.trim();
            if (!isPhoneLocalValid(visible)) {
                setErrorById('phone', "Phone invalid. Start with 1x then 7–8 digits.");
                valid = false;
            } else {
                hiddenPhoneInput.value = '+60' + visible;
            }

            if (!valid) {
                e.preventDefault();
                updateFieldState();
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            return true;
        });

        // initial lock: block all then unblock first
        (function initialLocking() {
            const containerIds = ['name_group', 'phone_group', 'email_group', 'address_group', 'password_group', 'confirm_password_group', 'role_group', 'seller-location-fields'];
            containerIds.forEach(id => {
                const c = document.getElementById(id);
                if (c) {
                    c.classList.remove('unblocked-container'); c.classList.add('blocked-container');
                    const elems = c.querySelectorAll('input, select, textarea, button'); elems.forEach(el => el.setAttribute('disabled', true));
                }
            });
            const first = document.getElementById(fieldSequence[0].id);
            const firstContainer = first ? first.closest('.mb-3') : null;
            if (firstContainer) {
                firstContainer.classList.remove('blocked-container'); firstContainer.classList.add('unblocked-container');
                const elems = firstContainer.querySelectorAll('input, select, textarea, button'); elems.forEach(el => el.removeAttribute('disabled'));
            }
        })();

        updateFieldState();

    });
</script>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/end.php'); ?>