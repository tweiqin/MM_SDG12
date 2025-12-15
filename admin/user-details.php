<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/adminheader.php');
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid User ID.</div></div>";
    exit;
}

$user_id = intval($_GET['id']);
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>User not found.</div></div>";
    exit;
}

$user = $result->fetch_assoc();
$is_active = $user['is_active'];
$status_text = ($is_active == 1) ? 'Active' : (($is_active == 2) ? 'Pending Approval' : 'Inactive');
$status_color = ($is_active == 1) ? 'text-success' : (($is_active == 2) ? 'text-warning' : 'text-danger');
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">User Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">Personal Info</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>User ID:</th>
                            <td><?= $user['user_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?= htmlspecialchars($user['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?= htmlspecialchars($user['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><?= ucfirst($user['role']); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td class="<?= $status_color; ?> fw-bold"><?= $status_text; ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?= nl2br(htmlspecialchars($user['address'])); ?></td>
                        </tr>
                    </table>
                </div>

                <?php if ($user['role'] === 'seller'): ?>
                    <div class="col-md-6">
                        <h5 class="card-title">Seller Info</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th>Logo:</th>
                                <td>
                                    <?php if (!empty($user['logo'])): ?>
                                        <img src="../assets/images/logos/<?= htmlspecialchars($user['logo']); ?>" alt="Logo"
                                            class="img-thumbnail" style="max-width: 150px;">
                                    <?php else: ?>
                                        <span class="text-muted">No logo uploaded</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td>
                                    <?php if (!empty($user['latitude']) && !empty($user['longitude'])): ?>
                                        Lat: <?= htmlspecialchars($user['latitude']); ?><br>
                                        Lng: <?= htmlspecialchars($user['longitude']); ?>
                                        <div class="mt-2">
                                            <a href="https://www.google.com/maps?q=<?= $user['latitude']; ?>,<?= $user['longitude']; ?>"
                                                target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-map-marker-alt"></i> View on Google Maps
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Location not set</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="manage-users.php" class="btn btn-secondary">Back to List</a>

                <?php if ($user['role'] !== 'admin'): ?>
                    <div>
                        <?php if ($is_active == 2): // Pending ?>
                            <a href="update-user-status.php?id=<?= $user['user_id']; ?>&action=activate" class="btn btn-success"
                                onclick="return confirm('Approve this seller and activate account?');">
                                <i class="fas fa-check"></i> Approve & Activate
                            </a>
                        <?php elseif ($is_active == 1): // Active ?>
                            <a href="update-user-status.php?id=<?= $user['user_id']; ?>&action=deactivate"
                                class="btn btn-danger" onclick="return confirm('Block this user?');">
                                <i class="fas fa-ban"></i> Block User
                            </a>
                        <?php else: // Blocked ?>
                            <a href="update-user-status.php?id=<?= $user['user_id']; ?>&action=activate" class="btn btn-success"
                                onclick="return confirm('Re-activate this user?');">
                                <i class="fas fa-unlock"></i> Activate User
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<br>
<?php include('../includes/footer.php'); ?>