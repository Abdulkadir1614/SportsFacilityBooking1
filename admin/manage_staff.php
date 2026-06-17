<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

// Only admin allowed
if ($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch staff
$sql = "SELECT * FROM users WHERE role='staff' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Staff – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_staff.css">
    </head>
    <body>
        <?php $page_title = 'Manage Staff'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Team</span>
                            <h1 class="page-title">Manage <span>Staff</span></h1>
                            <p class="page-sub">Add, edit, or remove staff accounts.</p>
                        </div>
                        <a href="add_staff.php" class="btn-primary">
                            <i class="bi bi-person-plus"></i> Add Staff
                        </a>
                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Staff Member</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td data-label="Staff Member">
                                                <div class="staff-cell">
                                                    <div class="staff-avatar">
                                                        <?= strtoupper(substr($row['name'], 0, 2)) ?>
                                                    </div>
                                                    <span class="staff-name"><?= htmlspecialchars($row['name']) ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Email">
                                                <span class="meta-cell">
                                                    <i class="bi bi-envelope"></i>
                                                    <?= htmlspecialchars($row['email']) ?>
                                                </span>
                                            </td>
                                            <td data-label="Phone">
                                                <span class="meta-cell">
                                                    <i class="bi bi-telephone"></i>
                                                    <?= htmlspecialchars($row['phone_number']) ?>
                                                </span>
                                            </td>
                                            <td data-label="Status">
                                                <?php if ($row['status'] == 'active'): ?>
                                                    <span class="status-badge status-active">
                                                        <i class="bi bi-check-circle-fill"></i> Active
                                                    </span>

                                                <?php elseif ($row['status'] == 'pending'): ?>
                                                    <span class="status-badge status-pending">
                                                        <i class="bi bi-hourglass-split"></i> Pending
                                                    </span>

                                                <?php else: ?>
                                                    <span class="status-badge status-rejected">
                                                        <i class="bi bi-x-circle-fill"></i> Rejected
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Actions">
                                                <div class="action-btns">
                                                    <?php if ($row['status'] == 'pending'): ?>

                                                    <a href="approve_staff.php?id=<?= $row['user_id'] ?>"
                                                        onclick="return confirm('Approve this staff member?')"
                                                        class="btn-action btn-approve-action">
                                                            <i class="bi bi-check-lg"></i> Approve
                                                    </a>

                                                    <a href="reject_staff.php?id=<?= $row['user_id'] ?>"
                                                        onclick="return confirm('Reject this staff member?')"
                                                        class="btn-action btn-reject-action">
                                                            <i class="bi bi-x-lg"></i> Reject
                                                    </a>

                                                    <?php endif; ?>
                                                    <a href="edit_staff.php?id=<?= $row['user_id'] ?>" class="btn-action btn-edit-action">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="delete_staff.php?id=<?= $row['user_id'] ?>"
                                                        onclick="return confirm('Delete this staff member?')"
                                                        class="btn-action btn-delete-action">
                                                        <i class="bi bi-trash3"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <i class="bi bi-people"></i>
                                                    <h3>No staff members yet</h3>
                                                    <p>Add your first staff account to get started.</p>
                                                    <a href="add_staff.php" class="btn-primary" style="margin-top:16px">
                                                        <i class="bi bi-person-plus"></i> Add Staff
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section>
            </main>
        </div>

        <script>
            const layout = document.getElementById('adminLayout');
            document.getElementById('toggleSidebar').addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');
        </script>
    </body>
</html>