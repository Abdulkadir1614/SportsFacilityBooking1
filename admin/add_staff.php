<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (name, email, password, phone_number, role, status)
              VALUES ('$name','$email','$password','$phone','staff','active')";

    mysqli_query($conn, $query);

    header("Location: manage_staff.php");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Staff – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_staff.css">
    </head>
    <body>
        <?php $page_title = 'Add Staff'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">New Member</span>
                            <h1 class="page-title">Add <span>Staff</span></h1>
                            <p class="page-sub">Create a new staff account.</p>
                        </div>
                        <a href="manage_staff.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="form-card">
                        <form method="POST" class="admin-form">

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-person"></i> Full Name</label>
                                    <input type="text" name="name" placeholder="e.g. Ahmed Hassan" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="bi bi-envelope"></i> Email Address</label>
                                    <input type="email" name="email" placeholder="staff@example.com" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-telephone"></i> Phone Number</label>
                                    <input type="text" name="phone" placeholder="+252 6XX XXX XXX">
                                </div>
                                <div class="form-group">
                                    <label><i class="bi bi-lock"></i> Password</label>
                                    <input type="password" name="password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="manage_staff.php" class="btn-cancel">Cancel</a>
                                <button type="submit" name="submit" class="btn-primary">
                                    <i class="bi bi-person-plus"></i> Add Staff
                                </button>
                            </div>

                        </form>
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