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
$query = "SELECT user_id, name, email, role, is_active FROM users";
$result = $conn->query($query);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Users</h1>
    </div>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()):
                $is_active = $user['is_active'];
                $status_text = ($is_active == 1) ? 'Active' : (($is_active == 2) ? 'Pending' : 'Inactive');
                $status_color = ($is_active == 1) ? 'text-success' : (($is_active == 2) ? 'text-warning' : 'text-danger');
                $button_action = ($is_active == 1) ? 'deactivate' : 'activate';
                $button_text = ($is_active == 1) ? 'Deactivate' : 'Activate';
                $button_class = ($is_active == 1) ? 'btn-danger' : 'btn-success';

                $can_modify = ($user['user_id'] != $_SESSION['admin_id']);
                ?>
                <tr>
                    <td><?= $user['user_id']; ?></td>
                    <td><a href="user-details.php?id=<?= $user['user_id']; ?>"
                            class="text-decoration-none fw-bold"><?= htmlspecialchars($user['name']); ?></a></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= ucfirst($user['role']); ?></td>

                    <td class="<?= $status_color; ?> fw-bold"><?= $status_text; ?></td>

                    <td>
                        <a href="edit-user.php?id=<?= $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>

                        <?php if ($can_modify): ?>
                            <a href="update-user-status.php?id=<?= $user['user_id']; ?>&action=<?= $button_action; ?>"
                                class="btn <?= $button_class; ?> btn-sm"
                                onclick="return confirm('Are you sure you want to <?= $button_action; ?> this user?');">
                                <?= $button_text; ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Self</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="mt-4 ">
        <a href="dashboard.php" class="btn btn-success ">Back to Dashboard</a>
    </div>
    <br>
</div>